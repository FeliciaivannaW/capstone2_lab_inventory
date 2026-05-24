@extends('layouts.app')

@section('title', 'Stok BHP')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Barang Habis Pakai (BHP)</h1>
    <p class="text-sm text-slate-500 mt-1">Pengelolaan stok BHP, minimum stok, dan riwayat pergerakan stok.</p>
</div>

{{-- Preview cards showing design direction --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    @php
        $bhpPreviews = [
            ['name' => 'Alkohol 70%',       'stock' => 85,  'min' => 20,  'unit' => 'Liter',  'color' => 'indigo'],
            ['name' => 'Sarung Tangan Latex','stock' => 12,  'min' => 50,  'unit' => 'Box',    'color' => 'amber'],
            ['name' => 'Masker N95',         'stock' => 3,   'min' => 10,  'unit' => 'Box',    'color' => 'red'],
        ];
    @endphp
    @foreach($bhpPreviews as $item)
        @php
            $pct = min(100, ($item['stock'] / max(1, $item['min'] * 2)) * 100);
            $barColor = $pct > 50 ? 'bg-emerald-500' : ($pct > 25 ? 'bg-amber-500' : 'bg-red-500');
            $statusColor = $pct > 50 ? 'badge-approved' : ($pct > 25 ? 'badge-pending' : 'badge-rejected');
            $statusLabel = $pct > 50 ? 'Aman' : ($pct > 25 ? 'Menipis' : 'Kritis');
        @endphp
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ $item['name'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Min. stok: {{ $item['min'] }} {{ $item['unit'] }}</p>
                </div>
                <span class="badge {{ $statusColor }} text-xs">{{ $statusLabel }}</span>
            </div>

            {{-- Stock gauge --}}
            <div class="mb-3">
                <div class="flex justify-between items-end mb-1.5">
                    <span class="text-xs text-slate-500">Stok saat ini</span>
                    <span class="text-sm font-bold text-slate-900">{{ $item['stock'] }} {{ $item['unit'] }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[0.6rem] text-slate-400">0</span>
                    <span class="text-[0.6rem] text-slate-400">{{ $item['min'] * 2 }} {{ $item['unit'] }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="glass-card rounded-2xl p-8">
    <div class="flex flex-col items-center text-center max-w-md mx-auto py-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-2">Manajemen Stok BHP</h2>
        <p class="text-sm text-slate-500">
            Fitur lengkap pengelolaan stok BHP, input pemakaian, histori pergerakan stok, dan auto-deduction saat maintenance sedang dalam pengembangan.
        </p>
        <div class="flex items-center gap-2 mt-5 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs text-amber-700 font-medium">Preview gauge di atas adalah demonstrasi UI</p>
        </div>
    </div>
</div>
@endsection
