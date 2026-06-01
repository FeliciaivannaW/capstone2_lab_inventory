@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'receipt'])

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('staf-admin.procurement-approved.detail', $draft['id']) }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Detail Draf
    </a>
</div>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Penerimaan Barang</h1>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5">
            <span class="flex items-center gap-1.5 text-sm text-slate-500">
                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ $draft['title'] }}
            </span>
            <span class="text-slate-300">·</span>
            <span class="flex items-center gap-1.5 text-sm text-slate-500">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
                {{ $draft['lab_name'] }}
            </span>
            <span class="text-slate-300">·</span>
            <span class="text-sm text-slate-500">TA {{ $draft['budget_year'] }}</span>
        </div>
    </div>

    {{-- Summary chips --}}
    @php
        $totalItems = count($approvedItems);
        $doneItems  = collect($approvedItems)->filter(function($item) use ($receiptMap) {
            $received = collect($receiptMap[$item['id']] ?? [])->sum('quantity_received');
            return $received >= $item['quantity'];
        })->count();
    @endphp
    <div class="flex items-center gap-2 flex-shrink-0">
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-700">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            {{ $doneItems }}/{{ $totalItems }} Lengkap
        </div>
    </div>
</div>

@if(empty($approvedItems))
    <div class="glass-card rounded-2xl flex flex-col items-center justify-center py-20 text-center">
        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-slate-600">Tidak ada item yang perlu diterima</p>
        <p class="text-xs text-slate-400 mt-1">Semua item sudah lengkap atau belum ada yang disetujui</p>
    </div>
