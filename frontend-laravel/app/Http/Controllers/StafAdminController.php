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

        return view('pages.staf-admin.procurement-approved-detail', [
            'draft' => $draft,
            'approvedItems' => $approvedItems,
            'rejectedItems' => $rejectedItems,
        ]);
    }

    // ============================================================
    // FITUR 2: Input Tanggal Penerimaan Barang (Bertahap)
    // ============================================================

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
            'received_date' => 'required|date',
            'quantity_received' => 'required|integer|min:1',
            'note' => 'nullable|string',
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
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        if ($request->filled('label_status')) {
            $filters['label_status'] = $request->label_status;
        }

        $assets = $this->getApiData('/inventory/assets', $filters);

        return view('pages.staf-admin.inventory-label', [
            'assets' => $assets,
            'filters' => $request->only(['search', 'status', 'label_status']),
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
            'label_number' => 'required|string|max:100',
            'qr_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'label_number' => $validated['label_number'],
        ];

        $files = [];
        if ($request->hasFile('qr_photo')) {
            $files['qr_photo'] = $request->file('qr_photo');
        }

        if (!empty($files)) {
            $result = $this->postApiDataWithFile("/inventory/assets/{$id}/label", $data, $files);
        } else {
            $result = $this->postApiData("/inventory/assets/{$id}/label", $data, 'PUT');
        }

        if (($result['status'] ?? '') === 'success') {
            return redirect()->route('staf-admin.inventory-label')
                ->with('success', 'Label dan foto berhasil diperbarui');
        }

        return back()->with('error', $result['message'] ?? 'Gagal memperbarui label');
    }

    // ============================================================
    // FITUR 4: Dashboard Ringkasan (Statistik)
    // ============================================================

    /**
     * Dashboard statistik untuk staf administrasi
     */
    public function dashboard()
    {
        $statistics = $this->getApiData('/statistics/summary');

        return view('pages.staf-admin.dashboard', [
            'stats' => $statistics,
        ]);
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

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        if ($request->filled('condition')) {
            $filters['condition'] = $request->condition;
        }

        $assets = $this->getApiData('/inventory/assets', $filters);

        return view('pages.staf-admin.asset-list', [
            'assets' => $assets,
            'filters' => $request->only(['search', 'condition']),
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
            'asset' => $asset,
            'timeline' => $timeline,
        ]);
    }
}
