@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $userName = session('auth_user')['name'] ?? 'Staf';
    $inv  = $stats['inventory'] ?? [];
    $recv = $stats['reception'] ?? [];

    // 3 fitur summary
    $draftsTotal     = $draftsTotal     ?? 0;
    $draftsPending   = $draftsPending   ?? 0;
    $itemsPending    = $itemsPending    ?? 0;
    $assetsUnlabeled = $assetsUnlabeled ?? 0;
    $assetsLabeled   = $assetsLabeled   ?? 0;
@endphp

{{-- Greeting --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">
        Selamat datang, {{ explode(' ', $userName)[0] }} 👋
    </h1>
    <p class="text-sm text-slate-500 mt-1">
        Pantau dan kelola 3 tugas utama Staf Administrasi: draf disetujui, penerimaan barang, dan pelabelan inventaris.
    </p>
</div>

{{-- ───── WORKFLOW STRIP ───── --}}
@include('components.staf-admin.workflow-strip', ['active' => null])

{{-- ───── 3 FITUR — ACTION CARDS ───── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- ▸ Fitur 1 — Draf Disetujui --}}
    <a href="{{ route('staf-admin.procurement-approved') }}"
       class="glass-card rounded-2xl p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all group block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-violet-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-[0.6rem] font-bold text-violet-700 bg-violet-100 px-2 py-1 rounded-full uppercase tracking-wider">Fitur 1</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Draf Disetujui</h3>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">Hasil finalisasi Kaprodi yang siap ditindaklanjuti</p>

        <div class="flex items-end gap-2">
            <p class="text-4xl font-bold text-slate-900 leading-none">{{ $draftsTotal }}</p>
            <p class="text-xs text-slate-500 pb-1">draf finalisasi</p>
        </div>
        @if($draftsPending > 0)
            <p class="text-xs text-amber-600 font-semibold mt-3 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 4a1 1 0 011 1v4a1 1 0 11-2 0V7a1 1 0 011-1zm0 9a1 1 0 110-2 1 1 0 010 2z"/></svg>
                {{ $draftsPending }} draf masih menunggu penerimaan
            </p>
        @else
            <p class="text-xs text-emerald-600 font-semibold mt-3">Semua draf sudah ditindaklanjuti ✓</p>
        @endif

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="font-semibold text-violet-600 group-hover:translate-x-1 transition-transform">Buka halaman →</span>
        </div>
    </a>

    {{-- ▸ Fitur 3 — Penerimaan Barang --}}
    <a href="{{ route('staf-admin.goods-receipt-index') }}"
       class="glass-card rounded-2xl p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all group block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                </svg>
            </div>
            <span class="text-[0.6rem] font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full uppercase tracking-wider">Fitur 3</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Penerimaan Barang</h3>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">Catat tanggal terima — barang bisa datang bertahap</p>

        <div class="flex items-end gap-2">
            <p class="text-4xl font-bold text-slate-900 leading-none">{{ $itemsPending }}</p>
            <p class="text-xs text-slate-500 pb-1">item belum diterima</p>
        </div>
        <p class="text-xs {{ $itemsPending > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-semibold mt-3">
            {{ $recv['received_items'] ?? 0 }} item sudah tercatat masuk
        </p>

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="font-semibold text-emerald-600 group-hover:translate-x-1 transition-transform">Catat penerimaan →</span>
        </div>
    </a>

    {{-- ▸ Fitur 2 — Update Label & Foto --}}
    <a href="{{ route('staf-admin.inventory-label') }}"
       class="glass-card rounded-2xl p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all group block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <span class="text-[0.6rem] font-bold text-amber-700 bg-amber-100 px-2 py-1 rounded-full uppercase tracking-wider">Fitur 2</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Update Label & Foto QR</h3>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">Nomor label dan foto QR/Barcode untuk aset</p>

        <div class="flex items-end gap-2">
            <p class="text-4xl font-bold text-slate-900 leading-none">{{ $assetsUnlabeled }}</p>
            <p class="text-xs text-slate-500 pb-1">aset belum berlabel</p>
        </div>
        <p class="text-xs {{ $assetsUnlabeled > 0 ? 'text-amber-600' : 'text-emerald-600' }} font-semibold mt-3">
            {{ $assetsLabeled }} aset sudah berlabel
        </p>

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="font-semibold text-amber-600 group-hover:translate-x-1 transition-transform">Beri label →</span>
        </div>
    </a>
</div>

{{-- ───── RECENT ACTIVITY (focus to 3 fitur) ───── --}}
<div class="glass-card rounded-2xl overflow-hidden mb-6">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block animate-pulse"></span>
                Aktivitas Inventaris Terbaru
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">5 aset yang baru diperbarui — siap untuk dilabel atau dilacak</p>
        </div>
        <a href="{{ route('staf-admin.asset-list') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
            Lihat semua →
        </a>
    </div>

    @if(empty($recentAssets))
        <div class="px-6 py-12 text-center">
            <svg class="w-12 h-12 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-slate-400">Belum ada aktivitas — mulai dari "Penerimaan Barang"</p>
        </div>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach($recentAssets as $a)
                @php
                    $hasLabel = !empty($a['label_number']);
                    $ts = $a['updated_at'] ?? $a['received_date'] ?? $a['created_at'] ?? null;
                @endphp
                <li class="px-6 py-3.5 flex items-center gap-4 hover:bg-slate-50/60 transition-colors">
                    {{-- Icon: label status --}}
                    <div class="w-9 h-9 rounded-full {{ $hasLabel ? 'bg-emerald-100' : 'bg-amber-100' }} flex items-center justify-center flex-shrink-0">
                        @if($hasLabel)
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $a['item_name'] ?? '—' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">
                            <span class="font-mono">{{ $a['asset_code'] ?? '—' }}</span> · {{ $a['room_name'] ?? 'tanpa ruangan' }}
                        </p>
                    </div>
                    @if($hasLabel)
                        <span class="badge badge-approved text-xs">{{ $a['label_number'] }}</span>
                    @else
                        <a href="{{ route('staf-admin.inventory-label', ['search' => $a['asset_code'] ?? '']) }}"
                           class="text-[0.65rem] font-semibold text-white bg-amber-500 hover:bg-amber-600 px-2.5 py-1 rounded-full transition-colors">
                            Beri Label
                        </a>
                    @endif
                    <span class="text-[0.65rem] text-slate-400 flex-shrink-0 hidden sm:inline">
                        {{ $ts ? date('d M', strtotime($ts)) : '—' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- ───── INVENTORY HEALTH (informasi tambahan) ───── --}}
<div class="glass-card rounded-2xl p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
            Status Inventaris
            <span class="text-[0.65rem] font-normal text-slate-400 ml-1">(informasi pelengkap)</span>
        </h3>
        @php $totalInv = ($inv['total'] ?? 0); @endphp
        @if($totalInv > 0)
            <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                {{ $totalInv }} aset total
            </span>
        @endif
    </div>
    @php
        $invBars = [
            ['label' => 'Kondisi Baik',  'value' => $inv['condition_good'] ?? 0,         'color' => 'bg-emerald-500'],
            ['label' => 'Rusak Ringan',  'value' => $inv['condition_light_damage'] ?? 0, 'color' => 'bg-amber-400'],
            ['label' => 'Rusak Berat',   'value' => $inv['condition_heavy_damage'] ?? 0, 'color' => 'bg-red-500'],
            ['label' => 'Sudah Label',   'value' => $inv['labeled'] ?? $assetsLabeled,   'color' => 'bg-indigo-500'],
            ['label' => 'Belum Label',   'value' => $inv['unlabeled'] ?? $assetsUnlabeled,'color' => 'bg-slate-400'],
        ];
        $invMax = max(array_column($invBars, 'value') ?: [1]);
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
        @foreach($invBars as $bar)
            @php $pct = $invMax > 0 ? ($bar['value'] / $invMax) * 100 : 0; @endphp
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs text-slate-600">{{ $bar['label'] }}</span>
                    <span class="text-xs font-bold text-slate-900">{{ $bar['value'] }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="{{ $bar['color'] }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ───── QUICK ACTIONS ───── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <a href="{{ route('staf-admin.goods-receipt-index', ['receipt_status' => 'belum']) }}"
       class="glass-card rounded-xl p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="w-9 h-9 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-4.5 h-4.5 text-rose-600" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-800">Belum Diterima</p>
            <p class="text-[0.65rem] text-slate-400 mt-0.5">Filter item pending</p>
        </div>
    </a>

    <a href="{{ route('staf-admin.inventory-label', ['label_status' => 'unlabeled']) }}"
       class="glass-card rounded-xl p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-4.5 h-4.5 text-amber-600" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-800">Belum Berlabel</p>
            <p class="text-[0.65rem] text-slate-400 mt-0.5">
                @if($assetsUnlabeled > 0)
                    {{ $assetsUnlabeled }} aset menunggu
                @else
                    Semua sudah berlabel ✓
                @endif
            </p>
        </div>
    </a>

    <a href="{{ route('staf-admin.asset-list') }}"
       class="glass-card rounded-xl p-4 flex items-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition-all group">
        <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-4.5 h-4.5 text-violet-600" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-slate-800">Lihat Siklus Barang</p>
            <p class="text-[0.65rem] text-slate-400 mt-0.5">Timeline per aset</p>
        </div>
    </a>
</div>
@endsection
