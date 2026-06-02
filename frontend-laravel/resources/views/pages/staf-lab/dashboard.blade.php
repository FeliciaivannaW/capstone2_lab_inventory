@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $userName    = session('auth_user')['name'] ?? 'Staf Lab';
    
    // Inventory condition metrics
    $totalAset   = (int)($inv['total'] ?? 0);
    $condBaik    = (int)($inv['cond_baik'] ?? 0);
    $condRingan  = (int)($inv['cond_rusak_ringan'] ?? 0);
    $condBerat   = (int)($inv['cond_rusak_berat'] ?? 0);
    $condMaint   = (int)($inv['cond_maintenance'] ?? 0);

    // BHP metrics
    $totalBhp    = (int)($bhp['total_items'] ?? 0);
    $bhpLowCount = (int)($bhp['low_stock_count'] ?? count($bhpLow));

    // Chart data for condition
    $chartCondLabels = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Maintenance'];
    $chartCondData   = [$condBaik, $condRingan, $condBerat, $condMaint];
    $chartCondColors = ['#10b981', '#f59e0b', '#ef4444', '#6366f1'];
@endphp

{{-- ══════════════════════════════════════
     GREETING
══════════════════════════════════════ --}}
<div class="flex items-start justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Dashboard Staf Laboratorium</h1>
        <p class="text-sm text-slate-500 mt-0.5">Selamat datang, <span class="font-semibold text-slate-700">{{ explode(' ', $userName)[0] }}</span> — pantau kondisi aset, log maintenance, dan stok BHP.</p>
    </div>
    <span class="text-xs text-slate-400 bg-slate-100 px-3 py-1.5 rounded-xl">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
</div>

{{-- ══════════════════════════════════════
     KPI CARDS
══════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

    {{-- Total Aset & Kondisi --}}
    <a href="{{ route('inventory') }}" class="glass-card rounded-2xl p-5 hover:shadow-md hover:-translate-y-0.5 transition-all group relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(99,102,241,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-indigo-500" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">INVENTARIS</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $totalAset }}</p>
        <p class="text-xs text-slate-500 mt-1">Total aset terdaftar</p>
        <div class="mt-3 flex items-center gap-2">
            <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $condBaik }} Baik</span>
            <span class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">{{ $condMaint }} Maint.</span>
        </div>
    </a>

    {{-- BHP Kritis --}}
    <a href="{{ route('bhp', ['low_stock' => 'true']) }}" class="glass-card rounded-2xl p-5 hover:shadow-md hover:-translate-y-0.5 transition-all group relative overflow-hidden {{ $bhpLowCount > 0 ? 'border border-red-200' : '' }}">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba({{ $bhpLowCount > 0 ? '239,68,68' : '16,185,129' }},0.06) 0%,transparent 60%)"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $bhpLowCount > 0 ? 'bg-red-50' : 'bg-emerald-50' }} flex items-center justify-center">
                <svg class="w-4.5 h-4.5 {{ $bhpLowCount > 0 ? 'text-red-500' : 'text-emerald-500' }}" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            @if($bhpLowCount > 0)
                <span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full animate-pulse">PERLU RESTOCK</span>
            @else
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">STOK AMAN</span>
            @endif
        </div>
        <p class="text-3xl font-bold {{ $bhpLowCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $bhpLowCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Item BHP stok di bawah minimum</p>
        <div class="mt-3">
            <span class="text-[11px] text-slate-400">Total: {{ $totalBhp }} item BHP terdaftar</span>
        </div>
    </a>

    {{-- Total Log Maintenance (Bulan Ini) --}}
    <a href="{{ route('maintenance') }}" class="glass-card rounded-2xl p-5 hover:shadow-md hover:-translate-y-0.5 transition-all group relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(245,158,11,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-amber-500" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">LOG MAINT.</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ count($recentMaintenance) }}<span class="text-sm text-slate-400 font-normal ml-1">Terbaru</span></p>
        <p class="text-xs text-slate-500 mt-1">Riwayat pencatatan maintenance</p>
        <div class="mt-3">
            <span class="text-[11px] text-slate-400">Termasuk perbaikan & pengecekan rutin</span>
        </div>
    </a>

</div>

