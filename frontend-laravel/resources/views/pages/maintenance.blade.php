@extends('layouts.app')

@section('title', 'Log Maintenance')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Log Maintenance</h1>
    <p class="text-sm text-slate-500 mt-1">Riwayat perbaikan aset, update kondisi barang, dan pemakaian BHP per sesi maintenance.</p>
</div>

{{-- Preview: activity feed style --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <div class="lg:col-span-2">
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Riwayat Maintenance</h2>
                <p class="text-xs text-slate-400">Demo — data sesungguhnya segera hadir</p>
            </div>

            {{-- Demo activity feed --}}
            @php
                $demoLogs = [
                    ['tech' => 'Ahmad R.', 'asset' => 'Mikroskop #A001', 'action' => 'Kalibrasi lensa dan pembersihan optik', 'condition' => 'baik', 'date' => '24 Mei 2025', 'bhp' => ['Alkohol 70%', 'Tisu Lensa']],
                    ['tech' => 'Siti W.',  'asset' => 'Centrifuge #A012', 'action' => 'Penggantian rotor + pelumasan motor', 'condition' => 'rusak_ringan', 'date' => '22 Mei 2025', 'bhp' => ['Oli Mesin']],
                    ['tech' => 'Budi S.',  'asset' => 'PCR Machine #A023', 'action' => 'Update firmware dan cek sensor suhu', 'condition' => 'baik', 'date' => '18 Mei 2025', 'bhp' => []],
                ];
                $condColors = ['baik' => 'badge-approved', 'rusak_ringan' => 'badge-pending', 'rusak_berat' => 'badge-rejected', 'maintenance' => 'badge-active'];
            @endphp

            <div class="divide-y divide-slate-100">
                @foreach($demoLogs as $log)
                    <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700 flex-shrink-0 mt-0.5">
                                {{ strtoupper(substr($log['tech'], 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <span class="text-sm font-semibold text-slate-800">{{ $log['tech'] }}</span>
                                    <span class="text-slate-400 text-xs">·</span>
                                    <span class="text-xs text-slate-500">{{ $log['date'] }}</span>
                                    <span class="badge {{ $condColors[$log['condition']] ?? 'badge-draft' }} text-xs ml-auto">
                                        {{ str_replace('_', ' ', ucfirst($log['condition'])) }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 mb-2">
                                    <span class="font-semibold text-indigo-600">{{ $log['asset'] }}</span>
                                    — {{ $log['action'] }}
                                </p>
                                @if(!empty($log['bhp']))
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-xs text-slate-400">BHP:</span>
                                        @foreach($log['bhp'] as $b)
                                            <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
                                                {{ $b }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-5">
        {{-- Stats --}}
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Statistik Bulan Ini</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Sesi</span>
                    <span class="text-sm font-bold text-slate-900">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Kondisi Baik</span>
                    <span class="text-sm font-bold text-emerald-600">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Perlu Perhatian</span>
                    <span class="text-sm font-bold text-amber-600">—</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">BHP Terpakai</span>
                    <span class="text-sm font-bold text-indigo-600">—</span>
                </div>
            </div>
        </div>

        {{-- Info banner --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-xs font-bold text-indigo-800 mb-1">Fitur dalam Pengembangan</p>
                    <p class="text-xs text-indigo-600 leading-relaxed">Log maintenance aktual dan form input sesi maintenance akan tersedia segera.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
