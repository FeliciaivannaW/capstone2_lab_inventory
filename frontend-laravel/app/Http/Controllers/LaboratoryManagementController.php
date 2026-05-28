<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LaboratoryManagementController extends Controller
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
                'DELETE' => $request->delete($this->apiUrl() . $endpoint),
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

    public function index()
    {
        $laboratories = $this->getApiData('/laboratories');
        $options = $this->getApiData('/laboratories/options');
        $availableRooms = $options['available_rooms'] ?? [];
        $heads = $options['heads'] ?? [];

        return view('pages.laboratories', compact('laboratories', 'availableRooms', 'heads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer',
            'head_user_id' => 'nullable|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData('/laboratories', $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }
}