@else
    <div class="space-y-3" x-data="receiptApp()">

        @foreach($approvedItems as $i => $item)
            @php
                $receipts      = $receiptMap[$item['id']] ?? [];
                $totalReceived = collect($receipts)->sum('quantity_received');
                $ordered       = $item['quantity'];
                $pct           = $ordered > 0 ? round(($totalReceived / $ordered) * 100) : 0;
                $isDone        = $totalReceived >= $ordered;
                $remaining     = $ordered - $totalReceived;
            @endphp

            <div class="glass-card rounded-2xl overflow-hidden transition-all">
                {{-- Item row --}}
                <div class="flex items-center gap-4 px-5 py-4">
                    {{-- Status icon --}}
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                        {{ $isDone ? 'bg-emerald-100' : 'bg-amber-100' }}">
                        @if($isDone)
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ $item['item_name'] }}</p>
                            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full
                                {{ $item['item_type'] === 'inventory' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $item['item_type'] === 'inventory' ? 'Inventaris' : 'BHP' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-slate-500">Dipesan: <strong class="text-slate-700">{{ $ordered }}</strong></span>
                            <span class="text-xs text-slate-500">Diterima: <strong class="{{ $isDone ? 'text-emerald-600' : 'text-amber-600' }}">{{ $totalReceived }}</strong></span>
                            @if(!$isDone)
                                <span class="text-xs text-slate-500">Sisa: <strong class="text-red-500">{{ $remaining }}</strong></span>
                            @endif
                        </div>
                        {{-- Progress bar --}}
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="{{ $isDone ? 'bg-emerald-500' : 'bg-amber-400' }} h-full rounded-full transition-all"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-[11px] font-bold {{ $isDone ? 'text-emerald-600' : 'text-amber-600' }} flex-shrink-0">{{ $pct }}%</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(!empty($receipts))
                            <button @click="toggleHistory({{ $item['id'] }})"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="openHistories.includes({{ $item['id'] }}) ? 'Tutup' : 'Riwayat'"></span>
                            </button>
                        @endif

                        @if(!$isDone)
                            <button @click="openModal({{ $item['id'] }}, '{{ addslashes($item['item_name']) }}', {{ $ordered }}, {{ $totalReceived }})"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-2 rounded-xl transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Terima
                            </button>
                        @else
                            <a href="{{ route('staf-admin.inventory-label', ['source_draft' => $draft['id']]) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded-xl transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Beri Label
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Receipt history (expandable) --}}
                @if(!empty($receipts))
                    <div x-show="openHistories.includes({{ $item['id'] }})" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Riwayat Penerimaan</p>
                        <div class="space-y-1.5">
                            @foreach($receipts as $r)
                                <div class="flex items-center justify-between py-2 px-3 bg-white rounded-xl border border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-emerald-700">+{{ $r['quantity_received'] }}</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-slate-700">{{ date('d M Y', strtotime($r['received_date'])) }}</p>
                                            <p class="text-[11px] text-slate-400">oleh {{ $r['received_by_name'] ?? '—' }}</p>
                                        </div>
                                    </div>
                                    @if(!empty($r['note']))
                                        <p class="text-xs text-slate-400 italic max-w-xs text-right">{{ $r['note'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Modal Terima Barang --}}
        <template x-teleport="body">
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4" style="display:none;">
            
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>
            
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-3">

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
                               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <div>
                        <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                            Jumlah Diterima <span class="text-red-500">*</span>
                            <span class="text-slate-400 font-normal normal-case" x-text="'(sisa: ' + modalRemaining + ')'"></span>
                        </label>
                        <input type="number" x-model="qtyReceived" :max="modalRemaining" min="1"
                               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>

                    {{-- Separator --}}
                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-3">Informasi Pembelian <span class="font-normal text-slate-300">(opsional)</span></p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                                    Harga Beli / Unit
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-semibold">Rp</span>
                                    <input type="number" x-model="purchasePrice" min="0" step="1"
                                           placeholder="0"
                                           style="padding-left: 2.5rem;"
                                           class="w-full pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                </div>
                            </div>
                            <div>
                                <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                                    Tanggal Faktur
                                </label>
                                <input type="date" x-model="purchaseDate"
                                       max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[0.7rem] font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Catatan</label>
                        <textarea x-model="receiptNote" rows="2" placeholder="Kondisi barang saat diterima, nomor surat, dll..."
                                  class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl resize-none focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="modalOpen = false"
                            class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button @click="submitReceipt()" :disabled="submitting"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="submitting ? 'Menyimpan…' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </div>
        </template>

    </div>
@endif

@push('scripts')
<script>
function receiptApp() {
    return {
        modalOpen: false,
        submitting: false,
        modalItemId: null,
        modalItemName: '',
        modalRemaining: 0,
        today: new Date().toISOString().split('T')[0],
        receivedDate: new Date().toISOString().split('T')[0],
        qtyReceived: 1,
        receiptNote: '',
        purchasePrice: '',
        purchaseDate: '',
        openHistories: [],

        openModal(id, name, ordered, received) {
            this.modalItemId    = id;
            this.modalItemName  = name;
            this.modalRemaining = ordered - received;
            this.receivedDate   = new Date().toISOString().split('T')[0];
            this.qtyReceived    = 1;
            this.receiptNote    = '';
            this.purchasePrice  = '';
            this.purchaseDate   = '';
            this.submitting     = false;
            this.modalOpen      = true;
        },

        toggleHistory(id) {
            const idx = this.openHistories.indexOf(id);
            if (idx >= 0) this.openHistories.splice(idx, 1);
            else this.openHistories.push(id);
        },

        async submitReceipt() {
            if (!this.receivedDate || !this.qtyReceived || parseInt(this.qtyReceived) < 1) {
                alert('Lengkapi tanggal dan jumlah terlebih dahulu');
                return;
            }
            const qty = parseInt(this.qtyReceived);
            if (qty > this.modalRemaining) {
                alert('Jumlah melebihi sisa pesanan (' + this.modalRemaining + ')');
                return;
            }
            this.submitting = true;
            try {
                const res = await fetch('{{ route('staf-admin.goods-receipt.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        procurement_item_id: this.modalItemId,
                        received_date: this.receivedDate,
                        quantity_received: qty,
                        note: this.receiptNote || null
                    })
                });
                const d = await res.json();
                if (d.ok || d.status === 'success' || d.status !== 'error') {
                    this.modalOpen = false;
                    const receiptId = d.data?.receipt_id;
                    if (receiptId) {
                        window.location.href = '{{ route('staf-admin.inventory-label') }}?batch=' + receiptId;
                    } else {
                        location.reload();
                    }
                } else {
                    alert('Gagal: ' + (d.message || 'Terjadi kesalahan'));
                }
            } catch(e) {
                alert('Terjadi kesalahan jaringan, coba lagi');
            }
            this.submitting = false;
        }
    }
}
</script>
@endpush
@endsection
