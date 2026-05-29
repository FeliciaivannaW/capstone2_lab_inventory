@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Manajemen Ruangan</h1>
    <p class="text-sm text-slate-500 mt-1">Kelola master gedung, lantai, tipe ruangan, dan ruangan.</p>
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
    <div class="glass-card rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-900 mb-1">Tambah Gedung</h2>
        <p class="text-xs text-slate-500 mb-4">Gedung akan dipakai sebagai induk lantai dan ruangan.</p>

        <form action="{{ route('buildings.store') }}" method="POST" class="space-y-3">
            @csrf
            <input
                name="code"
                value="{{ old('code') }}"
                placeholder="Kode gedung, contoh: GWM"
                class="w-full rounded-xl border-slate-200 text-sm"
                required
            >

            <input
                name="name"
                value="{{ old('name') }}"
                placeholder="Nama gedung"
                class="w-full rounded-xl border-slate-200 text-sm"
                required
            >

            <input
                name="address"
                value="{{ old('address') }}"
                placeholder="Alamat"
                class="w-full rounded-xl border-slate-200 text-sm"
            >

            <textarea
                name="description"
                rows="2"
                placeholder="Deskripsi"
                class="w-full rounded-xl border-slate-200 text-sm"
            >{{ old('description') }}</textarea>

            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                Simpan Gedung
            </button>
        </form>

        <div class="mt-4 border-t border-slate-100 pt-3 space-y-2">
            <p class="text-xs font-bold text-slate-400 uppercase">Gedung Terdaftar</p>

            @forelse($buildings ?? [] as $building)
                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-700">
                        {{ $building['code'] }} - {{ $building['name'] }}
                    </span>

                    <form
                        action="{{ route('buildings.destroy', $building['id']) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus gedung ini? Gedung tidak bisa dihapus kalau masih punya lantai atau ruangan.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-400">Belum ada gedung.</p>
            @endforelse
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-900 mb-1">Tambah Lantai</h2>
        <p class="text-xs text-slate-500 mb-4">Lantai harus terhubung ke gedung.</p>

        <form action="{{ route('floors.store') }}" method="POST" class="space-y-3">
            @csrf

            <select name="building_id" class="w-full rounded-xl border-slate-200 text-sm" required>
                <option value="">Pilih gedung</option>
                @foreach($buildings ?? [] as $building)
                    <option value="{{ $building['id'] }}" {{ old('building_id') == $building['id'] ? 'selected' : '' }}>
                        {{ $building['code'] }} - {{ $building['name'] }}
                    </option>
                @endforeach
            </select>

            <input
                type="number"
                name="floor_number"
                value="{{ old('floor_number') }}"
                placeholder="Nomor lantai, contoh: 8"
                class="w-full rounded-xl border-slate-200 text-sm"
                required
            >

            <textarea
                name="description"
                rows="2"
                placeholder="Deskripsi lantai, opsional"
                class="w-full rounded-xl border-slate-200 text-sm"
            >{{ old('description') }}</textarea>

            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                Simpan Lantai
            </button>
        </form>

        <div class="mt-4 border-t border-slate-100 pt-3 space-y-2">
            <p class="text-xs font-bold text-slate-400 uppercase">Lantai Terdaftar</p>

            @forelse($floors ?? [] as $floor)
                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-700">
                        {{ $floor['building_code'] ?? '-' }} - {{ $floor['name'] }}
                    </span>

                    <form
                        action="{{ route('floors.destroy', $floor['id']) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus lantai ini? Lantai tidak bisa dihapus kalau masih punya ruangan.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-400">Belum ada lantai.</p>
            @endforelse
        </div>
    </div>

    <div class="glass-card rounded-2xl p-5">
        <h2 class="text-sm font-bold text-slate-900 mb-1">Tambah Tipe Ruangan</h2>
        <p class="text-xs text-slate-500 mb-4">Contoh: laboratory, classroom, storage, office.</p>

        <form action="{{ route('room-types.store') }}" method="POST" class="space-y-3">
            @csrf

            <input
                name="name"
                value="{{ old('name') }}"
                placeholder="Nama tipe, contoh: lab riset"
                class="w-full rounded-xl border-slate-200 text-sm"
                required
            >

            <textarea
                name="description"
                rows="2"
                placeholder="Deskripsi"
                class="w-full rounded-xl border-slate-200 text-sm"
            >{{ old('description') }}</textarea>

            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                Simpan Tipe Ruangan
            </button>
        </form>

        <div class="mt-4 border-t border-slate-100 pt-3 space-y-2">
            <p class="text-xs font-bold text-slate-400 uppercase">Tipe Terdaftar</p>

            @forelse($roomTypes ?? [] as $type)
                <div class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-700">
                        {{ ucfirst(str_replace('_', ' ', $type['name'])) }}
                    </span>

                    <form
                        action="{{ route('room-types.destroy', $type['id']) }}"
                        method="POST"
                        onsubmit="return confirm('Hapus tipe ruangan ini? Tipe tidak bisa dihapus kalau masih dipakai ruangan.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                    </form>
                </div>
            @empty
                <p class="text-xs text-slate-400">Belum ada tipe ruangan.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="space-y-6">
        <div class="glass-card rounded-2xl p-6">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Tambah Ruangan Satuan</h2>
            <p class="text-xs text-slate-500 mb-4">Pilih lantai yang sudah memuat gedung, lalu isi data ruangannya.</p>

            <form action="{{ route('rooms.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-semibold text-slate-500">Kode Ruangan</label>
                    <input
                        name="code"
                        value="{{ old('code') }}"
                        class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                        required
                    >
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Nama Ruangan</label>
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                        required
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500">Gedung & Lantai</label>
                        <select name="floor_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                            <option value="">Pilih</option>
                            @foreach($floors ?? [] as $floor)
                                <option value="{{ $floor['id'] }}" {{ old('floor_id') == $floor['id'] ? 'selected' : '' }}>
                                    {{ $floor['building_code'] ?? $floor['building_name'] }} - {{ $floor['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500">Tipe Ruangan</label>
                        <select name="room_type_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                            <option value="">Pilih</option>
                            @foreach($roomTypes ?? [] as $type)
                                <option value="{{ $type['id'] }}" {{ old('room_type_id') == $type['id'] ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type['name'])) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Kapasitas</label>
                    <input
                        type="number"
                        min="0"
                        name="capacity"
                        value="{{ old('capacity') }}"
                        class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                    >
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">Deskripsi</label>
                    <textarea
                        name="description"
                        rows="3"
                        class="w-full mt-1 rounded-xl border-slate-200 text-sm"
                    >{{ old('description') }}</textarea>
                </div>

                <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                    Simpan Ruangan
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6" x-data="{ rows: [0, 1, 2] }">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Input Multiple Ruangan</h2>
            <p class="text-xs text-slate-500 mb-4">Tambah beberapa ruangan sekaligus.</p>

            <form action="{{ route('rooms.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="mode" value="bulk">

                <template x-for="(row, index) in rows" :key="row">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-600">Ruangan <span x-text="index + 1"></span></p>
                            <button type="button" @click="rows.splice(index, 1)" x-show="rows.length > 1" class="text-xs font-semibold text-red-500">Hapus</button>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <input :name="`rooms[${index}][code]`" class="rounded-xl border-slate-200 text-sm" placeholder="Kode" required>
                            <input :name="`rooms[${index}][name]`" class="rounded-xl border-slate-200 text-sm" placeholder="Nama ruangan" required>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <select :name="`rooms[${index}][floor_id]`" class="rounded-xl border-slate-200 text-sm" required>
                                <option value="">Gedung & lantai</option>
                                @foreach($floors ?? [] as $floor)
                                    <option value="{{ $floor['id'] }}">{{ $floor['building_code'] ?? $floor['building_name'] }} - {{ $floor['name'] }}</option>
                                @endforeach
                            </select>

                            <select :name="`rooms[${index}][room_type_id]`" class="rounded-xl border-slate-200 text-sm" required>
                                <option value="">Tipe</option>
                                @foreach($roomTypes ?? [] as $type)
                                    <option value="{{ $type['id'] }}">{{ ucfirst(str_replace('_', ' ', $type['name'])) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" min="0" :name="`rooms[${index}][capacity]`" class="rounded-xl border-slate-200 text-sm" placeholder="Kapasitas">
                            <input :name="`rooms[${index}][description]`" class="rounded-xl border-slate-200 text-sm col-span-2" placeholder="Deskripsi">
                        </div>
                    </div>
                </template>

                <button type="button" @click="rows.push(Date.now())" class="w-full rounded-xl border border-dashed border-indigo-300 text-indigo-600 text-sm font-semibold py-2 hover:bg-indigo-50">
                    + Tambah Baris
                </button>

                <button class="w-full rounded-xl bg-slate-900 text-white text-sm font-semibold py-2.5 hover:bg-slate-800">
                    Simpan Multiple
                </button>
            </form>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden xl:col-span-2 self-start" x-data="tablePagination({{ count($rooms ?? []) }})">
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-800">Daftar Ruangan</p>
                    <p class="text-xs text-slate-400">{{ count($rooms ?? []) }} ruangan</p>
                </div>

                <form method="GET" action="{{ route('rooms') }}" class="flex gap-2 items-center">
                    <div class="relative">
                        <input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari ruangan/gedung"
                            class="rounded-xl border-slate-200 text-sm pr-9"
                        >

                        @if(request()->filled('search'))
                            <a
                                href="{{ route('rooms') }}"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none"
                                title="Reset pencarian"
                            >
                                ×
                            </a>
                        @endif
                    </div>

                    <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4">
                        Cari
                    </button>

                    @if(request()->filled('search'))
                        <a
                            href="{{ route('rooms') }}"
                            class="rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200"
                        >
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
                <x-table-filter column="type" label="Tipe Ruangan" :options="collect($roomTypes ?? [])->pluck('name', 'name')->map(fn($v) => ucfirst(str_replace('_', ' ', $v)))->toArray()" />
                <x-table-filter column="building" label="Gedung" :options="collect($buildings ?? [])->pluck('name', 'name')->toArray()" />

                <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>
                    Reset Filter
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="code">Kode</x-sort-header>
                        <x-sort-header field="name">Nama</x-sort-header>
                        <x-sort-header field="type">Tipe</x-sort-header>
                        <x-sort-header field="building">Gedung</x-sort-header>
                        <x-sort-header field="floor">Lantai</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody x-data="{ editId: null }">
                    @forelse($rooms ?? [] as $index => $room)
                        <tr x-show="showRow({{ $index }})" x-cloak data-filter-type="{{ $room['room_type'] }}" data-filter-building="{{ $room['building_name'] }}">
                            <td>
                                <span class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $room['code'] }}
                                </span>
                            </td>

                            <td class="font-semibold text-slate-800">{{ $room['name'] }}</td>

                            <td>
                                <span class="badge {{ $room['room_type'] === 'laboratory' ? 'badge-active' : 'badge-draft' }} text-xs">
                                    {{ ucfirst(str_replace('_', ' ', $room['room_type'])) }}
                                </span>
                            </td>

                            <td class="text-slate-500">{{ $room['building_name'] }}</td>
                            <td class="text-slate-500">{{ $room['floor_name'] }}</td>

                            <td class="whitespace-nowrap space-x-2">
                                <button type="button" @click="editId = (editId === {{ $room['id'] }} ? null : {{ $room['id'] }})" class="text-xs font-semibold text-indigo-600">
                                    Edit
                                </button>

                                <form action="{{ route('rooms.destroy', $room['id']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs font-semibold text-red-600">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <tr x-show="showRow({{ $index }}) && editId === {{ $room['id'] }}" x-cloak class="bg-slate-50" data-filter-type="{{ $room['room_type'] }}" data-filter-building="{{ $room['building_name'] }}">
                            <td colspan="6">
                                <form action="{{ route('rooms.update', $room['id']) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3">
                                    @csrf
                                    @method('PUT')

                                    <input name="code" value="{{ $room['code'] }}" class="rounded-xl border-slate-200 text-sm" required>
                                    <input name="name" value="{{ $room['name'] }}" class="rounded-xl border-slate-200 text-sm" required>

                                    <select name="floor_id" class="rounded-xl border-slate-200 text-sm" required>
                                        @foreach($floors ?? [] as $floor)
                                            <option value="{{ $floor['id'] }}" {{ $room['floor_id'] == $floor['id'] ? 'selected' : '' }}>
                                                {{ $floor['building_code'] ?? $floor['building_name'] }} - {{ $floor['name'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="room_type_id" class="rounded-xl border-slate-200 text-sm" required>
                                        @foreach($roomTypes ?? [] as $type)
                                            <option value="{{ $type['id'] }}" {{ $room['room_type_id'] == $type['id'] ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type['name'])) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="number" min="0" name="capacity" value="{{ $room['capacity'] }}" class="rounded-xl border-slate-200 text-sm" placeholder="Kapasitas">
                                    <input name="description" value="{{ $room['description'] }}" class="rounded-xl border-slate-200 text-sm md:col-span-2" placeholder="Deskripsi">

                                    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-10">
                                Belum ada data ruangan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($rooms ?? []) > 0)
            <x-pagination :total="count($rooms)" />
        @endif
    </div>
</div>
@endsection