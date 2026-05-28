@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $authUser = session('auth_user');
    $role = $authUser['role'] ?? null;

    $roleGreetings = [
        'admin'               => 'Kelola pengguna, laboratorium, dan ruangan sistem.',
        'kepala_laboratorium' => 'Buat dan pantau draf pengadaan laboratorium Anda.',
        'ketua_program_studi' => 'Tinjau dan setujui item pengadaan yang diajukan.',
        'staf_administrasi'   => 'Kelola penerimaan barang, label aset, dan dokumen.',
        'staf_laboratorium'   => 'Pantau stok BHP, kondisi aset, dan log maintenance.',
    ];
    $greeting = $roleGreetings[$role] ?? 'Selamat datang di Labventory System.';

    $backendOk = isset($health['status']) && ($health['status'] === 'ok' || $health['status'] === 'success');
@endphp

{{-- Page heading --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-1">{{ $greeting }}</p>
</div>

{{-- ── Stat cards (bento grid) ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Backend status --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background:linear-gradient(135deg,rgba(99,102,241,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-indigo-500" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </div>
            <span class="badge {{ $backendOk ? 'badge-approved' : 'badge-rejected' }}">
                {{ $backendOk ? 'Online' : 'Offline' }}
            </span>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Backend</p>
        <p class="text-2xl font-bold text-slate-900">{{ strtoupper($health['status'] ?? 'error') }}</p>
    </div>

    {{-- Total roles --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background:linear-gradient(135deg,rgba(16,185,129,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#10B981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Role</p>
        <p class="text-3xl font-bold text-slate-900">{{ count($roles) }}</p>
        <p class="text-xs text-slate-400 mt-1">Tingkatan akses</p>
    </div>

    {{-- Total ruangan --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background:linear-gradient(135deg,rgba(245,158,11,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#F59E0B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Ruangan</p>
        <p class="text-3xl font-bold text-slate-900">{{ count($rooms) }}</p>
        <p class="text-xs text-slate-400 mt-1">Terdaftar di sistem</p>
    </div>

    {{-- Total laboratorium --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background:linear-gradient(135deg,rgba(99,102,241,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#7C3AED;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Laboratorium</p>
        <p class="text-3xl font-bold text-slate-900">{{ count($laboratories) }}</p>
        <p class="text-xs text-slate-400 mt-1">Lab aktif</p>
    </div>
</div>

{{-- ── Lab table ── --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($laboratories) }})">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Daftar Laboratorium</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ count($laboratories) }} laboratorium terdaftar</p>
        </div>
        @if($role === 'admin')
            <a href="{{ route('laboratories') }}"
               class="text-xs font-semibold text-indigo-500 hover:text-indigo-700 flex items-center gap-1 transition-colors">
                Kelola
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif
    </div>

    @if(empty($laboratories))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Belum ada data laboratorium</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="code">Kode Lab</x-sort-header>
                        <x-sort-header field="name">Nama Lab</x-sort-header>
                        <x-sort-header field="room">Ruangan</x-sort-header>
                        <x-sort-header field="building">Gedung</x-sort-header>
                        <x-sort-header field="floor">Lantai</x-sort-header>
                        <x-sort-header field="head">Penanggung Jawab</x-sort-header>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laboratories as $index => $lab)
                        <tr x-show="showRow({{ $index }})" x-cloak>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                                    {{ $lab['code'] }}
                                </span>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $lab['name'] }}</td>
                            <td class="text-slate-500">{{ $lab['room_name'] ?? $lab['room_code'] ?? '-' }}</td>
                            <td class="text-slate-500">{{ $lab['building_name'] ?? '-' }}</td>
                            <td class="text-slate-500">{{ $lab['floor_name'] ?? '-' }}</td>
                            <td>
                                @if($lab['head_name'] ?? null)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[0.6rem] font-bold text-indigo-600">
                                            {{ strtoupper(substr($lab['head_name'], 0, 1)) }}
                                        </div>
                                        <span class="text-slate-700">{{ $lab['responsible_name'] ?? $lab['head_name'] ?? 'Belum ditentukan' }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <x-pagination :total="count($laboratories)" />
    @endif
</div>
@endsection
