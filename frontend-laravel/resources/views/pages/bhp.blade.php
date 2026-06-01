@extends('layouts.app')

@section('title', 'Stok BHP')

@section('content')
<div x-data="{ 
    activeModal: '{{ request('stock_id') ? 'riwayat_' . request('stock_id') : '' }}',
    riwayatData: [],
    riwayatLoading: false,
    riwayatStockName: '',
    riwayatStockUnit: '',
    movementLabels: {
        'in': 'Masuk',
        'out': 'Keluar',
        'adjustment': 'Set Stok',
        'maintenance_usage': 'Maintenance'
    },
    formatDate(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    },
    async fetchRiwayat(id, name, unit) {
        this.riwayatStockName = name;
        this.riwayatStockUnit = unit;
        this.activeModal = 'riwayat_modal';
        this.riwayatLoading = true;
        this.riwayatData = [];
        try {
            const res = await fetch('/bhp/' + id + '/movements');
            if (res.ok) {
                this.riwayatData = await res.json();
            }
        } catch(e) {}
        this.riwayatLoading = false;
    }
}">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Stok BHP</h1>
            <p class="text-sm text-slate-500 mt-1">Tambah stok, kurangi stok, set stok, dan lihat riwayat pergerakan.</p>
        </div>
        <button type="button" @click="activeModal = 'tambah_bhp'" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-5 py-2.5 hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Item BHP
        </button>
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

    <div class="glass-card rounded-2xl overflow-hidden self-start" x-data="tablePagination({{ count($stocks) }})">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col xl:flex-row gap-4 xl:items-center justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800">Daftar Stok BHP</p>
                <p class="text-xs text-slate-400">{{ count($stocks) }} item stok</p>
            </div>

            <div class="flex flex-wrap items-center flex-1 justify-end gap-3 w-full">
                <x-table-filter column="status" label="Status Stok" :options="[
                    'aman' => 'Aman',
                    'menipis' => 'Menipis',
                    'kritis' => 'Kritis',
                    'habis' => 'Habis'
                ]" />

                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl px-3 py-1.5 cursor-pointer hover:bg-slate-50 transition-colors shadow-sm h-[32px]">
                    <input type="checkbox" @change="setFilter('lowStock', $event.target.checked ? '1' : '')" :checked="filters['lowStock'] === '1'" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                    Stok Rendah
                </label>

                <x-table-filter column="lab" label="Laboratorium" :options="collect($laboratories)->pluck('name', 'id')->toArray()" />

                <button
                    type="button"
                    @click="resetFilters(); searchQuery = ''"
                    x-show="Object.values(filters).some(v => v !== '') || searchQuery !== ''"
                    class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors h-[32px] px-2"
                    x-cloak
                >
                    Reset Filter
                </button>

                <div class="relative ml-auto">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input="applyFiltersAndSorting()"
                        placeholder="Cari BHP"
                        class="rounded-xl border-slate-200 text-xs px-3 py-1.5 pr-8 min-w-[200px] shadow-sm h-[32px]"
                    >
                    <button
                        type="button"
                        x-show="searchQuery.length > 0"
                        @click="searchQuery = ''; applyFiltersAndSorting()"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-sm leading-none"
                        title="Reset pencarian"
                        x-cloak
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th class="w-1/4 cursor-pointer group" @click="sortBy('item', $el)" data-sort-field="item">
                            <div class="flex items-center gap-2">
                                Item
                                <span class="text-slate-300 group-hover:text-indigo-400 transition-colors" 
                                      x-show="sortField === 'item'" x-cloak
                                      x-text="sortAsc ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="w-1/4 cursor-pointer group" @click="sortBy('stock', $el)" data-sort-field="stock">
                            <div class="flex items-center gap-2">
                                Stok
                                <span class="text-slate-300 group-hover:text-indigo-400 transition-colors" 
                                      x-show="sortField === 'stock'" x-cloak
                                      x-text="sortAsc ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="w-1/6 cursor-pointer group" @click="sortBy('status', $el)" data-sort-field="status">
                            <div class="flex items-center gap-2">
                                Status
                                <span class="text-slate-300 group-hover:text-indigo-400 transition-colors" 
                                      x-show="sortField === 'status'" x-cloak
                                      x-text="sortAsc ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="w-1/4 cursor-pointer group" @click="sortBy('lab', $el)" data-sort-field="lab">
                            <div class="flex items-center gap-2">
                                Lab
                                <span class="text-slate-300 group-hover:text-indigo-400 transition-colors" 
                                      x-show="sortField === 'lab'" x-cloak
                                      x-text="sortAsc ? '↑' : '↓'"></span>
                            </div>
                        </th>
                        <th class="w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $index => $stock)
                        @php
                            $stockStatusKey = $normalizeStockStatus($stock['stock_status'] ?? 'aman');
                            $stockStatusLabel = ucfirst($stockStatusKey);
                            $stockStatusClass = match($stockStatusKey) {
                                'aman' => 'badge-finalized',
                                'menipis' => 'badge-pending',
                                'kritis', 'habis' => 'badge-rejected',
                                default => 'badge-draft'
                            };
                            $stockLabName = $stock['laboratory_name'] ?? '-';
                            $stockLabId = $stock['lab_id'] ?? '';
                            $isLowStock = ($stock['current_stock'] <= $stock['minimum_stock']) ? '1' : '0';
                        @endphp

                        <tr
                            x-show="showRow({{ $index }})"
                            x-cloak
                            data-filter-status="{{ $stockStatusKey }}"
                            data-filter-lab="{{ $stockLabId }}"
                            data-filter-low-stock="{{ $isLowStock }}"
                        >
                            <td>
                                <div class="font-semibold text-slate-800">{{ $stock['item_name'] ?? '-' }}</div>
                                <a
                                    href="#"
                                    @click.prevent="fetchRiwayat({{ $stock['id'] }}, '{{ $stock['item_name'] }}', '{{ $stock['unit'] }}')"
                                    class="text-xs text-indigo-600 font-semibold hover:text-indigo-800 hover:underline"
                                >
                                    Lihat Riwayat
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
                                    @click="activeModal = 'stok_{{ $stock['id'] }}'"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                                >
                                    Stok
                                </button>

                                <button
                                    type="button"
                                    @click="activeModal = 'edit_{{ $stock['id'] }}'"
                                    class="text-xs font-semibold text-slate-600 hover:text-slate-800"
                                >
                                    Edit
                                </button>
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

    <!-- Modals -->

    <!-- Modal Tambah BHP -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_bhp'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
        <div x-show="activeModal === 'tambah_bhp'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
             @click="activeModal = null">
        </div>

        <div x-show="activeModal === 'tambah_bhp'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800">Tambah Item BHP</h3>
                <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6">
                <form action="{{ route('bhp.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Laboratorium</label>
                        <select name="lab_id" class="w-full rounded-xl border-slate-200 text-sm" required>
                            <option value="">Pilih laboratorium</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab['id'] }}" {{ old('lab_id') == $lab['id'] ? 'selected' : '' }}>
                                    {{ $lab['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Katalog BHP</label>
                        <select name="item_catalog_id" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="">Item baru manual</option>
                            @foreach($catalogs as $catalog)
                                <option value="{{ $catalog['id'] }}" {{ old('item_catalog_id') == $catalog['id'] ? 'selected' : '' }}>
                                    {{ $catalog['name'] }} ({{ $catalog['unit'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nama item baru</label>
                        <input name="item_name" value="{{ old('item_name') }}" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Isi kalau tidak pilih katalog">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Unit</label>
                            <input name="unit" value="{{ old('unit', 'pcs') }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Stok awal</label>
                            <input type="number" min="0" name="initial_stock" value="{{ old('initial_stock', 0) }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Min Stok</label>
                            <input type="number" min="0" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                        </div>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700 transition-colors">
                            Simpan BHP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    <!-- Modal Riwayat Stok (Satu Modal Dinamis) -->
    <template x-teleport="body">
        <div x-show="activeModal === 'riwayat_modal'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'riwayat_modal'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                 @click="activeModal = null">
            </div>

            <div x-show="activeModal === 'riwayat_modal'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
                    <div>
                        <h3 class="font-bold text-slate-800">Riwayat Stok</h3>
                        <p class="text-xs text-slate-500" x-text="riwayatStockName"></p>
                    </div>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div x-show="riwayatLoading" class="text-center py-8">
                        <svg class="w-8 h-8 text-slate-300 animate-spin mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <p class="text-sm text-slate-500">Memuat riwayat...</p>
                    </div>

                    <div x-show="!riwayatLoading">
                        <template x-if="riwayatData.length === 0">
                            <div class="text-center py-8">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm text-slate-500">Belum ada riwayat stok.</p>
                            </div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="movement in riwayatData" :key="movement.id">
                                <div class="rounded-xl border border-slate-100 p-4 hover:border-slate-200 transition-colors">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold px-2 py-1 rounded-md" 
                                              :class="['in', 'adjustment'].includes(movement.movement_type) ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" 
                                              x-text="movementLabels[movement.movement_type] || movement.movement_type">
                                        </span>
                                        <span class="text-sm font-bold text-slate-700" 
                                              x-text="(['in', 'adjustment'].includes(movement.movement_type) ? '+' : '-') + movement.quantity + ' ' + riwayatStockUnit">
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span x-text="formatDate(movement.movement_date)"></span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-1 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span x-text="movement.performed_by_name || '-'"></span>
                                    </p>

                                    <template x-if="movement.note">
                                        <p class="text-xs text-slate-600 mt-2 bg-slate-50 p-2 rounded-lg border border-slate-100" x-text="'&quot;' + movement.note + '&quot;'"></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Modals for each stock row (Edit & Stok) -->
    @foreach($stocks as $stock)
        <!-- Modal Set Stok -->
        <template x-teleport="body">
            <div x-show="activeModal === 'stok_{{ $stock['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'stok_{{ $stock['id'] }}'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                 @click="activeModal = null">
            </div>

            <div x-show="activeModal === 'stok_{{ $stock['id'] }}'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-800">Aksi Stok</h3>
                        <p class="text-xs text-slate-500">{{ $stock['item_name'] ?? '-' }}</p>
                    </div>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <form action="{{ route('bhp.movement', $stock['id']) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">Jenis Aksi</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="movement_type" value="in" class="peer sr-only" required>
                                    <div class="rounded-xl border border-slate-200 p-3 text-center hover:bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all text-slate-600">
                                        <svg class="w-5 h-5 mx-auto mb-1 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        <span class="text-xs font-semibold">Tambah</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="movement_type" value="out" class="peer sr-only" required>
                                    <div class="rounded-xl border border-slate-200 p-3 text-center hover:bg-slate-50 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition-all text-slate-600">
                                        <svg class="w-5 h-5 mx-auto mb-1 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        <span class="text-xs font-semibold">Kurangi</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="movement_type" value="adjustment" class="peer sr-only" required>
                                    <div class="rounded-xl border border-slate-200 p-3 text-center hover:bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 transition-all text-slate-600">
                                        <svg class="w-5 h-5 mx-auto mb-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="text-xs font-semibold">Set Stok</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Jumlah</label>
                            <input type="number" min="1" name="quantity" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Masukkan jumlah" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Catatan (Opsional)</label>
                            <input name="note" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Alasan / catatan">
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700 transition-colors">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Modal Edit -->
        <template x-teleport="body">
            <div x-show="activeModal === 'edit_{{ $stock['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'edit_{{ $stock['id'] }}'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                 @click="activeModal = null">
            </div>

            <div x-show="activeModal === 'edit_{{ $stock['id'] }}'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-800">Edit Data BHP</h3>
                        <p class="text-xs text-slate-500">{{ $stock['item_name'] ?? '-' }}</p>
                    </div>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <form action="{{ route('bhp.update', $stock['id']) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @php
                            $stockLabId = $stock['lab_id'] ?? $stock['laboratory_id'] ?? '';
                        @endphp
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Laboratorium</label>
                            <select name="lab_id" class="w-full rounded-xl border-slate-200 text-sm">
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab['id'] }}" {{ (string)$stockLabId === (string)$lab['id'] ? 'selected' : '' }}>
                                        {{ $lab['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Unit</label>
                                <input name="unit" value="{{ $stock['unit'] ?? '' }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Min Stok</label>
                                <input type="number" min="0" name="minimum_stock" value="{{ $stock['minimum_stock'] ?? 0 }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                            </div>
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700 transition-colors">
                                Update Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    @endforeach

</div>
@endsection