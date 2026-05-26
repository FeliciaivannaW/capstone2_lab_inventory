@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Manajemen Ruangan</h1>
    <p class="text-sm text-slate-500 mt-1">Tambah, edit, hapus, dan lihat data ruangan.</p>
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah Ruangan</h2>
        <form action="{{ route('rooms.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-500">Kode Ruangan</label>
                <input name="code" value="{{ old('code') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Nama Ruangan</label>
                <input name="name" value="{{ old('name') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Lantai</label>
                    <select name="floor_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="">Pilih</option>
                        @foreach($floors as $floor)
                            <option value="{{ $floor['id'] }}">{{ $floor['building_name'] }} - {{ $floor['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Tipe</label>
                    <select name="room_type_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="">Pilih</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type['id'] }}">{{ ucfirst($type['name']) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Kapasitas</label>
                <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full mt-1 rounded-xl border-slate-200 text-sm">{{ old('description') }}</textarea>
            </div>
            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">Simpan Ruangan</button>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden xl:col-span-2 self-start" x-data="tablePagination({{ count($rooms) }})">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800">Daftar Ruangan</p>
                <p class="text-xs text-slate-400">{{ count($rooms) }} ruangan</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Cari ruangan" class="rounded-xl border-slate-200 text-sm">
                <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4">Cari</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody x-data="{ editId: null }">
                    @forelse($rooms as $index => $room)
                        <tr x-show="showRow({{ $index }})" x-cloak>
                            <td><span class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md">{{ $room['code'] }}</span></td>
                            <td class="font-semibold text-slate-800">{{ $room['name'] }}</td>
                            <td><span class="badge {{ $room['room_type'] === 'laboratory' ? 'badge-active' : 'badge-draft' }} text-xs">{{ ucfirst($room['room_type']) }}</span></td>
                            <td class="text-slate-500">{{ $room['building_name'] }} · {{ $room['floor_name'] }}</td>
                            <td class="whitespace-nowrap space-x-2">
                                <button type="button" @click="editId = (editId === {{ $room['id'] }} ? null : {{ $room['id'] }})" class="text-xs font-semibold text-indigo-600">Edit</button>
                                <form action="{{ route('rooms.destroy', $room['id']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-red-600">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="showRow({{ $index }}) && editId === {{ $room['id'] }}" x-cloak class="bg-slate-50">
                            <td colspan="5">
                                <form action="{{ route('rooms.update', $room['id']) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3">
                                    @csrf @method('PUT')
                                    <input name="code" value="{{ $room['code'] }}" class="rounded-xl border-slate-200 text-sm" required>
                                    <input name="name" value="{{ $room['name'] }}" class="rounded-xl border-slate-200 text-sm" required>
                                    <select name="floor_id" class="rounded-xl border-slate-200 text-sm" required>
                                        @foreach($floors as $floor)
                                            <option value="{{ $floor['id'] }}" {{ $room['floor_id'] == $floor['id'] ? 'selected' : '' }}>{{ $floor['building_name'] }} - {{ $floor['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <select name="room_type_id" class="rounded-xl border-slate-200 text-sm" required>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type['id'] }}" {{ $room['room_type_id'] == $type['id'] ? 'selected' : '' }}>{{ ucfirst($type['name']) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="0" name="capacity" value="{{ $room['capacity'] }}" class="rounded-xl border-slate-200 text-sm" placeholder="Kapasitas">
                                    <input name="description" value="{{ $room['description'] }}" class="rounded-xl border-slate-200 text-sm md:col-span-2" placeholder="Deskripsi">
                                    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-10">Belum ada data ruangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(count($rooms) > 0)
            <x-pagination :total="count($rooms)" />
        @endif
    </div>
</div>
@endsection