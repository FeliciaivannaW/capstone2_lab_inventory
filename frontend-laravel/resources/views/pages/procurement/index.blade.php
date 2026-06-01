@extends('layouts.app')

@section('title', 'Draf Pengadaan')

@section('content')
    @php
        $authUser = session('auth_user');
        $role = $authUser['role'] ?? null;
        $canCreate = in_array($role, ['kepala_laboratorium', 'staf_administrasi']);

        $statusMap = [
            'draft' => ['label' => 'Draft', 'class' => 'badge-draft'],
            'submitted' => ['label' => 'Submitted', 'class' => 'badge-submitted'],
            'finalized' => ['label' => 'Finalized', 'class' => 'badge-finalized'],
            'rejected' => ['label' => 'Rejected', 'class' => 'badge-rejected'],
        ];
    @endphp

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Draf Pengadaan</h1>
            <p class="text-sm text-slate-500 mt-1">Riwayat draf pengadaan aset dan BHP dengan status review dan finalisasi.
            </p>
        </div>
        @if($canCreate)
            <a href="{{ route('procurement.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Draf Baru
            </a>
        @endif
    </div>

    {{-- Table card --}}
    <div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($drafts) }})">
        @php
            $years = collect($drafts)->pluck('budget_year')->unique()->filter()->values()->toArray();
            $yearOptions = count($years) ? array_combine($years, $years) : [];
            $labs = collect($drafts)->pluck('lab_name')->unique()->filter()->values()->toArray();
            $labOptions = count($labs) ? array_combine($labs, $labs) : [];
        @endphp
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700">{{ count($drafts) }} draf ditemukan</p>
            </div>
            <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
                <x-table-filter column="status" label="Status" :options="[
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'finalized' => 'Finalized'
        ]" />
                <x-table-filter column="lab" label="Lab" :options="$labOptions" />
                <x-table-filter column="year" label="Tahun" :options="$yearOptions" />
                <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')"
                    class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>
                    Reset Filter
                </button>
            </div>
        </div>

        @if(empty($drafts))
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-medium text-slate-400">Belum ada draf pengadaan</p>
                @if($canCreate)
                    <a href="{{ route('procurement.create') }}"
                        class="mt-3 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                        Buat draf pertama →
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto" x-data="{ activeModal: null }">
                <table class="lv-table">
                    <thead>
                        <tr>
                            <x-sort-header field="num">#</x-sort-header>
                            <x-sort-header field="title">Judul Draf</x-sort-header>
                            <x-sort-header field="lab">Lab</x-sort-header>
                            <x-sort-header field="year">Tahun</x-sort-header>
                            <x-sort-header field="status">Status</x-sort-header>
                            <th>
                                <span title="Pending / Disetujui / Ditolak" class="cursor-help border-b border-dashed border-slate-400">Item (P/S/T)</span>
                            </th>
                            <x-sort-header field="locked">Kunci</x-sort-header>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drafts as $index => $draft)
                            @php
                                $st = $statusMap[$draft['status']] ?? ['label' => ucfirst($draft['status']), 'class' => 'badge-draft'];
                            @endphp
                            <tr @click="activeModal = 'detail_draft_{{ $draft['id'] }}'"
                                class="cursor-pointer hover:bg-slate-50/50 transition-colors"
                                x-show="showRow({{ $index }})" x-cloak data-filter-status="{{ $draft['status'] }}"
                                data-filter-lab="{{ $draft['lab_name'] }}" data-filter-year="{{ $draft['budget_year'] }}">
                                <td class="text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                                <td class="font-semibold text-slate-800">{{ $draft['title'] }}</td>
                                <td>
                                    <span class="badge badge-active">{{ $draft['lab_name'] }}</span>
                                </td>
                                <td class="text-slate-600 font-semibold">{{ $draft['budget_year'] }}</td>
                                <td>
                                    <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1 text-xs">
                                        <span class="badge badge-pending" title="Pending">{{ $draft['pending_count'] ?? 0 }}</span>
                                        <span class="badge badge-approved" title="Disetujui">{{ $draft['approved_count'] ?? 0 }}</span>
                                        <span class="badge badge-rejected" title="Ditolak">{{ $draft['rejected_count'] ?? 0 }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($draft['is_locked'])
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Terkunci
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                            Terbuka
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('procurement.show', $draft['id']) }}"
                                        @click.stop
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                        Lihat Halaman
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Detail Draf -->
                            <template x-teleport="body">
                                <div x-show="activeModal === 'detail_draft_{{ $draft['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                    <!-- Backdrop -->
                                    <div x-show="activeModal === 'detail_draft_{{ $draft['id'] }}'"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         @click="activeModal = null"
                                         class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                    <!-- Modal Panel -->
                                    <div x-show="activeModal === 'detail_draft_{{ $draft['id'] }}'"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                            <div>
                                                <h2 class="text-lg font-bold text-slate-900">Ringkasan Draf</h2>
                                                <p class="text-xs text-slate-500 mt-1">Detail cepat informasi draf pengadaan.</p>
                                            </div>
                                            <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="col-span-2">
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Judul Draf</p>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $draft['title'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lab</p>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $draft['lab_name'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahun Anggaran</p>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $draft['budget_year'] }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pembuat</p>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[0.6rem] font-bold text-slate-600">
                                                            {{ strtoupper(substr($draft['created_by_name'], 0, 1)) }}
                                                        </div>
                                                        <span class="text-sm font-semibold text-slate-800">{{ $draft['created_by_name'] }}</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</p>
                                                    <span class="badge {{ $st['class'] }} text-xs mt-0.5">{{ $st['label'] }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Dibuat Pada</p>
                                                    <p class="text-sm font-semibold text-slate-800">{{ date('d M Y', strtotime($draft['created_at'])) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kunci</p>
                                                    @if($draft['is_locked'])
                                                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-slate-700">
                                                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                                            </svg>
                                                            Terkunci
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-sm text-slate-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                            </svg>
                                                            Terbuka
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="col-span-2">
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Ringkasan Item (P / S / T)</p>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="badge badge-pending">{{ $draft['pending_count'] ?? 0 }} Pending</span>
                                                        <span class="badge badge-approved">{{ $draft['approved_count'] ?? 0 }} Disetujui</span>
                                                        <span class="badge badge-rejected">{{ $draft['rejected_count'] ?? 0 }} Ditolak</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pt-4 mt-2 border-t border-slate-100 flex gap-3">
                                                <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                                                    Tutup
                                                </button>
                                                <a href="{{ route('procurement.show', $draft['id']) }}" class="flex-1 text-center rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700 transition-colors">
                                                    Buka Halaman Draf
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
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
