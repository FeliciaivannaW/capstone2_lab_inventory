@extends('layouts.app')
@section('title', 'Semua Inventaris')

@section('content')
@php
    $statusMeta = [
        'received'    => ['label' => 'Diterima',    'color' => 'blue',    'dot' => 'bg-blue-400',    'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'labeled'     => ['label' => 'Berlabel',    'color' => 'indigo',  'dot' => 'bg-indigo-400',  'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'available'   => ['label' => 'Tersedia',    'color' => 'emerald', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'in_use'      => ['label' => 'Digunakan',   'color' => 'violet',  'dot' => 'bg-violet-500',  'badge' => 'bg-violet-50 text-violet-700 border-violet-200'],
        'maintenance' => ['label' => 'Maintenance', 'color' => 'amber',   'dot' => 'bg-amber-400',   'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'disposed'    => ['label' => 'Dihapus',     'color' => 'red',     'dot' => 'bg-red-400',     'badge' => 'bg-red-50 text-red-700 border-red-200'],
        'replaced'    => ['label' => 'Diganti',     'color' => 'slate',   'dot' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-600 border-slate-200'],
    ];
    $condMeta = [
        'baik'         => ['label' => 'Baik',        'color' => 'bg-emerald-100 text-emerald-700'],
        'rusak_ringan' => ['label' => 'Rusak Ringan', 'color' => 'bg-amber-100 text-amber-700'],
        'rusak_berat'  => ['label' => 'Rusak Berat',  'color' => 'bg-red-100 text-red-700'],
        'maintenance'  => ['label' => 'Maintenance',  'color' => 'bg-orange-100 text-orange-700'],
        'dihapus'      => ['label' => 'Dihapus',      'color' => 'bg-slate-100 text-slate-600'],
        'diganti'      => ['label' => 'Diganti',      'color' => 'bg-violet-100 text-violet-700'],
    ];
    $totalLabeled   = $byStatus['labeled'] ?? 0;
    $totalReceived  = $byStatus['received'] ?? 0;
    $totalAvailable = ($byStatus['available'] ?? 0) + ($byStatus['in_use'] ?? 0);
    $totalBaik      = $byCondition['baik'] ?? 0;
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Semua Inventaris</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pantau seluruh aset — status, kondisi, harga, dan QR code.</p>
    </div>
    <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Read-Only
    </span>
</div>

{{-- ══ SUMMARY CHIPS (clickable as filter shortcuts) ══ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @php
    $summaryChips = [
        ['label' => 'Total Aset',      'count' => $totalAssets,   'sub' => 'semua status',  'color' => 'slate',   'param' => ''],
        ['label' => 'Perlu Label',      'count' => $totalReceived, 'sub' => 'status: diterima','color' => 'blue',  'param' => 'received'],
        ['label' => 'Sudah Berlabel',   'count' => $totalLabeled,  'sub' => 'siap digunakan', 'color' => 'indigo', 'param' => 'labeled'],
        ['label' => 'Kondisi Baik',     'count' => $totalBaik,     'sub' => 'tidak rusak',   'color' => 'emerald', 'param' => ''],
    ];
    $colorMap = [
        'slate'   => ['bg' => 'bg-slate-50',   'ring' => 'ring-slate-200',   'num' => 'text-slate-900', 'icon_bg' => 'bg-slate-100',   'icon' => 'text-slate-500'],
        'blue'    => ['bg' => 'bg-blue-50',    'ring' => 'ring-blue-200',    'num' => 'text-blue-700',  'icon_bg' => 'bg-blue-100',    'icon' => 'text-blue-600'],
        'indigo'  => ['bg' => 'bg-indigo-50',  'ring' => 'ring-indigo-200',  'num' => 'text-indigo-700','icon_bg' => 'bg-indigo-100',  'icon' => 'text-indigo-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200', 'num' => 'text-emerald-700','icon_bg'=> 'bg-emerald-100', 'icon' => 'text-emerald-600'],
    ];
    @endphp
    @foreach($summaryChips as $chip)
    @php $cm = $colorMap[$chip['color']]; $isActive = ($filters['status'] ?? '') === $chip['param'] && $chip['param']; @endphp
    <a href="{{ $chip['param'] ? route('staf-admin.inventaris', ['status' => $chip['param']]) : route('staf-admin.inventaris') }}"
       class="glass-card rounded-2xl p-4 flex items-center gap-3 transition-all hover:shadow-md
              {{ $isActive ? 'ring-2 ' . $cm['ring'] : 'hover:ring-1 hover:' . $cm['ring'] }}">
        <div class="w-10 h-10 rounded-xl {{ $cm['icon_bg'] }} flex items-center justify-center flex-shrink-0">
            <p class="text-lg font-bold {{ $cm['num'] }}">{{ $chip['count'] }}</p>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-700 truncate">{{ $chip['label'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $chip['sub'] }}</p>
        </div>
    </a>
    @endforeach
</div>

{{-- ══ KONDISI BAR ══ --}}
@if($totalAssets > 0)
<div class="glass-card rounded-2xl px-5 py-3.5 mb-5 flex items-center gap-3 flex-wrap">
    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex-shrink-0">Kondisi:</span>
    @foreach($byCondition as $cond => $cnt)
    @php $cm = $condMeta[$cond] ?? ['label' => $cond, 'color' => 'bg-slate-100 text-slate-600']; @endphp
    <a href="{{ route('staf-admin.inventaris', array_merge($filters, ['condition' => $cond])) }}"
       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $cm['color'] }} border border-current border-opacity-20 hover:opacity-80 transition-opacity {{ ($filters['condition'] ?? '') === $cond ? 'ring-2 ring-offset-1 ring-current ring-opacity-30' : '' }}">
        {{ $cm['label'] }} <span class="font-bold">{{ $cnt }}</span>
    </a>
    @endforeach
    @if($filters['condition'] ?? null)
        <a href="{{ route('staf-admin.inventaris', array_diff_key($filters, ['condition' => ''])) }}" class="text-[11px] text-slate-400 hover:text-slate-600 ml-1">✕ Reset kondisi</a>
    @endif
</div>
@endif

{{-- ══ FILTER BAR ══ --}}
<form method="GET" action="{{ route('staf-admin.inventaris') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-3">
    <div class="flex-[2] min-w-[180px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Kode, label, nama barang…"
                   class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
        </div>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="lab_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Lab</option>
            @foreach($labs as $lab)
                <option value="{{ $lab['id'] }}" {{ ($filters['lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>{{ $lab['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[130px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status</label>
        <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Status</option>
            @foreach($statusMeta as $val => $meta)
                <option value="{{ $val }}" {{ ($filters['status'] ?? '') == $val ? 'selected' : '' }}>{{ $meta['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[130px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi</label>
        <select name="condition" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Kondisi</option>
            @foreach($condMeta as $val => $cm)
                <option value="{{ $val }}" {{ ($filters['condition'] ?? '') == $val ? 'selected' : '' }}>{{ $cm['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
        @if(array_filter($filters))
            <a href="{{ route('staf-admin.inventaris') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Reset</a>
        @endif
    </div>
</form>

{{-- ══ TABLE ══ --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="inventarisApp({{ count($assets) }})">

    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">
            {{ count($assets) }} aset
            @if(array_filter($filters))<span class="text-slate-400 font-normal">dari {{ $totalAssets }} total</span>@endif
        </p>
        <p class="text-xs text-slate-400">Klik baris untuk detail lengkap</p>
    </div>

    @if(empty($assets))
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="text-sm font-semibold text-slate-500">Tidak ada aset ditemukan</p>
            <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau tunggu barang diterima.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider w-8">#</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Aset</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lab</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Harga Beli</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kondisi</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($assets as $i => $asset)
                    @php
                        $st    = $asset['status'] ?? 'received';
                        $sMeta = $statusMeta[$st] ?? ['label' => $st, 'dot' => 'bg-slate-300', 'badge' => 'bg-slate-100 text-slate-600 border-slate-200'];
                        $cond  = $asset['asset_condition'] ?? 'baik';
                        $cMeta = $condMeta[$cond] ?? ['label' => $cond, 'color' => 'bg-slate-100 text-slate-600'];
                        $price = $asset['purchase_price'] ?? null;
                        $hasQr = !empty($asset['qr_code']) || !empty($asset['photo_url']);
                        $qrUrl = $asset['qr_code'] ?? $asset['photo_url'] ?? null;
                    @endphp
                    {{-- Main row --}}
                    <tr x-show="showRow({{ $i }})" x-cloak
                        @click="toggleExpand({{ $i }})"
                        class="hover:bg-slate-50/70 cursor-pointer transition-colors"
                        :class="expanded === {{ $i }} ? 'bg-indigo-50/40' : ''">

                        <td class="px-4 py-3.5 text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>

                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                {{-- QR thumbnail jika ada --}}
                                @if($hasQr)
                                    <div class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0 bg-white">
                                        <img src="{{ $qrUrl }}" class="w-full h-full object-contain" alt="QR">
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-lg border-2 border-dashed border-slate-200 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </div>
                                @endif
                                <code class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $asset['asset_code'] }}</code>
                            </div>
                        </td>

                        <td class="px-4 py-3.5">
                            <p class="text-sm font-semibold text-slate-800">{{ $asset['item_name'] ?? '—' }}</p>
                            @if($asset['category_name'] ?? null)
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $asset['category_name'] }}</p>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($asset['lab_code'] ?? null)
                                <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">{{ $asset['lab_code'] }}</span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @if($price)
                                <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($price, 0, ',', '.') }}</p>
                            @else
                                <span class="text-slate-300 text-xs italic">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $cMeta['color'] }}">
                                {{ $cMeta['label'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border {{ $sMeta['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sMeta['dot'] }}"></span>
                                {{ $sMeta['label'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <svg class="w-4 h-4 text-slate-400 transition-transform mx-auto"
                                 :class="expanded === {{ $i }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </td>
                    </tr>

                    {{-- Expanded row --}}
                    <tr x-show="showRow({{ $i }}) && expanded === {{ $i }}" x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100">
                        <td colspan="8" class="px-4 pb-4 pt-0 bg-indigo-50/30">
                            <div class="rounded-2xl bg-white border border-indigo-100 p-4 flex flex-col sm:flex-row gap-5">

                                {{-- QR section --}}
                                <div class="flex-shrink-0 flex flex-col items-center gap-2">
                                    @if($hasQr)
                                        <div class="w-24 h-24 rounded-xl overflow-hidden border-2 border-indigo-200 bg-white p-1">
                                            <img src="{{ $qrUrl }}" class="w-full h-full object-contain" alt="QR {{ $asset['label_number'] ?? $asset['asset_code'] }}">
                                        </div>
                                        <p class="text-[10px] text-slate-400 text-center">Scan QR</p>
                                    @else
                                        <div class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-1 bg-slate-50">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            <p class="text-[10px] text-slate-400 text-center">Belum<br>ada QR</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Detail grid --}}
                                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3">
                                    @php
                                    $details = [
                                        ['Label',       $asset['label_number'] ?? null,   'mono'],
                                        ['Kode Aset',   $asset['asset_code'] ?? '—',      'mono'],
                                        ['Laboratorium',$asset['lab_name'] ?? '—',         'text'],
                                        ['Ruangan',     $asset['room_name'] ?? '—',        'text'],
                                        ['Serial No.',  $asset['serial_number'] ?? '—',   'mono'],
                                        ['Tgl Terima',  $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—', 'text'],
                                        ['Harga Beli',  $asset['purchase_price'] ? 'Rp ' . number_format($asset['purchase_price'], 0, ',', '.') : '—', 'text'],
                                        ['Tgl Beli',    $asset['purchase_date'] ? date('d M Y', strtotime($asset['purchase_date'])) : '—', 'text'],
                                    ];
                                    @endphp
                                    @foreach($details as [$dlabel, $dval, $dtype])
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $dlabel }}</p>
                                            @if($dlabel === 'Label' && $dval)
                                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full mt-0.5 inline-block">{{ $dval }}</span>
                                            @elseif($dval && $dval !== '—')
                                                <p class="text-sm font-semibold text-slate-700 mt-0.5 {{ $dtype === 'mono' ? 'font-mono' : '' }}">{{ $dval }}</p>
                                            @else
                                                <p class="text-xs text-slate-300 italic mt-0.5">Tidak ada</p>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($asset['notes'] ?? null)
                                        <div class="col-span-2 sm:col-span-3">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan</p>
                                            <p class="text-xs text-slate-600 mt-0.5">{{ $asset['notes'] }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex flex-row sm:flex-col gap-2 items-start">
                                    <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-600 bg-violet-50 hover:bg-violet-100 px-3 py-2 rounded-xl border border-violet-200 transition-colors whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Riwayat
                                    </a>
                                    @if($hasQr)
                                        <a href="{{ route('staf-admin.print-label', ['id' => $asset['id']]) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-xl border border-indigo-200 transition-colors whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak Label
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-pagination :total="count($assets)" />
    @endif
</div>

@push('scripts')
<script>
function inventarisApp(total) {
    return {
        ...window.tablePaginationData(total),
        expanded: null,
        toggleExpand(idx) {
            this.expanded = this.expanded === idx ? null : idx;
        }
    };
}
</script>
@endpush
@endsection
