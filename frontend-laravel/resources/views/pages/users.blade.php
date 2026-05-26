@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Manajemen User</h1>
        <p class="text-sm text-slate-500 mt-1">Tambah, edit, hapus, atur role, status akun, dan relasi lab.</p>
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="glass-card rounded-2xl p-6 xl:col-span-1">
        <h2 class="text-sm font-bold text-slate-900 mb-4">Tambah User</h2>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-500">Nama</label>
                <input name="name" value="{{ old('name') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">NRP/NIP</label>
                <input name="nrp_nip" value="{{ old('nrp_nip') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Password</label>
                <input type="password" name="password" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Role</label>
                    <select name="role_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="">Pilih</option>
                        @foreach($roles as $role)
                            <option value="{{ $role['id'] }}" {{ old('role_id') == $role['id'] ? 'selected' : '' }}>{{ str_replace('_', ' ', $role['name']) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Status</label>
                    <select name="status" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Laboratorium</label>
                <select name="lab_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
                    <option value="">Tidak terkait lab</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab['id'] }}" {{ old('lab_id') == $lab['id'] ? 'selected' : '' }}>{{ $lab['name'] }}</option>
                    @endforeach
                </select>
                <p class="text-[0.68rem] text-slate-400 mt-1">Dipakai untuk kepala lab/staf lab agar data mengikuti lab-nya.</p>
            </div>
            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">Simpan User</button>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden xl:col-span-2 self-start" x-data="tablePagination({{ count($users) }})">
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-800">Daftar User</p>
                    <p class="text-xs text-slate-400">{{ count($users) }} user terdaftar</p>
                </div>
                <form method="GET" class="flex gap-2">
                    <input name="search" value="{{ request('search') }}" placeholder="Cari nama/email" class="rounded-xl border-slate-200 text-sm">
                    <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4">Cari</button>
                </form>
            </div>
            <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-slate-50">
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
                <button type="button" @click="resetFilters()" x-show="Object.values(filters).some(v => v !== '')" class="text-xs text-red-600 font-semibold hover:text-red-700 transition-colors pb-2.5 h-fit" x-cloak>
                    Reset Filter
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <x-sort-header field="user">User</x-sort-header>
                        <x-sort-header field="role">Role</x-sort-header>
                        <x-sort-header field="lab">Lab</x-sort-header>
                        <x-sort-header field="status">Status</x-sort-header>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody x-data="{ editId: null }">
                    @forelse($users as $index => $user)
                        <tr x-show="showRow({{ $index }})" x-cloak data-filter-role="{{ $user['role'] }}" data-filter-status="{{ $user['status'] }}" data-filter-lab="{{ $user['lab_id'] ?? '' }}">
                            <td>
                                <div class="font-semibold text-slate-800">{{ $user['name'] }}</div>
                                <div class="text-xs text-slate-400">{{ $user['email'] }} · {{ $user['nrp_nip'] ?? '-' }}</div>
                            </td>
                            <td><span class="badge badge-active text-xs">{{ str_replace('_', ' ', $user['role']) }}</span></td>
                            <td class="text-slate-500">{{ $user['laboratory_name'] ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $user['status'] === 'active' ? 'badge-approved' : 'badge-rejected' }} text-xs">{{ $user['status'] }}</span>
                            </td>
                            <td class="space-x-2 whitespace-nowrap">
                                <button type="button" @click="editId = (editId === {{ $user['id'] }} ? null : {{ $user['id'] }})" class="text-xs font-semibold text-indigo-600">Edit</button>
                                <form action="{{ route('users.destroy', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-red-600">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="showRow({{ $index }}) && editId === {{ $user['id'] }}" x-cloak class="bg-slate-50" data-filter-role="{{ $user['role'] }}" data-filter-status="{{ $user['status'] }}" data-filter-lab="{{ $user['lab_id'] ?? '' }}">
                            <td colspan="5">
                                <form action="{{ route('users.update', $user['id']) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 p-3">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $user['name'] }}" class="rounded-xl border-slate-200 text-sm" required>
                                    <input name="nrp_nip" value="{{ $user['nrp_nip'] }}" class="rounded-xl border-slate-200 text-sm" placeholder="NRP/NIP">
                                    <input type="email" name="email" value="{{ $user['email'] }}" class="rounded-xl border-slate-200 text-sm" required>
                                    <input type="password" name="password" class="rounded-xl border-slate-200 text-sm" placeholder="Password baru optional">
                                    <select name="role_id" class="rounded-xl border-slate-200 text-sm" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role['id'] }}" {{ $user['role_id'] == $role['id'] ? 'selected' : '' }}>{{ str_replace('_', ' ', $role['name']) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="lab_id" class="rounded-xl border-slate-200 text-sm">
                                        <option value="">Tidak terkait lab</option>
                                        @foreach($laboratories as $lab)
                                            <option value="{{ $lab['id'] }}" {{ ($user['lab_id'] ?? null) == $lab['id'] ? 'selected' : '' }}>{{ $lab['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <select name="status" class="rounded-xl border-slate-200 text-sm">
                                        <option value="active" {{ $user['status'] === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $user['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    <button class="rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-10">Belum ada user.</td></tr>
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