{{-- ══════════════════════════════════════
     MAIN CONTENT — 2 columns
══════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- LEFT: Kondisi Aset (donut) --}}
    <div class="glass-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Distribusi Kondisi Aset</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Ringkasan kesehatan inventaris</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $totalAset }} total</span>
        </div>

        @if($totalAset > 0)
        <div class="flex flex-col items-center">
            <div class="relative w-40 h-40 mb-6 mt-2">
                <canvas id="conditionChart"></canvas>
            </div>
            <div class="w-full space-y-3">
                @php
                    $condItems = [
                        ['label' => 'Baik',          'value' => $condBaik,   'color' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
                        ['label' => 'Rusak Ringan',  'value' => $condRingan, 'color' => 'bg-amber-400',   'text' => 'text-amber-600'],
                        ['label' => 'Rusak Berat',   'value' => $condBerat,  'color' => 'bg-red-500',     'text' => 'text-red-600'],
                        ['label' => 'Maintenance',   'value' => $condMaint,  'color' => 'bg-indigo-400',  'text' => 'text-indigo-600'],
                    ];
                @endphp
                @foreach($condItems as $c)
                    @php $pct = $totalAset > 0 ? round($c['value'] / $totalAset * 100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full {{ $c['color'] }} flex-shrink-0"></div>
                                <span class="text-xs text-slate-600 font-medium">{{ $c['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold {{ $c['text'] }}">{{ $c['value'] }}</span>
                                <span class="text-[10px] text-slate-400 w-6 text-right">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="{{ $c['color'] }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400 font-medium">Belum ada data aset</p>
                <p class="text-[11px] text-slate-400 mt-1">Data akan muncul setelah aset ditambahkan.</p>
            </div>
        @endif
    </div>

    {{-- RIGHT: BHP Kritis & Maintenance --}}
    <div class="lg:col-span-2 space-y-5">
        
        {{-- BHP Stok Kritis List --}}
        <div class="glass-card rounded-2xl p-5 {{ $bhpLowCount > 0 ? 'border border-red-200 shadow-[0_4px_20px_-4px_rgba(239,68,68,0.1)]' : '' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl {{ $bhpLowCount > 0 ? 'bg-red-50' : 'bg-emerald-50' }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 {{ $bhpLowCount > 0 ? 'text-red-500' : 'text-emerald-500' }}" fill="currentColor" viewBox="0 0 20 20">
                            @if($bhpLowCount > 0)
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Perhatian: Stok BHP</h3>
                </div>
                @if($bhpLowCount > 0)
                    <a href="{{ route('bhp', ['low_stock' => 'true']) }}" class="text-[11px] font-semibold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition-colors">
                        Lihat Semua
                    </a>
                @endif
            </div>

            @if($bhpLowCount > 0)
                <div class="space-y-2">
                    @foreach(array_slice($bhpLow, 0, 4) as $item)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50/50 hover:bg-slate-50 transition-colors border border-slate-100/50">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $item['item_name'] }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ $item['lab_name'] }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-3">
                                <div class="inline-flex items-baseline gap-1">
                                    <span class="text-lg font-bold text-red-600">{{ $item['current_stock'] }}</span>
                                    <span class="text-[10px] font-semibold text-red-400 uppercase tracking-wide">{{ $item['unit'] }}</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-0.5">Minimum: {{ $item['minimum_stock'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    @if($bhpLowCount > 4)
                        <p class="text-[11px] text-center text-slate-400 pt-2">+ {{ $bhpLowCount - 4 }} item lainnya butuh perhatian</p>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <p class="text-sm font-medium text-slate-500">Semua item BHP dalam batas aman.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Tidak ada stok yang menipis saat ini.</p>
                </div>
            @endif
        </div>

        {{-- Log Maintenance Terbaru --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></div>
                    <h3 class="text-sm font-bold text-slate-900">Aktivitas Maintenance Terbaru</h3>
                </div>
                <a href="{{ route('maintenance') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-full transition-colors">
                    Kelola Log
                </a>
            </div>

            @if(empty($recentMaintenance))
                <div class="px-5 py-12 text-center">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-500">Belum ada catatan maintenance</p>
                    <p class="text-[11px] text-slate-400 mt-1">Log akan muncul saat ada perbaikan aset dicatat.</p>
                </div>
            @else
                <ul class="divide-y divide-slate-50">
                    @foreach($recentMaintenance as $log)
                        @php
                            $statusColors = [
                                'planned'     => 'bg-slate-100 text-slate-600',
                                'in_progress' => 'bg-amber-100 text-amber-700',
                                'done'        => 'bg-emerald-100 text-emerald-700',
                                'cancelled'   => 'bg-red-100 text-red-700',
                            ];
                            $statusLabels = [
                                'planned'     => 'Direncanakan',
                                'in_progress' => 'Proses',
                                'done'        => 'Selesai',
                                'cancelled'   => 'Dibatalkan',
                            ];
                            $stColor = $statusColors[$log['status']] ?? 'bg-slate-100 text-slate-600';
                            $stLabel = $statusLabels[$log['status']] ?? ucfirst($log['status']);
                        @endphp
                        <li class="px-5 py-4 flex items-start gap-4 hover:bg-slate-50/50 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 border border-slate-200/50">
                                <svg class="w-4.5 h-4.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <div class="flex justify-between items-start mb-1">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $log['asset_name'] ?? 'Aset Tidak Diketahui' }}</p>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md whitespace-nowrap {{ $stColor }}">{{ $stLabel }}</span>
                                </div>
                                <p class="text-xs text-slate-500 truncate mb-1">{{ $log['issue_description'] ?? 'Tidak ada deskripsi' }}</p>
                                <div class="flex items-center gap-3">
                                    <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ date('d M Y', strtotime($log['maintenance_date'])) }}
                                    </span>
                                    @if(isset($log['bhp_usages']) && count($log['bhp_usages']) > 0)
                                        <span class="text-[11px] font-medium text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            {{ count($log['bhp_usages']) }} BHP terpakai
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if(count($recentMaintenance) >= 5)
                    <div class="px-5 py-3 border-t border-slate-50 text-center">
                        <a href="{{ route('maintenance') }}" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                            Lihat Riwayat Lengkap →
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Donut: Kondisi Aset ──
    const condCtx = document.getElementById('conditionChart');
    if (condCtx) {
        const condData = @json($chartCondData);
        const hasData  = condData.some(v => v > 0);
        new Chart(condCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartCondLabels),
                datasets: [{
                    data: hasData ? condData : [1],
                    backgroundColor: hasData ? @json($chartCondColors) : ['#f8fafc'],
                    borderWidth: 0,
                    hoverOffset: 4,
                    cutout: '72%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: hasData,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 11 },
                        bodyFont: { size: 12, weight: 'bold' },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} Aset`
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }
});
</script>
@endpush
@endsection
