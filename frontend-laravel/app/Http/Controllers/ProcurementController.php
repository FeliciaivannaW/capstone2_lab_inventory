<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    private function apiUrl()
    {
        return env('API_BASE_URL', 'http://localhost:3000/api');
    }

    private function getApiData($endpoint)
    {
        try {
            $response = Http::get($this->apiUrl() . $endpoint);

            if ($response->successful()) {
                return $response->json('data') ?? $response->json();
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function postApiData($endpoint, $data = [])
    {
        try {
            $response = Http::post($this->apiUrl() . $endpoint, $data);

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

    /**
     * Show detail of a procurement draft with its items
     */
    public function show($id)
    {
        $draft = $this->getApiData("/procurement/drafts/{$id}");

        if (!$draft) {
            return redirect()->route('procurement')
                ->with('error', 'Draf pengadaan tidak ditemukan');
        }

        return view('pages.procurement-detail', compact('draft'));
    }

    /**
     * Review a procurement item (API endpoint)
     */
    public function reviewItem(Request $request, $draftId, $itemId)
    {
        $validated = $request->validate([
            'review_status' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string'
        ]);

        $result = $this->postApiData(
            "/procurement/drafts/{$draftId}/items/{$itemId}/review",
            $validated
        );

        return response()->json($result);
    }

    /**
     * Finalize a procurement draft (API endpoint)
     */
    public function finalize(Request $request, $id)
    {
        $result = $this->postApiData("/procurement/drafts/{$id}/finalize");

        return response()->json($result);
    }
}
