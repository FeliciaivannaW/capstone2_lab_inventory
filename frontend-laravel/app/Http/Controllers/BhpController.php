<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BhpController extends Controller
{
    private function apiUrl()
    {
        return env('API_BASE_URL', 'http://localhost:3000/api');
    }

    private function getApiData($endpoint, $queryParams = [])
    {
        try {
            $response = Http::withToken(session('auth_token'))->get(
                $this->apiUrl() . $endpoint,
                $queryParams
            );

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function sendApiData($endpoint, $data = [], $method = 'POST')
    {
        try {
            $request = Http::withToken(session('auth_token'));

            $response = match ($method) {
                'PUT' => $request->put($this->apiUrl() . $endpoint, $data),
                'PATCH' => $request->patch($this->apiUrl() . $endpoint, $data),
                'DELETE' => $request->delete($this->apiUrl() . $endpoint),
                default => $request->post($this->apiUrl() . $endpoint, $data),
            };

            return [
                'ok' => $response->successful(),
                'message' => $response->json('message') ?? ($response->successful() ? 'Berhasil' : 'Request gagal'),
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function index(Request $request)
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        if ($request->filled('low_stock')) {
            $filters['low_stock'] = $request->low_stock;
        }

        if ($request->filled('lab_id')) {
            $filters['lab_id'] = $request->lab_id;
        }

        $stocks = $this->getApiData('/bhp/stocks', $filters) ?: [];
        $catalogs = $this->getApiData('/bhp/catalogs') ?: [];
        $laboratories = $this->getApiData('/laboratories') ?: [];

        $selectedStockId = $request->query('stock_id') ?: ($stocks[0]['id'] ?? null);
        $movements = [];

        if ($selectedStockId) {
            $movements = $this->getApiData("/bhp/stocks/{$selectedStockId}/movements") ?: [];
        }

        return view('pages.bhp', [
            'stocks' => $stocks,
            'catalogs' => $catalogs,
            'laboratories' => $laboratories,
            'movements' => $movements,
            'selectedStockId' => $selectedStockId,
            'filters' => $request->only(['search', 'low_stock', 'lab_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'required|integer',
            'item_catalog_id' => 'nullable|integer',
            'item_name' => 'nullable|string|max:150',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'initial_stock' => 'required|integer|min:0',
        ]);

        if (empty($validated['item_catalog_id']) && empty($validated['item_name'])) {
            return back()
                ->withInput()
                ->with('error', 'Pilih katalog BHP atau isi nama item baru.');
        }

        $result = $this->sendApiData('/bhp/stocks', $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'lab_id' => 'nullable|integer',
            'item_catalog_id' => 'nullable|integer',
            'item_name' => 'nullable|string|max:150',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        $result = $this->sendApiData("/bhp/stocks/{$id}", $validated, 'PUT');

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function movement(Request $request, $id)
    {
        $validated = $request->validate([
            'movement_type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:1000',
        ]);

        $result = $this->sendApiData("/bhp/stocks/{$id}/movement", $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }
}