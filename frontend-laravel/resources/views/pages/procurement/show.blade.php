@extends('layouts.app')

@section('title', 'Detail Pengadaan')

@section('content')
@php
    $authUser = session('auth_user');
    $isCreator = $authUser['id'] === ($draft['created_by_id'] ?? null);
    $canEditDraft = ($authUser['role'] === 'staf_administrasi' || $isCreator)
                    && $draft['status'] === 'draft'
                    && !$draft['is_locked'];
    $canReview = $authUser['role'] === 'ketua_program_studi'
                 && !$draft['is_locked']
                 && $draft['status'] === 'submitted';
    // Kepala Lab / Staf Admin can submit when draft is in 'draft' status
    $canSubmitDraft = ($authUser['role'] === 'staf_administrasi' || $isCreator)
                     && $draft['status'] === 'draft'
                     && !$draft['is_locked'];

    $statusMap = [
        'draft'     => ['label' => 'Draft',    'class' => 'badge-draft'],
        'submitted' => ['label' => 'Submitted', 'class' => 'badge-submitted'],
        'finalized' => ['label' => 'Finalized', 'class' => 'badge-finalized'],
        'rejected'  => ['label' => 'Rejected',  'class' => 'badge-rejected'],
    ];
    $st = $statusMap[$draft['status']] ?? ['label' => ucfirst($draft['status']), 'class' => 'badge-draft'];
@endphp

