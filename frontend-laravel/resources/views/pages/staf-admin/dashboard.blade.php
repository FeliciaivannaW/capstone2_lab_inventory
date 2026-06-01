@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $userName    = session('auth_user')['name'] ?? 'Staf';
    $totalAset   = (int)($inv['total'] ?? 0);
    $unlabeled   = (int)($label['unlabeled'] ?? 0);
    $labeled     = (int)($label['labeled'] ?? 0);
    $pendingRecv = (int)($recv['not_started'] ?? 0) + (int)($recv['partial'] ?? 0);
    $bhpLowCount = (int)($bhp['low_stock_count'] ?? 0);
    $procFinal   = (int)($proc['finalized_count'] ?? 0);

    $condBaik    = (int)($inv['cond_baik'] ?? 0);
    $condRingan  = (int)($inv['cond_rusak_ringan'] ?? 0);
    $condBerat   = (int)($inv['cond_rusak_berat'] ?? 0);
    $condMaint   = (int)($inv['cond_maintenance'] ?? 0);

    $labelPct = $totalAset > 0 ? round(($labeled / $totalAset) * 100) : 0;
    $recvPct  = ($recv['total_approved_items'] ?? 0) > 0
        ? round(($recv['fully_received'] ?? 0) / $recv['total_approved_items'] * 100) : 0;

    // Chart.js data
    $chartCondLabels = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Maintenance'];
    $chartCondData   = [$condBaik, $condRingan, $condBerat, $condMaint];
    $chartCondColors = ['#10b981', '#f59e0b', '#ef4444', '#6366f1'];

    $trendLabels  = collect($trend)->pluck('month_label')->toArray();
    $trendQty     = collect($trend)->pluck('quantity_total')->map(fn($v) => (int)$v)->toArray();
@endphp

{{-- ══════════════════════════════════════
     GREETING
══════════════════════════════════════ --}}
<div class="flex items-start justify-between mb-6 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">Selamat datang, <span class="font-semibold text-slate-700">{{ explode(' ', $userName)[0] }}</span> — ringkasan kondisi lab hari ini.</p>
    </div>
    <span class="text-xs text-slate-400 bg-slate-100 px-3 py-1.5 rounded-xl">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
</div>

