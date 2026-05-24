@extends('layouts.app')

@section('title', 'Detail Draf Disetujui')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'draft'])

{{-- Back --}}
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('staf-admin.procurement-approved') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar Draf
    </a>
</div>

{{-- Header --}}
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2 flex-wrap">
        <span class="badge badge-finalized text-xs">✓ Finalized</span>
        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Terkunci
        </span>
    </div>
    <h1 class="text-2xl font-bold text-slate-900">{{ $draft['title'] }}</h1>
    <p class="text-sm text-slate-500 mt-1">Detail draf pengadaan yang sudah difinalisasi oleh Kaprodi</p>
</div>

{{-- ───── PROGRESS BANNER (3 fitur summary untuk draf ini) ───── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
    {{-- Penerimaan progress --}}
    <div class="glass-card rounded-2xl p-4 border-l-4 {{ $progress['receipt_status'] === 'selesai' ? 'border-emerald-500' : ($progress['receipt_status'] === 'sebagian' ? 'border-blue-500' : 'border-amber-400') }}">
        <p class="text-[0.65rem] font-bold text-slate-500 uppercase tracking-wider">Penerimaan Barang</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $progress['received'] }}<span class="text-slate-400 text-sm font-normal"> / {{ $progress['ordered'] }}</span></p>
        <div class="h-1.5 mt-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $progress['receipt_status'] === 'selesai' ? 'bg-emerald-500' : ($progress['receipt_status'] === 'sebagian' ? 'bg-blue-500' : 'bg-amber-400') }}"
                 style="width: {{ $progress['pct'] }}%"></div>
        </div>
        <p class="text-[0.65rem] text-slate-500 mt-1">{{ $progress['pct'] }}% diterima · sisa {{ $progress['remaining'] }} item</p>
    </div>

    {{-- Pelabelan progress --}}
    <div class="glass-card rounded-2xl p-4 border-l-4 {{ $progress['assets_unlabeled'] === 0 && $progress['assets_created'] > 0 ? 'border-emerald-500' : 'border-amber-400' }}">
        <p class="text-[0.65rem] font-bold text-slate-500 uppercase tracking-wider">Pelabelan Aset</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $progress['assets_labeled'] }}<span class="text-slate-400 text-sm font-normal"> / {{ $progress['assets_created'] }}</span></p>
        @if($progress['assets_created'] > 0)
            <div class="h-1.5 mt-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $progress['assets_unlabeled'] === 0 ? 'bg-emerald-500' : 'bg-amber-400' }}"
                     style="width: {{ $progress['assets_created'] > 0 ? round($progress['assets_labeled']/$progress['assets_created']*100) : 0 }}%"></div>
            </div>
            <p class="text-[0.65rem] text-slate-500 mt-1">{{ $progress['assets_unlabeled'] }} aset belum berlabel</p>
        @else
            <p class="text-[0.65rem] text-slate-400 mt-3 italic">Belum ada aset — terima barang dulu</p>
        @endif
    </div>

    {{-- Status overall --}}
    @php
        $overallStatus = match(true) {
            $progress['ordered'] === 0                          => ['Belum Ada Item',     'bg-slate-50', 'text-slate-600'],
            $progress['receipt_status'] === 'belum'             => ['Menunggu Penerimaan','bg-amber-50', 'text-amber-700'],
            $progress['receipt_status'] === 'sebagian'          => ['Penerimaan Berjalan','bg-blue-50',  'text-blue-700'],
            $progress['assets_unlabeled'] > 0                   => ['Perlu Pelabelan',    'bg-amber-50', 'text-amber-700'],
            default                                             => ['Selesai',            'bg-emerald-50','text-emerald-700'],
        };
    @endphp
    <div class="glass-card rounded-2xl p-4 flex flex-col justify-center {{ $overallStatus[1] }}">
        <p class="text-[0.65rem] font-bold text-slate-500 uppercase tracking-wider">Status Draf</p>
        <p class="text-lg font-bold {{ $overallStatus[2] }} mt-2">{{ $overallStatus[0] }}</p>
        <p class="text-[0.65rem] text-slate-500 mt-1">{{ count($approvedItems) }} item disetujui</p>
    </div>
</div>

