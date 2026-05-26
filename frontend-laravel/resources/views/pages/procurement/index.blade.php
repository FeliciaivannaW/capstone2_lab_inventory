@extends('layouts.app')

@section('title', 'Draf Pengadaan')

@section('content')
@php
    $authUser = session('auth_user');
    $role = $authUser['role'] ?? null;
    $canCreate = in_array($role, ['kepala_laboratorium', 'staf_administrasi']);

    $statusMap = [
        'draft'     => ['label' => 'Draft',     'class' => 'badge-draft'],
        'submitted' => ['label' => 'Submitted',  'class' => 'badge-submitted'],
        'finalized' => ['label' => 'Finalized',  'class' => 'badge-finalized'],
        'rejected'  => ['label' => 'Rejected',   'class' => 'badge-rejected'],
    ];
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Draf Pengadaan</h1>
        <p class="text-sm text-slate-500 mt-1">Riwayat draf pengadaan aset dan BHP dengan status review dan finalisasi.</p>
    </div>
    @if($canCreate)
        <a href="{{ route('procurement.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Draf Baru
        </a>
    @endif
</div>

{{-- Table card --}}
<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($drafts) }})">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">{{ count($drafts) }} draf ditemukan</p>
    </div>

    @if(empty($drafts))
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium text-slate-400">Belum ada draf pengadaan</p>
            @if($canCreate)
                <a href="{{ route('procurement.create') }}" class="mt-3 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                    Buat draf pertama →
                </a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul Draf</th>
                        <th>Lab</th>
                        <th>Tahun</th>
                        <th>Pembuat</th>
                        <th>Status</th>
                        <th>Item (P/S/T)</th>
                        <th>Kunci</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drafts as $index => $draft)
                        @php
                            $st = $statusMap[$draft['status']] ?? ['label' => ucfirst($draft['status']), 'class' => 'badge-draft'];
                        @endphp
                        <tr x-show="showRow({{ $index }})" x-cloak>
                            <td class="text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $draft['title'] }}</td>
                            <td>
                                <span class="badge badge-active">{{ $draft['lab_name'] }}</span>
                            </td>
                            <td class="text-slate-600 font-semibold">{{ $draft['budget_year'] }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[0.6rem] font-bold text-slate-600">
                                        {{ strtoupper(substr($draft['created_by_name'], 0, 1)) }}
                                    </div>
                                    <span class="text-slate-600 text-xs">{{ $draft['created_by_name'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="badge badge-pending">{{ $draft['pending_count'] ?? 0 }}</span>
                                    <span class="badge badge-approved">{{ $draft['approved_count'] ?? 0 }}</span>
                                    <span class="badge badge-rejected">{{ $draft['rejected_count'] ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                @if($draft['is_locked'])
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Terkunci
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                        </svg>
                                        Terbuka
                                    </span>
                                @endif
                            </td>
                            <td class="text-slate-400 text-xs">
                                {{ date('d M Y', strtotime($draft['created_at'])) }}
                            </td>
                            <td>
                                <a href="{{ route('procurement.show', $draft['id']) }}"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                    Detail
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
