{{--
    Workflow strip — indikator 3 langkah Staf Administrasi.
    Usage: @include('components.staf-admin.workflow-strip', ['active' => 'draft' | 'receipt' | 'label'])
--}}
@php
    $steps = [
        ['key' => 'draft',   'label' => 'Draf Disetujui',  'desc' => 'Lihat hasil finalisasi Kaprodi', 'route' => 'staf-admin.procurement-approved'],
        ['key' => 'receipt', 'label' => 'Penerimaan',      'desc' => 'Catat tanggal barang masuk',     'route' => 'staf-admin.goods-receipt-index'],
        ['key' => 'label',   'label' => 'Label & QR',      'desc' => 'Beri nomor & foto QR',           'route' => 'staf-admin.inventory-label'],
    ];
    $activeIdx = match($active ?? '') {
        'draft'   => 0,
        'receipt' => 1,
        'label'   => 2,
        default   => -1,
    };
@endphp

<div class="glass-card rounded-2xl p-3 mb-5">
    <div class="flex items-center gap-1 overflow-x-auto pb-1">
        @foreach($steps as $i => $step)
            @php
                $isPast    = $i < $activeIdx;
                $isCurrent = $i === $activeIdx;
                $tile      = $isCurrent ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-200'
                              : ($isPast ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                         : 'bg-slate-50 text-slate-500 border border-slate-100 hover:bg-slate-100');
                $bullet    = $isCurrent ? 'bg-white text-indigo-600'
                              : ($isPast ? 'bg-emerald-500 text-white'
                                         : 'bg-slate-200 text-slate-500');
            @endphp
            <a href="{{ route($step['route']) }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all flex-shrink-0 {{ $tile }}">
                <span class="w-6 h-6 rounded-full {{ $bullet }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                    @if($isPast)
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </span>
                <div class="text-left">
                    <p class="text-xs font-bold leading-tight">{{ $step['label'] }}</p>
                    <p class="text-[0.65rem] opacity-80 mt-0.5">{{ $step['desc'] }}</p>
                </div>
            </a>
            @if($i < count($steps) - 1)
                <svg class="w-4 h-4 text-slate-300 flex-shrink-0 mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
        @endforeach
    </div>
</div>