{{-- ───── PRIMARY CTAs ───── --}}
@if(!empty($approvedItems))
    <div class="glass-card rounded-2xl p-4 mb-6 bg-gradient-to-r from-indigo-50 to-emerald-50 border border-indigo-100">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex-1 min-w-[200px]">
                <p class="text-sm font-bold text-slate-800">Lanjutkan ke langkah berikutnya</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($progress['receipt_status'] !== 'selesai')
                        Catat penerimaan barang yang sudah datang.
                    @elseif($progress['assets_unlabeled'] > 0)
                        Beri nomor label dan foto QR untuk aset yang sudah diterima.
                    @else
                        Semua tugas untuk draf ini sudah selesai ✓
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                @if($progress['receipt_status'] !== 'selesai')
                    <a href="{{ route('staf-admin.goods-receipt', $draft['id']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                        </svg>
                        Catat Penerimaan
                    </a>
                @endif
                @if($progress['assets_unlabeled'] > 0)
                    <a href="{{ route('staf-admin.inventory-label') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Update Label
                    </a>
                @endif
                @if($progress['receipt_status'] === 'selesai' && $progress['assets_unlabeled'] === 0 && $progress['assets_created'] > 0)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 rounded-xl">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Selesai
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Draft meta --}}
<div class="glass-card rounded-2xl p-5 mb-5">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
            $metaFields = [
                ['Laboratorium',     $draft['lab_name']],
                ['Tahun Anggaran',   $draft['budget_year']],
                ['Dibuat oleh',      $draft['created_by_name']],
                ['Difinalisasi oleh',$draft['finalized_by_name'] ?? '—'],
                ['Tgl Finalisasi',   $draft['finalized_at'] ? date('d M Y', strtotime($draft['finalized_at'])) : '—'],
            ];
        @endphp
        @foreach($metaFields as [$label, $value])
            <div>
                <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ $label }}</p>
                <p class="text-sm font-semibold text-slate-800">{{ $value }}</p>
            </div>
        @endforeach
        @if($draft['notes'] ?? null)
            <div class="col-span-2 sm:col-span-3">
                <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1">Catatan</p>
                <p class="text-sm text-slate-600">{{ $draft['notes'] }}</p>
            </div>
        @endif
    </div>
</div>

{{-- ───── APPROVED ITEMS — with per-item progress ───── --}}
<div class="glass-card rounded-2xl overflow-hidden mb-5">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <span class="badge badge-approved text-xs">✓ Disetujui</span>
            <span class="text-sm font-bold text-slate-900">{{ count($approvedItems) }} Item</span>
        </div>
        @php $total = array_sum(array_map(fn($i) => $i['estimated_price'] * $i['quantity'], $approvedItems)); @endphp
        <div class="text-right">
            <p class="text-[0.68rem] text-slate-400 uppercase tracking-wider">Total Estimasi</p>
            <p class="text-base font-bold text-emerald-600">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>
    </div>

    @if(empty($approvedItems))
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-slate-400">Tidak ada item yang disetujui dalam draf ini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Diterima</th>
                        <th class="text-center">Berlabel</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedItems as $i => $item)
                        @php
                            $rs = $item['receipt_status'] ?? 'belum';
                            $rsMeta = match($rs) {
                                'selesai'  => ['Diterima Lengkap', 'bg-emerald-100 text-emerald-700','bg-emerald-500'],
                                'sebagian' => ['Sebagian',         'bg-blue-100 text-blue-700',     'bg-blue-500'],
                                default    => ['Belum',            'bg-amber-100 text-amber-700',   'bg-amber-400'],
                            };
                        @endphp
                        <tr>
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $item['item_name'] }}</td>
                            <td>
                                <span class="badge badge-active text-xs">{{ ucfirst($item['item_type']) }}</span>
                            </td>
                            <td class="text-center font-semibold text-slate-700">{{ $item['quantity'] }}</td>
                            <td class="text-center font-semibold text-emerald-600">{{ $item['received_qty'] ?? 0 }}</td>
                            <td class="text-center">
                                @if(($item['assets_count'] ?? 0) > 0)
                                    <span class="text-xs font-semibold {{ ($item['labeled_count'] ?? 0) >= $item['assets_count'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $item['labeled_count'] ?? 0 }} / {{ $item['assets_count'] }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            <td style="min-width:120px;">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $rsMeta[2] }} rounded-full" style="width: {{ $item['progress_pct'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-[0.65rem] font-bold text-slate-500">{{ $item['progress_pct'] ?? 0 }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold {{ $rsMeta[1] }}">
                                    {{ $rsMeta[0] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Total row --}}
        <div class="px-6 py-3 bg-emerald-50 border-t border-emerald-100 flex justify-end items-center gap-3">
            <span class="text-sm text-slate-600 font-semibold">Total Estimasi:</span>
            <span class="text-lg font-bold text-emerald-700 font-mono">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    @endif
</div>

{{-- Rejected items --}}
@if(!empty($rejectedItems))
    <div class="glass-card rounded-2xl overflow-hidden mb-5">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <span class="badge badge-rejected text-xs">✗ Ditolak</span>
            <span class="text-sm font-bold text-slate-900">{{ count($rejectedItems) }} Item</span>
        </div>
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Alasan Penolakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rejectedItems as $i => $item)
                        <tr class="opacity-60">
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td class="line-through text-slate-600">{{ $item['item_name'] }}</td>
                            <td class="text-slate-500 text-xs">{{ ucfirst($item['item_type']) }}</td>
                            <td class="text-slate-500">{{ $item['quantity'] }}</td>
                            <td class="text-slate-500 font-mono text-xs">Rp {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                            <td class="text-red-500 text-xs font-semibold">{{ $item['review_note'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
