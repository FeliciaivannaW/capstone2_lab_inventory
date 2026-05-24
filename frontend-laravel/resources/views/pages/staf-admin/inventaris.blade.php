@extends('layouts.app')

@section('title', 'Semua Inventaris')

@section('content')
@php
    $statusMeta = [
        'received'    => ['label' => 'Diterima',    'dot' => 'bg-blue-400',    'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'labeled'     => ['label' => 'Berlabel',    'dot' => 'bg-amber-400',   'badge' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'available'   => ['label' => 'Tersedia',    'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'in_use'      => ['label' => 'Digunakan',   'dot' => 'bg-indigo-500',  'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'maintenance' => ['label' => 'Maintenance', 'dot' => 'bg-orange-400',  'badge' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'disposed'    => ['label' => 'Dihapus',     'dot' => 'bg-red-400',     'badge' => 'bg-red-50 text-red-700 border-red-200'],
        'replaced'    => ['label' => 'Diganti',     'dot' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-600 border-slate-200'],
    ];
    $condMeta = [
        'baik'         => ['label' => 'Baik',         'class' => 'badge-approved'],
        'rusak_ringan' => ['label' => 'Rusak Ringan',  'class' => 'badge-pending'],
        'rusak_berat'  => ['label' => 'Rusak Berat',   'class' => 'badge-rejected'],
        'maintenance'  => ['label' => 'Maintenance',   'class' => 'badge-active'],
        'dihapus'      => ['label' => 'Dihapus',       'class' => 'badge-rejected'],
        'diganti'      => ['label' => 'Diganti',       'class' => 'badge-active'],
    ];
@endphp

{{-- ─── Header ─────────────────────────────────────────── --}}
<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Semua Inventaris</h1>
        <p class="text-sm text-slate-500 mt-1">
            Pantau seluruh aset inventaris laboratorium — status, kondisi, dan siklus hidup.
        </p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Read-Only View
    </span>
</div>

{{-- ─── Summary cards ───────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5 border border-slate-100">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Total Aset</p>
        <p class="text-3xl font-bold text-slate-900 mt-1 leading-none">{{ $totalAssets }}</p>
        <p class="text-[0.7rem] text-slate-400 mt-2">semua status</p>
    </div>
    @foreach([['received','Baru Terima'],['labeled','Berlabel'],['available','Tersedia'],['in_use','Digunakan']] as [$s,$l])
        @php $cnt = $byStatus[$s] ?? 0; $m = $statusMeta[$s]; @endphp
        <div class="glass-card rounded-2xl p-5 border {{ 'border-slate-100' }}">
            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">{{ $l }}</p>
            <p class="text-3xl font-bold text-slate-900 mt-1 leading-none">{{ $cnt }}</p>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="w-2 h-2 rounded-full {{ $m['dot'] }}"></span>
                <p class="text-[0.7rem] text-slate-400">{{ $m['label'] }}</p>
            </div>
        </div>
    @endforeach
</div>

{{-- ─── Condition mini-bar ──────────────────────────────── --}}
@if($totalAssets > 0)
<div class="glass-card rounded-2xl p-5 mb-6">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Kondisi Aset</p>
    <div class="flex gap-2 flex-wrap">
        @foreach($byCondition as $cond => $cnt)
            @php $cm = $condMeta[$cond] ?? ['label' => $cond, 'class' => 'badge-draft']; @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200">
                {{ $cm['label'] }}
                <span class="font-bold text-slate-900">{{ $cnt }}</span>
            </span>
        @endforeach
    </div>
</div>
@endif

{{-- ─── Filter bar ──────────────────────────────────────── --}}
<form method="GET" action="{{ route('staf-admin.inventaris') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    {{-- Search --}}
    <div class="flex-[2] min-w-[180px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Kode aset, label, nama barang…"
                   class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
        </div>
    </div>
    {{-- Lab --}}
    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="lab_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Lab</option>
            @foreach($labs as $lab)
                <option value="{{ $lab['id'] }}" {{ ($filters['lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>
                    {{ $lab['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    {{-- Status --}}
    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status</label>
        <select name="status" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Status</option>
            @foreach($statusMeta as $val => $meta)
                <option value="{{ $val }}" {{ ($filters['status'] ?? '') == $val ? 'selected' : '' }}>{{ $meta['label'] }}</option>
            @endforeach
        </select>
    </div>
    {{-- Condition --}}
    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi</label>
        <select name="condition" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Kondisi</option>
            @foreach($condMeta as $val => $cm)
                <option value="{{ $val }}" {{ ($filters['condition'] ?? '') == $val ? 'selected' : '' }}>{{ $cm['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filter
        </button>
        @if(array_filter($filters))
            <a href="{{ route('staf-admin.inventaris') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                Reset
            </a>
        @endif
    </div>
</form>

{{-- ─── Table ───────────────────────────────────────────── --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">
            {{ count($assets) }} aset ditemukan
            @if(array_filter($filters))
                <span class="text-slate-400 font-normal">dari {{ $totalAssets }} total</span>
            @endif
        </p>
        {{-- Lifecycle legend --}}
        <div class="hidden sm:flex items-center gap-3">
            @foreach(['received' => 'Terima','labeled' => 'Label','available' => 'Siap','in_use' => 'Pakai'] as $s => $l)
                <span class="inline-flex items-center gap-1 text-[0.6rem] font-semibold text-slate-500">
                    <span class="w-2 h-2 rounded-full {{ $statusMeta[$s]['dot'] }}"></span>{{ $l }}
                </span>
                @if($s !== 'in_use')<svg class="w-3 h-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>@endif
            @endforeach
        </div>
    </div>

    @if(empty($assets))
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-14 h-14 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-semibold text-slate-400">Tidak ada aset ditemukan</p>
            <p class="text-xs text-slate-300 mt-1">Coba ubah filter atau tunggu barang diterima dan diberi label.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Aset</th>
                        <th>Nama Barang</th>
                        <th>Lab</th>
                        <th>Ruangan</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Kondisi</th>
                        <th>Tgl Terima</th>
                        <th>Lifecycle</th>
                        <th>Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $i => $asset)
                        @php
                            $st     = $asset['status'] ?? 'received';
                            $sMeta  = $statusMeta[$st]   ?? ['label' => $st,                    'dot' => 'bg-slate-300', 'badge' => 'bg-slate-100 text-slate-600 border-slate-200'];
                            $cond   = $asset['asset_condition'] ?? 'baik';
                            $cMeta  = $condMeta[$cond]   ?? ['label' => $cond, 'class' => 'badge-draft'];

                            // Lifecycle step index
                            $steps    = ['received', 'labeled', 'available', 'in_use', 'maintenance'];
                            $stepIdx  = array_search($st, $steps);
                        @endphp
                        <tr>
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>

                            <td>
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $asset['asset_code'] }}
                                </span>
                            </td>

                            <td class="font-semibold text-slate-800">
                                {{ $asset['item_name'] ?? '—' }}
                                @if($asset['category_name'] ?? null)
                                    <p class="text-[0.65rem] text-slate-400 font-normal mt-0.5">{{ $asset['category_name'] }}</p>
                                @endif
                            </td>

                            <td>
                                @if($asset['lab_code'] ?? null)
                                    <span class="badge badge-active text-xs">{{ $asset['lab_code'] }}</span>
                                    <p class="text-[0.65rem] text-slate-400 mt-0.5">{{ $asset['lab_name'] ?? '' }}</p>
                                @else
                                    <span class="text-slate-300 text-xs italic">—</span>
                                @endif
                            </td>

                            <td class="text-slate-500 text-xs">{{ $asset['room_name'] ?? '—' }}</td>

                            <td>
                                @if($asset['label_number'] ?? null)
                                    <span class="badge badge-approved text-xs">{{ $asset['label_number'] }}</span>
                                @else
                                    <span class="badge badge-pending text-xs">Belum</span>
                                @endif
                            </td>

                            <td>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[0.65rem] font-semibold border {{ $sMeta['badge'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sMeta['dot'] }}"></span>
                                    {{ $sMeta['label'] }}
                                </span>
                            </td>

                            <td>
                                <span class="badge {{ $cMeta['class'] }} text-xs">{{ $cMeta['label'] }}</span>
                            </td>

                            <td class="text-slate-500 text-xs">
                                {{ $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—' }}
                            </td>

                            {{-- Lifecycle mini progress --}}
                            <td style="min-width: 130px;">
                                <div class="flex items-center gap-0.5">
                                    @foreach(['received','labeled','available','in_use'] as $idx => $step)
                                        @php
                                            $stepMeta  = $statusMeta[$step];
                                            $isDone    = $stepIdx !== false && $stepIdx >= $idx;
                                            $isCurrent = $st === $step;
                                        @endphp
                                        <div class="relative group">
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all
                                                        {{ $isCurrent ? 'border-indigo-500 bg-indigo-500 ring-2 ring-indigo-200' :
                                                          ($isDone ? 'border-emerald-400 bg-emerald-400' : 'border-slate-200 bg-white') }}">
                                                @if($isDone && !$isCurrent)
                                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block z-10">
                                                <span class="bg-slate-800 text-white text-[0.6rem] font-semibold px-2 py-0.5 rounded whitespace-nowrap">
                                                    {{ $stepMeta['label'] }}
                                                </span>
                                            </div>
                                        </div>
                                        @if($idx < 3)
                                            <div class="h-0.5 w-3 {{ $isDone && $stepIdx !== false && $stepIdx > $idx ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                                        @endif
                                    @endforeach
                                </div>
                            </td>

                            <td>
                                <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Riwayat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
