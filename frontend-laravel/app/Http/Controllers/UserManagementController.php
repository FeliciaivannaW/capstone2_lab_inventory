<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserManagementController extends Controller
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
                'data' => $response->json('data') ?? null,
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => $e->getMessage(), 'data' => null];
        }
    }

    public function index(Request $request)
    {
        $users = $this->getApiData('/users', $request->only(['search', 'role', 'status']));
        $roles = $this->getApiData('/roles');
        $laboratories = $this->getApiData('/laboratories');

        return view('pages.users', compact('users', 'roles', 'laboratories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'nrp_nip' => 'nullable|string|max:50',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:4',
            'role_id' => 'required|integer',
            'lab_id' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $result = $this->sendApiData('/users', $validated);
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'nrp_nip' => 'nullable|string|max:50',
            'email' => 'required|email|max:150',
            'password' => 'nullable|string|min:4',
            'role_id' => 'required|integer',
            'lab_id' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $result = $this->sendApiData("/users/{$id}", $validated, 'PUT');
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->sendApiData("/users/{$id}", [], 'DELETE');
        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}