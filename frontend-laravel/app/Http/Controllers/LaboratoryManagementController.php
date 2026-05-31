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
        $rooms = $this->getApiData('/rooms');

        $availableRooms = $options['available_rooms'] ?? [];
        $heads = $options['heads'] ?? [];
        $staffLabUsers = $options['staff_lab_users'] ?? [];
        $labGroups = $options['lab_groups'] ?? [];

        return view('pages.laboratories', compact(
            'laboratories',
            'availableRooms',
            'heads',
            'staffLabUsers',
            'labGroups',
            'rooms'
        ));
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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer',
            'head_user_id' => 'nullable|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData("/laboratories/{$id}", $validated, 'PUT');

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->sendApiData("/laboratories/{$id}", [], 'DELETE');
        
        if (request()->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 400);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|integer',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData('/lab-groups', $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function updateGroup(Request $request, $id)
    {
        $validated = $request->validate([
            'laboratory_id' => 'required|integer',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $result = $this->sendApiData("/lab-groups/{$id}", $validated, 'PUT');

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function destroyGroup($id)
    {
        $result = $this->sendApiData("/lab-groups/{$id}", [], 'DELETE');
        
        if (request()->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 400);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function addGroupUser(Request $request, $groupId)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'role_in_group' => 'required|in:kepala_lab,staf_lab',
        ]);

        $result = $this->sendApiData("/lab-groups/{$groupId}/users", $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function addGroupRoom(Request $request, $groupId)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer',
        ]);

        $result = $this->sendApiData("/lab-groups/{$groupId}/rooms", $validated);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->withInput()->with('error', $result['message']);
    }

    public function getGroupDetails($id)
    {
        try {
            $response = Http::withToken(session('auth_token'))->get($this->apiUrl() . "/lab-groups/{$id}/details");
            if ($response->successful()) {
                return response()->json($response->json());
            }
            return response()->json(['status' => 'error', 'message' => 'Gagal mengambil detail grup'], 500);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyGroupUser($groupId, $userId)
    {
        $result = $this->sendApiData("/lab-groups/{$groupId}/users/{$userId}", [], 'DELETE');

        if (request()->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 400);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function destroyGroupRoom($groupId, $roomId)
    {
        $result = $this->sendApiData("/lab-groups/{$groupId}/rooms/{$roomId}", [], 'DELETE');

        if (request()->expectsJson()) {
            return response()->json($result, $result['ok'] ? 200 : 400);
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}