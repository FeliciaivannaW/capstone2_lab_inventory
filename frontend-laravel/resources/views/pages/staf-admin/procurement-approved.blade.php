@extends('layouts.app')

@section('title', 'Draf Disetujui')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'draft'])

<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Draf Pengadaan yang Disetujui</h1>
        <p class="text-sm text-slate-500 mt-1">
            Draf yang sudah difinalisasi oleh Kaprodi. Tinjau, lalu lanjut ke <strong class="text-emerald-600">Penerimaan Barang</strong>.
        </p>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
        <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
        Fitur 1 — Lihat Draf
    </span>
</div>

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

{{-- Filter bar --}}
<form method="GET" action="{{ route('staf-admin.procurement-approved') }}"
      class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
    <div class="flex-1 min-w-[160px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Tahun Anggaran</label>
        <select name="budget_year"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            <option value="">Semua Tahun</option>
            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ ($filters['budget_year'] ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="flex-[2] min-w-[200px]">
        <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Judul / Lab</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ketik kata kunci..."
               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
    </div>
    <div class="flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Filter
        </button>
        <a href="{{ route('staf-admin.procurement-approved') }}"
           class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
            Reset
        </a>
    </div>
</form>

{{-- Table --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($drafts) }})">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($drafts) }} draf difinalisasi</p>
    </div>

    @if(empty($drafts))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Belum ada draf yang difinalisasi</p>
            <p class="text-xs text-slate-400 mt-1">Draf akan muncul setelah Kaprodi menfinalisasi dari Kepala Laboratorium.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="num">#</x-sort-header>
                        <x-sort-header field="title">Judul Draf</x-sort-header>
                        <x-sort-header field="lab">Laboratorium</x-sort-header>
                        <x-sort-header field="date">Tgl Finalisasi</x-sort-header>
                        <x-sort-header field="total" class="text-center">Total Item</x-sort-header>
                        <x-sort-header field="received" class="text-center">Diterima</x-sort-header>
                        <x-sort-header field="pending" class="text-center">Belum</x-sort-header>
                        <x-sort-header field="progress">Progress</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drafts as $index => $draft)
                        @php
                            $rs = $draft['receipt_status'] ?? 'belum';
                            $rsMeta = match($rs) {
                                'selesai'  => ['Semua Diterima',     'bg-emerald-100 text-emerald-700','bg-emerald-500'],
                                'sebagian' => ['Sebagian Diterima',  'bg-blue-100 text-blue-700',     'bg-blue-500'],
                                'kosong'   => ['Tidak Ada Item',     'bg-slate-100 text-slate-500',   'bg-slate-300'],
                                default    => ['Menunggu Penerimaan','bg-amber-100 text-amber-700',   'bg-amber-400'],
                            };
                        @endphp
                        <tr x-show="showRow({{ $index }})" x-cloak>
                            <td class="text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $draft['title'] }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-active text-xs">{{ $draft['counter'] ?? $draft['lab_code'] ?? '' }}</span>
                                    <span class="text-slate-600 text-xs">{{ $draft['lab_name'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($draft['finalized_at'])
                                    <p class="text-xs font-semibold text-slate-700">{{ date('d M Y', strtotime($draft['finalized_at'])) }}</p>
                                    <p class="text-[0.65rem] text-slate-400">oleh {{ $draft['finalized_by_name'] ?? '-' }}</p>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="text-center font-semibold text-slate-700">{{ $draft['total_ordered'] ?? 0 }}</td>
                            <td class="text-center font-semibold text-emerald-600">{{ $draft['total_received'] ?? 0 }}</td>
                            <td class="text-center font-semibold {{ ($draft['total_pending'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-300' }}">
                                {{ $draft['total_pending'] ?? 0 }}
                            </td>
                            <td style="min-width:140px;">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $rsMeta[2] }} rounded-full transition-all" style="width: {{ $draft['progress_pct'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-[0.65rem] font-bold text-slate-600">{{ $draft['progress_pct'] ?? 0 }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold {{ $rsMeta[1] }}">
                                    {{ $rsMeta[0] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('staf-admin.procurement-approved.detail', $draft['id']) }}"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                    Lihat Detail
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($drafts) > 0)
            <x-pagination :total="count($drafts)" />
        @endif
    @endif
</div>
@endsection