{{-- Back + title --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('procurement') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
    <span class="text-slate-300">/</span>
    <span class="text-sm text-slate-600 font-semibold">{{ $draft['title'] }}</span>
</div>

{{-- Step indicator (Only for draft status) --}}
@if($draft['status'] === 'draft')
<div class="flex items-center gap-0 mb-8">
    @php
        $hasItems = count($draft['items'] ?? []) > 0;
        $steps = [
            ['label' => 'Info Dasar',    'desc' => 'Judul & tahun anggaran'],
            ['label' => 'Tambah Item',   'desc' => 'Daftar barang pengadaan'],
            ['label' => 'Review & Kirim','desc' => 'Finalisasi draf'],
        ];
        $currentStep = $hasItems ? 3 : 2;
    @endphp
    @foreach($steps as $i => $step)
        @php $sNum = $i + 1; $done = $sNum < $currentStep; $active = $sNum === $currentStep; @endphp
        <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                    {{ $done ? 'bg-indigo-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-200 text-slate-500') }}">
                    @if($done)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        {{ $sNum }}
                    @endif
                </div>
                <div class="hidden sm:block">
                    <p class="text-xs font-bold {{ $active ? 'text-slate-900' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                    <p class="text-[0.65rem] text-slate-400">{{ $step['desc'] }}</p>
                </div>
            </div>
            @if($i < count($steps) - 1)
                <div class="flex-1 h-px mx-4 {{ $done ? 'bg-indigo-300' : 'bg-slate-200' }}"></div>
            @endif
        </div>
    @endforeach
</div>
@endif

{{-- Draft header card --}}
<div class="glass-card rounded-2xl p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <span class="badge {{ $st['class'] }} text-xs">{{ $st['label'] }}</span>
                @if($draft['is_locked'])
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Terkunci
                    </span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-slate-900">{{ $draft['title'] }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tahun Anggaran <span class="font-semibold text-slate-700">{{ $draft['budget_year'] }}</span>
                · Lab <span class="font-semibold text-slate-700">{{ $draft['lab_name'] }}</span>
            </p>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0 flex-wrap">
            @if($canReview)
                <button onclick="openFinalizeModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Finalisasi Draf
                </button>
            @endif
            @if($canSubmitDraft)
                <button {{ $hasItems ? 'onclick=openSubmitModal()' : 'disabled' }}
                        class="inline-flex items-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-xl transition-colors
                               {{ $hasItems ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-300 cursor-not-allowed' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Submit ke Kaprodi
                </button>
            @endif
            @if($canEditDraft)
                <a href="{{ route('procurement.edit', $draft['id']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Draf
                </a>
                <button onclick="openDeleteModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl border border-red-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            @endif
        </div>
    </div>

    {{-- Meta info grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100">
        <div>
            <p class="text-[0.68rem] font-semibold uppercase tracking-wider text-slate-400 mb-1">Dibuat oleh</p>
            <p class="text-sm font-semibold text-slate-700">{{ $draft['created_by_name'] }}</p>
            <p class="text-xs text-slate-400">{{ date('d M Y H:i', strtotime($draft['created_at'])) }}</p>
        </div>
        @if($draft['finalized_by_name'] ?? null)
            <div>
                <p class="text-[0.68rem] font-semibold uppercase tracking-wider text-slate-400 mb-1">Difinalisasi oleh</p>
                <p class="text-sm font-semibold text-slate-700">{{ $draft['finalized_by_name'] }}</p>
                <p class="text-xs text-slate-400">{{ date('d M Y H:i', strtotime($draft['finalized_at'])) }}</p>
            </div>
        @endif
        @if($draft['notes'] ?? null)
            <div class="col-span-2">
                <p class="text-[0.68rem] font-semibold uppercase tracking-wider text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-slate-600">{{ $draft['notes'] }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Items table --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($draft['items'] ?? []) }})">
    <div class="px-6 py-4 border-b border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Daftar Item Pengadaan</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ count($draft['items'] ?? []) }} item dalam draf ini</p>
            </div>
            {{-- Item count badges --}}
            <div class="flex items-center gap-2">
                <span class="badge badge-pending text-xs">
                    {{ collect($draft['items'] ?? [])->where('review_status','pending')->count() }} pending
                </span>
                <span class="badge badge-approved text-xs">
                    {{ collect($draft['items'] ?? [])->where('review_status','approved')->count() }} disetujui
                </span>
                <span class="badge badge-rejected text-xs">
                    {{ collect($draft['items'] ?? [])->where('review_status','rejected')->count() }} ditolak
                </span>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
            <x-table-filter column="status" label="Status Review" :options="[
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            ]" />
            <x-table-filter column="type" label="Tipe Barang" :options="[
                'inventory' => 'Inventaris',
                'bhp' => 'BHP'
            ]" />
            <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>
                Reset Filter
            </button>
        </div>
    </div>

    @if(empty($draft['items']))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-10 h-10 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm text-slate-400">Belum ada item dalam draf ini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="num">#</x-sort-header>
                        <x-sort-header field="item_name">Nama Barang</x-sort-header>
                        <x-sort-header field="item_type">Tipe</x-sort-header>
                        <x-sort-header field="qty">Qty</x-sort-header>
                        <x-sort-header field="price">Harga Perkiraan</x-sort-header>
                        <x-sort-header field="status">Status Review</x-sort-header>
                        @if($canReview)
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($draft['items'] as $index => $item)
                        <tr x-show="showRow({{ $index }})" x-cloak data-filter-status="{{ $item['review_status'] }}" data-filter-type="{{ $item['item_type'] }}"
                            onclick='openDetailModal(@json($item))'
                            class="cursor-pointer hover:bg-slate-50 transition-colors">
                            <td class="text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $item['item_name'] }}</td>
                            <td>
                                @if($item['item_type'] === 'inventory')
                                    <span class="badge badge-active">Inventaris</span>
                                @else
                                    <span class="badge badge-pending">BHP</span>
                                @endif
                            </td>
                            <td class="font-semibold text-slate-700">{{ $item['quantity'] }}</td>
                            <td class="text-slate-600 font-mono text-xs">Rp {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $rstatus = match($item['review_status']) {
                                        'approved' => ['badge-approved','Disetujui'],
                                        'rejected' => ['badge-rejected','Ditolak'],
                                        default    => ['badge-pending','Pending'],
                                    };
                                @endphp
                                <span class="badge {{ $rstatus[0] }}">{{ $rstatus[1] }}</span>
                            </td>
                            @if($canReview && $item['review_status'] === 'pending')
                                <td>
                                    <button onclick="event.stopPropagation(); openReviewModal({{ $item['id'] }}, '{{ addslashes($item['item_name']) }}')"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                                        Review
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($draft['items'] ?? []) > 0)
            <x-pagination :total="count($draft['items'])" />
        @endif
    @endif
</div>

{{-- ── MODALS ── --}}
<div x-data="{
    reviewOpen: false, reviewItemId: null, reviewItemName: '',
    deleteOpen: false, finalizeOpen: false, submitOpen: false,
    detailOpen: false, activeDetail: {},
    reviewStatus: '', reviewNote: '',
    loading: false,
    draftId: {{ $draft['id'] ?? 0 }},

    openReview(id, name) { this.reviewItemId = id; this.reviewItemName = name; this.reviewStatus = ''; this.reviewNote = ''; this.reviewOpen = true; },
    closeReview() { this.reviewOpen = false; },
    openDetail(item) { this.activeDetail = item; this.detailOpen = true; },
    closeDetail() { this.detailOpen = false; },

    isOk(d) { return d.success === true || d.status === 'success'; },

    async submitReview() {
        this.loading = true;
        const res = await fetch('/api/procurement/' + this.draftId + '/items/' + this.reviewItemId + '/review', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ review_status: this.reviewStatus, review_note: this.reviewNote })
        });
        const d = await res.json();
        this.loading = false;
        if (this.isOk(d)) location.reload();
        else alert('Error: ' + (d.message || 'Gagal'));
        this.closeReview();
    },

    async submitFinalize() {
        this.loading = true;
        const res = await fetch('/api/procurement/' + this.draftId + '/finalize', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const d = await res.json();
        this.loading = false;
        if (this.isOk(d)) location.reload();
        else alert('Error: ' + (d.message || 'Gagal'));
        this.finalizeOpen = false;
    },

    async submitDraft() {
        this.loading = true;
        const res = await fetch('/api/procurement/' + this.draftId + '/submit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const d = await res.json();
        this.loading = false;
        if (this.isOk(d)) location.reload();
        else alert('Error: ' + (d.message || 'Gagal'));
        this.submitOpen = false;
    }
}" id="modalsRoot">

    {{-- Review Modal --}}
    <template x-teleport="body">
        <div x-show="reviewOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="reviewOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeReview()"></div>
            <div x-show="reviewOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 m-auto">
                <h3 class="text-base font-bold text-slate-900 mb-1">Review Item Pengadaan</h3>
                <p class="text-sm text-slate-500 mb-5" x-text="'Barang: ' + reviewItemName"></p>

                <div class="mb-4">
                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Status Review</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" x-model="reviewStatus" value="approved" class="sr-only peer">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-slate-700">Setujui</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" x-model="reviewStatus" value="rejected" class="sr-only peer">
                            <div class="flex items-center gap-2 p-3 rounded-xl border-2 border-slate-200 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-slate-700">Tolak</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">Catatan (opsional)</label>
                    <textarea x-model="reviewNote" rows="3"
                              class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl resize-none focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                              placeholder="Tambahkan catatan..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button @click="closeReview()" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button @click="submitReview()" :disabled="!reviewStatus"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl transition-colors">
                        Simpan Review
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Delete Modal --}}
    <template x-teleport="body">
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="deleteOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="deleteOpen = false"></div>
            <div x-show="deleteOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center m-auto">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Hapus Draf Pengadaan?</h3>
                <p class="text-sm text-slate-500 mb-6">Tindakan ini tidak dapat dibatalkan. Semua item dalam draf akan ikut terhapus.</p>
                <div class="flex gap-3">
                    <button @click="deleteOpen = false" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <form method="POST" action="{{ route('procurement.destroy', $draft['id']) }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Submit Draf Modal --}}
    <template x-teleport="body">
        <div x-show="submitOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="submitOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="submitOpen = false"></div>
            <div x-show="submitOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center m-auto">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Submit Draf ke Kaprodi?</h3>
                <p class="text-sm text-slate-500 mb-6">Setelah di-submit, draf akan masuk antrian review Ketua Program Studi. Kamu masih bisa menambah item sebelum di-submit.</p>
                <div class="flex gap-3">
                    <button @click="submitOpen = false" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button @click="submitDraft()" :disabled="loading"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="loading ? 'Memproses…' : 'Ya, Submit'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Finalize Modal --}}
    <template x-teleport="body">
        <div x-show="finalizeOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="finalizeOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="finalizeOpen = false"></div>
            <div x-show="finalizeOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center m-auto">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Finalisasi Draf?</h3>
                <p class="text-sm text-slate-500 mb-6">Setelah difinalisasi, draf akan <strong>terkunci</strong> dan tidak bisa diubah lagi.</p>
                <div class="flex gap-3">
                    <button @click="finalizeOpen = false" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                    <button @click="submitFinalize()" :disabled="loading"
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="loading ? 'Memproses…' : 'Ya, Finalisasi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Item Detail Modal --}}
    <template x-teleport="body">
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            <div x-show="detailOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeDetail()"></div>
            <div x-show="detailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg m-auto flex flex-col max-h-[90vh]">
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-slate-900">Detail Item Pengadaan</h3>
                    <button @click="closeDetail()" class="text-slate-400 hover:text-slate-600 p-1 rounded-full hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Barang</p>
                        <p class="text-sm font-semibold text-slate-800" x-text="activeDetail.item_name"></p>
                    </div>
                    
                    <div>
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi / Spesifikasi</p>
                        <template x-if="activeDetail.item_description">
                            <p class="text-sm text-slate-600 whitespace-pre-wrap" x-text="activeDetail.item_description"></p>
                        </template>
                        <template x-if="!activeDetail.item_description">
                            <span class="text-sm text-slate-400">—</span>
                        </template>
                    </div>

                    <template x-if="activeDetail.replacement_asset_name || activeDetail.replacement_asset_code">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Aset yang Digantikan</p>
                            <div class="bg-amber-50 border border-amber-100/60 rounded-xl p-3 flex items-center gap-3">
                                <div class="p-2 bg-amber-100 text-amber-600 rounded-lg shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-amber-900" x-text="activeDetail.replacement_asset_name || activeDetail.replacement_asset_code"></p>
                                    <template x-if="activeDetail.replacement_asset_name && activeDetail.replacement_asset_code">
                                        <p class="text-xs text-amber-700/80 font-medium mt-0.5" x-text="activeDetail.replacement_asset_code"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Barang</p>
                            <span class="badge" 
                                  :class="activeDetail.item_type === 'inventory' ? 'badge-active' : 'badge-pending'" 
                                  x-text="activeDetail.item_type === 'inventory' ? 'Inventaris' : 'BHP'"></span>
                        </div>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah (Qty)</p>
                            <p class="text-sm font-semibold text-slate-700" x-text="activeDetail.quantity"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Harga Perkiraan</p>
                            <p class="text-sm font-mono text-slate-700" x-text="'Rp ' + (activeDetail.estimated_price ? parseInt(activeDetail.estimated_price).toLocaleString('id-ID') : '0')"></p>
                        </div>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-1">Link Pembelian</p>
                            <template x-if="activeDetail.purchase_link">
                                <a :href="activeDetail.purchase_link" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-500 hover:text-indigo-700 font-semibold transition-colors">
                                    Buka Tautan
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </template>
                            <template x-if="!activeDetail.purchase_link">
                                <span class="text-sm text-slate-400">—</span>
                            </template>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Review</p>
                        <div class="bg-slate-50 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-500">Status</span>
                                <span class="badge" 
                                      :class="{
                                          'badge-approved': activeDetail.review_status === 'approved',
                                          'badge-rejected': activeDetail.review_status === 'rejected',
                                          'badge-pending': activeDetail.review_status === 'pending'
                                      }" 
                                      x-text="activeDetail.review_status === 'approved' ? 'Disetujui' : (activeDetail.review_status === 'rejected' ? 'Ditolak' : 'Pending')">
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                                <span class="text-xs text-slate-500">Reviewer</span>
                                <div class="text-right">
                                    <template x-if="activeDetail.reviewed_by_name">
                                        <div>
                                            <p class="text-xs font-semibold text-slate-700" x-text="activeDetail.reviewed_by_name"></p>
                                            <p class="text-[0.65rem] text-slate-400" x-text="activeDetail.reviewed_at ? new Date(activeDetail.reviewed_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : ''"></p>
                                        </div>
                                    </template>
                                    <template x-if="!activeDetail.reviewed_by_name">
                                        <span class="text-sm text-slate-400">—</span>
                                    </template>
                                </div>
                            </div>
                            
                            <div class="border-t border-slate-200 pt-3">
                                <span class="text-xs text-slate-500 block mb-1">Catatan</span>
                                <template x-if="activeDetail.review_note">
                                    <p class="text-xs text-slate-700 bg-white p-2.5 rounded-xl border border-slate-200 max-h-32 overflow-y-auto" x-text="activeDetail.review_note"></p>
                                </template>
                                <template x-if="!activeDetail.review_note">
                                    <span class="text-sm text-slate-400">—</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
                
                <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl text-right">
                    <button @click="closeDetail()" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
    // Bridge vanilla onclick calls → Alpine
    function openReviewModal(id, name) {
        document.getElementById('modalsRoot')._x_dataStack[0].openReview(id, name);
    }
    function openDeleteModal()   { document.getElementById('modalsRoot')._x_dataStack[0].deleteOpen   = true; }
    function openFinalizeModal() { document.getElementById('modalsRoot')._x_dataStack[0].finalizeOpen = true; }
    function openSubmitModal()   { document.getElementById('modalsRoot')._x_dataStack[0].submitOpen   = true; }
    function openDetailModal(item) { document.getElementById('modalsRoot')._x_dataStack[0].openDetail(item); }
</script>
@endpush
@endsection
