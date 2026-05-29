@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'receipt'])

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

<div x-data="receiptIndexApp()">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Penerimaan Barang</h1>
            <p class="text-sm text-slate-500 mt-1">
                Catat tanggal barang masuk untuk setiap item draf yang sudah disetujui.
                Barang bisa datang bertahap — catat sesuai kedatangannya.
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Fitur 3 — Input Penerimaan
        </span>
    </div>

    {{-- Summary band --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['Total Draf',      $summary['drafts_total'],   'bg-indigo-50',   'text-indigo-600',   'border-indigo-200'],
                ['Belum Diterima',  $summary['drafts_belum'],   'bg-rose-50',     'text-rose-600',     'border-rose-200'],
                ['Sebagian',        $summary['drafts_sebagian'],'bg-amber-50',    'text-amber-600',    'border-amber-200'],
                ['Selesai',         $summary['drafts_selesai'], 'bg-emerald-50',  'text-emerald-600',  'border-emerald-200'],
            ];
        @endphp
        @foreach($cards as [$label, $val, $bg, $tx, $bd])
            <div class="glass-card rounded-2xl p-5 border {{ $bd }}">
                <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">{{ $label }}</p>
                <p class="text-3xl font-bold {{ $tx }} mt-1 leading-none">{{ $val }}</p>
                <p class="text-[0.7rem] text-slate-400 mt-2">draf pengadaan</p>
            </div>
        @endforeach
    </div>

    {{-- Global progress strip --}}
    <div class="glass-card rounded-2xl p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Progress Penerimaan Keseluruhan</p>
                <p class="text-sm text-slate-700 mt-0.5">
                    <span class="font-bold text-slate-900">{{ $summary['items_received'] }}</span>
                    dari
                    <span class="font-bold text-slate-900">{{ $summary['items_ordered'] }}</span>
                    item diterima
                </p>
            </div>
            <span class="text-2xl font-bold text-indigo-600">{{ $summary['pct'] }}%</span>
        </div>
        <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all"
                 style="width: {{ $summary['pct'] }}%"></div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="glass-card rounded-2xl p-4 mb-6 flex flex-wrap gap-3 items-center">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Cari nama draf…"
                   class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
        </div>

        {{-- Status pills --}}
        @php $active = $filters['receipt_status'] ?? ''; @endphp
        <div class="inline-flex bg-slate-100 rounded-lg p-1 text-xs font-semibold">
            @foreach([['','Semua'],['belum','Belum'],['sebagian','Sebagian'],['selesai','Selesai']] as [$v,$l])
                <a href="?{{ http_build_query(array_merge(request()->query(), ['receipt_status' => $v])) }}"
                   class="px-3 py-1.5 rounded-md transition-colors
                          {{ $active === $v ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ $l }}
                </a>
            @endforeach
        </div>

        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
            Terapkan
        </button>
        @if(!empty($filters['search']) || !empty($filters['receipt_status']))
            <a href="{{ route('staf-admin.goods-receipt-index') }}"
               class="text-xs text-slate-500 hover:text-slate-800 font-semibold underline-offset-2 hover:underline">
                Reset
            </a>
        @endif
    </form>

    {{-- Draft groups --}}
    @if(empty($groups))
        <div class="glass-card rounded-2xl py-20 text-center">
            <svg class="w-14 h-14 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
            </svg>
            <p class="text-sm text-slate-500 font-semibold">Tidak ada draf untuk ditampilkan</p>
            <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau tunggu draf difinalisasi oleh Kaprodi.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach($groups as $g)
                @php
                    $statusMeta = match($g['status']) {
                        'selesai'  => ['Semua Diterima',  'bg-emerald-100','text-emerald-700','bg-emerald-500'],
                        'sebagian' => ['Sebagian Diterima','bg-blue-100',  'text-blue-700',   'bg-blue-500'],
                        default    => ['Menunggu Penerimaan','bg-amber-100','text-amber-700', 'bg-amber-400'],
                    };
                @endphp

                <div class="glass-card rounded-2xl overflow-hidden hover:shadow-lg transition-shadow"
                     x-data="{ open: true }">

                    {{-- Group header --}}
                    <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center gap-4">
                        <button @click="open = !open" class="text-slate-400 hover:text-indigo-600 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $g['title'] }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-semibold {{ $statusMeta[1] }} {{ $statusMeta[2] }}">
                                    {{ $statusMeta[0] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Lab: <span class="font-semibold text-slate-700">{{ $g['lab_name'] }}</span>
                                · Tahun: <span class="font-semibold text-slate-700">{{ $g['budget_year'] }}</span>
                                @if($g['finalized_at'])
                                    · Final: <span class="font-semibold text-slate-700">{{ date('d M Y', strtotime($g['finalized_at'])) }}</span>
                                @endif
                            </p>
                        </div>

                        {{-- Mini progress --}}
                        <div class="flex items-center gap-3 min-w-[200px]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between text-[0.65rem] font-semibold text-slate-500 mb-1">
                                    <span>{{ $g['total_received'] }} / {{ $g['total_ordered'] }} item</span>
                                    <span class="text-slate-700">{{ $g['pct'] }}%</span>
                                </div>
                                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $statusMeta[3] }} rounded-full" style="width: {{ $g['pct'] }}%"></div>
                                </div>
                            </div>
                            <a href="{{ route('staf-admin.procurement-approved.detail', $g['id']) }}"
                               class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex-shrink-0">
                                Detail →
                            </a>
                        </div>
                    </div>

                    {{-- Item cards --}}
                    <div x-show="open" x-collapse class="bg-slate-50/50 p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        @foreach($g['items'] as $it)
                            @php
                                $itemMeta = match($it['status']) {
                                    'selesai'  => ['Diterima Lengkap', 'border-emerald-200','bg-emerald-50','text-emerald-700','bg-emerald-500'],
                                    'sebagian' => ['Sebagian Diterima', 'border-blue-200',   'bg-blue-50',   'text-blue-700',    'bg-blue-500'],
                                    default    => ['Belum Diterima',    'border-amber-200',  'bg-amber-50',  'text-amber-700',   'bg-amber-400'],
                                };
                            @endphp

                            <div class="bg-white rounded-xl border {{ $itemMeta[1] }} p-4 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $it['name'] }}</p>
                                        <span class="badge badge-active text-[0.65rem] mt-1">{{ ucfirst($it['type']) }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.6rem] font-bold {{ $itemMeta[2] }} {{ $itemMeta[3] }} flex-shrink-0">
                                        {{ $itemMeta[0] }}
                                    </span>
                                </div>

                                {{-- Qty stats --}}
                                <div class="grid grid-cols-3 gap-2 my-3 py-2 border-y border-slate-100">
                                    <div class="text-center">
                                        <p class="text-[0.6rem] font-semibold text-slate-400 uppercase tracking-wider">Pesan</p>
                                        <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $it['ordered'] }}</p>
                                    </div>
                                    <div class="text-center border-x border-slate-100">
                                        <p class="text-[0.6rem] font-semibold text-slate-400 uppercase tracking-wider">Terima</p>
                                        <p class="text-sm font-bold text-emerald-600 mt-0.5">{{ $it['received'] }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[0.6rem] font-semibold text-slate-400 uppercase tracking-wider">Sisa</p>
                                        <p class="text-sm font-bold {{ $it['remaining'] > 0 ? 'text-amber-600' : 'text-slate-300' }} mt-0.5">{{ $it['remaining'] }}</p>
                                    </div>
                                </div>

                                {{-- Progress bar --}}
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $itemMeta[4] }} rounded-full transition-all" style="width: {{ $it['pct'] }}%"></div>
                                    </div>
                                    <span class="text-[0.65rem] font-bold text-slate-500">{{ $it['pct'] }}%</span>
                                </div>

                                {{-- Action --}}
                                <div class="flex items-center gap-2">
                                    @if($it['status'] !== 'selesai')
                                        <button
                                            @click="openModal({{ $it['id'] }}, '{{ addslashes($it['name']) }}', {{ $it['ordered'] }}, {{ $it['received'] }})"
                                            class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Tandai Diterima
                                        </button>
                                    @else
                                        {{-- Item lengkap → CTA ke Fitur 2 (Label) --}}
                                        <a href="{{ route('staf-admin.inventory-label', ['source_draft' => $g['id']]) }}"
                                           class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            Beri Label
                                        </a>
                                    @endif
                                    @if(!empty($it['receipts']))
                                        <button @click="toggleLog({{ $it['id'] }})"
                                                class="inline-flex items-center text-xs font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition-colors">
                                            Log
                                        </button>
                                    @endif
                                </div>

                                {{-- Receipt history (toggle) --}}
                                @if(!empty($it['receipts']))
                                    <div x-show="openLogs.includes({{ $it['id'] }})" x-collapse class="mt-3 pt-3 border-t border-slate-100">
                                        <p class="text-[0.6rem] font-bold text-slate-500 uppercase tracking-wider mb-2">Riwayat</p>
                                        <ul class="space-y-1.5">
                                            @foreach($it['receipts'] as $r)
                                                <li class="flex items-center justify-between text-[0.7rem]">
                                                    <span class="text-slate-600">
                                                        {{ date('d M Y', strtotime($r['received_date'])) }}
                                                        · oleh <span class="font-semibold">{{ $r['received_by_name'] ?? '—' }}</span>
                                                    </span>
                                                    <span class="font-bold text-emerald-600">+{{ $r['quantity_received'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ───── Receipt Modal ───── --}}
    <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            <div class="flex items-start gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-slate-900">Catat Penerimaan</h3>
                    <p class="text-xs text-slate-500 mt-0.5 truncate" x-text="modalItemName"></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Tanggal Terima <span class="text-red-500">*</span>
                    </label>
                    <input type="date" x-model="receivedDate"
                           max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <div>
                    <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                        Jumlah Diterima <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal normal-case" x-text="'(sisa: ' + modalRemaining + ')'"></span>
                    </label>
                    <input type="number" x-model="qtyReceived" :max="modalRemaining" min="1"
                           class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <div>
                    <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Catatan</label>
                    <textarea x-model="receiptNote" rows="2" placeholder="Opsional…"
                              class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl resize-none focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"></textarea>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button @click="modalOpen = false"
                        class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button @click="submitReceipt()" :disabled="loading"
                        class="flex-1 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-text="loading ? 'Menyimpan…' : 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function receiptIndexApp() {
    return {
        modalOpen: false,
        modalItemId: null,
        modalItemName: '',
        modalRemaining: 0,
        receivedDate: '{{ date('Y-m-d') }}',
        qtyReceived: '',
        receiptNote: '',
        openLogs: [],
        loading: false,

        openModal(id, name, ordered, received) {
            this.modalItemId = id;
            this.modalItemName = name;
            this.modalRemaining = ordered - received;
            this.qtyReceived = this.modalRemaining;
            this.receiptNote = '';
            this.modalOpen = true;
        },

        toggleLog(id) {
            const idx = this.openLogs.indexOf(id);
            if (idx >= 0) this.openLogs.splice(idx, 1);
            else this.openLogs.push(id);
        },

        async submitReceipt() {
            if (!this.receivedDate || !this.qtyReceived) {
                alert('Lengkapi tanggal dan jumlah');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route('staf-admin.goods-receipt.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        procurement_item_id: this.modalItemId,
                        received_date: this.receivedDate,
                        quantity_received: parseInt(this.qtyReceived),
                        note: this.receiptNote || null
                    })
                });
                const d = await res.json();
                if (d.status === 'success') {
                    this.modalOpen = false;
                    location.reload();
                } else {
                    alert('Error: ' + (d.message || 'Gagal'));
                }
            } catch (e) {
                alert('Terjadi kesalahan jaringan');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
