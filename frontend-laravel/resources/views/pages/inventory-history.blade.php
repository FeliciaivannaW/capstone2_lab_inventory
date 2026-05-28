@extends('layouts.app')

@section('title', 'History Kondisi Aset')

@section('content')
@php
    $conditionMeta = [
        'baik' => ['label' => 'Baik', 'class' => 'badge-approved'],
        'rusak_ringan' => ['label' => 'Rusak Ringan', 'class' => 'badge-pending'],
        'rusak_berat' => ['label' => 'Rusak Berat', 'class' => 'badge-rejected'],
        'maintenance' => ['label' => 'Maintenance', 'class' => 'badge-active'],
        'dihapus' => ['label' => 'Dihapus', 'class' => 'badge-rejected'],
        'diganti' => ['label' => 'Diganti', 'class' => 'badge-draft'],
    ];
@endphp

<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">History Kondisi Aset</h1>
        <p class="text-sm text-slate-500 mt-1">Riwayat perubahan kondisi aset, termasuk aset yang dihapus atau diganti.</p>
    </div>
</div>

<form method="GET" action="{{ route('inventory.history') }}" class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    <div class="flex-[2] min-w-[220px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari History</label>
        <input
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="Kode aset, label, nama barang, catatan..."
            class="w-full rounded-xl border-slate-200 text-sm"
        >
    </div>

    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi Baru</label>
        <select name="condition" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Kondisi</option>
            @foreach($conditionMeta as $value => $meta)
                <option value="{{ $value }}" {{ ($filters['condition'] ?? '') === $value ? 'selected' : '' }}>
                    {{ $meta['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">
        Filter
    </button>

    <a href="{{ route('inventory.history') }}" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200">
        Reset
    </a>
</form>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($history ?? []) }} history ditemukan</p>
    </div>

    @if(empty($history))
        <div class="py-16 text-center text-sm text-slate-400">Belum ada history kondisi aset.</div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aset</th>
                        <th>Ruangan</th>
                        <th>Kondisi Lama</th>
                        <th>Kondisi Baru</th>
                        <th>Catatan</th>
                        <th>Diupdate Oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $i => $row)
                        @php
                            $old = $conditionMeta[$row['old_condition'] ?? ''] ?? ['label' => $row['old_condition'] ?? '-', 'class' => 'badge-draft'];
                            $new = $conditionMeta[$row['new_condition'] ?? ''] ?? ['label' => $row['new_condition'] ?? '-', 'class' => 'badge-draft'];
                        @endphp
                        <tr>
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md inline-block">
                                    {{ $row['asset_code'] }}
                                </div>
                                <div class="text-sm font-semibold text-slate-800 mt-1">{{ $row['item_name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $row['label_number'] ?? 'Belum berlabel' }}</div>
                            </td>
                            <td class="text-xs text-slate-500">
                                {{ $row['room_name'] ?? '-' }}
                                <div class="font-mono text-[11px] text-slate-400">{{ $row['room_code'] ?? '-' }}</div>
                            </td>
                            <td><span class="badge {{ $old['class'] }} text-xs">{{ $old['label'] }}</span></td>
                            <td><span class="badge {{ $new['class'] }} text-xs">{{ $new['label'] }}</span></td>
                            <td class="text-sm text-slate-600">{{ $row['note'] ?? '-' }}</td>
                            <td class="text-sm text-slate-600">{{ $row['updated_by_name'] ?? '-' }}</td>
                            <td class="text-xs text-slate-500">
                                {{ !empty($row['updated_at']) ? \Carbon\Carbon::parse($row['updated_at'])->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection