@extends('layouts.app')
@section('title', 'Timeline Siklus — ' . ($asset['asset_code'] ?? ''))

@section('content')
@php
    // Consistent lifecycle mapping
    $lcStages = [
        ['status' => 'received',            'label' => 'Diterima',    'color' => 'blue',    'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4'],
        ['status' => 'labeled',             'label' => 'Berlabel',    'color' => 'indigo',  'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
        ['status' => 'available',           'label' => 'Aktif',       'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['status' => 'maintenance',         'label' => 'Maintenance', 'color' => 'amber',   'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ['status' => 'disposed',            'label' => 'Selesai',     'color' => 'slate',   'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    ];

    $statusToIdx = [
        'received'    => 0,
        'labeled'     => 1,
        'available'   => 2,
        'in_use'      => 2,
        'maintenance' => 3,
        'disposed'    => 4,
        'replaced'    => 4,
    ];
    $currentIdx = $statusToIdx[$asset['status'] ?? ''] ?? 0;

    $lcColors = [
        'blue'    => ['dot' => 'bg-blue-500',    'text' => 'text-blue-700',    'bg' => 'bg-blue-100'],
        'indigo'  => ['dot' => 'bg-indigo-500',  'text' => 'text-indigo-700',  'bg' => 'bg-indigo-100'],
        'emerald' => ['dot' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-100'],
        'amber'   => ['dot' => 'bg-amber-500',   'text' => 'text-amber-700',   'bg' => 'bg-amber-100'],
        'slate'   => ['dot' => 'bg-slate-400',   'text' => 'text-slate-500',   'bg' => 'bg-slate-100'],
    ];

    $condColors = [
        'baik'         => 'bg-emerald-100 text-emerald-700',
        'rusak_ringan' => 'bg-amber-100 text-amber-700',
        'rusak_berat'  => 'bg-red-100 text-red-700',
        'maintenance'  => 'bg-indigo-100 text-indigo-700',
        'dihapus'      => 'bg-slate-100 text-slate-600',
        'diganti'      => 'bg-violet-100 text-violet-700',
    ];
    $condColor = $condColors[$asset['asset_condition'] ?? ''] ?? 'bg-slate-100 text-slate-600';

    $eventConfig = [
        'procurement'      => ['color' => 'violet', 'border' => 'border-l-violet-400', 'dot' => 'bg-violet-500', 'bg' => 'bg-violet-50',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'receipt'          => ['color' => 'emerald','border' => 'border-l-emerald-400','dot' => 'bg-emerald-500','bg' => 'bg-emerald-50',  'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4'],
        'condition_change' => ['color' => 'amber',  'border' => 'border-l-amber-400',  'dot' => 'bg-amber-500',  'bg' => 'bg-amber-50',   'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
        'maintenance'      => ['color' => 'blue',   'border' => 'border-l-blue-400',   'dot' => 'bg-blue-500',   'bg' => 'bg-blue-50',    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        'disposal'         => ['color' => 'red',    'border' => 'border-l-red-400',    'dot' => 'bg-red-500',    'bg' => 'bg-red-50',     'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    ];
@endphp

{{-- Back --}}
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('staf-admin.asset-list') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar
    </a>
</div>

{{-- ══ ASSET INFO CARD ══ --}}
<div class="glass-card rounded-2xl overflow-hidden mb-5">
    {{-- Top bar: kode + kondisi + stage --}}
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $lcColors[$lcStages[$currentIdx]['color']]['bg'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 {{ $lcColors[$lcStages[$currentIdx]['color']]['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $lcStages[$currentIdx]['icon'] }}"/>
                </svg>
            </div>
            <div>
                <code class="text-sm font-bold text-slate-900 font-mono">{{ $asset['asset_code'] }}</code>
                <p class="text-xs text-slate-500 mt-0.5">{{ $asset['item_name'] ?? '—' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border {{ $condColor }}">
                {{ str_replace('_', ' ', ucfirst($asset['asset_condition'] ?? '—')) }}
            </span>
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full
                         {{ $lcColors[$lcStages[$currentIdx]['color']]['bg'] }}
                         {{ $lcColors[$lcStages[$currentIdx]['color']]['text'] }}
                         border border-current border-opacity-20">
                <span class="w-1.5 h-1.5 rounded-full {{ $lcColors[$lcStages[$currentIdx]['color']]['dot'] }}"></span>
                {{ $lcStages[$currentIdx]['label'] }}
            </span>
        </div>
    </div>

    {{-- Info grid --}}
    <div class="px-5 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Label</p>
            @if($asset['label_number'] ?? null)
                <code class="text-sm font-bold text-indigo-700">{{ $asset['label_number'] }}</code>
            @else
                <p class="text-sm text-slate-400 italic">Belum ada</p>
            @endif
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Laboratorium</p>
            <p class="text-sm font-semibold text-slate-700">{{ $asset['lab_name'] ?? '—' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ruangan</p>
            <p class="text-sm font-semibold text-slate-700">{{ $asset['room_name'] ?? '—' }}</p>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tgl Diterima</p>
            <p class="text-sm font-semibold text-slate-700">
                {{ $asset['received_date'] ? date('d M Y', strtotime($asset['received_date'])) : '—' }}
            </p>
        </div>
    </div>

    {{-- Lifecycle strip --}}
    <div class="px-5 pb-5">
        <div class="flex items-center">
            @foreach($lcStages as $sIdx => $stage)
            @php
                $isPast    = $sIdx < $currentIdx;
                $isCurrent = $sIdx === $currentIdx;
                $c = $lcColors[$stage['color']];
            @endphp
            <div class="flex items-center {{ $sIdx < count($lcStages) - 1 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        {{ $isCurrent ? $c['dot'] . ' ring-4 ring-offset-1 ' . str_replace('bg-', 'ring-', $c['dot']) . '/30' : ($isPast ? $c['dot'] : 'bg-slate-200') }}">
                        @if($isPast)
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($isCurrent)
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        @endif
                    </div>
                    <span class="text-[10px] font-bold whitespace-nowrap
                        {{ $isCurrent ? $c['text'] : ($isPast ? 'text-slate-500' : 'text-slate-300') }}">
                        {{ $stage['label'] }}
                    </span>
                </div>
                @if($sIdx < count($lcStages) - 1)
                    <div class="flex-1 h-0.5 mx-1 -mt-5
                        {{ $sIdx < $currentIdx ? $c['dot'] . ' opacity-40' : 'bg-slate-200' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ TIMELINE ══ --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Riwayat Siklus Hidup</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ count($timeline ?? []) }} event tercatat</p>
        </div>
    </div>

    @if(empty($timeline))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-500">Belum ada riwayat</p>
            <p class="text-xs text-slate-400 mt-1">Timeline terisi otomatis saat ada aktivitas aset.</p>
        </div>
    @else
        <div class="px-5 py-5">
            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-200 ml-px"></div>

                <div class="space-y-4">
                    @foreach($timeline as $idx => $event)
                    @php
                        $cfg = $eventConfig[$event['type'] ?? ''] ?? $eventConfig['condition_change'];
                        $isLast = $idx === count($timeline) - 1;
                    @endphp
                    <div class="flex gap-4">
                        {{-- Dot --}}
                        <div class="flex-shrink-0 z-10 mt-3">
                            <div class="w-9 h-9 rounded-xl {{ $cfg['bg'] }} border border-{{ $cfg['color'] }}-200 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-{{ $cfg['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Card --}}
                        <div class="flex-1 bg-white rounded-xl border border-slate-100 border-l-4 {{ $cfg['border'] }} p-4 shadow-sm mb-0.5">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900">{{ $event['title'] ?? '—' }}</p>
                                    @if($event['description'] ?? null)
                                        <p class="text-sm text-slate-600 mt-1">{{ $event['description'] }}</p>
                                    @endif
                                    @if($event['detail'] ?? null)
                                        <p class="text-xs text-slate-400 mt-1 italic">{{ $event['detail'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-semibold text-slate-600">
                                        {{ $event['date'] ? date('d M Y', strtotime($event['date'])) : '—' }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $event['date'] ? date('H:i', strtotime($event['date'])) : '' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Footer meta --}}
                            @if(($event['user'] ?? null) || (isset($event['cost']) && $event['cost']))
                            <div class="flex items-center gap-4 mt-2.5 pt-2.5 border-t border-slate-50">
                                @if($event['user'] ?? null)
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        {{ $event['user'] }}
                                    </div>
                                @endif
                                @if(isset($event['cost']) && $event['cost'])
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Rp {{ number_format($event['cost'], 0, ',', '.') }}
                                    </div>
                                @endif
                                @if($event['status'] ?? null)
                                    <span class="text-[10px] font-semibold text-{{ $cfg['color'] }}-700 bg-{{ $cfg['color'] }}-50 px-2 py-0.5 rounded-full border border-{{ $cfg['color'] }}-200">
                                        {{ ucfirst(str_replace('_', ' ', $event['status'])) }}
                                    </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
