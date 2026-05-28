@extends('layouts.app')

@section('title', 'Stok BHP')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Kelola Stok BHP</h1>
    <p class="text-sm text-slate-500 mt-1">Tambah stok, kurangi stok, set stok, dan lihat riwayat pergerakan.</p>
</div>

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

@php
    $stocks = $stocks ?? [];
    $catalogs = $catalogs ?? [];
    $laboratories = $laboratories ?? [];
    $movements = $movements ?? [];

    $statusClass = [
        'aman' => 'badge-approved',
        'menipis' => 'badge-pending',
        'kritis' => 'badge-rejected',
        'habis' => 'badge-rejected',
    ];

    $statusLabels = [
        'aman' => 'Aman',
        'menipis' => 'Menipis',
        'kritis' => 'Kritis',
        'habis' => 'Habis',
    ];

    $movementLabels = [
        'in' => 'Masuk',
        'out' => 'Keluar',
        'adjustment' => 'Set Stok',
        'maintenance_usage' => 'Maintenance',
    ];

    $normalizeStockStatus = function ($value) {
        $value = strtolower(str_replace(' ', '_', $value ?? ''));

        return match($value) {
            'safe', 'aman' => 'aman',
            'warning', 'menipis' => 'menipis',
            'critical', 'kritis' => 'kritis',
            'out_of_stock', 'habis' => 'habis',
            default => $value,
        };
    };
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="space-y-6">
        <div class="glass-card rounded-2xl p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah Item BHP</h2>

            <form action="{{ route('bhp.store') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="text-xs font-semibold text-slate-500">Laboratorium</label>
                    <select name="lab_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="">Pilih laboratorium</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab['id'] }}" {{ old('lab_id') == $lab['id'] ? 'selected' : '' }}>
                                {{ $lab['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Katalog BHP</label>
                    <select name="item_catalog_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
                        <option value="">Item baru manual</option>
                        @foreach($catalogs as $catalog)
                            <option value="{{ $catalog['id'] }}" {{ old('item_catalog_id') == $catalog['id'] ? 'selected' : '' }}>
                                {{ $catalog['name'] }} ({{ $catalog['unit'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Nama item baru</label>
                    <input
                        name="item_name"
                        value="{{ old('item_name') }}"
                        class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                        placeholder="Isi kalau tidak pilih katalog"
                    >
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Unit</label>
                        <input
                            name="unit"
                            value="{{ old('unit', 'pcs') }}"
                            class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                            required
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Stok awal</label>
                        <input
                            type="number"
                            min="0"
                            name="initial_stock"
                            value="{{ old('initial_stock', 0) }}"
                            class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                            required
                        >
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Min</label>
                        <input
                            type="number"
                            min="0"
                            name="minimum_stock"
                            value="{{ old('minimum_stock', 0) }}"
                            class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                            required
                        >
                    </div>
                </div>

                <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                    Simpan BHP
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-4">Riwayat Stok</h2>

            @if(empty($movements))
                <p class="text-sm text-slate-400">Belum ada riwayat untuk stok yang dipilih.</p>
            @else
                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1">
                    @foreach($movements as $movement)
                        <div class="rounded-xl border border-slate-100 p-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-700">
                                    {{ $movementLabels[$movement['movement_type']] ?? $movement['movement_type'] }}
                                </span>
                                <span class="text-xs font-mono text-slate-500">
                                    {{ $movement['quantity'] }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-400 mt-1">
                                {{ $movement['movement_date'] ?? '-' }} · {{ $movement['performed_by_name'] ?? '-' }}
                            </p>

                            @if(!empty($movement['note']))
                                <p class="text-xs text-slate-500 mt-1">{{ $movement['note'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden xl:col-span-2 self-start" x-data="tablePagination({{ count($stocks) }})">
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-800">Daftar Stok BHP</p>
                    <p class="text-xs text-slate-400">{{ count($stocks) }} item stok</p>
                </div>

                <form method="GET" action="{{ route('bhp') }}" class="flex gap-2 items-center">
                    <div class="relative">
                        <input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari BHP"
                            class="rounded-xl border-slate-200 text-sm pr-9"
                        >

                        @if(request()->filled('search'))
                            <a
                                href="{{ route('bhp') }}"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none"
                                title="Reset pencarian"
                            >
                                ×
                            </a>
                        @endif
                    </div>

                    <label class="flex items-center gap-1 text-xs text-slate-500">
                        <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}>
                        stok rendah
                    </label>

                    <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4">
                        Filter
                    </button>
                </form>
            </div>

            <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
                <x-table-filter column="status" label="Status Stok" :options="[
                    'aman' => 'Aman',
                    'menipis' => 'Menipis',
                    'kritis' => 'Kritis',
                    'habis' => 'Habis'
                ]" />

                <x-table-filter column="lab" label="Laboratorium" :options="collect($laboratories)->pluck('name', 'id')->toArray()" />

                <button
                    type="button"
                    @click="resetFilters()"
                    x-show="Object.values(filters).some(v => v !== '')"
                    class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit"
                    x-cloak
                >
                    Reset Filter
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="item">Item</x-sort-header>
                        <x-sort-header field="stock">Stok</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <x-sort-header field="lab">Lab</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody x-data="{ moveId: null, editId: null }">
                    @forelse($stocks as $index => $stock)
                        @php
                            $stockStatusKey = $normalizeStockStatus($stock['stock_status'] ?? '');
                            $stockStatusLabel = $statusLabels[$stockStatusKey] ?? ucfirst(str_replace('_', ' ', $stockStatusKey));
                            $stockStatusClass = $statusClass[$stockStatusKey] ?? 'badge-draft';
                            $stockLabId = $stock['lab_id'] ?? $stock['laboratory_id'] ?? '';
                            $stockLabName = $stock['laboratory_name'] ?? $stock['lab_name'] ?? '-';
                        @endphp

                        <tr
                            x-show="showRow({{ $index }})"
                            x-cloak
                            data-filter-status="{{ $stockStatusKey }}"
                            data-filter-lab="{{ $stockLabId }}"
                        >
                            <td>
                                <div class="font-semibold text-slate-800">{{ $stock['item_name'] ?? '-' }}</div>
                                <a
                                    href="{{ route('bhp', array_merge(request()->query(), ['stock_id' => $stock['id']])) }}"
                                    class="text-xs text-indigo-600 font-semibold"
                                >
                                    lihat riwayat
                                </a>
                            </td>

                            <td>
                                <div class="font-bold text-slate-900">
                                    {{ $stock['current_stock'] ?? 0 }} {{ $stock['unit'] ?? '' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    Minimum: {{ $stock['minimum_stock'] ?? 0 }} {{ $stock['unit'] ?? '' }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $stockStatusClass }} text-xs">
                                    {{ $stockStatusLabel }}
                                </span>
                            </td>

                            <td class="text-slate-500">{{ $stockLabName }}</td>

                            <td class="space-x-2 whitespace-nowrap">
                                <button
                                    type="button"
                                    @click="moveId = (moveId === {{ $stock['id'] }} ? null : {{ $stock['id'] }}); editId = null"
                                    class="text-xs font-semibold text-indigo-600"
                                >
                                    Stok
                                </button>

                                <button
                                    type="button"
                                    @click="editId = (editId === {{ $stock['id'] }} ? null : {{ $stock['id'] }}); moveId = null"
                                    class="text-xs font-semibold text-slate-600"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>

                        <tr
                            x-show="showRow({{ $index }}) && moveId === {{ $stock['id'] }}"
                            x-cloak
                            class="bg-slate-50"
                            data-filter-status="{{ $stockStatusKey }}"
                            data-filter-lab="{{ $stockLabId }}"
                        >
                            <td colspan="5">
                                <form action="{{ route('bhp.movement', $stock['id']) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3">
                                    @csrf

                                    <select name="movement_type" class="rounded-xl border-slate-200 text-sm" required>
                                        <option value="in">Tambah stok</option>
                                        <option value="out">Kurangi stok</option>
                                        <option value="adjustment">Set stok jadi angka ini</option>
                                    </select>

                                    <input
                                        type="number"
                                        min="1"
                                        name="quantity"
                                        class="rounded-xl border-slate-200 text-sm"
                                        placeholder="Jumlah"
                                        required
                                    >

                                    <input
                                        name="note"
                                        class="rounded-xl border-slate-200 text-sm"
                                        placeholder="Catatan"
                                    >

                                    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <tr
                            x-show="showRow({{ $index }}) && editId === {{ $stock['id'] }}"
                            x-cloak
                            class="bg-slate-50"
                            data-filter-status="{{ $stockStatusKey }}"
                            data-filter-lab="{{ $stockLabId }}"
                        >
                            <td colspan="5">
                                <form action="{{ route('bhp.update', $stock['id']) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3">
                                    @csrf
                                    @method('PUT')

                                    <select name="lab_id" class="rounded-xl border-slate-200 text-sm">
                                        @foreach($laboratories as $lab)
                                            <option value="{{ $lab['id'] }}" {{ (string)$stockLabId === (string)$lab['id'] ? 'selected' : '' }}>
                                                {{ $lab['name'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input
                                        name="unit"
                                        value="{{ $stock['unit'] ?? '' }}"
                                        class="rounded-xl border-slate-200 text-sm"
                                        required
                                    >

                                    <input
                                        type="number"
                                        min="0"
                                        name="minimum_stock"
                                        value="{{ $stock['minimum_stock'] ?? 0 }}"
                                        class="rounded-xl border-slate-200 text-sm"
                                        required
                                    >

                                    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-10">
                                Belum ada stok BHP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($stocks) > 0)
            <x-pagination :total="count($stocks)" />
        @endif
    </div>
</div>
@endsection