{{-- ══════════════════════════════════════
     KPI CARDS (4 metrics)
══════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Aset Aktif --}}
    <div class="glass-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-indigo-600" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">INVENTARIS</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $totalAset }}</p>
        <p class="text-xs text-slate-500 mt-1">Total aset tercatat</p>
        <div class="mt-2.5 flex items-center gap-1.5">
            <span class="text-[11px] font-semibold text-emerald-600">{{ (int)($inv['active'] ?? 0) }} aktif</span>
            <span class="text-slate-300">·</span>
            <span class="text-[11px] text-slate-400">{{ (int)($inv['in_maintenance'] ?? 0) }} maintenance</span>
        </div>
    </div>

    {{-- Pending Penerimaan --}}
    <a href="{{ route('staf-admin.goods-receipt-index') }}"
       class="glass-card rounded-2xl p-5 hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $pendingRecv > 0 ? 'bg-amber-100' : 'bg-emerald-100' }} flex items-center justify-center">
                <svg class="w-4.5 h-4.5 {{ $pendingRecv > 0 ? 'text-amber-600' : 'text-emerald-600' }}" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                </svg>
            </div>
            @if($pendingRecv > 0)
                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full animate-pulse">PENDING</span>
            @else
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">SELESAI</span>
            @endif
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $pendingRecv }}</p>
        <p class="text-xs text-slate-500 mt-1">Item belum diterima</p>
        <div class="mt-2.5">
            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="{{ $recvPct == 100 ? 'bg-emerald-500' : 'bg-amber-400' }} h-full rounded-full" style="width: {{ $recvPct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ $recvPct }}% terpenuhi</p>
        </div>
    </a>

    {{-- Belum Berlabel --}}
    <a href="{{ route('staf-admin.inventory-label') }}"
       class="glass-card rounded-2xl p-5 hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $unlabeled > 0 ? 'bg-amber-100' : 'bg-emerald-100' }} flex items-center justify-center">
                <svg class="w-4.5 h-4.5 {{ $unlabeled > 0 ? 'text-amber-600' : 'text-emerald-600' }}" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            @if($unlabeled > 0)
                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">PERLU AKSI</span>
            @else
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">LENGKAP</span>
            @endif
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $unlabeled }}</p>
        <p class="text-xs text-slate-500 mt-1">Aset belum berlabel</p>
        <div class="mt-2.5">
            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="{{ $labelPct == 100 ? 'bg-emerald-500' : 'bg-indigo-400' }} h-full rounded-full" style="width: {{ $labelPct }}%"></div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ $labelPct }}% sudah berlabel</p>
        </div>
    </a>

    {{-- BHP Stok Kritis --}}
    <div class="glass-card rounded-2xl p-5 {{ $bhpLowCount > 0 ? 'border border-red-200' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 rounded-xl {{ $bhpLowCount > 0 ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center">
                <svg class="w-4.5 h-4.5 {{ $bhpLowCount > 0 ? 'text-red-600' : 'text-slate-400' }}" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            @if($bhpLowCount > 0)
                <span class="text-[10px] font-bold text-red-700 bg-red-50 px-2 py-0.5 rounded-full">STOK KRITIS</span>
            @else
                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">BHP</span>
            @endif
        </div>
        <p class="text-3xl font-bold {{ $bhpLowCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $bhpLowCount }}</p>
        <p class="text-xs text-slate-500 mt-1">Item stok di bawah minimum</p>
        <p class="text-[11px] text-slate-400 mt-2.5">dari {{ (int)($bhp['total_items'] ?? 0) }} item BHP terdaftar</p>
    </div>
</div>

