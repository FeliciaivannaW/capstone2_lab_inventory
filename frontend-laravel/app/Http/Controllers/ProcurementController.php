<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProcurementController extends Controller
{
    private function apiUrl()
    {
        return env('API_BASE_URL', 'http://localhost:3000/api');
    }

    private function getApiData($endpoint)
    {
        try {
            $token = session('auth_token');
            $response = Http::withToken($token)->get($this->apiUrl() . $endpoint);

            if ($response->successful()) {
                return $response->json('data') ?? $response->json();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function postApiData($endpoint, $data = [], $method = 'POST')
    {
        try {
            $token = session('auth_token');
            $response = match(strtoupper($method)) {
                'PUT'    => Http::withToken($token)->put($this->apiUrl() . $endpoint, $data),
                'PATCH'  => Http::withToken($token)->patch($this->apiUrl() . $endpoint, $data),
                'DELETE' => Http::withToken($token)->delete($this->apiUrl() . $endpoint),
                default  => Http::withToken($token)->post($this->apiUrl() . $endpoint, $data),
            };

            if ($response->successful()) {
                return [
                    'status'  => $response->json('status') ?? ($response->json('success') ? 'success' : 'error'),
                    'success' => $response->json('success'),
                    'data'    => $response->json('data'),
                    'message' => $response->json('message')
                ];
            }

            return [
                'status'  => 'error',
                'message' => $response->json('message') ?? 'Request failed'
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Show create draft form
     */
    public function create()
    {
        $authUser = session('auth_user');
        $laboratories = [];

        if ($authUser['role'] === 'staf_administrasi') {
            $laboratories = $this->getApiData('/laboratories');
            $laboratories = collect($laboratories)->pluck('name', 'id')->toArray();
        } elseif ($authUser['role'] === 'kepala_laboratorium' && empty($authUser['lab_id'])) {
            return redirect()->route('procurement')
                ->with('error', 'Akun Anda belum terhubung ke laboratorium. Hubungi Administrator.');
        }

        return view('pages.procurement.form', [
            'isEdit' => false,
            'formAction' => route('procurement.store'),
            'formMethod' => 'POST',
            'draft' => [],
            'laboratories' => $laboratories,
            'authUser' => $authUser
        ]);
    }

    /**
     * Store new procurement draft
     */
    public function store(Request $request)
    {
        $authUser = session('auth_user');
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'lab_id'      => 'required|integer|min:1',
            'budget_year' => 'required|integer|min:2000|max:2100',
            'notes'       => 'nullable|string|max:1000',
        ], [
            'lab_id.required' => 'Laboratorium wajib dipilih.',
            'lab_id.min'      => 'Laboratorium tidak valid.',
        ]);

        $result = $this->postApiData('/procurement/drafts', $validated);

        if ($result['status'] === 'success') {
            $draftId = $result['data']['id'] ?? null;
            if ($draftId) {
                return redirect()->route('procurement.edit', $draftId)
                    ->with('success', 'Draf pengadaan berhasil dibuat. Silakan tambahkan item pengadaan.');
            }
            return redirect()->route('procurement')
                ->with('success', 'Draf pengadaan berhasil dibuat');
        }

        return back()->with('error', $result['message'] ?? 'Gagal membuat draf');
    }

    /**
     * Show detail draft
     */
    public function show($id)
    {
        $authUser = session('auth_user');
        $draft = $this->getApiData("/procurement/drafts/{$id}");

        if (!$draft) {
            return redirect()->route('procurement')
                ->with('error', 'Draf pengadaan tidak ditemukan');
        }

        // Check authorization
        $canEdit = ($authUser['role'] === 'staf_administrasi' || $draft['created_by_id'] === $authUser['id'])
            && $draft['status'] === 'draft'
            && !$draft['is_locked'];

        return view('pages.procurement.show', compact('draft', 'canEdit', 'authUser'));
    }

    /**
     * Show edit draft form
     */
    public function edit($id)
    {
        $authUser = session('auth_user');
        $draft = $this->getApiData("/procurement/drafts/{$id}");

        if (!$draft) {
            return redirect()->route('procurement')
                ->with('error', 'Draf pengadaan tidak ditemukan');
        }

        // Authorization check
        if ($authUser['role'] !== 'staf_administrasi' && $draft['created_by_id'] !== $authUser['id']) {
            return redirect()->route('procurement')
                ->with('error', 'Anda tidak memiliki wewenang untuk mengubah draf ini');
        }

        if ($draft['status'] !== 'draft' || $draft['is_locked']) {
            return redirect()->route('procurement')
                ->with('error', 'Draf tidak bisa diubah dalam status ini');
        }

        $laboratories = [];
        if ($authUser['role'] === 'staf_administrasi') {
            $laboratories = $this->getApiData('/laboratories');
            $laboratories = collect($laboratories)->pluck('name', 'id')->toArray();
        }

        return view('pages.procurement.form', [
            'isEdit' => true,
            'formAction' => route('procurement.update', $id),
            'formMethod' => 'PUT',
            'draft' => $draft,
            'laboratories' => $laboratories,
            'authUser' => $authUser
        ]);
    }

    /**
     * Update draft
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'lab_id' => 'required|integer',
            'budget_year' => 'required|integer',
            'notes' => 'nullable|string'
        ]);

        $result = $this->postApiData("/procurement/drafts/{$id}", $validated, 'PUT');

        if ($result['status'] === 'success') {
            return redirect()->route('procurement.show', $id)
                ->with('success', 'Draf pengadaan berhasil diperbarui');
        }

        return back()->with('error', $result['message'] ?? 'Gagal memperbarui draf');
    }

    /**
     * Delete draft
     */
    public function destroy($id)
    {
        $result = $this->postApiData("/procurement/drafts/{$id}", [], 'DELETE');

        if ($result['status'] === 'success') {
            return redirect()->route('procurement')
                ->with('success', 'Draf pengadaan berhasil dihapus');
        }

        return back()->with('error', $result['message'] ?? 'Gagal menghapus draf');
    }

    /**
     * Add item to draft (API endpoint)
     */
    public function addItem(Request $request, $draftId)
    {
        $validated = $request->validate([
            'item_name' => 'required|string',
            'item_type' => 'required|in:inventory,bhp',
            'quantity' => 'required|integer|min:1',
            'estimated_price' => 'required|numeric|min:0',
            'purchase_link' => 'nullable|url'
        ]);

        $result = $this->postApiData("/procurement/drafts/{$draftId}/items", $validated);

        return response()->json($result);
    }

    /**
     * Delete item from draft (API endpoint)
     */
    public function deleteItem($draftId, $itemId)
    {
        $result = $this->postApiData("/procurement/drafts/{$draftId}/items/{$itemId}", [], 'DELETE');

        return response()->json($result);
    }

    /**
     * Submit draft to Kaprodi (AJAX endpoint)
     * Proxies to PATCH /procurement/drafts/:id/submit on Node.js API
     */
    public function submit(Request $request, $id)
    {
        $result = $this->postApiData("/procurement/drafts/{$id}/submit", [], 'PATCH');
        return response()->json($result);
    }

    /**
     * Update a single item in draft (AJAX endpoint)
     * Proxies to PATCH /procurement/drafts/:draftId/items/:itemId
     */
    public function updateItem(Request $request, $draftId, $itemId)
    {
        $validated = $request->validate([
            'item_name'       => 'nullable|string',
            'item_type'       => 'nullable|in:inventory,bhp',
            'quantity'        => 'nullable|integer|min:1',
            'estimated_price' => 'nullable|numeric|min:0',
            'purchase_link'   => 'nullable|url',
        ]);

        $result = $this->postApiData(
            "/procurement/drafts/{$draftId}/items/{$itemId}",
            array_filter($validated, fn($v) => $v !== null),
            'PATCH'
        );

        return response()->json($result);
    }

    /**
     * Review item (API endpoint)
     */
    public function reviewItem(Request $request, $draftId, $itemId)
    {
        $validated = $request->validate([
            'review_status' => 'required|in:approved,rejected',
            'review_note'   => 'nullable|string'
        ]);

        $result = $this->postApiData(
            "/procurement/drafts/{$draftId}/items/{$itemId}/review",
            $validated
        );

        return response()->json($result);
    }

    /**
     * Finalize draft (API endpoint)
     */
    public function finalize(Request $request, $id)
    {
        $result = $this->postApiData("/procurement/drafts/{$id}/finalize");
        return response()->json($result);
    }
}
