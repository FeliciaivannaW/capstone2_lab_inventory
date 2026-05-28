@extends('layouts.app')

@section('title', 'Katalog Inventaris')

@section('content')
@php
    $statusMeta = [
        'received'    => ['label' => 'Diterima',    'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'labeled'     => ['label' => 'Berlabel',    'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'available'   => ['label' => 'Tersedia',    'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'in_use'      => ['label' => 'Digunakan',   'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'disposed'    => ['label' => 'Dihapus',     'class' => 'bg-red-50 text-red-700 border-red-200'],
        'replaced'    => ['label' => 'Diganti',     'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
    ];

    $conditionMeta = [
        'baik'         => ['label' => 'Baik',         'class' => 'badge-approved'],
        'rusak_ringan' => ['label' => 'Rusak Ringan', 'class' => 'badge-pending'],
        'rusak_berat'  => ['label' => 'Rusak Berat',  'class' => 'badge-rejected'],
        'maintenance'  => ['label' => 'Maintenance',  'class' => 'badge-active'],
        'dihapus'      => ['label' => 'Dihapus',      'class' => 'badge-rejected'],
        'diganti'      => ['label' => 'Diganti',      'class' => 'badge-draft'],
    ];
@endphp

<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Katalog Inventaris</h1>
        <p class="text-sm text-slate-500 mt-1">
            Data aset inventaris laboratorium beserta kode label, kondisi, dan siklus hidup.
        </p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
        Read-only katalog
    </span>
</div>

<div class="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Total Aset</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $totalAssets }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Tersedia</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $availableCount }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Maintenance</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $maintenanceCount }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Sudah Berlabel</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $labeledCount }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Belum Berlabel</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ $unlabeledCount }}</p>
    </div>
</div>

@if($totalAssets > 0)
<div class="glass-card rounded-2xl p-5 mb-6">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Ringkasan Kondisi</p>
    <div class="flex gap-2 flex-wrap">
        @foreach($byCondition as $condition => $count)
            @php
                $meta = $conditionMeta[$condition] ?? ['label' => ucfirst($condition), 'class' => 'badge-draft'];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                {{ $meta['label'] }}
                <span class="font-bold text-slate-900">{{ $count }}</span>
            </span>
        @endforeach
    </div>
</div>
@endif

<form method="GET" action="{{ route('inventory') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    <div class="flex-[2] min-w-[180px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
               placeholder="Kode aset, label, nama barang..."
               class="w-full rounded-xl border-slate-200 text-sm">
    </div>

    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="lab_id" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Lab</option>
            @foreach($labs as $lab)
                <option value="{{ $lab['id'] }}" {{ ($filters['lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>
                    {{ $lab['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status</label>
        <select name="status" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Status</option>
            @foreach($statusMeta as $value => $meta)
                <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $meta['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi</label>
        <select name="condition" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Kondisi</option>
            @foreach($conditionMeta as $value => $meta)
                <option value="{{ $value }}" {{ ($filters['condition'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $meta['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Label</label>
        <select name="label_status" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua</option>
            <option value="labeled" {{ ($filters['label_status'] ?? '') === 'labeled' ? 'selected' : '' }}>Sudah berlabel</option>
            <option value="unlabeled" {{ ($filters['label_status'] ?? '') === 'unlabeled' ? 'selected' : '' }}>Belum berlabel</option>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">
            Filter
        </button>
        <a href="{{ route('inventory') }}"
           class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200">
            Reset
        </a>
    </div>
</form>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($assets ?? []) }} aset ditemukan</p>
    </div>

    @if(empty($assets))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl mb-4">📦</div>
            <p class="text-sm font-medium text-slate-500">Belum ada data aset inventaris</p>
            <p class="text-xs text-slate-400 mt-1">Coba cek seed database atau filter yang dipakai.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Aset</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Lab</th>
                        <th>Ruangan</th>
                        <th>Label</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Tgl Terima</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $i => $asset)
                        @php
                            $condition = $conditionMeta[$asset['asset_condition'] ?? ''] ?? ['label' => ucfirst($asset['asset_condition'] ?? '-'), 'class' => 'badge-draft'];
                            $status = $statusMeta[$asset['status'] ?? ''] ?? ['label' => ucfirst($asset['status'] ?? '-'), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                        @endphp
                        <tr>
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $asset['asset_code'] ?? '-' }}
                                </span>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $asset['item_name'] ?? '-' }}</td>
                            <td class="text-slate-500 text-xs">{{ $asset['category_name'] ?? '-' }}</td>
                            <td class="text-slate-500 text-xs">{{ $asset['lab_name'] ?? '-' }}</td>
                            <td class="text-slate-500 text-xs">
                                {{ $asset['room_name'] ?? '-' }}
                                @if(!empty($asset['room_code']))
                                    <div class="font-mono text-[11px] text-slate-400">{{ $asset['room_code'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($asset['label_number']))
                                    <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">
                                        {{ $asset['label_number'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold bg-slate-100 text-slate-500 border border-slate-200 rounded-md">
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $condition['class'] }} text-xs">
                                    {{ $condition['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold border rounded-md {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="text-slate-500 text-xs">
                                {{ !empty($asset['received_date']) ? \Carbon\Carbon::parse($asset['received_date'])->format('d M Y') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection