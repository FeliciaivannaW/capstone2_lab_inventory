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
            $response = Http::withToken(session('auth_token'))->get($this->apiUrl() . $endpoint, $queryParams);
            return $response->successful() ? ($response->json('data') ?? []) : [];
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
                default => $request->post($this->apiUrl() . $endpoint, $data),
            };

            return [
                'ok' => $response->successful(),
                'message' => $response->json('message') ?? ($response->successful() ? 'Berhasil' : 'Request gagal'),
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function index(Request $request)
    {
        $stocks = $this->getApiData('/bhp/stocks', $request->only(['search', 'low_stock', 'lab_id']));
        $catalogs = $this->getApiData('/bhp/catalogs');
        $laboratories = $this->getApiData('/laboratories');

        $selectedStockId = $request->query('stock_id') ?: ($stocks[0]['id'] ?? null);
        $movements = $selectedStockId ? $this->getApiData("/bhp/stocks/{$selectedStockId}/movements") : [];

        return view('pages.bhp', compact('stocks', 'catalogs', 'laboratories', 'movements', 'selectedStockId'));
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

        $result = $this->sendApiData('/bhp/stocks', $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lab_id' => 'nullable|integer',
            'item_catalog_id' => 'nullable|integer',
            'item_name' => 'nullable|string|max:150',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'initial_stock' => 'required|integer|min:0',
        ]);

        $result = $this->sendApiData('/bhp/stocks', $validated);
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
            'note' => 'nullable|string',
        ]);

        $result = $this->sendApiData("/bhp/stocks/{$id}/movements", $validated);
        return $result['ok']
            ? redirect()->route('bhp', ['stock_id' => $id])->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }
}