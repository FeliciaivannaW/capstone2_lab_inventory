@extends('layouts.app')

@section('title', 'Pelacakan Siklus Barang')

@section('content')

{{-- Alerts --}}
@if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
@endif

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Pelacakan Siklus Barang</h1>
    <p class="text-sm text-slate-500 mt-1">Riwayat lengkap siklus hidup setiap aset inventaris dari pengadaan hingga penghapusan.</p>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('staf-admin.asset-list') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    <div class="flex-[2] min-w-[200px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode, nama..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="lab_id"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Lab</option>
            @foreach($labs ?? [] as $lab)
                <option value="{{ $lab['id'] }}" {{ ($filters['lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>
                    {{ $lab['name'] }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi</label>
        <select name="condition"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua</option>
            @foreach(['baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti'] as $cond)
                <option value="{{ $cond }}" {{ ($filters['condition'] ?? '') == $cond ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', ucfirst($cond)) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filter
        </button>
        <a href="{{ route('staf-admin.asset-list') }}"
           class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Table --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($assets ?? []) }})">
    @php
        $categories = collect($assets)->pluck('category_name')->unique()->filter()->values()->toArray();
        $categoryOptions = count($categories) ? array_combine($categories, $categories) : [];
        $conditions = collect($assets)->pluck('asset_condition')->unique()->filter()->values()->toArray();
        $conditionOptions = count($conditions) ? array_combine($conditions, collect($conditions)->map(fn($c) => str_replace('_', ' ', ucfirst($c)))->toArray()) : [];
        $statuses = collect($assets)->pluck('status')->unique()->filter()->values()->toArray();
        $statusOptions = count($statuses) ? array_combine($statuses, collect($statuses)->map(fn($s) => str_replace('_', ' ', ucfirst($s)))->toArray()) : [];
    @endphp
    <div class="px-6 py-4 border-b border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-700">{{ count($assets ?? []) }} aset ditemukan</p>
        </div>
        <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
            <x-table-filter column="category" label="Kategori" :options="$categoryOptions" />
            <x-table-filter column="condition" label="Kondisi" :options="$conditionOptions" />
            <x-table-filter column="status" label="Status" :options="$statusOptions" />
            <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>
                Reset Filter
            </button>
        </div>
    </div>

    @if(empty($assets))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Belum ada data aset inventaris</p>
            <p class="text-xs text-slate-400 mt-1">Data muncul setelah barang diterima dan dicatat.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="num">#</x-sort-header>
                        <x-sort-header field="code">Kode Aset</x-sort-header>
                        <x-sort-header field="name">Nama</x-sort-header>
                        <x-sort-header field="category">Kategori</x-sort-header>
                        <x-sort-header field="condition">Kondisi</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <x-sort-header field="date">Tgl Terima</x-sort-header>
                        <x-sort-header field="cycle">Siklus</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $i => $asset)
                        @php
                            $condMap = [
                                'baik'         => 'badge-approved',
                                'rusak_ringan' => 'badge-pending',
                                'rusak_berat'  => 'badge-rejected',
                                'maintenance'  => 'badge-active',
                                'dihapus'      => 'badge-rejected',
                                'diganti'      => 'badge-draft',
                            ];
                            $condClass = $condMap[$asset['asset_condition']] ?? 'badge-draft';

                            // Lifecycle stages: Diterima → Berlabel → Aktif → Maintenance → Diganti/Dihapus
                            $lcStages = [
                                ['key' => 'received',    'label' => 'Diterima'],
                                ['key' => 'labeled',     'label' => 'Berlabel'],
                                ['key' => 'active',      'label' => 'Aktif'],
                                ['key' => 'maintenance', 'label' => 'Maint.'],
                                ['key' => 'replaced',    'label' => 'Selesai'],
                            ];
                            $lcCurrent = match($asset['status'] ?? '') {
                                'procured','received' => 0,
                                'labeled'             => 1,
                                'active'              => 2,
                                'maintenance'         => 3,
                                default               => in_array($asset['asset_condition'] ?? '', ['dihapus','diganti']) ? 4 : 2,
                            };
                        @endphp
                        <tr x-show="showRow({{ $i }})" x-cloak data-filter-category="{{ $asset['category_name'] ?? '—' }}" data-filter-condition="{{ $asset['asset_condition'] }}" data-filter-status="{{ $asset['status'] ?? '—' }}">
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $asset['asset_code'] }}
                                </span>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $asset['item_name'] }}</td>
                            <td class="text-slate-500 text-xs">{{ $asset['category_name'] ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $condClass }} text-xs">
                                    {{ str_replace('_', ' ', ucfirst($asset['asset_condition'])) }}
                                </span>
                            </td>
                            <td class="text-slate-500 text-xs">
                                {{ ucfirst(str_replace('_', ' ', $asset['status'] ?? '—')) }}
                            </td>
                            <td class="text-slate-500 text-xs">
                                {{ $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—' }}
                            </td>
                            <td style="min-width:240px;">
                                {{-- Labeled sequential lifecycle chips: past=green, current=indigo, future=gray --}}
                                <div class="flex items-center gap-0.5">
                                    @foreach($lcStages as $sIdx => $stage)
                                        @php
                                            $isPast    = $sIdx < $lcCurrent;
                                            $isCurrent = $sIdx === $lcCurrent;
                                            $color     = $isCurrent ? 'bg-indigo-500 text-white shadow-sm'
                                                         : ($isPast ? 'bg-emerald-100 text-emerald-700'
                                                                    : 'bg-slate-100 text-slate-400');
                                        @endphp
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[0.6rem] font-bold {{ $color }} whitespace-nowrap">
                                            {{ $stage['label'] }}
                                        </span>
                                        @if($sIdx < count($lcStages) - 1)
                                            <span class="text-slate-300 text-[0.5rem]">›</span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-600 hover:text-violet-800 bg-violet-50 hover:bg-violet-100 px-3 py-1.5 rounded-lg border border-violet-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Timeline
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($assets ?? []) > 0)
            <x-pagination :total="count($assets)" />
        @endif
    @endif
</div>
@endsection
