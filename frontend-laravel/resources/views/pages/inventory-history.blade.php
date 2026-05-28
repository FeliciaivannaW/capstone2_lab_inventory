@extends('layouts.app')

@section('title', 'History Kondisi Aset')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">History Kondisi Aset</h1>
    <p class="text-sm text-slate-500 mt-1">
        Riwayat perubahan kondisi aset, termasuk aset yang dihapus atau diganti.
    </p>
</div>

@php
    $history = $history ?? [];

    $conditionLabels = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat' => 'Rusak Berat',
        'maintenance' => 'Maintenance',
        'dihapus' => 'Dihapus',
        'diganti' => 'Diganti',
    ];

    $conditionClasses = [
        'baik' => 'badge-approved',
        'rusak_ringan' => 'badge-pending',
        'rusak_berat' => 'badge-rejected',
        'maintenance' => 'badge-active',
        'dihapus' => 'badge-rejected',
        'diganti' => 'badge-draft',
    ];

    $formatCondition = function ($condition) use ($conditionLabels) {
        return $conditionLabels[$condition] ?? ucfirst(str_replace('_', ' ', $condition ?? '-'));
    };
@endphp

<div class="glass-card rounded-2xl p-5 mb-6">
    <form method="GET" action="{{ route('inventory.history') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
        <div class="lg:col-span-7">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">
                Cari History
            </label>
            <div class="relative">
                <input
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Kode aset, label, nama barang, catatan..."
                    class="w-full rounded-xl border-slate-200 text-sm pr-9"
                >

                @if(request()->filled('search'))
                    <a
                        href="{{ route('inventory.history', request()->except('search')) }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none"
                        title="Hapus pencarian"
                    >
                        ×
                    </a>
                @endif
            </div>
        </div>

        <div class="lg:col-span-3">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">
                Kondisi Baru
            </label>
            <select
                name="condition"
                class="w-full rounded-xl border-slate-200 text-sm"
                onchange="this.form.submit()"
            >
                <option value="">Semua Kondisi</option>
                <option value="baik" {{ request('condition') === 'baik' ? 'selected' : '' }}>Baik</option>
                <option value="rusak_ringan" {{ request('condition') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="rusak_berat" {{ request('condition') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                <option value="maintenance" {{ request('condition') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="dihapus" {{ request('condition') === 'dihapus' ? 'selected' : '' }}>Dihapus</option>
                <option value="diganti" {{ request('condition') === 'diganti' ? 'selected' : '' }}>Diganti</option>
            </select>
        </div>

        <div class="lg:col-span-2 flex gap-2">
            <button class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">
                Filter
            </button>

            <a href="{{ route('inventory.history') }}"
               class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-bold text-slate-800">{{ count($history) }} history ditemukan</p>
    </div>

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
                    <th>Diubah Oleh</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($history as $index => $row)
                    @php
                        $oldCondition = $row['old_condition'] ?? '-';
                        $newCondition = $row['new_condition'] ?? '-';

                        $oldClass = $conditionClasses[$oldCondition] ?? 'badge-draft';
                        $newClass = $conditionClasses[$newCondition] ?? 'badge-draft';
                    @endphp

                    <tr>
                        <td class="text-slate-500">{{ $index + 1 }}</td>

                        <td>
                            <div class="font-mono text-xs font-bold bg-slate-100 px-2 py-1 rounded-md inline-block mb-1">
                                {{ $row['asset_code'] ?? '-' }}
                            </div>

                            <div class="font-semibold text-slate-800">
                                {{ $row['item_name'] ?? '-' }}
                            </div>

                            @if(!empty($row['label_number']))
                                <div class="text-xs text-slate-400">
                                    {{ $row['label_number'] }}
                                </div>
                            @endif
                        </td>

                        <td class="text-slate-500">
                            <div>{{ $row['room_name'] ?? '-' }}</div>
                            @if(!empty($row['room_code']))
                                <div class="text-xs text-slate-400 font-mono">{{ $row['room_code'] }}</div>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $oldClass }} text-xs">
                                {{ $formatCondition($oldCondition) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge {{ $newClass }} text-xs">
                                {{ $formatCondition($newCondition) }}
                            </span>
                        </td>

                        <td class="text-slate-600">
                            {{ $row['note'] ?? '-' }}
                        </td>

                        <td class="text-slate-600">
                            {{ $row['updated_by_name'] ?? '-' }}
                        </td>

                        <td class="text-slate-500">
                            @if(!empty($row['updated_at']))
                                {{ \Carbon\Carbon::parse($row['updated_at'])->format('d M Y H:i') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-400 py-10">
                            Belum ada history kondisi aset.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection