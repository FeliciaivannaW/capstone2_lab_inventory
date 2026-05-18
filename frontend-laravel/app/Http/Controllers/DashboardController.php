<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
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

            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function index()
    {
        try {
            $health = Http::get($this->apiUrl() . '/health')->json();
        } catch (\Exception $e) {
            $health = [
                'status' => 'error',
                'message' => 'Tidak dapat terhubung ke backend',
                'error' => $e->getMessage()
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

    public function inventory()
    {
        return view('pages.inventory');
    }

    public function bhp()
    {
        return view('pages.bhp');
    }

    public function procurement()
    {
        $drafts = $this->getApiData('/procurement/drafts');

        return view('pages.procurement', compact('drafts'));
    }

    public function maintenance()
    {
        return view('pages.maintenance');
    }
}