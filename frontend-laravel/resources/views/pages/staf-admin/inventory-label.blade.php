@extends('layouts.app')
@section('title', 'Label & Foto Aset')

@section('content')
@include('components.staf-admin.workflow-strip', ['active' => 'label'])

@if(session('success'))
    <div id="flash-success" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
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

{{-- Page header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">Label & Foto Aset</h1>
    <p class="text-sm text-slate-500 mt-1">Berikan nomor label dan foto QR/Barcode untuk setiap aset yang sudah diterima.</p>
</div>

{{-- Tabs and Search --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-1 bg-slate-100 rounded-xl p-1 w-fit">
        <a href="{{ route('staf-admin.inventory-label') }}?tab=unlabeled&search={{ request('search') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $tab === 'unlabeled' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Perlu Dilabel
            @if($tab === 'unlabeled' && count($batches))
                <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full">{{ $paginator ? $paginator->total() : collect($batches)->sum('unlabeled_count') }}</span>
            @endif
        </a>
        <a href="{{ route('staf-admin.inventory-label') }}?tab=labeled&search={{ request('search') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $tab === 'labeled' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Sudah Dilabel
            @if($tab === 'labeled' && count($batches))
                <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-700 rounded-full">{{ $paginator ? $paginator->total() : collect($batches)->sum('labeled_count') }}</span>
            @endif
        </a>
    </div>

    {{-- Search Form --}}
    <form action="{{ route('staf-admin.inventory-label') }}" method="GET" class="relative w-full sm:w-72">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari pengadaan atau lab..."
               style="padding-left: 2.5rem;"
               class="w-full pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all bg-white shadow-sm">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </form>
</div>

@if(empty($batches))
    <div class="glass-card rounded-2xl flex flex-col items-center justify-center py-20 text-center">
        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        @if($tab === 'unlabeled')
            <p class="text-sm font-semibold text-slate-600">Semua aset sudah dilabel!</p>
            <p class="text-xs text-slate-400 mt-1">Tidak ada aset yang perlu dilabel saat ini.</p>
        @else
            <p class="text-sm font-semibold text-slate-600">Belum ada aset yang dilabel</p>
            <p class="text-xs text-slate-400 mt-1">Aset yang sudah dilabel akan muncul di sini.</p>
        @endif
    </div>
@else
    <div class="space-y-4" x-data="labelApp()" x-init="init()">

        @foreach($batches as $batch)
        @php
            $isHighlight = ($highlightBatch == $batch['receipt_id']);
            $pct = $batch['total_assets'] > 0 ? round(($batch['labeled_count'] / $batch['total_assets']) * 100) : 0;
        @endphp

        <div class="glass-card rounded-2xl overflow-hidden {{ $isHighlight ? 'ring-2 ring-indigo-400 ring-offset-2' : '' }}"
             id="batch-{{ $batch['receipt_id'] }}">

            {{-- Batch header --}}
            <div class="px-5 py-4 flex items-start gap-4 cursor-pointer hover:bg-slate-50/50 transition-colors"
                 @click="toggleBatch({{ $batch['receipt_id'] }})">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-bold text-slate-900">{{ $batch['draft_title'] }}</p>
                        <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $batch['lab_name'] }}</span>
                        @if($isHighlight)
                            <span class="text-xs font-semibold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full">Baru diterima</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Diterima {{ \Carbon\Carbon::parse($batch['received_date'])->locale('id')->isoFormat('D MMMM Y') }}
                        · {{ $batch['total_assets'] }} aset
                    </p>
                </div>
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-1 transition-transform"
                     :class="openBatches.includes({{ $batch['receipt_id'] }}) ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Header progress --}}
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 pb-5">
                <div class="flex-1 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    @php
                        $progress = $batch['total_assets'] > 0 
                            ? round(($batch['labeled_count'] / $batch['total_assets']) * 100) 
                            : 0;
                    @endphp
                    <div class="bg-indigo-500 h-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <p class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">{{ $batch['labeled_count'] }}/{{ $batch['total_assets'] }} label</p>
                    @if($tab === 'unlabeled' && $batch['unlabeled_count'] > 0)
                        <button type="button" @click.stop="labelAll({{ $batch['receipt_id'] }}, {{ $batch['unlabeled_count'] }})"
                                class="text-[10px] font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg shadow-sm transition-colors flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            Label Semua Sekaligus
                        </button>
                    @endif
                </div>
            </div>

            {{-- Asset list (expandable) --}}
            <div x-show="openBatches.includes({{ $batch['receipt_id'] }})" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="border-t border-slate-100">

                {{-- Local search inside batch --}}
                <div class="px-5 py-3 bg-slate-50/80 border-b border-slate-100" x-show="batchAssets[{{ $batch['receipt_id'] }}]?.length > 0">
                    <div class="relative">
                        <input type="text" x-model="searchAssets[{{ $batch['receipt_id'] }}]" 
                               placeholder="Cari aset berdasarkan kode, nama, atau label..." 
                               style="padding-left: 2.25rem;"
                               class="w-full pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 bg-white shadow-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                {{-- Loading state --}}
                <div x-show="loadingBatch === {{ $batch['receipt_id'] }}" class="px-5 py-6 flex items-center justify-center gap-2 text-sm text-slate-400">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Memuat aset...
                </div>

                {{-- Assets --}}
                <template x-if="batchAssets[{{ $batch['receipt_id'] }}]">
                    <div>
                        <div class="divide-y divide-slate-50">
                            <template x-for="asset in filteredAssets({{ $batch['receipt_id'] }})" :key="asset.id">
                                <div class="px-5 py-3.5 flex items-center gap-4 hover:bg-slate-50/50 transition-colors">
                                    {{-- Status dot --}}
                                    <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center"
                                         :class="asset.label_number ? 'bg-emerald-100' : 'bg-amber-100'">
                                        <svg x-show="asset.label_number" class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <svg x-show="!asset.label_number" class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>

                                    {{-- QR thumbnail (jika sudah ada) --}}
                                    <template x-if="asset.qr_code || asset.photo_url">
                                        <a :href="'{{ route('staf-admin.print-label') }}?id=' + asset.id"
                                           target="_blank"
                                           class="flex-shrink-0 w-10 h-10 rounded-lg overflow-hidden border border-slate-200 hover:border-indigo-400 transition-colors"
                                           title="Klik untuk cetak label">
                                            <img :src="asset.qr_code || asset.photo_url" class="w-full h-full object-contain bg-white">
                                        </a>
                                    </template>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <code class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded" x-text="asset.asset_code"></code>
                                            <span class="text-sm text-slate-700 truncate" x-text="asset.item_name || '—'"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <template x-if="asset.label_number">
                                                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full" x-text="asset.label_number"></span>
                                            </template>
                                            <template x-if="!asset.label_number">
                                                <span class="text-xs text-amber-600 font-medium">Belum ada label</span>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Action button --}}
                                    <button @click="openDrawer({ id: asset.id, asset_code: asset.asset_code, item_name: asset.item_name, label_number: asset.label_number, qr_code: asset.qr_code, photo_url: asset.photo_url }, {{ $batch['receipt_id'] }}, '{{ $batch['lab_code'] }}')"
                                            class="flex-shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-xl transition-colors"
                                            :class="asset.label_number ? 'text-slate-500 bg-slate-100 hover:bg-slate-200' : 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span x-text="asset.label_number ? 'Edit' : 'Beri Label'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endforeach

        {{-- Pagination --}}
        @if(isset($paginator) && $paginator->hasPages())
            <div class="mt-6">
                {{ $paginator->links('pagination::tailwind') }}
            </div>
        @endif

        {{-- DRAWER: Form Label + QR --}}
        <template x-teleport="body">
        <div>{{-- single root wrapper (x-teleport requires 1 root element) --}}

        {{-- Toast notification --}}
        <div x-show="toast.show" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl text-sm font-semibold"
             :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
             style="display:none;">
            <svg x-show="toast.type === 'success'" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <svg x-show="toast.type === 'error'" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span x-text="toast.message"></span>
        </div>

        <div x-show="drawerOpen" style="display:none;" class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeDrawer()"></div>
            <aside x-show="drawerOpen"
                   x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                   class="fixed right-0 top-0 h-screen w-full sm:w-[460px] bg-white shadow-2xl flex flex-col z-10"
                   @click.stop>

                {{-- Header --}}
                <div class="flex-shrink-0 flex items-start justify-between px-6 pt-5 pb-4 border-b border-slate-100">
                    <div class="min-w-0">
                        <p class="text-[0.65rem] font-bold text-amber-600 uppercase tracking-wider"
                           x-text="asset.label_number ? 'Edit Label & QR' : 'Beri Label & Generate QR'"></p>
                        <h2 class="text-base font-bold text-slate-900 mt-0.5 truncate" x-text="asset.item_name || asset.asset_code"></h2>
                        <code class="text-xs text-slate-400 font-mono" x-text="asset.asset_code"></code>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Cetak Label button (muncul jika sudah ada QR) --}}
                        <template x-if="asset.qr_code || asset.photo_url">
                            <a :href="`{{ route('staf-admin.print-label') }}?id=${_assetId}`"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl border border-indigo-200 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Cetak
                            </a>
                        </template>
                        <button @click="closeDrawer()" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    {{-- QR Live Preview --}}
                    <div class="bg-slate-50 rounded-2xl p-4">
                        <div class="flex items-start gap-4">
                            {{-- QR canvas --}}
                            <div class="flex-shrink-0 w-28 h-28 bg-white rounded-xl border-2 border-slate-200 flex items-center justify-center overflow-hidden" id="qr-preview-wrap">
                                <div id="qr-canvas-container" class="w-full h-full flex items-center justify-center"></div>
                            </div>
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-bold text-slate-700">Preview QR Code</p>
                                    <button type="button" @click="$refs.qrInput.click()" 
                                            class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded border border-indigo-200 transition-colors">
                                        Upload QR Manual
                                    </button>
                                </div>
                                <input type="file" name="qr_photo" accept="image/*" x-ref="qrInput" class="hidden" @change="handleQrFile($event.target.files[0])">
                                <template x-if="qrPreviewUrl">
                                    <div class="mb-2 flex items-center justify-between bg-emerald-50 px-2 py-1.5 rounded-lg border border-emerald-200">
                                        <span class="text-[10px] font-semibold text-emerald-700">QR Manual dipilih</span>
                                        <button type="button" @click="qrPreviewUrl = null; $refs.qrInput.value = ''" class="text-[10px] text-red-500 hover:text-red-700">Batal</button>
                                    </div>
                                </template>
                                
                                <template x-if="form.label_number">
                                    <p class="text-[11px] text-slate-500">QR berisi: <code class="font-mono text-indigo-600" x-text="form.label_number"></code></p>
                                </template>
                                <template x-if="!form.label_number">
                                    <p class="text-[11px] text-slate-400 italic">Ketik nomor label untuk preview QR</p>
                                </template>
                                <p class="text-[10px] text-slate-400 mt-2">QR akan di-generate otomatis saat simpan. Tidak perlu upload foto secara manual.</p>
                                <template x-if="asset.qr_code">
                                    <div class="mt-2 flex items-center gap-2">
                                        <a :href="asset.qr_code" target="_blank" download
                                           class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download QR tersimpan
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Label input --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                            Nomor Label <span class="text-red-500">*</span>
                        </label>
                        <template x-if="suggestedLabel && !form.label_number">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[0.65rem] text-slate-400">Saran:</span>
                                <code class="text-xs font-mono bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded border border-indigo-200" x-text="suggestedLabel"></code>
                                <button type="button" @click="useSuggestion()"
                                        class="text-[0.65rem] font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-0.5 rounded-full border border-indigo-200 transition-colors">
                                    Pakai &rarr;
                                </button>
                            </div>
                        </template>
                        <div class="relative">
                            <input type="text" x-model="form.label_number"
                                   @input="onLabelInput($event.target.value)"
                                   required placeholder="contoh: LAB-COMNET-001"
                                   :class="{
                                       'border-red-400 focus:border-red-500 focus:ring-red-500/20': labelStatus === 'taken',
                                       'border-emerald-400 focus:border-emerald-500 focus:ring-emerald-500/20': labelStatus === 'available',
                                       'border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20': !labelStatus || labelStatus === 'checking'
                                   }"
                                   class="w-full px-3 py-2.5 pr-24 text-sm font-mono border rounded-xl focus:outline-none focus:ring-2 transition-all">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold pointer-events-none">
                                <span x-show="labelStatus === 'checking'" class="text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>Cek...
                                </span>
                                <span x-show="labelStatus === 'available'" class="text-emerald-600 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Tersedia
                                </span>
                                <span x-show="labelStatus === 'taken'" class="text-red-500 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Dipakai
                                </span>
                            </span>
                        </div>
                        <p x-show="labelStatus === 'taken'" class="text-[0.65rem] text-red-500 mt-1" x-text="labelMsg"></p>
                        <p x-show="!labelStatus" class="text-[0.65rem] text-slate-400 mt-1.5">Format: <code class="font-mono bg-slate-100 px-1 rounded">LBL-[KODE]-[NO]</code></p>
                    </div>

                    {{-- Serial Number (opsional) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                            Serial Number
                            <span class="text-slate-400 font-normal normal-case ml-1">(opsional)</span>
                        </label>
                        <input type="text" x-model="form.serial_number"
                               placeholder="contoh: SN-12345-XY"
                               class="w-full px-3 py-2.5 text-sm font-mono border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                    </div>

                    {{-- Ruangan (dropdown) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
                            Ruangan
                            <span class="text-slate-400 font-normal normal-case ml-1">(opsional)</span>
                        </label>
                        <div class="relative">
                            <select x-model="form.room_id"
                                    class="w-full appearance-none px-3 py-2.5 pr-8 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all bg-white">
                                <option value="">— Pilih ruangan —</option>
                                <template x-for="room in rooms" :key="room.id">
                                    <option :value="room.id" x-text="room.name + (room.code ? ' (' + room.code + ')' : '')"></option>
                                </template>
                            </select>
                            <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <template x-if="roomsLoading">
                            <p class="text-[0.65rem] text-slate-400 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                Memuat data ruangan...
                            </p>
                        </template>
                    </div>

                    {{-- Foto override (opsional) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Foto Aset
                                <span class="text-slate-400 font-normal normal-case ml-1">(opsional)</span>
                            </label>
                            <button type="button" x-show="previewUrl" @click="previewUrl = null; $refs.fileInput.value = ''"
                                    class="text-[11px] font-semibold text-red-500 hover:text-red-700 transition-colors">
                                Hapus
                            </button>
                        </div>
                        <div @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="handleDrop($event)"
                             @click="$refs.fileInput.click()"
                             :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-slate-50/60 hover:border-indigo-300 hover:bg-indigo-50/30'"
                             class="border-2 border-dashed rounded-xl p-3 cursor-pointer transition-all">
                             
                            <template x-if="previewUrl || asset.photo_url">
                                <div class="flex items-center gap-3">
                                    <img :src="previewUrl || asset.photo_url" class="w-16 h-16 rounded-lg object-cover border border-slate-200 flex-shrink-0 bg-white">
                                    <div>
                                        <p class="text-xs font-semibold text-emerald-700" x-text="previewUrl ? 'Foto siap di-upload' : 'Foto Aset Tersimpan'"></p>
                                        <p class="text-[11px] text-slate-400 mt-0.5" x-text="previewUrl ? 'Akan ditambahkan ke aset' : 'Klik/drag untuk mengganti foto'"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!previewUrl && !asset.photo_url">
                                <div class="flex items-center gap-3 py-1">
                                    <svg class="w-7 h-7 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-xs text-slate-400">Klik atau drag foto wujud fisik aset · JPG, PNG · max 2MB</p>
                                </div>
                            </template>
                            <input type="file" name="asset_photo" accept="image/*" x-ref="fileInput" class="hidden"
                                   @change="handleFile($event.target.files[0])">
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex-shrink-0 flex gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
                    <button @click="closeDrawer()" type="button"
                            class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 transition-colors">
                        Batal
                    </button>
                    <button @click="submitDrawer()" type="button" :disabled="drawerLoading"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <svg x-show="drawerLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <span x-text="drawerLoading ? 'Menyimpan & Generate QR…' : 'Simpan Label'"></span>
                    </button>
                </div>
            </aside>
        </div>
        </div>{{-- /single root wrapper --}}
        </template>

    </div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let _qrInstance = null;

function renderQR(text) {
    const container = document.getElementById('qr-canvas-container');
    if (!container) return;
    container.innerHTML = '';
    if (!text?.trim()) return;
    try {
        _qrInstance = new QRCode(container, {
            text: text.trim(),
            width: 104,
            height: 104,
            colorDark: '#1e293b',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    } catch(e) { console.warn('QR render error', e); }
}

function labelApp() {
    return {
        openBatches: [],
        loadingBatch: null,
        batchAssets: {},
        searchAssets: {}, // Local search per batch
        drawerOpen: false,
        drawerLoading: false,
        dragging: false,
        previewUrl: null,
        qrPreviewUrl: null,
        currentReceiptId: null,
        _assetId: 0,
        asset: {},
        form: { label_number: '', serial_number: '', room_id: '' },
        rooms: [],
        roomsLoading: false,
        suggestedLabel: '',
        labelStatus: null,
        labelMsg: '',
        toast: { show: false, type: 'success', message: '' },

        init() {
            const highlight = {{ $highlightBatch ? $highlightBatch : 'null' }};
            if (highlight) {
                this.openBatches.push(highlight);
                this.loadBatchAssets(highlight);
                setTimeout(() => {
                    const el = document.getElementById('batch-' + highlight);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
            // Load rooms list for the dropdown
            this.loadRooms();
        },

        async toggleBatch(receiptId) {
            const idx = this.openBatches.indexOf(receiptId);
            if (idx >= 0) {
                this.openBatches.splice(idx, 1);
            } else {
                this.openBatches.push(receiptId);
                if (!this.batchAssets[receiptId]) await this.loadBatchAssets(receiptId);
            }
        },

        async loadBatchAssets(receiptId) {
            this.loadingBatch = receiptId;
            try {
                const url = '{{ route('staf-admin.inventory-assets-api') }}?receipt_id=' + receiptId;
                const r = await fetch(url, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } });
                const d = await r.json();
                this.batchAssets = { ...this.batchAssets, [receiptId]: d.data || [] };
            } catch(e) {
                this.batchAssets = { ...this.batchAssets, [receiptId]: [] };
            }
            this.loadingBatch = null;
        },

        filteredAssets(receiptId) {
            const list = this.batchAssets[receiptId] || [];
            const query = (this.searchAssets[receiptId] || '').toLowerCase();
            if (!query) return list;
            return list.filter(a => 
                (a.asset_code || '').toLowerCase().includes(query) ||
                (a.item_name || '').toLowerCase().includes(query) ||
                (a.label_number || '').toLowerCase().includes(query)
            );
        },

        openDrawer(asset, receiptId, labCode) {
            this._assetId = Number(asset.id);
            this.asset = asset;
            this.form.label_number  = asset.label_number  || '';
            this.form.serial_number = asset.serial_number || '';
            this.form.room_id       = asset.room_id       ? String(asset.room_id) : '';
            this.previewUrl = null;
            this.qrPreviewUrl = null;
            if (this.$refs.qrInput) this.$refs.qrInput.value = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            
            this.drawerLoading = false;
            this.labelStatus = null;
            this.labelMsg = '';
            this.currentReceiptId = receiptId || null;
            this.suggestedLabel = '';

            this.drawerOpen = true;
            document.body.style.overflow = 'hidden';

            if (!this.form.label_number && labCode) {
                this.fetchNextLabel(labCode);
            }

            this.$nextTick(() => {
                renderQR(this.form.label_number);
                if (this.form.label_number) this.checkLabel(this.form.label_number);
            });
        },

        async fetchNextLabel(labCode) {
            try {
                const token = document.querySelector('meta[name="auth-token"]')?.content;
                const r = await fetch(`/api/next-label?lab_code=${labCode}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const d = await r.json();
                if (d && d.label_number) {
                    this.suggestedLabel = d.label_number;
                    if (!this.form.label_number) renderQR(this.suggestedLabel);
                }
            } catch (err) {
                console.error("Gagal mendapatkan saran label", err);
            }
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.previewUrl = null;
            this.qrPreviewUrl = null;
            this.dragging = false;
            document.body.style.overflow = '';
        },

        useSuggestion() {
            this.form.label_number = this.suggestedLabel;
            renderQR(this.suggestedLabel);
            this.checkLabel(this.suggestedLabel);
        },

        onLabelInput(val) {
            renderQR(val);
            clearTimeout(this._labelTimer);
            if (!val?.trim()) { this.labelStatus = null; return; }
            this.labelStatus = 'checking';
            this._labelTimer = setTimeout(() => this.checkLabel(val), 500);
        },

        checkLabel(val) {
            if (!val?.trim()) { this.labelStatus = null; return; }
            this.labelStatus = 'checking';
            clearTimeout(this._labelTimer);
            this._labelTimer = setTimeout(async () => {
                try {
                    const r = await fetch(`{{ route('staf-admin.label-check') }}?label=${encodeURIComponent(val.trim())}&exclude_id=${this.asset.id}`, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    const d = await r.json();
                    const p = d.data ?? d;
                    this.labelStatus = p.available ? 'available' : 'taken';
                    this.labelMsg = p.message || '';
                } catch { this.labelStatus = null; }
            }, 500);
        },

        handleDrop(e) {
            this.dragging = false;
            const f = e.dataTransfer.files[0];
            if (f) { this.handleFile(f); const dt = new DataTransfer(); dt.items.add(f); this.$refs.fileInput.files = dt.files; }
        },

        handleFile(f) {
            if (!f) return;
            if (f.size > 2 * 1024 * 1024) { this.showToast('error', 'Ukuran file maksimal 2MB'); return; }
            this.previewUrl = URL.createObjectURL(f);
        },

        handleQrFile(f) {
            if (!f) return;
            if (f.size > 2 * 1024 * 1024) { this.showToast('error', 'Ukuran file maksimal 2MB'); return; }
            this.qrPreviewUrl = URL.createObjectURL(f);
        },

        async submitDrawer() {
            if (!this.form.label_number?.trim()) { this.showToast('error', 'Nomor label wajib diisi'); return; }
            if (this.labelStatus === 'taken') { this.showToast('error', 'Nomor label sudah dipakai aset lain'); return; }
            if (this.labelStatus === 'checking') { this.showToast('error', 'Tunggu pengecekan label selesai...'); return; }
            if (!this._assetId) { this.showToast('error', 'Error: Asset ID hilang. Tutup dan buka drawer lagi.'); return; }

            this.drawerLoading = true;

            const assetId = this._assetId;
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
            formData.append('label_number', this.form.label_number.trim());
            if (this.form.serial_number?.trim()) {
                formData.append('serial_number', this.form.serial_number.trim());
            }
            if (this.form.room_id) {
                formData.append('room_id', this.form.room_id);
            }

            if (this.$refs.qrInput?.files[0]) {
                formData.append('qr_photo', this.$refs.qrInput.files[0]);
            }
            if (this.$refs.fileInput?.files[0]) {
                formData.append('asset_photo', this.$refs.fileInput.files[0]);
            }

            try {
                const r = await fetch(`/inventory-label/${assetId}`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await r.json();

                if (data.ok) {
                    this.showToast('success', 'Berhasil update label & foto QR');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showToast('error', data.message || 'Gagal update label');
                }
            } catch (err) {
                this.showToast('error', 'Terjadi kesalahan sistem');
            } finally {
                this.drawerLoading = false;
            }
        },

        async labelAll(receiptId, count) {
            Swal.fire({
                title: 'Label Semua Aset?',
                text: `Anda akan melabeli dan membuat QR Code otomatis untuk ${count} aset sekaligus. Proses ini mungkin memakan waktu beberapa detik.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Label Sekarang!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const token = document.querySelector('meta[name="auth-token"]')?.content;
                        const response = await fetch(`/api/inventory/batches/${receiptId}/label-all`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Authorization': `Bearer ${token}`
                            }
                        });
                        const data = await response.json();
                        if (data.status === 'success') {
                            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan');
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire('Gagal!', err.message, 'error');
                    }
                }
            });
        },

        showToast(type, message) {
            this.toast = { show: true, type, message };
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        async loadRooms() {
            if (this.rooms.length > 0) return; // already loaded
            this.roomsLoading = true;
            try {
                const r = await fetch('{{ route('staf-admin.rooms-api') }}', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                const d = await r.json();
                this.rooms = d.data || [];
            } catch(e) {
                this.rooms = [];
            }
            this.roomsLoading = false;
        },

        openBulkModal(receiptId) {
            this.showToast('error', 'Fitur "Label Semua" coming soon!');
        }
    };
}
</script>
@endpush
@endsection
