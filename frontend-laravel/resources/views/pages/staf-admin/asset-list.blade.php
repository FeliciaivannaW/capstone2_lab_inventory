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

{{-- Wrapper for Modal State --}}
<div x-data="{ showModal: false, selectedAsset: null }" @open-modal.window="selectedAsset = $event.detail; showModal = true">

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
    <div class="glass-card rounded-2xl overflow-hidden flex flex-col" x-data="tablePagination({{ count($filteredAssets ?? $assets) }})">

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
                <table class="lv-table w-full">
                    <thead>
                        <tr>
                            <x-sort-header field="num">#</x-sort-header>
                            <x-sort-header field="code">Kode Aset</x-sort-header>
                            <x-sort-header field="name">Nama Barang</x-sort-header>
                            <x-sort-header field="lab">Lab</x-sort-header>
                            <x-sort-header field="condition">Kondisi</x-sort-header>
                            <x-sort-header field="date">Tgl Terima</x-sort-header>
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

                            $assetData = [
                                'code' => $asset['asset_code'],
                                'name' => $asset['item_name'] ?? '—',
                                'category' => $asset['category_name'] ?? '—',
                                'lab' => $asset['lab_name'] ?? '—',
                                'condition' => str_replace('_', ' ', ucfirst($asset['asset_condition'] ?? '—')),
                                'conditionColorClass' => $condColor,
                                'stage_label' => $stage['label'],
                                'stage_badge' => $sc['badge'],
                                'stage_dot' => $sc['dot'],
                                'date' => $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—',
                                'label' => $asset['label_number'],
                                'timeline_url' => route('staf-admin.asset-timeline', $asset['id'])
                            ];
                        @endphp
                        @if(!$showRow) @continue @endif
                        <tr x-show="showRow({{ $i }})" x-cloak 
                            class="hover:bg-slate-50 cursor-pointer transition-colors" 
                            @click="$dispatch('open-modal', {{ json_encode($assetData) }})">
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td>
                                <code class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-lg">{{ $asset['asset_code'] }}</code>
                            </td>
                            <td>
                                <p class="text-sm font-semibold text-slate-800 leading-tight truncate max-w-[200px] xl:max-w-[300px]" title="{{ $asset['item_name'] ?? '—' }}">
                                    {{ $asset['item_name'] ?? '—' }}
                                </p>
                                @if(!empty($asset['category_name']))
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $asset['category_name'] }}</p>
                                @else
                                    <p class="text-[11px] text-slate-400 mt-0.5 italic">Tanpa kategori</p>
                                @endif
                            </td>
                            <td class="text-xs text-slate-500">{{ $asset['lab_name'] ?? '—' }}</td>
                            <td>
                                <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full border {{ $condColor }}">
                                    {{ str_replace('_', ' ', ucfirst($asset['asset_condition'] ?? '—')) }}
                                </span>
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}"
                                   @click.stop
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
            
            <div class="px-5 py-3 border-t border-slate-100 bg-white">
                <x-pagination :total="count($displayAssets)" />
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak 
             class="fixed inset-0 z-[100] overflow-y-auto"
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="showModal = false">
            
            {{-- Overlay --}}
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" 
                     @click="showModal = false"
                     aria-hidden="true"></div>

                {{-- Center modal trick --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    {{-- Header --}}
                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-slate-900" id="modal-title">
                            Detail Aset
                        </h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-6">
                        {{-- Identitas Utama --}}
                        <div class="flex items-start gap-4 mb-6">
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-1 leading-snug" x-text="selectedAsset?.name"></h4>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <code class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md" x-text="selectedAsset?.code"></code>
                                    <span class="text-xs text-slate-400">&mdash;</span>
                                    
                                    <template x-if="selectedAsset?.category && selectedAsset.category !== '—'">
                                        <p class="text-xs font-medium text-slate-500" x-text="selectedAsset.category"></p>
                                    </template>
                                    
                                    <template x-if="!selectedAsset?.category || selectedAsset.category === '—'">
                                        <p class="text-xs font-medium text-slate-400 italic">Tanpa kategori</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Info Grid --}}
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Laboratorium</p>
                                <p class="text-sm font-semibold text-slate-800" x-text="selectedAsset?.lab"></p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1">Tanggal Terima</p>
                                <p class="text-sm font-semibold text-slate-800" x-text="selectedAsset?.date"></p>
                            </div>
                        </div>

                        {{-- Status & Siklus --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                                <span class="text-sm font-medium text-slate-500">Kondisi Aset</span>
                                <span :class="selectedAsset?.conditionColorClass" class="px-2.5 py-1 rounded-md text-xs font-semibold border" x-text="selectedAsset?.condition"></span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-slate-100">
                                <span class="text-sm font-medium text-slate-500">Tahap Siklus</span>
                                <div class="flex items-center gap-1.5">
                                    <span :class="selectedAsset?.stage_dot" class="w-1.5 h-1.5 rounded-full"></span>
                                    <span :class="selectedAsset?.stage_badge" class="px-2.5 py-1 rounded-md text-xs font-bold" x-text="selectedAsset?.stage_label"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm font-medium text-slate-500">Nomor Label</span>
                                <template x-if="selectedAsset?.label">
                                    <code class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md" x-text="selectedAsset?.label"></code>
                                </template>
                                <template x-if="!selectedAsset?.label">
                                    <span class="text-xs font-medium italic text-slate-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Belum berlabel
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-between rounded-b-2xl">
                        <button @click="showModal = false" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-200">
                            Tutup
                        </button>
                        <a :href="selectedAsset?.timeline_url" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Lihat Timeline
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
