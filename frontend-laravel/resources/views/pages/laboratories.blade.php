@extends('layouts.app')

@section('title', 'Laboratorium')

@section('content')
<div x-data="laboratoriesData">
    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity x-cloak
         class="fixed bottom-6 right-6 z-[100] px-5 py-3 rounded-xl shadow-lg border text-sm font-semibold flex items-center gap-3 transition-colors duration-300"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-red-600 text-white border-red-500'">
        <svg x-show="toast.type === 'success'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <svg x-show="toast.type === 'error'" class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span x-text="toast.message"></span>
    </div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laboratorium</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola laboratorium, grup lab, staf lab, dan akses ruangan.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="activeModal = 'tambah_grup'" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2 hover:bg-indigo-700 transition-colors">
                + Buat Grup Lab
            </button>
            <button @click="activeModal = 'tambah_lab'" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2 hover:bg-indigo-700 transition-colors">
                + Tambah Laboratorium
            </button>
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

    @php
        $laboratories = $laboratories ?? [];
        $availableRooms = $availableRooms ?? [];
        $heads = $heads ?? [];
        $staffLabUsers = $staffLabUsers ?? [];
        $labGroups = $labGroups ?? [];
        $rooms = $rooms ?? [];
    @endphp

    <!-- Modals Section -->
    <!-- Modal Tambah Lab -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_lab'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'tambah_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'tambah_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Laboratorium</h2>
                        <p class="text-xs text-slate-500 mt-1">Pilih ruangan yang bertipe laboratory dan belum dipakai sebagai lab.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('laboratories.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $roomOpts = collect($availableRooms)->mapWithKeys(fn($r) => [$r['id'] => $r['code'] . ' - ' . $r['name'] . ' | ' . $r['building_name'] . ' ' . $r['floor_name']])->toArray();
                                $headOpts = collect($heads)->mapWithKeys(fn($h) => [$h['id'] => $h['name'] . ' — ' . (($h['role_name'] ?? '') === 'kepala_laboratorium' ? 'Kepala Lab' : 'Staf Lab')])->toArray();
                            @endphp
                            <div class="md:col-span-2">
                                <x-form.field type="select" name="room_id" label="Ruangan Laboratory" :options="$roomOpts" value="{{ old('room_id') }}" required />
                            </div>
                            <x-form.field type="text" name="code" label="Kode Lab" placeholder="LAB-XXX" value="{{ old('code') }}" required />
                            <x-form.field type="text" name="name" label="Nama Lab" placeholder="Nama lab" value="{{ old('name') }}" required />
                            <div class="md:col-span-2">
                                <x-form.field type="select" name="head_user_id" label="Penanggung Jawab" :options="['' => 'Belum ditentukan'] + $headOpts" value="{{ old('head_user_id') }}" />
                            </div>
                        </div>
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi lab" value="{{ old('description') }}" />
                        
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Lab
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Buat Grup Lab -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_grup'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'tambah_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="activeModal === 'tambah_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Buat Grup Lab</h2>
                        <p class="text-xs text-slate-500 mt-1">Grup dipakai agar staf lab bisa punya akses ke beberapa ruangan/lab.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form action="{{ route('lab-groups.store') }}" method="POST" class="space-y-4">
                        @csrf
                        @php
                            $labOpts = collect($laboratories)->mapWithKeys(fn($l) => [$l['id'] => $l['code'] . ' - ' . $l['name']])->toArray();
                        @endphp
                        <x-form.field type="select" name="laboratory_id" label="Laboratorium" :options="$labOpts" value="{{ old('laboratory_id') }}" required />
                        <x-form.field type="text" name="name" label="Nama Grup" placeholder="contoh: Programming Group" value="{{ old('name') }}" required />
                        <x-form.field type="textarea" name="description" label="Deskripsi (Opsional)" placeholder="Deskripsi grup" value="{{ old('description') }}" />
                        
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan Grup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Tambah User ke Grup -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_user'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'tambah_user'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="activeModal === 'tambah_user'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah User ke Grup</h2>
                        <p class="text-xs text-slate-500 mt-1">Satu staf lab bisa dimasukkan ke lebih dari satu grup.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form method="POST" class="space-y-4" :action="formGroupId ? '{{ url('/lab-groups') }}/' + formGroupId + '/users' : '#'">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pilih Grup Lab</label>
                            <select x-model="formGroupId" name="group_id_user" class="w-full mt-1 rounded-xl border-slate-200 text-sm" :class="fixedGroupId ? 'pointer-events-none bg-slate-50 opacity-80' : ''" :tabindex="fixedGroupId ? '-1' : '0'" :readonly="fixedGroupId" required>
                                <option value="">Pilih grup lab</option>
                                @foreach($labGroups as $group)
                                    <option value="{{ $group['id'] }}">{{ $group['laboratory_name'] }} - {{ $group['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        @php
                            $roleOpts = ['staf_lab' => 'Staf Lab', 'kepala_lab' => 'Kepala Lab'];
                        @endphp
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Staf Lab <span class="text-red-500">*</span></label>
                            <select x-model="formUserId" name="user_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all" required>
                                <option value="" disabled>-- Pilih Staf Lab --</option>
                                @foreach($staffLabUsers as $u)
                                    <option value="{{ $u['id'] }}" x-show="!isUserInGroup({{ $u['id'] }})">
                                        {{ $u['name'] }} - {{ $u['email'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-form.field type="select" name="role_in_group" label="Role dalam Grup" :options="$roleOpts" required />
                        
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Tambah User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Tambah Ruangan ke Grup -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_ruangan_grup'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'tambah_ruangan_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="activeModal === 'tambah_ruangan_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah Ruangan ke Grup</h2>
                        <p class="text-xs text-slate-500 mt-1">Ruangan yang dipilih akan bisa dikelola oleh staf dalam grup tersebut.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form method="POST" class="space-y-4" :action="formGroupId ? '{{ url('/lab-groups') }}/' + formGroupId + '/rooms' : '#'">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pilih Grup Lab</label>
                            <select x-model="formGroupId" name="group_id_room" class="w-full mt-1 rounded-xl border-slate-200 text-sm" :class="fixedGroupId ? 'pointer-events-none bg-slate-50 opacity-80' : ''" :tabindex="fixedGroupId ? '-1' : '0'" :readonly="fixedGroupId" required>
                                <option value="">Pilih grup lab</option>
                                @foreach($labGroups as $group)
                                    <option value="{{ $group['id'] }}">{{ $group['laboratory_name'] }} - {{ $group['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Ruangan <span class="text-red-500">*</span></label>
                            <select x-model="formRoomId" name="room_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all" required>
                                <option value="" disabled>-- Pilih Ruangan --</option>
                                @foreach($rooms as $r)
                                    @php
                                        $bldg = !empty($r['building_name']) ? ' | ' . $r['building_name'] . ' ' . ($r['floor_name'] ?? '') : '';
                                    @endphp
                                    <option value="{{ $r['id'] }}" x-show="!isRoomInGroup({{ $r['id'] }})">
                                        {{ $r['code'] }} - {{ $r['name'] }}{{ $bldg }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Tambah Ruangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Daftar Grup Lab -->
    <div class="glass-card rounded-2xl overflow-hidden mb-6" x-data="{ searchGroup: '' }">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Grup Lab</p>
                <p class="text-xs text-slate-400 mt-1">{{ count($labGroups) }} grup terdaftar</p>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <input type="text" x-model="searchGroup" placeholder="Cari grup/lab..." class="w-full rounded-xl border-slate-200 text-sm pr-9 bg-white" />
                    <button x-show="searchGroup !== ''" @click="searchGroup = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none" x-cloak>×</button>
                </div>
                
                <div x-data="{ openGroupDropdown: false }" class="relative">
                    <button @click="openGroupDropdown = !openGroupDropdown" @click.outside="openGroupDropdown = false" class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2 hover:bg-slate-200 flex items-center gap-2 transition-colors">
                        + Tambah...
                        <svg class="w-4 h-4 transition-transform duration-200" :class="openGroupDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="openGroupDropdown" 
                         x-transition.opacity.duration.200ms
                         class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-20"
                         style="display: none;">
                        <button @click="openGroupDropdown = false; activeModal = 'tambah_user'; fixedGroupId = null; formGroupId = ''; formUserId = ''; formRoomId = ''" class="w-full text-left px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors border-b border-slate-50 flex items-center gap-2">
                            User ke Grup
                        </button>
                        <button @click="openGroupDropdown = false; activeModal = 'tambah_ruangan_grup'; fixedGroupId = null; formGroupId = ''; formUserId = ''; formRoomId = ''" class="w-full text-left px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors flex items-center gap-2">
                            Ruangan ke Grup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[300px] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="lv-table">
                <thead class="sticky top-0 bg-white shadow-sm z-10">
                    <tr>
                        <th>Grup</th>
                        <th>Laboratorium</th>
                        <th>Jumlah User</th>
                        <th>Jumlah Ruangan</th>
                        <th>Deskripsi</th>
                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labGroups as $group)
                        <tr x-data="{ totalUsers: {{ $group['total_users'] ?? 0 }}, totalRooms: {{ $group['total_rooms'] ?? 0 }} }"
                            @update-group-counts.window="if ($event.detail.groupId == {{ $group['id'] }}) { totalUsers = $event.detail.users; totalRooms = $event.detail.rooms; }"
                            x-show="searchGroup === '' || '{{ addslashes(strtolower($group['name'] . ' ' . $group['laboratory_name'] . ' ' . $group['laboratory_code'] . ' ' . ($group['description'] ?? ''))) }}'.includes(searchGroup.toLowerCase())" 
                            @click="openGroupDetail({{ $group['id'] }})" class="cursor-pointer">
                            <td class="font-semibold text-slate-800">{{ $group['name'] }}</td>
                            <td class="text-slate-600">
                                {{ $group['laboratory_name'] }}
                                <div class="font-mono text-[11px] text-slate-400">{{ $group['laboratory_code'] }}</div>
                            </td>
                            <td x-text="totalUsers + ' user'"></td>
                            <td x-text="totalRooms + ' ruangan'"></td>
                            <td class="text-slate-500">{{ $group['description'] ?? '-' }}</td>
                            <td class="text-center" @click.stop>
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="editData = {{ json_encode($group) }}; activeModal = 'edit_grup'" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button @click="deleteGroup({{ $group['id'] }}, $event)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-10">Belum ada grup lab.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daftar Laboratorium -->
    <div class="glass-card rounded-2xl overflow-hidden mb-8" x-data="tablePagination({{ count($laboratories) }})">
        @php
            $buildings = collect($laboratories)->pluck('building_name')->unique()->filter()->values()->toArray();
            $floors = collect($laboratories)->pluck('floor_name')->unique()->filter()->values()->toArray();
            $buildingOptions = count($buildings) ? array_combine($buildings, $buildings) : [];
            $floorOptions = count($floors) ? array_combine($floors, $floors) : [];
        @endphp

        <div class="px-6 py-4 border-b border-slate-100 flex flex-col xl:flex-row gap-4 xl:items-end justify-between">
            <div class="flex-shrink-0">
                <p class="text-sm font-bold text-slate-800 uppercase tracking-wider">Laboratorium Terdaftar</p>
                <p class="text-xs text-slate-400 mt-1">{{ count($laboratories) }} laboratorium</p>
            </div>

            <div class="flex flex-col md:flex-row flex-wrap items-center gap-4 flex-grow xl:justify-end">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4 w-full md:w-auto">
                    <x-table-filter column="building" label="Gedung" :options="$buildingOptions" />
                    <x-table-filter column="floor" label="Lantai" :options="$floorOptions" />

                    <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors h-fit" x-cloak>
                        Reset Filter
                    </button>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="currentPage = 1; applyFiltersAndSorting()" placeholder="Cari kode/nama lab..." class="w-full rounded-xl border-slate-200 text-sm pr-9 bg-white" />
                    <button x-show="searchQuery !== ''" @click="searchQuery = ''; currentPage = 1; applyFiltersAndSorting()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none" x-cloak>×</button>
                </div>
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
                            <x-sort-header field="building">Lokasi</x-sort-header>
                            <x-sort-header field="head">Penanggung Jawab</x-sort-header>
                            <th class="w-24 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($laboratories as $index => $lab)
                            <tr x-show="showRow({{ $index }})" x-cloak data-filter-building="{{ $lab['building_name'] }}" data-filter-floor="{{ $lab['floor_name'] }}" @click="detailData = {{ json_encode($lab) }}; activeModal = 'detail_lab'" class="cursor-pointer">
                                <td>
                                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $lab['code'] }}
                                    </span>
                                </td>

                                <td class="font-semibold text-slate-800">{{ $lab['name'] }}</td>

                                <td class="text-slate-600">
                                    {{ $lab['room_name'] }}
                                    <div class="font-mono text-[11px] text-slate-400">{{ $lab['room_code'] }}</div>
                                </td>

                                <td class="text-slate-500">
                                    {{ $lab['building_name'] }}
                                    <div class="text-[11px] text-slate-400">{{ $lab['floor_name'] }}</div>
                                </td>

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
                                <td class="text-center" @click.stop>
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="editData = {{ json_encode($lab) }}; activeModal = 'edit_lab'" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button @click="deleteLab({{ $lab['id'] }}, $event)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(count($laboratories) > 0)
                <div class="border-t border-slate-100">
                    <x-pagination :total="count($laboratories)" />
                </div>
            @endif
        @endif
    </div>

    <!-- Template Edit Grup Lab -->
    <template x-teleport="body">
        <div x-show="activeModal === 'edit_grup'" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" style="display: none;">
            <div x-show="activeModal === 'edit_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="activeModal === 'edit_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h2 class="text-lg font-bold text-slate-900">Edit Grup Lab</h2>
                    <button @click="activeModal = null" class="p-2 rounded-xl hover:bg-slate-200/50 text-slate-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 max-h-[calc(100vh-10rem)] overflow-y-auto">
                    <form :action="`/lab-groups/${editData.id}`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-5">
                            <x-form.field label="Nama Grup" name="name" required="true" x-bind:value="editData.name" />
                            
                            <div>
                                <label for="edit_laboratory_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Laboratorium Induk <span class="text-red-500">*</span></label>
                                <select name="laboratory_id" id="edit_laboratory_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm" required>
                                    <option value="" disabled>Pilih Laboratorium</option>
                                    @foreach($laboratories as $lab)
                                        <option value="{{ $lab['id'] }}" x-bind:selected="editData.laboratory_id == {{ $lab['id'] }}">{{ $lab['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-form.field label="Deskripsi (Opsional)" name="description" type="textarea" x-bind:value="editData.description" />

                            <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                                <button type="button" @click="activeModal = null" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm shadow-indigo-200">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Template Edit Laboratorium -->
    <template x-teleport="body">
        <div x-show="activeModal === 'edit_lab'" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" style="display: none;">
            <div x-show="activeModal === 'edit_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <div x-show="activeModal === 'edit_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h2 class="text-lg font-bold text-slate-900">Edit Laboratorium</h2>
                    <button @click="activeModal = null" class="p-2 rounded-xl hover:bg-slate-200/50 text-slate-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 max-h-[calc(100vh-10rem)] overflow-y-auto">
                    <form :action="`/laboratories/${editData.id}`" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <x-form.field label="Kode Laboratorium" name="code" required="true" x-bind:value="editData.code" />
                                <x-form.field label="Nama Laboratorium" name="name" required="true" x-bind:value="editData.name" />
                            </div>

                            <div>
                                <label for="edit_room_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Ruangan <span class="text-red-500">*</span></label>
                                <select name="room_id" id="edit_room_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm" required>
                                    <option value="" disabled>Pilih Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room['id'] }}" x-bind:selected="editData.room_id == {{ $room['id'] }}">{{ $room['name'] }} ({{ $room['code'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="edit_head_user_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Penanggung Jawab</label>
                                <select name="head_user_id" id="edit_head_user_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm">
                                    <option value="">Pilih Nanti</option>
                                    @foreach($heads as $head)
                                        <option value="{{ $head['id'] }}" x-bind:selected="editData.head_user_id == {{ $head['id'] }}">{{ $head['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-form.field label="Deskripsi (Opsional)" name="description" type="textarea" x-bind:value="editData.description" />

                            <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                                <button type="button" @click="activeModal = null" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm shadow-indigo-200">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Detail Laboratorium -->
    <template x-teleport="body">
        <div x-show="activeModal === 'detail_lab'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <div x-show="activeModal === 'detail_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="activeModal = null"></div>
            <div x-show="activeModal === 'detail_lab'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800" x-text="detailData.name || 'Detail Laboratorium'"></h3>
                        <p class="text-xs text-slate-400 mt-0.5">Detail Informasi Laboratorium</p>
                    </div>
                    <button @click="activeModal = null" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="px-6 pb-6 pt-4 flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Kode Lab</p>
                            <p class="text-sm font-bold text-slate-800 mt-1 font-mono" x-text="detailData.code"></p>
                        </div>
                        <div>
                            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Nama Lab</p>
                            <p class="text-sm font-bold text-slate-800 mt-1" x-text="detailData.name"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Ruangan</p>
                            <p class="text-sm text-slate-700 mt-1" x-text="(detailData.room_name || '-') + ' (' + (detailData.room_code || '') + ')'"></p>
                        </div>
                        <div>
                            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Gedung / Lantai</p>
                            <p class="text-sm text-slate-700 mt-1" x-text="(detailData.building_name || '-') + ' — ' + (detailData.floor_name || '')"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Penanggung Jawab</p>
                        <p class="text-sm text-slate-700 mt-1" x-text="detailData.responsible_name || detailData.head_name || 'Belum ditentukan'"></p>
                    </div>
                    <div>
                        <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider">Deskripsi</p>
                        <p class="text-sm text-slate-600 mt-1" x-text="detailData.description || '-'"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal Detail Grup Lab -->
    <template x-teleport="body">
        <div x-show="activeModal === 'detail_grup'" class="fixed inset-0 z-[999] flex items-start justify-center p-4 pt-[5vh] sm:pt-[10vh]" x-cloak>
            <div x-show="activeModal === 'detail_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="activeModal = null"></div>
            <div x-show="activeModal === 'detail_grup'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800" x-text="detailData.name || 'Detail Grup Lab'"></h3>
                        <p class="text-xs text-slate-400 mt-0.5">Lab: <span x-text="detailData.laboratory_name || '-'" class="font-semibold text-slate-500"></span> <span class="font-mono" x-text="'(' + (detailData.laboratory_code || '') + ')'"></span></p>
                    </div>
                    <button @click="activeModal = null" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Loading -->
                <div x-show="groupDetail.loading" class="p-8 text-center">
                    <div class="inline-block w-8 h-8 border-[3px] border-slate-200 border-t-indigo-500 rounded-full animate-spin"></div>
                    <p class="text-xs text-slate-400 mt-3">Memuat detail grup...</p>
                </div>

                <!-- Content -->
                <div x-show="!groupDetail.loading" class="px-6 pb-6 pt-4 flex flex-col gap-5">
                    <!-- Deskripsi -->
                    <template x-if="detailData.description">
                        <div>
                            <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</p>
                            <p class="text-sm text-slate-600" x-text="detailData.description"></p>
                        </div>
                    </template>

                    <!-- Anggota User -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Anggota (<span x-text="groupDetail.users.length"></span>)
                            </p>
                            <button @click="activeModal = 'tambah_user'; fixedGroupId = detailData.id; formGroupId = detailData.id; formUserId = '';" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Tambah User
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="user in groupDetail.users" :key="user.user_id">
                                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:border-indigo-100 bg-white transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase" x-text="user.name.charAt(0)"></div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800" x-text="user.name"></p>
                                            <p class="text-xs text-slate-400" x-text="user.email"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[0.65rem] font-semibold px-2 py-0.5 rounded-full" :class="user.role_in_group === 'kepala_lab' ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-500'" x-text="user.role_in_group === 'kepala_lab' ? 'Kepala Lab' : 'Staf Lab'"></span>
                                        <button @click="removeGroupUser(detailData.id, user.user_id)" class="p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus dari grup">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="groupDetail.users.length === 0">
                                <div class="p-6 text-center rounded-xl border border-dashed border-slate-200">
                                    <p class="text-sm text-slate-400">Belum ada anggota user</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Ruangan -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                Ruangan (<span x-text="groupDetail.rooms.length"></span>)
                            </p>
                            <button @click="activeModal = 'tambah_ruangan_grup'; fixedGroupId = detailData.id; formGroupId = detailData.id; formRoomId = '';" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Tambah Ruangan
                            </button>
                        </div>
                        <div class="rounded-xl border border-slate-100 overflow-hidden">
                            <template x-if="groupDetail.rooms.length === 0">
                                <div class="p-6 text-center text-xs text-slate-400">Belum ada ruangan di grup ini.</div>
                            </template>
                            <template x-for="room in groupDetail.rooms" :key="room.room_id">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50 last:border-b-0 hover:bg-slate-50 transition-colors">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800" x-text="room.room_name"></p>
                                        <p class="text-xs text-slate-400">
                                            <span class="font-mono" x-text="room.room_code"></span> — <span x-text="room.building_name"></span>, <span x-text="room.floor_name"></span>
                                        </p>
                                    </div>
                                    <button @click="removeGroupRoom(detailData.id, room.room_id)" class="p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus dari grup">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
@push('scripts')
<script src="{{ asset('js/laboratories.js') }}"></script>
@endpush
@endsection