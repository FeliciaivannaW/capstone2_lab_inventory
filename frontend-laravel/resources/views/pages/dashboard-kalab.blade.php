@extends('layouts.app')

@section('title', 'Dashboard Kepala Laboratorium')

@section('content')
@php
    $authUser = session('auth_user');
@endphp

{{-- Page heading --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Dashboard Kepala Lab</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola dan pantau draf pengadaan untuk laboratorium Anda dengan cepat.</p>
    </div>
    <a href="{{ route('procurement.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm w-full sm:w-auto">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat Draf Baru
    </a>
</div>

{{-- ── Stat cards (bento grid) ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Total Draf --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(99,102,241,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#6366F1;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Draf</p>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Keseluruhan draf</p>
    </div>

    {{-- Siap Kirim --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden ring-1 ring-amber-500/20 shadow-[0_0_15px_rgba(245,158,11,0.1)]">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(245,158,11,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#F59E0B;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            @if($stats['draft'] > 0)
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
            @endif
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Siap Kirim</p>
        <p class="text-3xl font-bold text-amber-600">{{ $stats['draft'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Berstatus draft</p>
    </div>

    {{-- Menunggu Kaprodi --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(14,165,233,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#0EA5E9;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Menunggu Review</p>
        <p class="text-3xl font-bold text-sky-600">{{ $stats['submitted'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Di Kaprodi</p>
    </div>

    {{-- Finalized --}}
    <div class="glass-card rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(135deg,rgba(16,185,129,0.06) 0%,transparent 60%)"></div>
        <div class="flex items-start justify-between mb-4">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg style="width:18px;height:18px;color:#10B981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Selesai / Final</p>
        <p class="text-3xl font-bold text-emerald-600">{{ $stats['finalized'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Siap direalisasikan</p>
    </div>
</div>

{{-- ── Quick Action Table ── --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="{
    submitting: null,
    submitOpen: false,
    activeDraftId: null,
    
    openSubmitModal(id) {
        this.activeDraftId = id;
        this.submitOpen = true;
    },

    async submitDraft() {
        if (!this.activeDraftId) return;
        
        this.submitting = this.activeDraftId;
        this.submitOpen = false;
        
        try {
            const res = await fetch(`/api/procurement/${this.activeDraftId}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });
            const data = await res.json();
            if (data.status === 'success') {
                window.showToast('Draf berhasil dikirim ke Kaprodi', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                window.showToast(data.message || 'Gagal mengirim draf', 'error');
                this.submitting = null;
            }
        } catch (e) {
            window.showToast('Terjadi kesalahan', 'error');
            this.submitting = null;
        }
    }
}">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Draf Aktif (Siap Kirim)</h2>
            <p class="text-xs text-slate-400 mt-0.5">Draf yang masih dapat Anda ubah dan belum dikirim.</p>
        </div>
        <a href="{{ route('procurement') }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-700 flex items-center gap-1 transition-colors">
            Lihat Semua Draf
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if(empty($actionableDrafts))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Tidak ada draf aktif saat ini</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Judul Draf</th>
                        <th>Tahun</th>
                        <th>Status Item</th>
                        <th class="text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($actionableDrafts as $index => $draft)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="text-slate-400 font-mono text-xs text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ $draft['title'] }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $draft['lab_name'] }}</div>
                            </td>
                            <td class="text-slate-600 font-semibold">{{ $draft['budget_year'] }}</td>
                            <td>
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="badge badge-pending" title="Total Item">{{ $draft['pending_count'] ?? 0 }} Item</span>
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('procurement.show', $draft['id']) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                                        Detail
                                    </a>
                                    <button type="button" @click="openSubmitModal({{ $draft['id'] }})" :disabled="submitting === {{ $draft['id'] }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg x-show="submitting === {{ $draft['id'] }}" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" x-cloak>
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg x-show="submitting !== {{ $draft['id'] }}" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        <span x-text="submitting === {{ $draft['id'] } ? 'Mengirim...' : 'Kirim Kaprodi'"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

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
                    <button @click="submitDraft()" 
                            class="flex-1 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors inline-flex items-center justify-center gap-2">
                        <span>Ya, Submit</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection
