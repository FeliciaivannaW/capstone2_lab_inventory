@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
<div x-data="{ activeModal: null, rows: [0] }">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Ruangan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola master gedung, lantai, tipe ruangan, dan ruangan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="activeModal = 'gedung'" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2 hover:bg-slate-200">
                + Gedung
            </button>
            <button @click="activeModal = 'lantai'" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2 hover:bg-slate-200">
                + Lantai
            </button>
            <button @click="activeModal = 'tipe'" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2 hover:bg-slate-200">
                + Tipe Ruangan
            </button>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2 hover:bg-indigo-700 flex items-center gap-2 transition-colors">
                    + Tambah Ruangan
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-52 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-slate-200 focus:outline-none overflow-hidden" 
                     style="display: none;"
                     x-cloak>
                    <div class="py-1">
                        <button @click="activeModal = 'ruangan'; open = false" class="block w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium transition-colors">
                            Satu Ruangan
                        </button>
                        <button @click="activeModal = 'ruangan_multiple'; rows = [0]; open = false" class="block w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 hover:text-indigo-600 font-medium transition-colors">
                            Banyak Ruangan (Multiple)
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

    <!-- Daftar Ruangan Table -->
    <div class="glass-card rounded-2xl overflow-hidden mb-8" x-data="tablePagination({{ count($rooms ?? []) }})">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col xl:flex-row gap-4 xl:items-end justify-between">
            <div class="flex-shrink-0">
                <p class="text-sm font-bold text-slate-800">Daftar Ruangan</p>
                <p class="text-xs text-slate-400">{{ count($rooms ?? []) }} ruangan</p>
            </div>

            <div class="flex flex-col md:flex-row flex-wrap items-center gap-4 flex-grow xl:justify-end">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4 w-full md:w-auto">
                    <x-table-filter column="type" label="Tipe Ruangan" :options="collect($roomTypes ?? [])->pluck('name', 'name')->map(fn($v) => ucwords(str_replace('_', ' ', $v)))->toArray()" />
                    <x-table-filter column="building" label="Gedung" :options="collect($buildings ?? [])->pluck('name', 'name')->toArray()" />

                    <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors h-fit" x-cloak>
                        Reset Filter
                    </button>
                </div>

                <!-- Search -->
                <form method="GET" action="{{ route('rooms') }}" class="flex gap-2 items-center w-full md:w-auto">
                    <div class="relative w-full md:w-auto">
                        <input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari ruangan/gedung"
                            class="rounded-xl border-slate-200 text-sm pr-9 w-full md:w-auto"
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

                    <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-5 py-2 hover:bg-slate-800 transition-colors whitespace-nowrap">
                        Cari
                    </button>

                    @if(request()->filled('search'))
                        <a
                            href="{{ route('rooms') }}"
                            class="rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold px-4 py-2 hover:bg-slate-200 whitespace-nowrap"
                        >
                            Reset
                        </a>
                    @endif
                </form>
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
                        <tr @click="activeModal = 'detail_ruangan_{{ $room['id'] }}'" class="cursor-pointer hover:bg-slate-50/50 transition-colors" x-show="showRow({{ $index }})" x-cloak data-filter-type="{{ $room['room_type'] }}" data-filter-building="{{ $room['building_name'] }}">
                            <td>
                                <span class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $room['code'] }}
                                </span>
                            </td>

                            <td class="font-semibold text-slate-800">{{ $room['name'] }}</td>

                            <td>
                                <span class="badge {{ $room['room_type'] === 'laboratory' ? 'badge-active' : 'badge-draft' }} text-xs">
                                    {{ ucwords(str_replace('_', ' ', $room['room_type'])) }}
                                </span>
                            </td>

                            <td class="text-slate-500">
                                <span class="font-mono text-xs font-semibold text-slate-600">{{ $room['building_code'] }}</span>
                                <span class="mx-1 text-slate-300">-</span>
                                {{ $room['building_name'] }}
                            </td>
                            <td class="text-slate-500">{{ $room['floor_name'] }}</td>

                            <td class="whitespace-nowrap space-x-2">
                                <button type="button" @click.stop="activeModal = 'edit_ruangan_{{ $room['id'] }}'" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                    Edit
                                </button>

                                <form action="{{ route('rooms.destroy', $room['id']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus ruangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button @click.stop class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Detail Ruangan -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'detail_ruangan_{{ $room['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'detail_ruangan_{{ $room['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     @click="activeModal = null"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'detail_ruangan_{{ $room['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Detail Ruangan</h2>
                                            <p class="text-xs text-slate-500 mt-1">Informasi lengkap tentang ruangan ini.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-2 gap-4 text-left">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Ruangan</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $room['code'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Ruangan</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $room['name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Ruangan</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ ucwords(str_replace('_', ' ', $room['room_type'])) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kapasitas</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ !empty($room['capacity']) ? $room['capacity'] . ' Orang' : '-' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $room['building_name'] }} - {{ $room['floor_name'] }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                                                <p class="text-sm text-slate-600">{{ $room['description'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-100">
                                            <button type="button" @click="activeModal = null" class="w-full rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Edit Ruangan -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'edit_ruangan_{{ $room['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'edit_ruangan_{{ $room['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'edit_ruangan_{{ $room['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Edit Ruangan</h2>
                                            <p class="text-xs text-slate-500 mt-1">Ubah data ruangan yang sudah ada.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 text-left">
                                        <form action="{{ route('rooms.update', $room['id']) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid grid-cols-2 gap-4">
                                                <x-form.field type="text" name="code" label="Kode Ruangan" value="{{ old('code', $room['code']) }}" required />
                                                <x-form.field type="text" name="name" label="Nama Ruangan" value="{{ old('name', $room['name']) }}" required />
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                @php
                                                    $fOpts = [];
                                                    foreach($floors ?? [] as $floor) {
                                                        $fOpts[$floor['id']] = ($floor['building_code'] ?? $floor['building_name']) . ' - ' . $floor['name'];
                                                    }
                                                    $tOpts = [];
                                                    foreach($roomTypes ?? [] as $type) {
                                                        $tOpts[$type['id']] = ucwords(str_replace('_', ' ', $type['name']));
                                                    }
                                                @endphp
                                                <x-form.field type="select" name="floor_id" label="Gedung & Lantai" :options="$fOpts" value="{{ old('floor_id', $room['floor_id']) }}" required />
                                                <x-form.field type="select" name="room_type_id" label="Tipe Ruangan" :options="$tOpts" value="{{ old('room_type_id', $room['room_type_id']) }}" required />
                                            </div>
                                            <x-form.field type="number" name="capacity" label="Kapasitas (Opsional)" value="{{ old('capacity', $room['capacity']) }}" />
                                            <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" value="{{ old('description', $room['description']) }}" />
                                            <div class="pt-2 flex gap-3">
                                                <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                    Batal
                                                </button>
                                                <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
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

    <!-- Master Data Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gedung Terdaftar -->
        <div class="glass-card rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Gedung Terdaftar</h2>
            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($buildings ?? [] as $building)
                    <div @click="activeModal = 'detail_gedung_{{ $building['id'] }}'" class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2 cursor-pointer hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">
                            {{ $building['code'] }} - {{ $building['name'] }}
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button" @click.stop="activeModal = 'edit_gedung_{{ $building['id'] }}'" class="text-indigo-600 font-semibold hover:text-indigo-700">Edit</button>
                            <form
                                action="{{ route('buildings.destroy', $building['id']) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus gedung ini? Gedung tidak bisa dihapus kalau masih punya lantai atau ruangan.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button @click.stop class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                            </form>
                        </div>

                        <!-- Modal Detail Gedung -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'detail_gedung_{{ $building['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'detail_gedung_{{ $building['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     @click="activeModal = null"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'detail_gedung_{{ $building['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Detail Gedung</h2>
                                            <p class="text-xs text-slate-500 mt-1">Informasi lengkap gedung.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-2 gap-4 text-left">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Gedung</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $building['code'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Gedung</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $building['name'] }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $building['address'] ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                                                <p class="text-sm text-slate-600">{{ $building['description'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-100">
                                            <button type="button" @click="activeModal = null" class="w-full rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Edit Gedung -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'edit_gedung_{{ $building['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'edit_gedung_{{ $building['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'edit_gedung_{{ $building['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Edit Gedung</h2>
                                            <p class="text-xs text-slate-500 mt-1">Ubah data gedung yang sudah ada.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 text-left">
                                        <form action="{{ route('buildings.update', $building['id']) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <x-form.field type="text" name="code" label="Kode Gedung" placeholder="contoh: GWM" value="{{ old('code', $building['code']) }}" required />
                                            <x-form.field type="text" name="name" label="Nama Gedung" placeholder="Nama gedung" value="{{ old('name', $building['name']) }}" required />
                                            <x-form.field type="text" name="address" label="Alamat (Opsional)" placeholder="Alamat" value="{{ old('address', $building['address'] ?? '') }}" />
                                            <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi" value="{{ old('description', $building['description'] ?? '') }}" />
                                            <div class="pt-2 flex gap-3">
                                                <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                    Batal
                                                </button>
                                                <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada gedung.</p>
                @endforelse
            </div>
        </div>

        <!-- Lantai Terdaftar -->
        <div class="glass-card rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Lantai Terdaftar</h2>
            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($floors ?? [] as $floor)
                    <div @click="activeModal = 'detail_lantai_{{ $floor['id'] }}'" class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2 cursor-pointer hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">
                            {{ $floor['building_code'] ?? '-' }} - {{ $floor['name'] }}
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button" @click.stop="activeModal = 'edit_lantai_{{ $floor['id'] }}'" class="text-indigo-600 font-semibold hover:text-indigo-700">Edit</button>
                            <form
                                action="{{ route('floors.destroy', $floor['id']) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus lantai ini? Lantai tidak bisa dihapus kalau masih punya ruangan.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button @click.stop class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                            </form>
                        </div>

                        <!-- Modal Detail Lantai -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'detail_lantai_{{ $floor['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'detail_lantai_{{ $floor['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     @click="activeModal = null"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'detail_lantai_{{ $floor['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Detail Lantai</h2>
                                            <p class="text-xs text-slate-500 mt-1">Informasi lengkap lantai.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-2 gap-4 text-left">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Gedung</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $floor['building_code'] ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lantai</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $floor['name'] }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                                                <p class="text-sm text-slate-600">{{ $floor['description'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-100">
                                            <button type="button" @click="activeModal = null" class="w-full rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Edit Lantai -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'edit_lantai_{{ $floor['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'edit_lantai_{{ $floor['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'edit_lantai_{{ $floor['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Edit Lantai</h2>
                                            <p class="text-xs text-slate-500 mt-1">Ubah data lantai yang sudah ada.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 text-left">
                                        <form action="{{ route('floors.update', $floor['id']) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            @php
                                                $bldgOptions = [];
                                                foreach($buildings ?? [] as $b) {
                                                    $bldgOptions[$b['id']] = $b['code'] . ' - ' . $b['name'];
                                                }
                                            @endphp
                                            <x-form.field type="select" name="building_id" label="Pilih Gedung" :options="$bldgOptions" value="{{ old('building_id', $floor['building_id']) }}" required />
                                            <x-form.field type="number" name="floor_number" label="Nomor Lantai" placeholder="contoh: 8" value="{{ old('floor_number', $floor['floor_number'] ?? str_replace('Lantai ', '', $floor['name'])) }}" required />
                                            <x-form.field type="text" name="name" label="Nama Lantai" placeholder="contoh: Lantai 8" value="{{ old('name', $floor['name'] ?? '') }}" />
                                            <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi lantai" value="{{ old('description', $floor['description'] ?? '') }}" />
                                            <div class="pt-2 flex gap-3">
                                                <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                    Batal
                                                </button>
                                                <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada lantai.</p>
                @endforelse
            </div>
        </div>

        <!-- Tipe Ruangan Terdaftar -->
        <div class="glass-card rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Tipe Terdaftar</h2>
            <div class="space-y-2 max-h-[250px] overflow-y-auto pr-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($roomTypes ?? [] as $type)
                    <div @click="activeModal = 'detail_tipe_{{ $type['id'] }}'" class="flex items-center justify-between gap-2 text-xs bg-slate-50 rounded-lg px-3 py-2 cursor-pointer hover:bg-slate-100 transition-colors">
                        <span class="font-semibold text-slate-700">
                            {{ ucwords(str_replace('_', ' ', $type['name'])) }}
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button" @click.stop="activeModal = 'edit_tipe_{{ $type['id'] }}'" class="text-indigo-600 font-semibold hover:text-indigo-700">Edit</button>
                            <form
                                action="{{ route('room-types.destroy', $type['id']) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus tipe ruangan ini? Tipe tidak bisa dihapus kalau masih dipakai ruangan.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button @click.stop class="text-red-600 font-semibold hover:text-red-700">Hapus</button>
                            </form>
                        </div>

                        <!-- Modal Detail Tipe Ruangan -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'detail_tipe_{{ $type['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'detail_tipe_{{ $type['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     @click="activeModal = null"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'detail_tipe_{{ $type['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Detail Tipe Ruangan</h2>
                                            <p class="text-xs text-slate-500 mt-1">Informasi lengkap tipe ruangan.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 space-y-4">
                                        <div class="grid grid-cols-1 gap-4 text-left">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Tipe Ruangan</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ ucwords(str_replace('_', ' ', $type['name'])) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Sistem (Database)</p>
                                                <p class="text-sm font-semibold text-slate-500 font-mono">{{ $type['name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                                                <p class="text-sm text-slate-600">{{ $type['description'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-100">
                                            <button type="button" @click="activeModal = null" class="w-full rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Edit Tipe Ruangan -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'edit_tipe_{{ $type['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'edit_tipe_{{ $type['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'edit_tipe_{{ $type['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Edit Tipe Ruangan</h2>
                                            <p class="text-xs text-slate-500 mt-1">Ubah data tipe ruangan.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 text-left">
                                        <form action="{{ route('room-types.update', $type['id']) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <x-form.field type="text" name="name" label="Nama Tipe" placeholder="contoh: Lab Riset" value="{{ old('name', ucwords(str_replace('_', ' ', $type['name']))) }}" required />
                                            <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi" value="{{ old('description', $type['description'] ?? '') }}" />
                                            <div class="pt-2 flex gap-3">
                                                <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                                    Batal
                                                </button>
                                                <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Belum ada tipe ruangan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modals -->

    <!-- Modal Gedung -->
    <template x-teleport="body">
        <div x-show="activeModal === 'gedung'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'gedung'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'gedung'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Gedung</h2>
                        <p class="text-xs text-slate-500 mt-1">Gedung akan dipakai sebagai induk lantai dan ruangan.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('buildings.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <x-form.field type="text" name="code" label="Kode Gedung" placeholder="contoh: GWM" value="{{ old('code') }}" required />
                        <x-form.field type="text" name="name" label="Nama Gedung" placeholder="Nama gedung" value="{{ old('name') }}" required />
                        <x-form.field type="text" name="address" label="Alamat (Opsional)" placeholder="Alamat" value="{{ old('address') }}" />
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi" value="{{ old('description') }}" />
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Gedung
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Lantai -->
    <template x-teleport="body">
        <div x-show="activeModal === 'lantai'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'lantai'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'lantai'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Lantai</h2>
                        <p class="text-xs text-slate-500 mt-1">Lantai harus terhubung ke gedung.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('floors.store') }}" method="POST" class="space-y-4">
                        @csrf
                        @php
                            $bldgOptions = [];
                            foreach($buildings ?? [] as $b) {
                                $bldgOptions[$b['id']] = $b['code'] . ' - ' . $b['name'];
                            }
                        @endphp
                        <x-form.field type="select" name="building_id" label="Pilih Gedung" :options="$bldgOptions" value="{{ old('building_id') }}" required />
                        <x-form.field type="number" name="floor_number" label="Nomor Lantai" placeholder="contoh: 8" value="{{ old('floor_number') }}" required />
                        <x-form.field type="text" name="name" label="Nama Lantai" placeholder="contoh: Lantai 8" value="{{ old('name') }}" />
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi lantai" value="{{ old('description') }}" />
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Lantai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Tipe Ruangan -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tipe'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'tipe'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'tipe'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Tipe Ruangan</h2>
                        <p class="text-xs text-slate-500 mt-1">Contoh: laboratory, classroom, storage, office.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('room-types.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <x-form.field type="text" name="name" label="Nama Tipe" placeholder="contoh: Lab Riset" value="{{ old('name') }}" required />
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi" value="{{ old('description') }}" />
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Tipe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Ruangan Satuan -->
    <template x-teleport="body">
        <div x-show="activeModal === 'ruangan'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'ruangan'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'ruangan'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Ruangan</h2>
                        <p class="text-xs text-slate-500 mt-1">Pilih lantai yang sudah memuat gedung, lalu isi data ruangannya.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('rooms.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <x-form.field type="text" name="code" label="Kode Ruangan" value="{{ old('code') }}" required />
                            <x-form.field type="text" name="name" label="Nama Ruangan" value="{{ old('name') }}" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            @php
                                $floorOptions = [];
                                foreach($floors ?? [] as $floor) {
                                    $floorOptions[$floor['id']] = ($floor['building_code'] ?? $floor['building_name']) . ' - ' . $floor['name'];
                                }
                                $typeOptions = [];
                                foreach($roomTypes ?? [] as $type) {
                                    $typeOptions[$type['id']] = ucfirst(str_replace('_', ' ', $type['name']));
                                }
                            @endphp
                            <x-form.field type="select" name="floor_id" label="Gedung & Lantai" :options="$floorOptions" value="{{ old('floor_id') }}" required />
                            <x-form.field type="select" name="room_type_id" label="Tipe Ruangan" :options="$typeOptions" value="{{ old('room_type_id') }}" required />
                        </div>
                        <x-form.field type="number" name="capacity" label="Kapasitas (Opsional)" value="{{ old('capacity') }}" />
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" value="{{ old('description') }}" />
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Ruangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Ruangan Multiple -->
    <template x-teleport="body">
        <div x-show="activeModal === 'ruangan_multiple'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'ruangan_multiple'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'ruangan_multiple'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Input Multiple Ruangan</h2>
                        <p class="text-xs text-slate-500 mt-1">Tambah beberapa ruangan sekaligus. Kolom bertanda * wajib diisi.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 overflow-y-auto">
                    <form action="{{ route('rooms.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="mode" value="bulk">

                        <template x-for="(row, index) in rows" :key="row">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-600">Ruangan <span x-text="index + 1"></span></p>
                                    <button type="button" @click="rows.splice(index, 1)" x-show="rows.length > 1" class="text-xs font-semibold text-red-500">Hapus</button>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <input :name="`rooms[${index}][code]`" class="rounded-xl border-slate-200 text-sm" placeholder="Kode *" required>
                                    <input :name="`rooms[${index}][name]`" class="rounded-xl border-slate-200 text-sm" placeholder="Nama ruangan *" required>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <select :name="`rooms[${index}][floor_id]`" class="rounded-xl border-slate-200 text-sm" required>
                                        <option value="" disabled selected>Gedung & lantai *</option>
                                        @foreach($floors ?? [] as $floor)
                                            <option value="{{ $floor['id'] }}">{{ $floor['building_code'] ?? $floor['building_name'] }} - {{ $floor['name'] }}</option>
                                        @endforeach
                                    </select>

                                    <select :name="`rooms[${index}][room_type_id]`" class="rounded-xl border-slate-200 text-sm" required>
                                        <option value="" disabled selected>Tipe *</option>
                                        @foreach($roomTypes ?? [] as $type)
                                            <option value="{{ $type['id'] }}">{{ ucwords(str_replace('_', ' ', $type['name'])) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <input type="number" min="0" :name="`rooms[${index}][capacity]`" class="rounded-xl border-slate-200 text-sm w-full md:w-1/2" placeholder="Kapasitas (Opsional)">
                                </div>
                                
                                <div>
                                    <textarea :name="`rooms[${index}][description]`" rows="2" class="rounded-xl border-slate-200 text-sm w-full resize-y" placeholder="Deskripsi (Opsional)"></textarea>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="rows.push(Date.now())" class="w-full rounded-xl border border-dashed border-indigo-300 text-indigo-600 text-sm font-semibold py-3 hover:bg-indigo-50 transition-colors mt-2">
                            + Tambah Baris
                        </button>

                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-slate-900 text-white text-sm font-semibold py-2.5 hover:bg-slate-800">
                                Simpan Multiple
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection