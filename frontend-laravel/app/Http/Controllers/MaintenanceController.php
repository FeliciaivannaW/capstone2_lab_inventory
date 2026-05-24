<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MaintenanceController extends Controller
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

    private function sendApiData($endpoint, $data = [])
    {
        try {
            $response = Http::withToken(session('auth_token'))->post($this->apiUrl() . $endpoint, $data);
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
        $logs = $this->getApiData('/maintenance/logs', $request->only(['search', 'status', 'asset_id']));
        $assets = $this->getApiData('/inventory/assets');
        $stocks = $this->getApiData('/bhp/stocks');

        return view('pages.maintenance', compact('logs', 'assets', 'stocks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_asset_id' => 'required|integer',
            'maintenance_date' => 'required|date',
            'issue_description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'condition_after' => 'required|in:baik,rusak_ringan,rusak_berat,maintenance,dihapus,diganti',
            'status' => 'required|in:planned,in_progress,done,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'bhp_stock_id' => 'array',
            'bhp_stock_id.*' => 'nullable|integer',
            'bhp_quantity' => 'array',
            'bhp_quantity.*' => 'nullable|integer|min:1',
        ]);

        $bhpUsages = [];
        $stockIds = $request->input('bhp_stock_id', []);
        $quantities = $request->input('bhp_quantity', []);

        foreach ($stockIds as $index => $stockId) {
            $quantity = $quantities[$index] ?? null;
            if ($stockId && $quantity) {
                $bhpUsages[] = [
                    'stock_id' => (int) $stockId,
                    'quantity' => (int) $quantity,
                ];
            }
        }

        $payload = collect($validated)->except(['bhp_stock_id', 'bhp_quantity'])->toArray();
        $payload['bhp_usages'] = $bhpUsages;
        $payload['cost'] = $payload['cost'] ?? 0;

        $result = $this->sendApiData('/maintenance/logs', $payload);
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }
}