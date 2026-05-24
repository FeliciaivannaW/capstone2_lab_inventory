@extends('layouts.app')

@section('title', 'Timeline Aset')

@section('content')

{{-- Back --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('staf-admin.asset-list') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar Aset
    </a>
</div>

<div class="mb-7">
    <h1 class="text-2xl font-bold text-slate-900">Timeline Siklus Barang</h1>
    <p class="text-sm text-slate-500 mt-1">Riwayat lengkap dari pengadaan hingga penghapusan.</p>
</div>

{{-- Asset info --}}
<div class="glass-card rounded-2xl p-5 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $assetFields = [
                ['Kode Aset',  $asset['asset_code']],
                ['Nama',       $asset['item_name']],
                ['Kategori',   $asset['category_name'] ?? '—'],
                ['Status',     ucfirst(str_replace('_', ' ', $asset['status'] ?? '—'))],
                ['Ruangan',    $asset['room_name'] ?? '—'],
            ];
            $condMap = ['baik'=>'badge-approved','rusak_ringan'=>'badge-pending','rusak_berat'=>'badge-rejected','maintenance'=>'badge-active','dihapus'=>'badge-rejected','diganti'=>'badge-draft'];
        @endphp
        @foreach($assetFields as [$label, $value])
            <div>
                <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ $label }}</p>
                <p class="text-sm font-semibold text-slate-800">{{ $value }}</p>
            </div>
        @endforeach
        <div>
            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1">Kondisi</p>
            <span class="badge {{ $condMap[$asset['asset_condition']] ?? 'badge-draft' }} text-xs">
                {{ str_replace('_', ' ', ucfirst($asset['asset_condition'])) }}
            </span>
        </div>
    </div>

    {{-- Lifecycle strip --}}
    @php
        $lcStages = [
            ['key' => 'procured',    'label' => 'Procured',    'color' => 'bg-violet-500'],
            ['key' => 'received',    'label' => 'Received',    'color' => 'bg-blue-500'],
            ['key' => 'labeled',     'label' => 'Labeled',     'color' => 'bg-cyan-500'],
            ['key' => 'active',      'label' => 'Active',      'color' => 'bg-emerald-500'],
            ['key' => 'maintenance', 'label' => 'Maintenance', 'color' => 'bg-amber-500'],
            ['key' => 'replaced',    'label' => 'Replaced',    'color' => 'bg-slate-400'],
        ];
        $currentStageIdx = match($asset['status'] ?? '') {
            'procured'    => 0,
            'received'    => 1,
            'labeled'     => 2,
            'active'      => 3,
            'maintenance' => 4,
            default       => in_array($asset['asset_condition'] ?? '', ['dihapus','diganti']) ? 5 : 3,
        };
    @endphp
    <div class="flex items-center gap-0 mt-5 pt-5 border-t border-slate-100 overflow-x-auto pb-1">
        @foreach($lcStages as $i => $stage)
            <div class="flex items-center">
                <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                    <div class="w-3 h-3 rounded-full {{ $i <= $currentStageIdx ? $stage['color'] : 'bg-slate-200' }} ring-2 ring-white ring-offset-1"></div>
                    <span class="text-[0.6rem] font-semibold {{ $i <= $currentStageIdx ? 'text-slate-700' : 'text-slate-400' }} whitespace-nowrap">
                        {{ $stage['label'] }}
                    </span>
                </div>
                @if($i < count($lcStages) - 1)
                    <div class="w-12 sm:w-20 h-px {{ $i < $currentStageIdx ? 'bg-indigo-300' : 'bg-slate-200' }} flex-shrink-0 -mt-4"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Timeline --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-900">Timeline Siklus Hidup</h2>
        <p class="text-xs text-slate-400 mt-0.5">{{ count($timeline ?? []) }} event tercatat</p>
    </div>

    @if(empty($timeline))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-slate-400">Belum ada riwayat siklus untuk aset ini</p>
            <p class="text-xs text-slate-400 mt-1">Timeline terisi otomatis saat ada aktivitas aset.</p>
        </div>
    @else
        @php
            $typeConfig = [
                'procurement'      => ['icon' => '🛒', 'color' => 'border-violet-400', 'dot' => 'bg-violet-500', 'badge' => 'badge-active'],
                'receipt'          => ['icon' => '📦', 'color' => 'border-emerald-400','dot' => 'bg-emerald-500','badge' => 'badge-approved'],
                'condition_change' => ['icon' => '🔄', 'color' => 'border-amber-400',  'dot' => 'bg-amber-500',  'badge' => 'badge-pending'],
                'maintenance'      => ['icon' => '🔧', 'color' => 'border-blue-400',   'dot' => 'bg-blue-500',   'badge' => 'badge-active'],
                'disposal'         => ['icon' => '🗑️', 'color' => 'border-red-400',    'dot' => 'bg-red-500',    'badge' => 'badge-rejected'],
            ];
        @endphp

        <div class="relative px-6 py-6">
            {{-- Vertical line --}}
            <div class="absolute left-[2.875rem] top-6 bottom-6 w-px bg-slate-200"></div>

            <div class="space-y-5">
                @foreach($timeline as $event)
                    @php
                        $cfg = $typeConfig[$event['type']] ?? ['icon'=>'📌','color'=>'border-slate-300','dot'=>'bg-slate-400','badge'=>'badge-draft'];
                    @endphp
                    <div class="flex gap-4">
                        {{-- Dot --}}
                        <div class="flex-shrink-0 w-5 h-5 rounded-full {{ $cfg['dot'] }} border-2 border-white shadow-sm mt-3 z-10"></div>

                        {{-- Card --}}
                        <div class="flex-1 bg-white border border-slate-200 rounded-xl p-4 border-l-4 {{ $cfg['color'] }} shadow-sm">
                            <div class="flex items-start justify-between gap-2 mb-1 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $cfg['icon'] }}</span>
                                    <span class="text-sm font-bold text-slate-900">{{ $event['title'] }}</span>
                                    @if($event['status'] ?? null)
                                        <span class="badge {{ $cfg['badge'] }} text-xs">
                                            {{ ucfirst(str_replace('_', ' ', $event['status'])) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 flex-shrink-0">
                                    {{ $event['date'] ? date('d M Y, H:i', strtotime($event['date'])) : '—' }}
                                </span>
                            </div>

                            @if($event['description'] ?? null)
                                <p class="text-sm text-slate-600 mt-1">{{ $event['description'] }}</p>
                            @endif
                            @if($event['detail'] ?? null)
                                <p class="text-xs text-slate-400 italic mt-1">{{ $event['detail'] }}</p>
                            @endif

                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                @if($event['user'] ?? null)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $event['user'] }}
                                    </span>
                                @endif
                                @if(isset($event['cost']) && $event['cost'])
                                    <span class="flex items-center gap-1 font-semibold text-emerald-600">
                                        Rp {{ number_format($event['cost'], 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
