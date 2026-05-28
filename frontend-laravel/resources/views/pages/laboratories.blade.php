@extends('layouts.app')

@section('title', 'Laboratorium')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Laboratorium</h1>
    <p class="text-sm text-slate-500 mt-1">Kelola data laboratorium. Laboratorium dibuat dari ruangan bertipe laboratory.</p>
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

<div class="glass-card rounded-2xl p-6 mb-6">
    <h2 class="text-sm font-bold text-slate-900 mb-1">Tambah Laboratorium</h2>
    <p class="text-xs text-slate-500 mb-4">Pilih ruangan yang bertipe laboratory dan belum dipakai sebagai lab.</p>

    <form action="{{ route('laboratories.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        @csrf
        <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-500">Ruangan Laboratory</label>
            <select name="room_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                <option value="">Pilih ruangan</option>
                @foreach($availableRooms ?? [] as $room)
                    <option value="{{ $room['id'] }}">{{ $room['code'] }} - {{ $room['name'] }} | {{ $room['building_name'] }} {{ $room['floor_name'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500">Kode Lab</label>
            <input name="code" class="w-full mt-1 rounded-xl border-slate-200 text-sm" placeholder="LAB-XXX" required>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500">Nama Lab</label>
            <input name="name" class="w-full mt-1 rounded-xl border-slate-200 text-sm" placeholder="Nama lab" required>
        </div>

        <div>
            <label class="text-xs font-semibold text-slate-500">Penanggung Jawab</label>
            <select name="head_user_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
            <option value="">Belum ditentukan</option>
                @foreach($heads ?? [] as $head)
                    <option value="{{ $head['id'] }}">
                        {{ $head['name'] }}
                        @if(($head['role_name'] ?? '') === 'kepala_laboratorium')
                            — Kepala Lab
                        @elseif(($head['role_name'] ?? '') === 'staf_laboratorium')
                            — Staf Lab
                        @endif
                    </option>
                @endforeach
                </option>
            </select>
        </div>

        <div class="md:col-span-4">
            <label class="text-xs font-semibold text-slate-500">Deskripsi</label>
            <input name="description" class="w-full mt-1 rounded-xl border-slate-200 text-sm" placeholder="Deskripsi lab">
        </div>

        <div class="flex items-end">
            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">Simpan Lab</button>
        </div>
    </form>
</div>

<div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($laboratories) }})">
    @php
        $buildings = collect($laboratories)->pluck('building_name')->unique()->filter()->values()->toArray();
        $floors = collect($laboratories)->pluck('floor_name')->unique()->filter()->values()->toArray();
        $buildingOptions = count($buildings) ? array_combine($buildings, $buildings) : [];
        $floorOptions = count($floors) ? array_combine($floors, $floors) : [];
    @endphp
    <div class="px-6 py-4 border-b border-slate-100 space-y-4">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <p class="text-sm font-semibold text-slate-700">{{ count($laboratories) }} laboratorium</p>
        </div>
        <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
            <x-table-filter column="building" label="Gedung" :options="$buildingOptions" />
            <x-table-filter column="floor" label="Lantai" :options="$floorOptions" />
            <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>Reset Filter</button>
        </div>
    </div>
    @if(empty($laboratories))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl mb-3">🧪</div>
            <p class="text-sm text-slate-400">Belum ada data laboratorium</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="code">Kode Lab</x-sort-header>
                        <x-sort-header field="name">Nama Lab</x-sort-header>
                        <x-sort-header field="room_name">Ruangan</x-sort-header>
                        <x-sort-header field="building">Gedung</x-sort-header>
                        <x-sort-header field="floor">Lantai</x-sort-header>
                        <x-sort-header field="head">Penanggung Jawab</x-sort-header>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laboratories as $index => $lab)
                        <tr x-show="showRow({{ $index }})" x-cloak data-filter-building="{{ $lab['building_name'] }}" data-filter-floor="{{ $lab['floor_name'] }}">
                            <td><span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">{{ $lab['code'] }}</span></td>
                            <td class="font-semibold text-slate-800">{{ $lab['name'] }}</td>
                            <td class="text-slate-600">
                                {{ $lab['room_name'] }}
                                <div class="font-mono text-[11px] text-slate-400">{{ $lab['room_code'] }}</div>
                            </td>
                            <td class="text-slate-500">{{ $lab['building_name'] }}</td>
                            <td class="text-slate-500">{{ $lab['floor_name'] }}</td>
                            <td>
                                @if(($lab['responsible_name'] ?? null) && $lab['responsible_name'] !== 'Belum ditentukan')
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[0.6rem] font-bold text-indigo-600">
                                            {{ strtoupper(substr($lab['responsible_name'], 0, 1)) }}
                                        </div>
                                        <span class="text-slate-700 text-sm">{{ $lab['responsible_name'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">Belum ditentukan</span>
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