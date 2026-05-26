@extends('layouts.app')

@section('title', 'Laboratorium')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Laboratorium</h1>
    <p class="text-sm text-slate-500 mt-1">Daftar laboratorium dari denah Gedung GWM lantai 8.</p>
</div>

<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($laboratories) }})">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($laboratories) }} laboratorium</p>
    </div>
    @if(empty($laboratories))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <p class="text-sm text-slate-400">Belum ada data laboratorium</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="code">Kode Lab</x-sort-header>
                        <x-sort-header field="name">Nama Lab</x-sort-header>
                        <x-sort-header field="room_code">Kode Ruangan</x-sort-header>
                        <x-sort-header field="room_name">Nama Ruangan</x-sort-header>
                        <x-sort-header field="building">Gedung</x-sort-header>
                        <x-sort-header field="floor">Lantai</x-sort-header>
                        <x-sort-header field="head">Kepala Lab</x-sort-header>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laboratories as $index => $lab)
                        <tr x-show="showRow({{ $index }})" x-cloak>
                            <td>
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $lab['code'] }}
                                </span>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $lab['name'] }}</td>
                            <td class="text-slate-500 text-xs font-mono">{{ $lab['room_code'] }}</td>
                            <td class="text-slate-600">{{ $lab['room_name'] }}</td>
                            <td class="text-slate-500">{{ $lab['building_name'] }}</td>
                            <td class="text-slate-500">{{ $lab['floor_name'] }}</td>
                            <td>
                                @if($lab['head_name'] ?? null)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[0.6rem] font-bold text-indigo-600">
                                            {{ strtoupper(substr($lab['head_name'], 0, 1)) }}
                                        </div>
                                        <span class="text-slate-700 text-sm">{{ $lab['head_name'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($laboratories) > 0)
            <x-pagination :total="count($laboratories)" />
        @endif
    @endif
</div>
@endsection
