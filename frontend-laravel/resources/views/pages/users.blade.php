@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div x-data="{ activeModal: null }">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen User</h1>
            <p class="text-sm text-slate-500 mt-1">Tambah, edit, hapus, atur role, status akun, lab utama, dan akses grup lab.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="activeModal = 'tambah_user'" class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2 hover:bg-indigo-700 transition-colors">
                + Tambah User
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
        $users = $users ?? [];
        $roles = $roles ?? [];
        $laboratories = $laboratories ?? [];
        $labGroups = $labGroups ?? [];
        $oldGroupIds = collect(old('lab_group_ids', []))->map(fn($id) => (string) $id)->toArray();

        $roleOptions = [];
        foreach($roles as $role) {
            $roleOptions[$role['id']] = ucwords(str_replace('_', ' ', $role['name']));
        }

        $labOptions = ['' => 'Tidak terkait lab'];
        foreach($laboratories as $lab) {
            $labOptions[$lab['id']] = $lab['name'];
        }

        $statusOptions = ['active' => 'Active', 'inactive' => 'Inactive'];
    @endphp

    <!-- Modal Tambah User -->
    <template x-teleport="body">
        <div x-show="activeModal === 'tambah_user'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="activeModal === 'tambah_user'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

            <!-- Modal Panel -->
            <div x-show="activeModal === 'tambah_user'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                 class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden text-left">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Tambah User</h2>
                        <p class="text-xs text-slate-500 mt-1">Buat akun user baru dengan role, lab utama, dan akses grup lab.</p>
                    </div>
                    <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 max-h-[calc(100vh-10rem)] overflow-y-auto">
                    <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.field type="text" name="name" label="Nama" placeholder="Nama lengkap" value="{{ old('name') }}" required />
                            <x-form.field type="text" name="nrp_nip" label="NRP/NIP" placeholder="Nomor NRP atau NIP" value="{{ old('nrp_nip') }}" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.field type="email" name="email" label="Email" placeholder="email@contoh.com" value="{{ old('email') }}" required />
                            <x-form.field type="password" name="password" label="Password" placeholder="Buat password" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.field type="select" name="role_id" label="Role" :options="$roleOptions" value="{{ old('role_id') }}" required />
                            <x-form.field type="select" name="status" label="Status" :options="$statusOptions" value="{{ old('status', 'active') }}" />
                        </div>

                        <x-form.field type="select" name="lab_id" label="Lab Utama" :options="$labOptions" value="{{ old('lab_id') }}" />
                        <p class="text-[0.68rem] text-slate-400 -mt-3">Opsional. Dipakai sebagai lab utama user.</p>

                        <!-- Akses Grup Lab (Checkbox) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Akses Grup Lab</label>
                            <div class="border border-slate-200 rounded-xl max-h-48 overflow-y-auto p-3 space-y-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                                @forelse($labGroups as $group)
                                    <label class="flex items-center gap-2.5 cursor-pointer px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                        <input
                                            type="checkbox"
                                            name="lab_group_ids[]"
                                            value="{{ $group['id'] }}"
                                            {{ in_array((string) $group['id'], $oldGroupIds, true) ? 'checked' : '' }}
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20"
                                        >
                                        <span class="text-sm text-slate-700">{{ $group['laboratory_name'] }} — {{ $group['name'] }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-400 py-2 text-center">Belum ada grup lab.</p>
                                @endforelse
                            </div>
                            <p class="text-[0.68rem] text-slate-400 mt-1">Centang satu atau lebih grup lab untuk memberikan akses.</p>
                        </div>

                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="activeModal = null" class="flex-1 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">
                                Simpan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Daftar User Table -->
    <div class="glass-card rounded-2xl overflow-hidden" x-data="tablePagination({{ count($users) }})">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col xl:flex-row gap-4 xl:items-end justify-between">
            <div class="flex-shrink-0">
                <p class="text-sm font-bold text-slate-800">Daftar User</p>
                <p class="text-xs text-slate-400">{{ count($users) }} user terdaftar</p>
            </div>

            <div class="flex flex-col md:flex-row flex-wrap items-center gap-4 flex-grow xl:justify-end">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4 w-full md:w-auto">
                    <x-table-filter column="role" label="Role" :options="[
                        'administrator' => 'Administrator',
                        'admin' => 'Admin',
                        'staf_laboratorium' => 'Staf Laboratorium',
                        'kepala_laboratorium' => 'Kepala Laboratorium',
                        'staf_administrasi' => 'Staf Administrasi',
                        'ketua_program_studi' => 'Ketua Program Studi',
                    ]" />
                    <x-table-filter column="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive']" />
                    <x-table-filter column="lab" label="Laboratorium" :options="collect($laboratories)->pluck('name', 'id')->toArray()" />
                    
                    <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors h-fit" x-cloak>
                        Reset Filter
                    </button>
                </div>

                <!-- Search -->
                <form method="GET" action="{{ route('users') }}" class="flex gap-2 items-center w-full md:w-auto">
                    <div class="relative w-full md:w-auto">
                        <input
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama/email"
                            class="rounded-xl border-slate-200 text-sm pr-9 w-full md:w-auto"
                        >

                        @if(request()->filled('search'))
                            <a
                                href="{{ route('users') }}"
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
                        <a href="{{ route('users') }}" class="rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold px-4 py-2 hover:bg-slate-200 transition-colors whitespace-nowrap">
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
                        <x-sort-header field="user">User</x-sort-header>
                        <x-sort-header field="role">Role</x-sort-header>
                        <x-sort-header field="lab">Akses Lab</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $index => $user)
                        @php
                            $selectedGroupIds = collect(explode(',', $user['lab_group_ids'] ?? ''))
                                ->filter()
                                ->map(fn($id) => trim((string) $id))
                                ->toArray();
                        @endphp

                        <tr @click="activeModal = 'detail_user_{{ $user['id'] }}'" class="cursor-pointer hover:bg-slate-50/50 transition-colors" x-show="showRow({{ $index }})" x-cloak data-filter-role="{{ $user['role'] }}" data-filter-status="{{ $user['status'] }}" data-filter-lab="{{ $user['lab_id'] ?? '' }}">
                            <td>
                                <div class="font-semibold text-slate-800">{{ $user['name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $user['email'] }} · {{ $user['nrp_nip'] ?? '-' }}</div>
                            </td>

                            <td>
                                <span class="badge badge-active text-xs">{{ ucwords(str_replace('_', ' ', $user['role'])) }}</span>
                            </td>

                            <td class="text-slate-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-400">Utama:</span>
                                    {{ $user['laboratory_name'] ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">
                                    <span class="font-semibold">Grup:</span>
                                    {{ $user['lab_group_names'] ?? '—' }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $user['status'] === 'active' ? 'badge-approved' : 'badge-rejected' }} text-xs">
                                    {{ ucfirst($user['status']) }}
                                </span>
                            </td>

                            <td class="space-x-2 whitespace-nowrap">
                                <button type="button" @click.stop="activeModal = 'edit_user_{{ $user['id'] }}'" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                                    Edit
                                </button>

                                <form action="{{ route('users.destroy', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button @click.stop class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Detail User -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'detail_user_{{ $user['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'detail_user_{{ $user['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     @click="activeModal = null"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm cursor-pointer"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'detail_user_{{ $user['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Detail User</h2>
                                            <p class="text-xs text-slate-500 mt-1">Informasi lengkap akun user.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $user['name'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">NRP / NIP</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $user['nrp_nip'] ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                                                <p class="text-sm font-semibold text-slate-800 break-all">{{ $user['email'] }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Role</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ ucwords(str_replace('_', ' ', $user['role'])) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status Akun</p>
                                                <span class="badge {{ $user['status'] === 'active' ? 'badge-approved' : 'badge-rejected' }} text-xs">
                                                    {{ ucfirst($user['status']) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lab Utama</p>
                                                <p class="text-sm font-semibold text-slate-800">{{ $user['laboratory_name'] ?? '—' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Akses Grup Lab</p>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                                                    @if(!empty($user['lab_group_names']))
                                                        <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                                            @foreach(explode(',', $user['lab_group_names']) as $gName)
                                                                <li>{{ trim($gName) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p class="text-sm text-slate-500 italic">Tidak memiliki akses grup lab.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-4 border-t border-slate-100">
                                            <button type="button" @click="activeModal = null" class="w-full rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 hover:bg-slate-200 transition-colors">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Modal Edit User -->
                        <template x-teleport="body">
                            <div x-show="activeModal === 'edit_user_{{ $user['id'] }}'" class="fixed inset-0 z-[999] flex items-center justify-center p-4" x-cloak>
                                <!-- Backdrop -->
                                <div x-show="activeModal === 'edit_user_{{ $user['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                                <!-- Modal Panel -->
                                <div x-show="activeModal === 'edit_user_{{ $user['id'] }}'"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-90 translate-y-8"
                                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden text-left">
                                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <div>
                                            <h2 class="text-lg font-bold text-slate-900">Edit User</h2>
                                            <p class="text-xs text-slate-500 mt-1">Ubah data user <span class="font-semibold">{{ $user['name'] }}</span>.</p>
                                        </div>
                                        <button type="button" @click="activeModal = null" class="text-slate-400 hover:text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    <div class="p-5 max-h-[calc(100vh-10rem)] overflow-y-auto">
                                        <form action="{{ route('users.update', $user['id']) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <x-form.field type="text" name="name" label="Nama" value="{{ $user['name'] }}" required />
                                                <x-form.field type="text" name="nrp_nip" label="NRP/NIP" placeholder="NRP/NIP" value="{{ $user['nrp_nip'] ?? '' }}" />
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <x-form.field type="email" name="email" label="Email" value="{{ $user['email'] }}" required />
                                                <x-form.field type="password" name="password" label="Password" placeholder="Kosongkan jika tidak diubah" />
                                            </div>

                                            @php
                                                $editRoleOptions = [];
                                                foreach($roles as $role) {
                                                    $editRoleOptions[$role['id']] = ucwords(str_replace('_', ' ', $role['name']));
                                                }

                                                $editLabOptions = ['' => 'Tidak terkait lab'];
                                                foreach($laboratories as $lab) {
                                                    $editLabOptions[$lab['id']] = $lab['name'];
                                                }
                                            @endphp

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <x-form.field type="select" name="role_id" label="Role" :options="$editRoleOptions" value="{{ $user['role_id'] }}" required />
                                                <x-form.field type="select" name="lab_id" label="Lab Utama" :options="$editLabOptions" value="{{ $user['lab_id'] ?? '' }}" />
                                                <x-form.field type="select" name="status" label="Status" :options="$statusOptions" value="{{ $user['status'] }}" />
                                            </div>

                                            <!-- Akses Grup Lab (Checkbox) -->
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-600 mb-1">Akses Grup Lab</label>
                                                <div class="border border-slate-200 rounded-xl max-h-48 overflow-y-auto p-3 space-y-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                                                    @forelse($labGroups as $group)
                                                        <label class="flex items-center gap-2.5 cursor-pointer px-2 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                                                            <input
                                                                type="checkbox"
                                                                name="lab_group_ids[]"
                                                                value="{{ $group['id'] }}"
                                                                {{ in_array((string) $group['id'], $selectedGroupIds, true) ? 'checked' : '' }}
                                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20"
                                                            >
                                                            <span class="text-sm text-slate-700">{{ $group['laboratory_name'] }} — {{ $group['name'] }}</span>
                                                        </label>
                                                    @empty
                                                        <p class="text-xs text-slate-400 py-2 text-center">Belum ada grup lab.</p>
                                                    @endforelse
                                                </div>
                                                <p class="text-[0.68rem] text-slate-400 mt-1">Centang satu atau lebih grup lab untuk memberikan akses.</p>
                                            </div>

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
                            <td colspan="5" class="text-center text-slate-400 py-10">
                                Belum ada user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(count($users) > 0)
            <x-pagination :total="count($users)" />
        @endif
    </div>
</div>
@endsection
