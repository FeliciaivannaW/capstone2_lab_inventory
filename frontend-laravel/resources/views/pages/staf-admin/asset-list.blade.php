@extends('layouts.app')
@section('title', 'Pelacakan Siklus Barang')

@section('content')
@php
    // Fixed lifecycle mapping — pakai status DB yang benar
    function assetStageIndex(string $status, string $condition): int {
        return match($status) {
            'received'             => 0,
            'labeled'              => 1,
            'available', 'in_use'  => 2,
            'maintenance'          => 3,
            'disposed', 'replaced' => 4,
            default                => 0,
        };
    }

    $stages = [
        ['key' => 'received',            'label' => 'Diterima',   'color' => 'blue',   'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4'],
        ['key' => 'labeled',             'label' => 'Berlabel',   'color' => 'indigo', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ['key' => 'available,in_use',    'label' => 'Aktif',      'color' => 'emerald','icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['key' => 'maintenance',         'label' => 'Maintenance','color' => 'amber',  'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['key' => 'disposed,replaced',   'label' => 'Selesai',    'color' => 'slate',  'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    ];

    $stageColors = [
        'blue'   => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'ring' => 'ring-blue-300',    'dot' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700 border-blue-200'],
        'indigo' => ['bg' => 'bg-indigo-100',  'text' => 'text-indigo-700',  'ring' => 'ring-indigo-300',  'dot' => 'bg-indigo-500',  'badge' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
        'emerald'=> ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-300', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
        'amber'  => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'ring' => 'ring-amber-300',   'dot' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-700 border-amber-200'],
        'slate'  => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'ring' => 'ring-slate-300',   'dot' => 'bg-slate-400',   'badge' => 'bg-slate-100 text-slate-600 border-slate-200'],
    ];

    // Hitung jumlah per stage
    $stageCounts = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];
    foreach ($assets as $a) {
        $idx = assetStageIndex($a['status'] ?? '', $a['asset_condition'] ?? '');
        $stageCounts[$idx] = ($stageCounts[$idx] ?? 0) + 1;
    }

    $activeStage = request('stage', '');
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Pelacakan Siklus Barang</h1>
        <p class="text-sm text-slate-500 mt-0.5">Riwayat lengkap siklus hidup setiap aset dari penerimaan hingga penghapusan.</p>
    </div>
    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">{{ count($assets) }} aset total</span>
</div>

{{-- Stage summary cards --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
    @foreach($stages as $sIdx => $stage)
    @php $c = $stageColors[$stage['color']]; @endphp
    <a href="{{ route('staf-admin.asset-list', array_merge(request()->except('stage'), ['stage' => $activeStage == $sIdx ? '' : $sIdx])) }}"
       class="glass-card rounded-xl p-3.5 flex items-center gap-3 transition-all hover:shadow-md
              {{ $activeStage === (string)$sIdx ? 'ring-2 ' . $c['ring'] . ' shadow-sm' : '' }}">
        <div class="w-8 h-8 rounded-lg {{ $c['bg'] }} flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stage['icon'] }}"/>
            </svg>
        </div>
        <div>
            <p class="text-lg font-bold text-slate-900 leading-none">{{ $stageCounts[$sIdx] }}</p>
            <p class="text-[10px] font-semibold {{ $c['text'] }} mt-0.5">{{ $stage['label'] }}</p>
        </div>
    </a>
    @endforeach
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('staf-admin.asset-list') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-3">
    <input type="hidden" name="stage" value="{{ $activeStage }}">
    <div class="flex-[2] min-w-[180px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode aset, nama..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    </div>
    <div class="flex-1 min-w-[150px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Laboratorium</label>
        <select name="lab_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Lab</option>
            @foreach($labs ?? [] as $lab)
                <option value="{{ $lab['id'] }}" {{ ($filters['lab_id'] ?? '') == $lab['id'] ? 'selected' : '' }}>{{ $lab['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Kondisi</label>
        <select name="condition" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Kondisi</option>
            @foreach(['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'maintenance' => 'Maintenance'] as $val => $lbl)
                <option value="{{ $val }}" {{ ($filters['condition'] ?? '') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
        <a href="{{ route('staf-admin.asset-list') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Reset</a>
    </div>
</form>

{{-- Table --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($filteredAssets ?? $assets) }})">

    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">
            @if($activeStage !== '')
                {{ $stageCounts[(int)$activeStage] ?? 0 }} aset
                <span class="font-normal text-slate-400">— {{ $stages[(int)$activeStage]['label'] ?? '' }}</span>
            @else
                {{ count($assets) }} aset ditemukan
            @endif
        </p>
        @if($activeStage !== '')
            <a href="{{ route('staf-admin.asset-list', request()->except('stage')) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                Lihat semua →
            </a>
        @endif
    </div>

    @php
        // Apply stage filter client-side via data attributes
        $displayAssets = $assets;
    @endphp

    @if(empty($displayAssets))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Belum ada data aset</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="num">#</x-sort-header>
                        <x-sort-header field="code">Kode Aset</x-sort-header>
                        <x-sort-header field="name">Nama Barang</x-sort-header>
                        <x-sort-header field="lab">Lab</x-sort-header>
                        <x-sort-header field="condition">Kondisi</x-sort-header>
                        <x-sort-header field="stage">Tahap Siklus</x-sort-header>
                        <x-sort-header field="date">Tgl Terima</x-sort-header>
                        <x-sort-header field="label">Label</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayAssets as $i => $asset)
                    @php
                        $stageIdx = assetStageIndex($asset['status'] ?? '', $asset['asset_condition'] ?? '');
                        $stage    = $stages[$stageIdx];
                        $sc       = $stageColors[$stage['color']];

                        $condColors = [
                            'baik'         => 'bg-emerald-100 text-emerald-700',
                            'rusak_ringan' => 'bg-amber-100 text-amber-700',
                            'rusak_berat'  => 'bg-red-100 text-red-700',
                            'maintenance'  => 'bg-indigo-100 text-indigo-700',
                            'dihapus'      => 'bg-slate-100 text-slate-600',
                            'diganti'      => 'bg-slate-100 text-slate-600',
                        ];
                        $condColor = $condColors[$asset['asset_condition'] ?? ''] ?? 'bg-slate-100 text-slate-600';

                        // Filter: jika ada stage filter, sembunyikan baris yang tidak sesuai
                        $showRow = ($activeStage === '' || (string)$stageIdx === (string)$activeStage);
                    @endphp
                    @if(!$showRow) @continue @endif
                    <tr x-show="showRow({{ $i }})" x-cloak>
                        <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                        <td>
                            <code class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $asset['asset_code'] }}</code>
                        </td>
                        <td>
                            <p class="text-sm font-semibold text-slate-800">{{ $asset['item_name'] ?? '—' }}</p>
                            @if($asset['category_name'] ?? null)
                                <p class="text-[11px] text-slate-400">{{ $asset['category_name'] }}</p>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500">{{ $asset['lab_name'] ?? '—' }}</td>
                        <td>
                            <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $condColor }}">
                                {{ str_replace('_', ' ', ucfirst($asset['asset_condition'] ?? '—')) }}
                            </span>
                        </td>
                        <td>
                            {{-- Single stage badge (bukan breadcrumb) --}}
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $sc['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                {{ $stage['label'] }}
                            </span>
                        </td>
                        <td class="text-xs text-slate-500 whitespace-nowrap">
                            {{ $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—' }}
                        </td>
                        <td>
                            @if($asset['label_number'] ?? null)
                                <span class="text-xs font-mono font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg">{{ $asset['label_number'] }}</span>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-600 hover:text-violet-800 bg-violet-50 hover:bg-violet-100 px-3 py-1.5 rounded-xl border border-violet-200 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Timeline
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-pagination :total="count($displayAssets)" />
    @endif
</div>

@endsection
