@extends('layouts.app')

@section('title', 'Log Maintenance')

@section('content')
<div x-data="{ activeModal: null }">
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Log Maintenance Inventaris</h1>
            <p class="text-sm text-slate-500 mt-1">Input maintenance, update kondisi aset, dan kurangi stok BHP otomatis saat dipakai.</p>
        </div>
        <button type="button" @click="activeModal = 'tambah_maintenance'" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-5 py-2.5 hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Log Maintenance
        </button>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @php
        $conditionClass = ['baik'=>'badge-approved','rusak_ringan'=>'badge-pending','rusak_berat'=>'badge-rejected','maintenance'=>'badge-active','dihapus'=>'badge-rejected','diganti'=>'badge-draft'];
        $statusClass = ['planned'=>'badge-draft','in_progress'=>'badge-active','done'=>'badge-approved','cancelled'=>'badge-rejected'];
    @endphp

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800">Riwayat Maintenance</p>
                <p class="text-xs text-slate-400">{{ count($logs) }} log maintenance</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Cari aset/masalah" class="rounded-xl border-slate-200 text-sm">
                <select name="status" class="rounded-xl border-slate-200 text-sm">
                    <option value="">Semua Status</option>
                    <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4 py-2">Filter</button>
            </form>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md">{{ $log['asset_code'] }}</span>
                                <span class="text-sm font-bold text-slate-800">{{ $log['item_name'] }}</span>
                                <span class="badge {{ $statusClass[$log['status']] ?? 'badge-draft' }} text-xs">{{ ucfirst(str_replace('_', ' ', $log['status'])) }}</span>
                                <span class="badge {{ $conditionClass[$log['condition_after']] ?? 'badge-draft' }} text-xs">{{ ucfirst(str_replace('_', ' ', $log['condition_after'])) }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mb-2">{{ $log['maintenance_date'] }} · {{ $log['performed_by_name'] }} · {{ $log['room_name'] ?? 'tanpa ruangan' }}</p>
                            @if($log['issue_description'])
                                <p class="text-sm text-slate-600"><span class="font-semibold">Masalah:</span> {{ $log['issue_description'] }}</p>
                            @endif
                            @if($log['action_taken'])
                                <p class="text-sm text-slate-600"><span class="font-semibold">Tindakan:</span> {{ $log['action_taken'] }}</p>
                            @endif
                            @if(!empty($log['bhp_usages']))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($log['bhp_usages'] as $usage)
                                        <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
                                            {{ $usage['item_name'] }} -{{ $usage['quantity'] }} {{ $usage['unit'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 lg:text-right whitespace-nowrap">
                            Biaya<br>
                            <span class="text-sm font-bold text-slate-900">Rp {{ number_format($log['cost'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-sm text-slate-400">Belum ada log maintenance.</div>
            @endforelse
        </div>
    </div>

    <!-- Modal Input Maintenance -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_maintenance'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'tambah_maintenance'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                 @click="activeModal = null">
            </div>

            <div x-show="activeModal === 'tambah_maintenance'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh]"
                 x-data="{
                     bhpRows: [],
                     bhpStocks: @js($stocks),
                     addBhpRow() {
                         this.bhpRows.push({ stock_id: '', quantity: 1 });
                     },
                     removeBhpRow(index) {
                         this.bhpRows.splice(index, 1);
                     }
                 }"
                 x-init="
                     $watch('activeModal', (val) => {
                         if (val === 'tambah_maintenance') {
                             setTimeout(() => {
                                 const el = document.getElementById('asset-select');
                                 if (el && !el.tomselect) {
                                     new TomSelect(el, {
                                         placeholder: 'Cari atau pilih aset...',
                                         controlInput: '<input>',
                                         render: {
                                             no_results: function() {
                                                 return '<div class=\'p-3 text-sm text-slate-400 text-center\'>Aset tidak ditemukan</div>';
                                             }
                                         }
                                     });
                                 }
                             }, 100);
                         }
                     });
                 ">
                
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
                    <div>
                        <h3 class="font-bold text-slate-800">Input Log Maintenance</h3>
                        <p class="text-xs text-slate-500">Catat tindakan pemeliharaan aset</p>
                    </div>
                    <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('maintenance.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    <div class="p-6 overflow-y-auto space-y-4">
                        <!-- 1. Aset Inventaris (Searchable via Tom Select) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Aset Inventaris</label>
                            <select id="asset-select" name="inventory_asset_id" class="w-full rounded-xl border-slate-200 text-sm" required>
                                <option value="" disabled selected hidden>Cari atau pilih aset...</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset['id'] }}">
                                        {{ $asset['asset_code'] }} — {{ $asset['item_name'] ?? $asset['catalog_name'] ?? 'Aset' }} {{ $asset['label_number'] ? '(' . $asset['label_number'] . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- 2. Tanggal + Status -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Tanggal</label>
                                <input type="date" name="maintenance_date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                                <select name="status" class="w-full rounded-xl border-slate-200 text-sm" required>
                                    <option value="" disabled selected hidden>Pilih Status</option>
                                    <option value="planned">Planned</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- 3. Masalah -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Masalah</label>
                            <textarea name="issue_description" rows="2" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Contoh: keyboard tidak responsif"></textarea>
                        </div>
                        
                        <!-- 4. Tindakan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Tindakan</label>
                            <textarea name="action_taken" rows="2" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Contoh: ganti switch keyboard dan cleaning"></textarea>
                        </div>
                        
                        <!-- 5. Kondisi Akhir + Biaya -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Kondisi Akhir</label>
                                <select name="condition_after" class="w-full rounded-xl border-slate-200 text-sm" required>
                                    <option value="" disabled selected hidden>Pilih Kondisi</option>
                                    <option value="baik">Baik</option>
                                    <option value="rusak_ringan">Rusak Ringan</option>
                                    <option value="rusak_berat">Rusak Berat</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="dihapus">Dihapus</option>
                                    <option value="diganti">Diganti</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Biaya</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400 pointer-events-none select-none">Rp</span>
                                    <input type="number" min="0" name="cost" value="0" class="w-full rounded-xl border-slate-200 text-sm !pl-9">
                                </div>
                            </div>
                        </div>

                        <!-- 6. BHP yang Dipakai (Dynamic) -->
                        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-bold text-amber-800">BHP yang dipakai</p>
                                <button type="button" @click="addBhpRow()" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-900 transition-colors bg-amber-100 hover:bg-amber-200 px-2.5 py-1 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah BHP
                                </button>
                            </div>
                            
                            <!-- Empty state -->
                            <div x-show="bhpRows.length === 0" class="text-center py-3">
                                <p class="text-xs text-amber-600/70">Belum ada BHP yang dipakai.</p>
                                <p class="text-[0.65rem] text-amber-600/50 mt-0.5">Klik "+ Tambah BHP" untuk menambahkan.</p>
                            </div>

                            <!-- Dynamic rows -->
                            <template x-for="(row, index) in bhpRows" :key="index">
                                <div class="flex items-start gap-2 mb-2 last:mb-0">
                                    <select :name="'bhp_stock_id[]'" x-model="row.stock_id" class="flex-1 rounded-xl border-amber-200 text-xs" required>
                                        <option value="" disabled>Pilih BHP</option>
                                        <template x-for="stock in bhpStocks" :key="stock.id">
                                            <option :value="stock.id" x-text="stock.item_name + ' — stok ' + stock.current_stock + ' ' + stock.unit"></option>
                                        </template>
                                    </select>
                                    <input type="number" min="1" :name="'bhp_quantity[]'" x-model="row.quantity" class="w-20 rounded-xl border-amber-200 text-xs text-center" placeholder="Qty" required>
                                    <button type="button" @click="removeBhpRow(index)" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>

                            <p x-show="bhpRows.length > 0" class="text-[0.68rem] text-amber-700 mt-2">Saat form disimpan, stok BHP otomatis berkurang sesuai qty.</p>
                        </div>

                        <!-- 7. Catatan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Catatan</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Tambahkan catatan tambahan jika diperlukan..."></textarea>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-slate-100 bg-white flex gap-3 shrink-0">
                        <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700 transition-colors shadow-sm">
                            Simpan Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<!-- Tom Select CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<style>
    /* Tom Select custom styling to match the app's design */
    .ts-wrapper.single .ts-control {
        border-radius: 0.75rem !important;
        border-color: #e2e8f0 !important;
        padding: 0.55rem 0.875rem !important;
        font-size: 0.875rem !important;
        background: #fff !important;
        box-shadow: none !important;
        min-height: unset !important;
        transition: border-color 0.15s, box-shadow 0.15s !important;
    }
    .ts-wrapper.single .ts-control:hover {
        border-color: #cbd5e1 !important;
    }
    .ts-wrapper.single.focus .ts-control {
        border-color: #6366F1 !important;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
    }
    .ts-wrapper .ts-control > input {
        font-size: 0.875rem !important;
    }
    .ts-wrapper .ts-control > .item {
        font-size: 0.875rem !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        margin-top: 4px !important;
        overflow: hidden !important;
    }
    .ts-dropdown .option {
        font-size: 0.8125rem !important;
        padding: 8px 12px !important;
    }
    .ts-dropdown .option.active {
        background: #EEF2FF !important;
        color: #4338CA !important;
    }
    .ts-dropdown .option:hover {
        background: #F8FAFC !important;
    }
    /* Biaya input left padding for Rp prefix */
    input.\\!pl-9 {
        padding-left: 2.25rem !important;
    }
</style>
@endpush