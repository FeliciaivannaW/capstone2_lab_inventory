<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StafAdminController extends Controller
{
    private function apiUrl()
    {
        return env('API_BASE_URL', 'http://localhost:3000/api');
    }

    private function getApiData($endpoint, $queryParams = [])
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get($this->apiUrl() . $endpoint, $queryParams);

            if ($response->successful()) {
                return $response->json('data') ?? $response->json();
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function postApiData($endpoint, $data = [], $method = 'POST')
    {
        try {
            $token = session('auth_token');

            $response = match ($method) {
                'PUT' => Http::withToken($token)->put($this->apiUrl() . $endpoint, $data),
                'PATCH' => Http::withToken($token)->patch($this->apiUrl() . $endpoint, $data),
                'DELETE' => Http::withToken($token)->delete($this->apiUrl() . $endpoint),
                default => Http::withToken($token)->post($this->apiUrl() . $endpoint, $data),
            };

            if ($response->successful()) {
                return [
                    'status' => $response->json('status'),
                    'data' => $response->json('data'),
                    'message' => $response->json('message')
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json('message') ?? 'Request failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function postApiDataWithFile($endpoint, $data = [], $files = [])
    {
        try {
            $token = session('auth_token');
            $request = Http::withToken($token);

            foreach ($files as $key => $file) {
                $request = $request->attach(
                    $key,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }

            $response = $request->post($this->apiUrl() . $endpoint, $data);

            if ($response->successful()) {
                return [
                    'status' => $response->json('status'),
                    'data' => $response->json('data'),
                    'message' => $response->json('message')
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json('message') ?? 'Request failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // ============================================================
    // FITUR 1: Lihat Draf Pengadaan yang Disetujui Kaprodi
    // ============================================================

    /**
     * List draf pengadaan yang sudah difinalisasi
     */
    public function procurementApproved(Request $request)
    {
        $filters = [
            'status' => 'finalized',
        ];

        if ($request->filled('budget_year')) {
            $filters['budget_year'] = $request->budget_year;
        }

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        $drafts = $this->getApiData('/procurement/drafts', $filters);

        // Enrich each draft dengan progress penerimaan
        foreach ($drafts as &$draft) {
            $detail = $this->getApiData("/procurement/drafts/{$draft['id']}");
            $approved = array_filter($detail['items'] ?? [], fn($i) => ($i['review_status'] ?? '') === 'approved');
            $totalOrdered = array_sum(array_map(fn($i) => (int)($i['quantity'] ?? 0), $approved));

            $receipts = $this->getApiData("/goods-receipts/by-draft/{$draft['id']}");
            $totalReceived = array_sum(array_map(fn($r) => (int)($r['quantity_received'] ?? 0), $receipts));

            $draft['total_ordered']  = $totalOrdered;
            $draft['total_received'] = $totalReceived;
            $draft['total_pending']  = max(0, $totalOrdered - $totalReceived);
            $draft['progress_pct']   = $totalOrdered > 0 ? (int) round($totalReceived / $totalOrdered * 100) : 0;
            $draft['receipt_status'] = match (true) {
                $totalOrdered === 0          => 'kosong',
                $totalReceived <= 0          => 'belum',
                $totalReceived >= $totalOrdered => 'selesai',
                default                      => 'sebagian',
            };
        }
        unset($draft);

        return view('pages.staf-admin.procurement-approved', [
            'drafts' => $drafts,
            'filters' => $request->only(['budget_year', 'search']),
        ]);
    }

    /**
     * Detail draf pengadaan yang sudah difinalisasi
     */
    public function procurementApprovedDetail($id)
    {
        $draft = $this->getApiData("/procurement/drafts/{$id}");

        if (empty($draft)) {
            return redirect()->route('staf-admin.procurement-approved')
                ->with('error', 'Draf pengadaan tidak ditemukan');
        }

        // Pastikan hanya draf finalized yang bisa dilihat di halaman ini
        if (($draft['status'] ?? '') !== 'finalized') {
            return redirect()->route('staf-admin.procurement-approved')
                ->with('error', 'Draf belum difinalisasi oleh Kaprodi');
        }

        // Pisahkan items berdasarkan review_status
        $approvedItems = [];
        $rejectedItems = [];

        foreach ($draft['items'] ?? [] as $item) {
            if ($item['review_status'] === 'approved') {
                $approvedItems[] = $item;
            } elseif ($item['review_status'] === 'rejected') {
                $rejectedItems[] = $item;
            }
        }

        // Enrich approved items dengan progress penerimaan + status label
        $receipts = $this->getApiData("/goods-receipts/by-draft/{$id}");
        $receiptMap = [];
        foreach ($receipts as $r) {
            $receiptMap[$r['procurement_item_id']][] = $r;
        }

        // Asset map by procurement_item_id (untuk cek label)
        $allAssets = $this->getApiData('/inventory/assets') ?: [];
        $assetsByProcItem = [];
        foreach ($allAssets as $a) {
            $pid = $a['procurement_item_id'] ?? null;
            if ($pid) {
                $assetsByProcItem[$pid][] = $a;
            }
        }

        $totalOrdered = 0;
        $totalReceived = 0;
        $totalAssetsCreated = 0;
        $totalAssetsLabeled = 0;

        foreach ($approvedItems as &$it) {
            $ordered = (int) ($it['quantity'] ?? 0);
            $recs = $receiptMap[$it['id']] ?? [];
            $received = array_sum(array_map(fn($r) => (int)($r['quantity_received'] ?? 0), $recs));
            $assets = $assetsByProcItem[$it['id']] ?? [];
            $labeled = count(array_filter($assets, fn($a) => !empty($a['label_number'])));

            $it['received_qty']   = $received;
            $it['remaining_qty']  = max(0, $ordered - $received);
            $it['progress_pct']   = $ordered > 0 ? (int) round($received / $ordered * 100) : 0;
            $it['receipt_status'] = $received <= 0 ? 'belum'
                                  : ($received >= $ordered ? 'selesai' : 'sebagian');
            $it['assets_count']   = count($assets);
            $it['labeled_count']  = $labeled;
            $it['receipts']       = $recs;

            $totalOrdered       += $ordered;
            $totalReceived      += $received;
            $totalAssetsCreated += count($assets);
            $totalAssetsLabeled += $labeled;
        }
        unset($it);

        $progress = [
            'ordered'         => $totalOrdered,
            'received'        => $totalReceived,
            'remaining'       => max(0, $totalOrdered - $totalReceived),
            'pct'             => $totalOrdered > 0 ? (int) round($totalReceived / $totalOrdered * 100) : 0,
            'receipt_status'  => $totalReceived <= 0 ? 'belum'
                                : ($totalReceived >= $totalOrdered && $totalOrdered > 0 ? 'selesai' : 'sebagian'),
            'assets_created'  => $totalAssetsCreated,
            'assets_labeled'  => $totalAssetsLabeled,
            'assets_unlabeled'=> max(0, $totalAssetsCreated - $totalAssetsLabeled),
        ];

        return view('pages.staf-admin.procurement-approved-detail', [
            'progress' => $progress,
            'draft' => $draft,
            'approvedItems' => $approvedItems,
            'rejectedItems' => $rejectedItems,
        ]);
    }

    // ============================================================
    // FITUR 2: Input Tanggal Penerimaan Barang (Bertahap)
    // ============================================================

    /**
     * Halaman utama Penerimaan Barang — agregasi semua item dari draf yang
     * sudah difinalisasi, dikelompokkan per draf. Mendukung filter status
     * penerimaan (belum / sebagian / selesai) dan pencarian draf.
     */
    public function goodsReceiptIndex(Request $request)
    {
        $filters = ['status' => 'finalized'];
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        $drafts = $this->getApiData('/procurement/drafts', $filters);
        $statusFilter = $request->input('receipt_status'); // belum | sebagian | selesai | null

        $groups = [];
        $sumOrdered = 0;
        $sumReceived = 0;
        $countBelum = 0;
        $countSebagian = 0;
        $countSelesai = 0;

        foreach ($drafts as $draft) {
            // Hanya tarik detail draft yang finalized untuk mendapatkan items
            $detail = $this->getApiData("/procurement/drafts/{$draft['id']}");
            if (empty($detail) || ($detail['status'] ?? '') !== 'finalized') {
                continue;
            }

            $receipts = $this->getApiData("/goods-receipts/by-draft/{$draft['id']}");
            $receiptMap = [];
            foreach ($receipts as $r) {
                $receiptMap[$r['procurement_item_id']][] = $r;
            }

            $approved = array_values(array_filter($detail['items'] ?? [], fn($i) => ($i['review_status'] ?? '') === 'approved'));

            // Hitung status per item & total draft
            $items = [];
            $draftOrdered = 0;
            $draftReceived = 0;
            foreach ($approved as $item) {
                $ordered = (int) ($item['quantity'] ?? 0);
                $rec = $receiptMap[$item['id']] ?? [];
                $received = array_sum(array_map(fn($r) => (int)($r['quantity_received'] ?? 0), $rec));
                $status = $received <= 0 ? 'belum' : ($received >= $ordered ? 'selesai' : 'sebagian');

                $items[] = [
                    'id'         => $item['id'],
                    'name'       => $item['item_name'],
                    'type'       => $item['item_type'],
                    'ordered'    => $ordered,
                    'received'   => $received,
                    'remaining'  => max(0, $ordered - $received),
                    'pct'        => $ordered > 0 ? (int) round($received / $ordered * 100) : 0,
                    'status'     => $status,
                    'receipts'   => $rec,
                ];

                $draftOrdered  += $ordered;
                $draftReceived += $received;
            }

            // Filter status per request
            if ($statusFilter) {
                $items = array_values(array_filter($items, fn($i) => $i['status'] === $statusFilter));
                if (empty($items)) continue;
            }

            // Status draft
            $draftStatus = $draftReceived <= 0 ? 'belum'
                : ($draftReceived >= $draftOrdered && $draftOrdered > 0 ? 'selesai' : 'sebagian');

            $groups[] = [
                'id'             => $draft['id'],
                'title'          => $draft['title'],
                'lab_name'       => $draft['lab_name'] ?? '—',
                'budget_year'    => $draft['budget_year'] ?? '—',
                'finalized_at'   => $draft['finalized_at'] ?? null,
                'total_ordered'  => $draftOrdered,
                'total_received' => $draftReceived,
                'pct'            => $draftOrdered > 0 ? (int) round($draftReceived / $draftOrdered * 100) : 0,
                'status'         => $draftStatus,
                'items'          => $items,
            ];

            $sumOrdered  += $draftOrdered;
            $sumReceived += $draftReceived;
            if ($draftStatus === 'belum') $countBelum++;
            elseif ($draftStatus === 'sebagian') $countSebagian++;
            else $countSelesai++;
        }

        $summary = [
            'drafts_total'   => count($groups),
            'drafts_belum'   => $countBelum,
            'drafts_sebagian'=> $countSebagian,
            'drafts_selesai' => $countSelesai,
            'items_ordered'  => $sumOrdered,
            'items_received' => $sumReceived,
            'items_pending'  => max(0, $sumOrdered - $sumReceived),
            'pct'            => $sumOrdered > 0 ? (int) round($sumReceived / $sumOrdered * 100) : 0,
        ];

        return view('pages.staf-admin.goods-receipt-index', [
            'groups'  => $groups,
            'summary' => $summary,
            'filters' => $request->only(['search', 'receipt_status']),
        ]);
    }

    /**
     * Halaman penerimaan barang — list item yang disetujui
     */
    public function goodsReceipt($draftId)
    {
        $draft = $this->getApiData("/procurement/drafts/{$draftId}");

        if (empty($draft)) {
            return redirect()->route('staf-admin.procurement-approved')
                ->with('error', 'Draf pengadaan tidak ditemukan');
        }

        if (($draft['status'] ?? '') !== 'finalized') {
            return redirect()->route('staf-admin.procurement-approved')
                ->with('error', 'Draf belum difinalisasi');
        }

        // Ambil data penerimaan untuk draft ini
        $receipts = $this->getApiData("/goods-receipts/by-draft/{$draftId}");

        // Map receipts ke procurement_item_id agar mudah dicek di view
        $receiptMap = [];
        foreach ($receipts as $receipt) {
            $itemId = $receipt['procurement_item_id'];
            if (!isset($receiptMap[$itemId])) {
                $receiptMap[$itemId] = [];
            }
            $receiptMap[$itemId][] = $receipt;
        }

        // Filter hanya item yang approved
        $approvedItems = array_filter($draft['items'] ?? [], function ($item) {
            return $item['review_status'] === 'approved';
        });

        return view('pages.staf-admin.goods-receipt', [
            'draft' => $draft,
            'approvedItems' => array_values($approvedItems),
            'receiptMap' => $receiptMap,
        ]);
    }

    /**
     * Simpan penerimaan barang (AJAX endpoint)
     */
    public function storeGoodsReceipt(Request $request)
    {
        $validated = $request->validate([
            'procurement_item_id' => 'required|integer',
            'received_date'       => 'required|date',
            'quantity_received'   => 'required|integer|min:1',
            'note'                => 'nullable|string',
            'purchase_price'      => 'nullable|numeric|min:0',
            'purchase_date'       => 'nullable|date',
        ]);

        $result = $this->postApiData('/goods-receipts', $validated);

        return response()->json($result);
    }

    // ============================================================
    // FITUR 3: Update Nomor Label & Foto QR/Barcode
    // ============================================================

    /**
     * Halaman list inventaris untuk update label
     */
    public function inventoryLabel(Request $request)
    {
        $tab           = $request->input('tab', 'unlabeled');
        $highlightBatch = $request->input('batch');
        $search        = $request->input('search');
        $page          = $request->input('page', 1);

        $labelStatus = $tab === 'labeled' ? 'labeled' : 'unlabeled';
        
        try {
            $response = Http::withToken(session('auth_token'))->get($this->apiUrl() . '/inventory/batches', [
                'label_status' => $labelStatus,
                'search'       => $search,
                'page'         => $page,
                'limit'        => 10
            ]);
            $resData = $response->json();
            $batches = $resData['data'] ?? [];
            $meta = $resData['meta'] ?? ['total' => count($batches), 'limit' => 10, 'page' => 1];
        } catch (\Exception $e) {
            $batches = [];
            $meta = ['total' => 0, 'limit' => 10, 'page' => 1];
        }

        // Jika ada highlight_batch, taruh batch itu di posisi pertama
        if ($highlightBatch) {
            usort($batches, fn($a, $b) =>
                ($b['receipt_id'] == $highlightBatch ? 1 : 0) - ($a['receipt_id'] == $highlightBatch ? 1 : 0)
            );
        }

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $batches,
            $meta['total'],
            $meta['limit'],
            $meta['page'],
            ['path' => route('staf-admin.inventory-label'), 'query' => $request->query()]
        );

        return view('pages.staf-admin.inventory-label', [
            'batches'        => $batches,
            'paginator'      => $paginator,
            'tab'            => $tab,
            'search'         => $search,
            'highlightBatch' => $highlightBatch,
        ]);
    }

    /**
     * Halaman edit label per inventory asset
     */
    public function inventoryLabelEdit($id)
    {
        $asset = $this->getApiData("/inventory/assets/{$id}");

        if (empty($asset)) {
            return redirect()->route('staf-admin.inventory-label')
                ->with('error', 'Aset inventaris tidak ditemukan');
        }

        return view('pages.staf-admin.inventory-label-edit', [
            'asset' => $asset,
        ]);
    }

    /**
     * Update label & foto QR/Barcode
     */
    public function inventoryLabelUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'label_number'  => 'required|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'room_id'       => 'nullable|integer',
            'qr_photo'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data  = [
            'label_number'  => $validated['label_number'],
            'serial_number' => $validated['serial_number'] ?? null,
            'room_id'       => $validated['room_id'] ?? null,
        ];
        $files = [];
        if ($request->hasFile('qr_photo')) {
            $files['qr_photo'] = $request->file('qr_photo');
        }

        $result = !empty($files)
            ? $this->postApiDataWithFile("/inventory/assets/{$id}/label", $data, $files)
            : $this->postApiData("/inventory/assets/{$id}/label", $data, 'PUT');

        $success = ($result['status'] ?? '') === 'success';

        // AJAX request → return JSON with QR URL
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'           => $success,
                'message'      => $result['message'] ?? ($success ? 'Berhasil' : 'Gagal'),
                'qr_code'      => $result['data']['qr_code']   ?? null,
                'photo_url'    => $result['data']['photo_url'] ?? null,
                'label_number' => $result['data']['label_number'] ?? $validated['label_number'],
            ]);
        }

        // Normal form submit → redirect
        if ($success) {
            return redirect()->route('staf-admin.inventory-label')
                ->with('success', 'Label dan QR berhasil disimpan');
        }

        return back()->with('error', $result['message'] ?? 'Gagal memperbarui label');
    }

    // ============================================================
    // FITUR 4: Dashboard Ringkasan (Statistik)
    // ============================================================

    /**
     * Dashboard statistik untuk staf administrasi.
     * Fokus ke 3 fitur tugas utama:
     *   1. Lihat draf disetujui
     *   2. Update label & foto QR
     *   3. Input tanggal penerimaan barang
     */
    public function dashboard()
    {
        // Single API call — semua data dari statisticsController
        $stats = $this->getApiData('/statistics/summary') ?: [];

        $inv        = $stats['inventory']      ?? [];
        $label      = $stats['label']          ?? [];
        $bhp        = $stats['bhp']            ?? [];
        $bhpLow     = $stats['bhpLowStock']    ?? [];
        $proc       = $stats['procurement']    ?? [];
        $recv       = $stats['reception']      ?? [];
        $trend      = $stats['monthlyTrend']   ?? [];
        $activity   = $stats['recentActivity'] ?? [];

        return view('pages.staf-admin.dashboard', compact(
            'inv', 'label', 'bhp', 'bhpLow', 'proc', 'recv', 'trend', 'activity'
        ));
    }

    // ============================================================
    // FITUR 5: Pelacakan Siklus Barang
    // ============================================================

    /**
     * List semua aset inventaris
     */
    public function assetList(Request $request)
    {
        $filters = [];

        if ($request->filled('search'))    { $filters['search']    = $request->search; }
        if ($request->filled('condition')) { $filters['condition'] = $request->condition; }
        if ($request->filled('lab_id'))    { $filters['lab_id']    = $request->lab_id; }

        $assets = $this->getApiData('/inventory/assets', $filters) ?: [];
        $labs   = $this->getApiData('/laboratories') ?: [];

        return view('pages.staf-admin.asset-list', [
            'assets'  => $assets,
            'labs'    => $labs,
            'filters' => $request->only(['search', 'condition', 'lab_id']),
        ]);
    }

    /**
     * Timeline siklus hidup per aset
     */
    public function assetTimeline($id)
    {
        $asset = $this->getApiData("/inventory/assets/{$id}");

        if (empty($asset)) {
            return redirect()->route('staf-admin.asset-list')
                ->with('error', 'Aset inventaris tidak ditemukan');
        }

        $timeline = $this->getApiData("/inventory/assets/{$id}/timeline");

        return view('pages.staf-admin.asset-timeline', [
            'asset'    => $asset,
            'timeline' => $timeline,
        ]);
    }

    // ============================================================
    // FITUR 6: Semua Inventaris (Read-only, dedicated page)
    // ============================================================

    /**
     * Read-only full inventory list for Staf Administrasi.
     * Different from asset-list: no edit/delete actions, focuses on
     * lab/condition/status filtering for audit/monitoring purposes.
     */
    public function inventaris(Request $request)
    {
        $tab = $request->input('tab', 'aset');
        $filters = [];

        if ($request->filled('search'))    { $filters['search']    = $request->search; }
        if ($request->filled('lab_id'))    { $filters['lab_id']    = $request->lab_id; }
        if ($request->filled('condition')) { $filters['condition'] = $request->condition; }
        if ($request->filled('status'))    { $filters['status']    = $request->status; }

        $assets = [];
        $bhpStocks = [];

        if ($tab === 'aset') {
            $assets = $this->getApiData('/inventory/assets', $filters) ?: [];
        } else {
            $bhpFilters = [
                'search' => $filters['search'] ?? null,
                'lab_id' => $filters['lab_id'] ?? null,
            ];
            $bhpStocks = $this->getApiData('/bhp/catalog-readonly', $bhpFilters) ?: [];
        }

        // Fetch labs for filter dropdown
        $labs = $this->getApiData('/laboratories') ?: [];

        // Compute summary counts
        $allAssets   = $this->getApiData('/inventory/assets') ?: [];
        $totalAssets = count($allAssets);
        $byStatus    = [];
        $byCondition = [];
        foreach ($allAssets as $a) {
            $s = $a['status'] ?? 'unknown';
            $c = $a['asset_condition'] ?? 'unknown';
            $byStatus[$s]    = ($byStatus[$s]    ?? 0) + 1;
            $byCondition[$c] = ($byCondition[$c] ?? 0) + 1;
        }

        return view('pages.staf-admin.inventaris', [
            'tab'         => $tab,
            'assets'      => $assets,
            'bhpStocks'   => $bhpStocks,
            'labs'        => $labs,
            'filters'     => $request->only(['search', 'lab_id', 'condition', 'status']),
            'totalAssets' => $totalAssets,
            'byStatus'    => $byStatus,
            'byCondition' => $byCondition,
        ]);
    }

    /**
     * Print label page for single asset
     */
    public function printLabel(Request $request)
    {
        $id = $request->query('id');
        if (!$id) abort(404);

        $asset = $this->getApiData("/inventory/assets/{$id}");
        if (empty($asset)) abort(404);

        return view('pages.staf-admin.print-label', compact('asset'));
    }

    /**
     * Proxy: GET /api/inventory/assets → Node.js backend
     */
    public function inventoryAssetsApi(Request $request)
    {
        $filters = $request->only(['search', 'status', 'condition', 'label_status', 'lab_id', 'receipt_id']);
        $assets = $this->getApiData('/inventory/assets', $filters) ?: [];
        return response()->json(['data' => $assets]);
    }

    /**
     * Proxies to GET /inventory/next-label on the Node.js API
     */
    public function nextLabelApi(Request $request)
    {
        $result = $this->getApiData('/inventory/next-label', [
            'lab_code' => $request->query('lab_code', '')
        ]);
        return response()->json($result);
    }

    /**
     * Proxies to POST /inventory/batches/{id}/label-all on Node.js API
     */
    public function labelAllAjax(Request $request, $id)
    {
        $result = $this->postApiData("/inventory/batches/{$id}/label-all", [], 'POST');
        return response()->json($result);
    }

    /**
     * Proxies to GET /inventory/label-check on the Node.js API.
     */
    public function labelCheck(Request $request)
    {
        $result = $this->getApiData('/inventory/label-check', [
            'label'      => $request->query('label', ''),
            'exclude_id' => $request->query('exclude_id', ''),
        ]);

        // Empty result means API call failed — return neutral (not "taken")
        // so the user can still submit; the backend will do the final duplicate check.
        if (empty($result)) {
            return response()->json(['available' => null, 'message' => 'Tidak dapat memeriksa (akan dicek saat simpan)']);
        }

        return response()->json($result);
    }

    /**
     * AJAX endpoint: update label without page reload.
     * Sends JSON body (asset_code, label_number, barcode, photo_url)
     * to PATCH /inventory/assets/:id/label on Node.js API.
     */
    public function inventoryLabelUpdateAjax(Request $request, $id)
    {
        $validated = $request->validate([
            'label_number'  => 'required|string|max:100',
            'asset_code'    => 'nullable|string|max:100',
            'barcode'       => 'nullable|string|max:255',
            'photo_url'     => 'nullable|string',
            'serial_number' => 'nullable|string|max:100',
            'room_id'       => 'nullable|integer',
        ]);

        $result = $this->postApiData("/inventory/assets/{$id}/label", $validated, 'PATCH');

        return response()->json($result);
    }

    /**
     * Proxy: GET /api/rooms → Node.js backend
     * Used by the label drawer to populate the room dropdown.
     */
    public function roomsApi(Request $request)
    {
        $rooms = $this->getApiData('/rooms') ?: [];
        return response()->json(['data' => $rooms]);
    }
}
