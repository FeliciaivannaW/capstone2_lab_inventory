@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'receipt'])

{{-- Back --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('staf-admin.procurement-approved.detail', $draft['id']) }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Detail Draf
    </a>
</div>

<div class="mb-7">
    <h1 class="text-2xl font-bold text-slate-900">Penerimaan Barang</h1>
    <p class="text-sm text-slate-500 mt-1">
        Draf: <span class="font-semibold text-slate-700">{{ $draft['title'] }}</span>
        · Lab: <span class="font-semibold text-slate-700">{{ $draft['lab_name'] }}</span>
        · Tahun: <span class="font-semibold text-slate-700">{{ $draft['budget_year'] }}</span>
    </p>
</div>

<div class="glass-card rounded-2xl overflow-hidden" x-data="receiptApp()">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-900">Item Disetujui — Input Tanggal Terima</h2>
        <p class="text-xs text-slate-400 mt-0.5">Item dapat diterima secara bertahap</p>
    </div>

    @if(empty($approvedItems))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm text-slate-400">Tidak ada item yang disetujui untuk diterima.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="num">#</x-sort-header>
                        <x-sort-header field="name">Nama Barang</x-sort-header>
                        <x-sort-header field="type">Tipe</x-sort-header>
                        <x-sort-header field="ordered">Dipesan</x-sort-header>
                        <x-sort-header field="received">Diterima</x-sort-header>
                        <x-sort-header field="progress">Progress</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($approvedItems as $i => $item)
                        @php
                            $receipts = $receiptMap[$item['id']] ?? [];
                            $totalReceived = collect($receipts)->sum('quantity_received');
                            $ordered = $item['quantity'];
                            $pct = $ordered > 0 ? round(($totalReceived / $ordered) * 100) : 0;
                            $isDone = $totalReceived >= $ordered;
                        @endphp
                        <tr x-show="showRow({{ $i }})" x-cloak>
                            <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $item['item_name'] }}</td>
                            <td>
                                <span class="badge badge-active text-xs">{{ ucfirst($item['item_type']) }}</span>
                            </td>
                            <td class="font-semibold text-slate-700">{{ $ordered }}</td>
                            <td class="font-semibold {{ $isDone ? 'text-emerald-600' : 'text-amber-600' }}">{{ $totalReceived }}</td>
                            <td style="min-width:140px;">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="{{ $isDone ? 'bg-emerald-500' : 'bg-amber-400' }} h-full rounded-full"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 flex-shrink-0">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($isDone)
                                    <span class="badge badge-approved text-xs">Lengkap</span>
                                @else
                                    <span class="badge badge-pending text-xs">Belum Lengkap</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if(!$isDone)
                                        <button
                                            @click="openModal({{ $item['id'] }}, '{{ addslashes($item['item_name']) }}', {{ $ordered }}, {{ $totalReceived }})"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 rounded-lg transition-colors">
                                            + Terima
                                        </button>
                                    @else
                                        <a href="{{ route('staf-admin.inventory-label', ['source_draft' => $draft['id']]) }}"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            Beri Label
                                        </a>
                                    @endif
                                    @if(!empty($receipts))
                                        <button @click="toggleHistory({{ $item['id'] }})"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Log
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if(!empty($receipts))
                            <tr x-show="showRow({{ $i }}) && openHistories.includes({{ $item['id'] }})" x-cloak>
                                <td colspan="8" class="bg-slate-50 px-6 py-3">
                                    <p class="text-xs font-bold text-slate-600 mb-2">Riwayat Penerimaan</p>
                                    <table class="lv-table text-xs">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Qty</th>
                                                <th>Diterima oleh</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($receipts as $r)
                                                <tr>
                                                    <td>{{ date('d M Y', strtotime($r['received_date'])) }}</td>
                                                    <td class="font-semibold">{{ $r['quantity_received'] }}</td>
                                                    <td>{{ $r['received_by_name'] ?? '—' }}</td>
                                                    <td class="text-slate-400">{{ $r['note'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($approvedItems) > 0)
            <x-pagination :total="count($approvedItems)" />
        @endif

        {{-- Receipt Modal --}}
        <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-navy/60 backdrop-blur-sm" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-base font-bold text-slate-900 mb-1">Input Penerimaan Barang</h3>
                <p class="text-sm text-slate-500 mb-5" x-text="'Barang: ' + modalItemName"></p>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Tanggal Terima <span class="text-red-500">*</span></label>
                        <input type="date" x-model="receivedDate"
                               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                            Jumlah Diterima <span class="text-red-500">*</span>
                            <span class="text-slate-400 font-normal normal-case" x-text="'(sisa: ' + modalRemaining + ')'"></span>
                        </label>
                        <input type="number" x-model="qtyReceived" :max="modalRemaining" min="1"
                               class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Catatan</label>
                        <textarea x-model="receiptNote" rows="2" placeholder="Opsional..."
                                  class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl resize-none focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-5">
                    <button @click="modalOpen = false" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button @click="submitReceipt()" class="flex-1 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function receiptApp() {
    return {
        ...window.tablePaginationData({{ count($approvedItems) }}),
        modalOpen: false,
        modalItemId: null,
        modalItemName: '',
        modalRemaining: 0,
        receivedDate: '{{ date('Y-m-d') }}',
        qtyReceived: '',
        receiptNote: '',
        openHistories: [],

        openModal(id, name, ordered, received) {
            this.modalItemId = id;
            this.modalItemName = name;
            this.modalRemaining = ordered - received;
            this.qtyReceived = '';
            this.receiptNote = '';
            this.modalOpen = true;
        },

        toggleHistory(id) {
            const idx = this.openHistories.indexOf(id);
            if (idx >= 0) this.openHistories.splice(idx, 1);
            else this.openHistories.push(id);
        },

        async submitReceipt() {
            if (!this.receivedDate || !this.qtyReceived) { alert('Lengkapi tanggal dan jumlah'); return; }
            const data = {
                procurement_item_id: this.modalItemId,
                received_date: this.receivedDate,
                quantity_received: parseInt(this.qtyReceived),
                note: this.receiptNote || null
            };
            try {
                const res = await fetch('{{ route('staf-admin.goods-receipt.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(data)
                });
                const d = await res.json();
                if (d.status === 'success') { alert('Penerimaan berhasil dicatat!'); location.reload(); }
                else alert('Error: ' + (d.message || 'Gagal'));
            } catch(e) { alert('Terjadi kesalahan'); }
            this.modalOpen = false;
        }
    }
}
</script>
@endpush
@endsection