{{-- ══════════════════════════════════════
     MAIN CONTENT — 2 columns
══════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- LEFT: Kondisi Aset (donut) + Trend --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Donut chart: kondisi aset --}}
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Kondisi Aset Inventaris</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Distribusi berdasarkan kondisi fisik</p>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">{{ $totalAset }} total</span>
            </div>

            @if($totalAset > 0)
            <div class="flex items-center gap-6">
                <div class="flex-shrink-0" style="width:160px;height:160px;">
                    <canvas id="conditionChart"></canvas>
                </div>
                <div class="flex-1 space-y-2.5">
                    @php
                        $condItems = [
                            ['label' => 'Baik',         'value' => $condBaik,   'color' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
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
                                    <span class="text-xs text-slate-600">{{ $c['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold {{ $c['text'] }}">{{ $c['value'] }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $pct }}%</span>
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
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <p class="text-sm text-slate-400">Belum ada data aset</p>
                </div>
            @endif
        </div>

        {{-- Trend chart: penerimaan 6 bulan --}}
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tren Penerimaan Barang</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Jumlah unit diterima per bulan (6 bulan terakhir)</p>
                </div>
            </div>
            @if(count($trend) > 0)
                <div style="height: 140px;">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <p class="text-xs text-slate-400">Belum ada data penerimaan</p>
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Pipeline + BHP Alert --}}
    <div class="space-y-5">

        {{-- Procurement Pipeline --}}
        <div class="glass-card rounded-2xl p-5">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Pipeline Pengadaan</h3>
            @php
                $pipeline = [
                    ['label' => 'Draft',       'count' => (int)($proc['draft_count'] ?? 0),     'color' => 'bg-slate-400',   'dot' => 'bg-slate-400'],
                    ['label' => 'Diajukan',    'count' => (int)($proc['submitted_count'] ?? 0), 'color' => 'bg-blue-400',    'dot' => 'bg-blue-400'],
                    ['label' => 'Difinalisasi','count' => (int)($proc['finalized_count'] ?? 0), 'color' => 'bg-emerald-500', 'dot' => 'bg-emerald-500'],
                    ['label' => 'Ditolak',     'count' => (int)($proc['rejected_count'] ?? 0),  'color' => 'bg-red-400',     'dot' => 'bg-red-400'],
                ];
                $procTotal = max((int)($proc['total'] ?? 0), 1);
            @endphp
            <div class="space-y-3">
                @foreach($pipeline as $p)
                    @php $pct = round($p['count'] / $procTotal * 100); @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $p['dot'] }} flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-slate-600">{{ $p['label'] }}</span>
                                <span class="text-xs font-bold text-slate-800">{{ $p['count'] }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="{{ $p['color'] }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                @php
                    $partialCount = (int)($recv['partial'] ?? 0);
                    $notStarted   = (int)($recv['not_started'] ?? 0);
                @endphp
                <p class="text-xs font-semibold text-slate-700 mb-2">Status Penerimaan</p>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Sudah lengkap</span>
                        <span class="font-semibold text-emerald-600">{{ (int)($recv['fully_received'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Sebagian diterima</span>
                        <span class="font-semibold text-amber-600">{{ $partialCount }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Belum ada penerimaan</span>
                        <span class="font-semibold text-red-500">{{ $notStarted }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BHP Stok Kritis --}}
        @if(count($bhpLow) > 0)
        <div class="glass-card rounded-2xl p-5 border border-red-100">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">BHP Stok Kritis</h3>
            </div>
            <div class="space-y-2">
                @foreach($bhpLow as $item)
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $item['item_name'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $item['lab_name'] }}</p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            <p class="text-xs font-bold text-red-600">{{ $item['current_stock'] }} {{ $item['unit'] }}</p>
                            <p class="text-[10px] text-slate-400">min. {{ $item['minimum_stock'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="glass-card rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">BHP Stok Aman</h3>
            </div>
            <p class="text-xs text-slate-400">Semua item BHP di atas batas minimum</p>
        </div>
        @endif

    </div>
</div>

{{-- ══════════════════════════════════════
     RECENT ACTIVITY
══════════════════════════════════════ --}}
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
            <h3 class="text-sm font-bold text-slate-900">Aktivitas Terbaru</h3>
        </div>
        <a href="{{ route('staf-admin.goods-receipt-index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
            Semua →
        </a>
    </div>

    @if(empty($activity))
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-slate-400">Belum ada aktivitas penerimaan barang</p>
        </div>
    @else
        <ul class="divide-y divide-slate-50">
            @foreach($activity as $a)
            <li class="px-5 py-3.5 flex items-center gap-3 hover:bg-slate-50/60 transition-colors">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                    {{ ($a['item_type'] ?? '') === 'inventory' ? 'bg-indigo-100' : 'bg-purple-100' }}">
                    <svg class="w-3.5 h-3.5 {{ ($a['item_type'] ?? '') === 'inventory' ? 'text-indigo-600' : 'text-purple-600' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if(($a['item_type'] ?? '') === 'inventory')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $a['item_name'] ?? '—' }}</p>
                    <p class="text-xs text-slate-400 truncate mt-0.5">
                        {{ \Illuminate\Support\Str::limit($a['draft_title'] ?? '', 30) }} · {{ $a['lab_name'] ?? '' }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs font-bold text-emerald-600">+{{ $a['quantity_received'] }} unit</p>
                    <p class="text-[11px] text-slate-400">{{ $a['received_date'] ? date('d M', strtotime($a['received_date'])) : '—' }}</p>
                </div>
            </li>
            @endforeach
        </ul>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Donut: kondisi aset ──
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
                    backgroundColor: hasData ? @json($chartCondColors) : ['#e2e8f0'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: hasData,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} aset`
                        }
                    }
                }
            }
        });
    }

    // ── Bar: trend penerimaan ──
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Unit Diterima',
                    data: @json($trendQty),
                    backgroundColor: 'rgba(99,102,241,0.15)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.raw} unit` } }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#94a3b8' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
