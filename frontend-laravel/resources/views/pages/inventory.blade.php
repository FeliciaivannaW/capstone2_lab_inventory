@extends('layouts.app')

@section('title', 'Katalog Inventaris')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Katalog Inventaris</h1>
    <p class="text-sm text-slate-500 mt-1">Data aset inventaris laboratorium beserta kode label, kondisi, dan siklus hidup.</p>
</div>

{{-- Coming soon placeholder with lifecycle preview --}}
<div class="glass-card rounded-2xl p-8">
    <div class="flex flex-col items-center text-center max-w-md mx-auto py-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>

        <h2 class="text-lg font-bold text-slate-800 mb-2">Katalog Inventaris</h2>
        <p class="text-sm text-slate-500 mb-8">
            Akan berisi data aset, kode label, QR/barcode, kondisi barang, dan siklus hidup per ruangan.
        </p>

        {{-- Asset lifecycle timeline preview --}}
        <div class="w-full bg-slate-50 rounded-2xl p-5 border border-slate-200">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 text-left">Contoh: Siklus Hidup Aset</p>
            <div class="flex items-center gap-0 overflow-x-auto pb-2">
                @php
                    $stages = [
                        ['label' => 'Procured',   'color' => 'bg-indigo-500'],
                        ['label' => 'Received',   'color' => 'bg-blue-500'],
                        ['label' => 'Labeled',    'color' => 'bg-cyan-500'],
                        ['label' => 'Active',     'color' => 'bg-emerald-500'],
                        ['label' => 'Maintenance','color' => 'bg-amber-500'],
                        ['label' => 'Replaced',   'color' => 'bg-slate-400'],
                    ];
                @endphp
                @foreach($stages as $i => $stage)
                    <div class="flex items-center">
                        <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                            <div class="w-3 h-3 rounded-full {{ $stage['color'] }} ring-2 ring-white ring-offset-1"></div>
                            <span class="text-[0.6rem] font-semibold text-slate-500 whitespace-nowrap">{{ $stage['label'] }}</span>
                        </div>
                        @if($i < count($stages) - 1)
                            <div class="w-8 sm:w-12 h-px bg-slate-300 flex-shrink-0 -mt-4"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
