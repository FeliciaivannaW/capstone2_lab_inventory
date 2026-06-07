<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private function apiUrl()
    {
        return env('API_BASE_URL', 'http://localhost:3000/api');
    }

    private function getApiData($endpoint, $queryParams = [])
    {
        try {
            $token = session('auth_token');

            $response = Http::withToken($token)->get(
                $this->apiUrl() . $endpoint,
                $queryParams
            );

            if ($response->successful()) {
                return $response->json('data') ?? $response->json();
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

    public function index()
    {
        $role = session('auth_user')['role'] ?? null;

        if ($role === 'staf_administrasi') {
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

        if ($role === 'staf_laboratorium') {
            $stats = $this->getApiData('/statistics/summary') ?: [];

            $inv      = $stats['inventory']      ?? [];
            $bhp      = $stats['bhp']            ?? [];
            $bhpLow   = $stats['bhpLowStock']    ?? [];

            $maintenanceLogs = $this->getApiData('/maintenance/logs') ?: [];
            $recentMaintenance = array_slice($maintenanceLogs, 0, 5);
            $labAccess = $this->getApiData('/me/lab-access') ?: [];

            return view('pages.staf-lab.dashboard', compact(
                'inv', 'bhp', 'bhpLow', 'recentMaintenance', 'labAccess'
            ));
        }

        if ($role === 'kepala_laboratorium') {
            $drafts = $this->getApiData('/procurement/drafts') ?: [];
            
            $stats = [
                'total' => count($drafts),
                'draft' => 0,
                'submitted' => 0,
                'finalized' => 0,
            ];
            
            $actionableDrafts = [];

            foreach ($drafts as $draft) {
                if ($draft['status'] === 'draft') {
                    $stats['draft']++;
                    $actionableDrafts[] = $draft;
                } elseif ($draft['status'] === 'submitted') {
                    $stats['submitted']++;
                } elseif ($draft['status'] === 'finalized') {
                    $stats['finalized']++;
                }
            }

            return view('pages.dashboard-kalab', compact('drafts', 'stats', 'actionableDrafts'));
        }

        try {
            $health = Http::get($this->apiUrl() . '/health')->json();
        } catch (\Exception $e) {
            $health = [
                'status' => 'error',
                'message' => 'Tidak dapat terhubung ke backend',
                'error' => $e->getMessage(),
            ];
        }

        $roles = $this->getApiData('/roles');
        $rooms = $this->getApiData('/rooms');
        $laboratories = $this->getApiData('/laboratories');

        return view('dashboard', compact('health', 'roles', 'rooms', 'laboratories'));
    }

    public function laboratories()
    {
        $laboratories = $this->getApiData('/laboratories');

        return view('pages.laboratories', compact('laboratories'));
    }

    public function rooms()
    {
        $rooms = $this->getApiData('/rooms');

        return view('pages.rooms', compact('rooms'));
    }

    public function inventory(Request $request)
    {
        $activeTab = $request->get('tab', 'inventaris');

        $filters = [];
        if ($request->filled('search'))       $filters['search']       = $request->search;
        if ($request->filled('lab_id'))       $filters['lab_id']       = $request->lab_id;
        if ($request->filled('status'))       $filters['status']       = $request->status;
        if ($request->filled('condition'))    $filters['condition']    = $request->condition;
        if ($request->filled('label_status')) $filters['label_status'] = $request->label_status;

        $bhpFilters = [];
        if ($request->filled('bhp_search')) $bhpFilters['search']       = $request->bhp_search;
        if ($request->filled('bhp_lab_id')) $bhpFilters['lab_id']       = $request->bhp_lab_id;
        if ($request->filled('bhp_status')) $bhpFilters['stock_status'] = $request->bhp_status;

        $assets    = $this->getApiData('/inventory/assets', $filters) ?: [];
        $labs      = $this->getApiData('/laboratories') ?: [];
        $allAssets = $this->getApiData('/inventory/assets') ?: [];

        $totalAssets    = count($allAssets);
        $byStatus       = [];
        $byCondition    = [];
        $labeledCount   = 0;
        $unlabeledCount = 0;
        $availableCount = 0;
        $maintenanceCount = 0;

        foreach ($allAssets as $asset) {
            $status    = $asset['status']          ?? 'unknown';
            $condition = $asset['asset_condition'] ?? 'unknown';
            $byStatus[$status]       = ($byStatus[$status]       ?? 0) + 1;
            $byCondition[$condition] = ($byCondition[$condition] ?? 0) + 1;
            if (!empty($asset['label_number'])) $labeledCount++; else $unlabeledCount++;
            if ($status === 'available') $availableCount++;
            if ($status === 'maintenance' || $condition === 'maintenance') $maintenanceCount++;
        }

        $bhpStocks   = $this->getApiData('/bhp/catalog-readonly', $bhpFilters) ?: [];
        $bhpByStatus = [];
        foreach ($bhpStocks as $s) {
            $st = $s['stock_status'] ?? 'unknown';
            $bhpByStatus[$st] = ($bhpByStatus[$st] ?? 0) + 1;
        }

        return view('pages.inventory', [
            'activeTab'        => $activeTab,
            'assets'           => $assets,
            'labs'             => $labs,
            'filters'          => $request->only(['search', 'lab_id', 'status', 'condition', 'label_status']),
            'totalAssets'      => $totalAssets,
            'byStatus'         => $byStatus,
            'byCondition'      => $byCondition,
            'labeledCount'     => $labeledCount,
            'unlabeledCount'   => $unlabeledCount,
            'availableCount'   => $availableCount,
            'maintenanceCount' => $maintenanceCount,
            'bhpStocks'        => $bhpStocks,
            'bhpByStatus'      => $bhpByStatus,
            'bhpFilters'       => $request->only(['bhp_search', 'bhp_lab_id', 'bhp_status']),
        ]);
    }

    public function updateInventoryCondition(Request $request, $id)
    {
        $validated = $request->validate([
            'asset_condition' => 'required|in:baik,rusak_ringan,rusak_berat,maintenance,dihapus,diganti',
            'note' => 'nullable|string|max:1000',
        ]);

        $result = $this->sendApiData("/inventory/assets/{$id}/condition", $validated, 'PATCH');

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function inventoryHistory(Request $request)
    {
        $history = $this->getApiData(
            '/inventory/condition-history',
            $request->only(['search', 'condition'])
        ) ?: [];

        return view('pages.inventory-history', [
            'history' => $history,
            'filters' => $request->only(['search', 'condition']),
        ]);
    }

    public function procurement()
    {
        $authUser = session('auth_user');
        $params = [];

        $drafts = $this->getApiData('/procurement/drafts', $params);

        return view('pages.procurement.index', compact('drafts'));
    }
}