@extends('layouts.app')

@section('title', 'Pelacakan Siklus Barang')

@section('content')

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
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($assets ?? []) }} aset ditemukan</p>
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
                        <tr x-show="showRow({{ $i }})" x-cloak>
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
