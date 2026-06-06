@extends('layouts.app')

@section('title', $activeTab === 'bhp' ? 'Katalog BHP' : 'Katalog Inventaris')

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

    $bhpStatusMeta = [
        'aman'    => ['label' => 'Aman',    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'card' => 'text-emerald-600'],
        'menipis' => ['label' => 'Menipis', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200',   'card' => 'text-amber-600'],
        'kritis'  => ['label' => 'Kritis',  'badge' => 'bg-orange-50 text-orange-700 border-orange-200', 'card' => 'text-orange-600'],
        'habis'   => ['label' => 'Habis',   'badge' => 'bg-red-50 text-red-700 border-red-200',          'card' => 'text-red-600'],
    ];
@endphp

{{-- Page Header --}}
<div class="mb-5 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Katalog Laboratorium</h1>
        <p class="text-sm text-slate-500 mt-1">Data inventaris aset dan bahan habis pakai laboratorium.</p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
        Read-only katalog
    </span>
</div>

{{-- Tab Navigation --}}
<div class="flex gap-0 border-b border-slate-200 mb-6">
    <a href="{{ route('inventory', ['tab' => 'inventaris']) }}"
       class="px-5 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px
              {{ $activeTab === 'inventaris'
                  ? 'text-indigo-600 border-indigo-600'
                  : 'text-slate-500 border-transparent hover:text-slate-800 hover:border-slate-300' }}">
        Katalog Inventaris
    </a>
    <a href="{{ route('inventory', ['tab' => 'bhp']) }}"
       class="px-5 py-2.5 text-sm font-semibold transition-colors border-b-2 -mb-px
              {{ $activeTab === 'bhp'
                  ? 'text-indigo-600 border-indigo-600'
                  : 'text-slate-500 border-transparent hover:text-slate-800 hover:border-slate-300' }}">
        Katalog BHP
    </a>
</div>

{{-- ============================================================ --}}
{{-- TAB: KATALOG INVENTARIS                                      --}}
{{-- ============================================================ --}}
@if($activeTab !== 'bhp')

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
            @php $meta = $conditionMeta[$condition] ?? ['label' => ucfirst($condition), 'class' => 'badge-draft']; @endphp
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
    <input type="hidden" name="tab" value="inventaris">
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
            <option value="labeled"   {{ ($filters['label_status'] ?? '') === 'labeled'   ? 'selected' : '' }}>Sudah berlabel</option>
            <option value="unlabeled" {{ ($filters['label_status'] ?? '') === 'unlabeled' ? 'selected' : '' }}>Belum berlabel</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">Filter</button>
        <a href="{{ route('inventory', ['tab' => 'inventaris']) }}"
           class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200">Reset</a>
    </div>
</form>

<div class="glass-card rounded-2xl overflow-hidden" x-data="{ showModal: false, activeAsset: {} }">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">{{ count($assets ?? []) }} aset ditemukan</p>
        @if(in_array($role ?? session('auth_user')['role'] ?? '', ['staf_laboratorium']))
            <p class="text-xs text-slate-400">Klik baris untuk melihat detail dan edit kondisi</p>
        @else
            <p class="text-xs text-slate-400">Klik baris untuk melihat detail aset</p>
        @endif
    </div>

    @if(empty($assets))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl mb-4">📦</div>
            <p class="text-sm font-medium text-slate-500">Belum ada data aset inventaris</p>
            <p class="text-xs text-slate-400 mt-1">Coba cek filter yang dipakai.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Barang</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $i => $asset)
                        @php
                            $condition = $conditionMeta[$asset['asset_condition'] ?? ''] ?? ['label' => ucfirst($asset['asset_condition'] ?? '-'), 'class' => 'badge-draft'];
                            $status    = $statusMeta[$asset['status'] ?? '']            ?? ['label' => ucfirst($asset['status'] ?? '-'),            'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                        @endphp
                        <tr @click="activeAsset = JSON.parse($el.dataset.asset); showModal = true"
                            data-asset="{{ json_encode([
                                'id'              => $asset['id'],
                                'asset_code'      => $asset['asset_code']      ?? '-',
                                'item_name'       => $asset['item_name']       ?? '-',
                                'category_name'   => $asset['category_name']   ?? '-',
                                'lab_name'        => $asset['lab_name']        ?? '-',
                                'room_name'       => $asset['room_name']       ?? '-',
                                'room_code'       => $asset['room_code']       ?? '',
                                'label_number'    => $asset['label_number']    ?? '',
                                'asset_condition' => $asset['asset_condition'] ?? '',
                                'status'          => $asset['status']          ?? '',
                                'received_date'   => !empty($asset['received_date']) ? \Carbon\Carbon::parse($asset['received_date'])->format('d M Y') : '-',
                                'condition_label' => $condition['label'],
                                'condition_class' => $condition['class'],
                                'status_label'    => $status['label'],
                                'status_class'    => $status['class'],
                            ]) }}"
                            class="cursor-pointer hover:bg-slate-50/80 transition-colors">
                            <td class="text-center text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ $asset['item_name'] ?? '-' }}</div>
                                <div class="text-xs font-mono text-slate-500 mt-0.5">{{ $asset['asset_code'] ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-medium text-slate-700 text-sm">{{ $asset['lab_name'] ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $asset['room_name'] ?? '-' }} {{ !empty($asset['room_code']) ? '('.$asset['room_code'].')' : '' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $condition['class'] }} text-xs">{{ $condition['label'] }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold border rounded-md {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="text-right text-slate-400">
                                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Modal Detail & Edit -->
    <template x-teleport="body">
        <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 @click="showModal = false"
                 x-show="showModal" x-transition.opacity.duration.300ms></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg m-auto flex flex-col max-h-[90vh]"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <h3 class="text-lg font-bold text-slate-900">Detail Aset Inventaris</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-full hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-5">
                    <div>
                        <h4 class="text-xl font-bold text-slate-800" x-text="activeAsset.item_name"></h4>
                        <p class="font-mono text-sm text-slate-500 mt-1" x-text="activeAsset.asset_code"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</p>
                            <p class="text-sm font-semibold text-slate-700" x-text="activeAsset.category_name"></p>
                        </div>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Terima</p>
                            <p class="text-sm font-semibold text-slate-700" x-text="activeAsset.received_date"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Laboratorium</p>
                            <p class="text-sm font-semibold text-slate-700" x-text="activeAsset.lab_name"></p>
                        </div>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Ruangan</p>
                            <p class="text-sm font-semibold text-slate-700">
                                <span x-text="activeAsset.room_name"></span>
                                <span class="text-slate-400 text-xs font-mono ml-1" x-show="activeAsset.room_code" x-text="'('+activeAsset.room_code+')'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Label Aset</p>
                            <template x-if="activeAsset.label_number">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md" x-text="activeAsset.label_number"></span>
                            </template>
                            <template x-if="!activeAsset.label_number">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200 rounded-md">Belum berlabel</span>
                            </template>
                        </div>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</p>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold border rounded-md" :class="activeAsset.status_class" x-text="activeAsset.status_label"></span>
                        </div>
                    </div>

                    @if(in_array($role ?? session('auth_user')['role'] ?? '', ['staf_laboratorium']))
                    <div class="pt-4 mt-2 border-t border-slate-200">
                        <h5 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Update Kondisi Aset
                        </h5>
                        <form :action="'/inventory/' + activeAsset.id + '/condition'" method="POST" class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Baru</label>
                                <select name="asset_condition" x-model="activeAsset.asset_condition" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 bg-white" required>
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="dihapus">Dihapus</option>
                                    <option value="diganti">Diganti</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                                <input name="note" class="w-full rounded-xl border-slate-200 text-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Contoh: Terjatuh saat praktikum...">
                            </div>
                            <div class="pt-2 text-right">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl text-sm transition-colors shadow-sm w-full sm:w-auto">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="pt-4 mt-2 border-t border-slate-200">
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-2">Kondisi Saat Ini</p>
                        <span class="badge px-3 py-1 text-sm" :class="activeAsset.condition_class" x-text="activeAsset.condition_label"></span>
                    </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0 rounded-b-2xl">
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

{{-- ============================================================ --}}
{{-- TAB: KATALOG BHP                                             --}}
{{-- ============================================================ --}}
@else

{{-- Summary Cards --}}
<div class="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Total Item BHP</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ count($bhpStocks) }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Aman</p>
        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $bhpByStatus['aman'] ?? 0 }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Menipis</p>
        <p class="text-3xl font-bold text-amber-500 mt-1">{{ $bhpByStatus['menipis'] ?? 0 }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Kritis</p>
        <p class="text-3xl font-bold text-orange-500 mt-1">{{ $bhpByStatus['kritis'] ?? 0 }}</p>
    </div>
    <div class="glass-card rounded-2xl p-5">
        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Habis</p>
        <p class="text-3xl font-bold text-red-600 mt-1">{{ $bhpByStatus['habis'] ?? 0 }}</p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('inventory') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    <input type="hidden" name="tab" value="bhp">
    <div class="flex-[2] min-w-[180px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Item BHP</label>
        <input type="text" name="bhp_search" value="{{ $bhpFilters['bhp_search'] ?? '' }}"
               placeholder="Nama item atau laboratorium..."
               class="w-full rounded-xl border-slate-200 text-sm">
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="bhp_lab_id" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Lab</option>
            @foreach($labs as $lab)
                <option value="{{ $lab['id'] }}" {{ ($bhpFilters['bhp_lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>
                    {{ $lab['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status Stok</label>
        <select name="bhp_status" class="w-full rounded-xl border-slate-200 text-sm">
            <option value="">Semua Status</option>
            @foreach($bhpStatusMeta as $value => $meta)
                <option value="{{ $value }}" {{ ($bhpFilters['bhp_status'] ?? '') === $value ? 'selected' : '' }}>
                    {{ $meta['label'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">Filter</button>
        <a href="{{ route('inventory', ['tab' => 'bhp']) }}"
           class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200">Reset</a>
    </div>
</form>

{{-- BHP Table --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">{{ count($bhpStocks) }} item BHP ditemukan</p>
        <p class="text-xs text-slate-400">Data stok bahan habis pakai per laboratorium</p>
    </div>

    @if(empty($bhpStocks))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl mb-4">🧴</div>
            <p class="text-sm font-medium text-slate-500">Belum ada data stok BHP</p>
            <p class="text-xs text-slate-400 mt-1">Coba cek filter yang dipakai.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Nama Item</th>
                        <th>Laboratorium</th>
                        <th class="text-right">Stok Saat Ini</th>
                        <th class="text-right">Min. Stok</th>
                        <th>Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bhpStocks as $i => $stock)
                        @php
                            $st   = $stock['stock_status'] ?? 'aman';
                            $meta = $bhpStatusMeta[$st] ?? ['label' => ucfirst($st), 'badge' => 'bg-slate-100 text-slate-600 border-slate-200'];
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="text-center text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ $stock['item_name'] ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-medium text-slate-700 text-sm">{{ $stock['laboratory_name'] ?? '-' }}</div>
                                <div class="text-xs font-mono text-slate-400 mt-0.5">{{ $stock['laboratory_code'] ?? '' }}</div>
                            </td>
                            <td class="text-right">
                                <span class="font-bold text-slate-900 tabular-nums">{{ number_format($stock['current_stock'] ?? 0) }}</span>
                            </td>
                            <td class="text-right">
                                <span class="text-slate-500 tabular-nums text-sm">{{ number_format($stock['minimum_stock'] ?? 0) }}</span>
                            </td>
                            <td>
                                <span class="text-slate-600 text-sm">{{ $stock['unit'] ?? $stock['catalog_unit'] ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold border rounded-md {{ $meta['badge'] }}">
                                    {{ $meta['label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endif
@endsection
