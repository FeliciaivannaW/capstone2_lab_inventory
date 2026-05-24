<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoomManagementController extends Controller
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

    public function index(Request $request)
    {
        $rooms = $this->getApiData('/rooms', $request->only(['search', 'room_type_id', 'floor_id']));
        $options = $this->getApiData('/rooms/options');
        $floors = $options['floors'] ?? [];
        $roomTypes = $options['room_types'] ?? [];

        return view('pages.rooms', compact('rooms', 'floors', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'floor_id' => 'required|integer',
            'room_type_id' => 'required|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData('/rooms', $validated);
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'floor_id' => 'required|integer',
            'room_type_id' => 'required|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData("/rooms/{$id}", $validated, 'PUT');
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->sendApiData("/rooms/{$id}", [], 'DELETE');
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}