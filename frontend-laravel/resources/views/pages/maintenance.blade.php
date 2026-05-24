@extends('layouts.app')

@section('title', 'Log Maintenance')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Log Maintenance Inventaris</h1>
    <p class="text-sm text-slate-500 mt-1">Input maintenance, update kondisi aset, dan kurangi stok BHP otomatis saat dipakai.</p>
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
    $conditionClass = ['baik'=>'badge-approved','rusak_ringan'=>'badge-pending','rusak_berat'=>'badge-rejected','maintenance'=>'badge-active','dihapus'=>'badge-rejected','diganti'=>'badge-draft'];
    $statusClass = ['planned'=>'badge-draft','in_progress'=>'badge-active','done'=>'badge-approved','cancelled'=>'badge-rejected'];
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-sm font-bold text-slate-900 mb-4">Input Log Maintenance</h2>
        <form action="{{ route('maintenance.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-semibold text-slate-500">Aset Inventaris</label>
                <select name="inventory_asset_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                    <option value="">Pilih aset</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset['id'] }}">
                            {{ $asset['asset_code'] }} — {{ $asset['item_name'] ?? $asset['catalog_name'] ?? 'Aset' }} {{ $asset['label_number'] ? '(' . $asset['label_number'] . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Tanggal</label>
                    <input type="date" name="maintenance_date" value="{{ date('Y-m-d') }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Status</label>
                    <select name="status" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="done">Done</option>
                        <option value="planned">Planned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Masalah</label>
                <textarea name="issue_description" rows="2" class="w-full mt-1 rounded-xl border-slate-200 text-sm" placeholder="Contoh: keyboard tidak responsif"></textarea>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-500">Tindakan</label>
                <textarea name="action_taken" rows="2" class="w-full mt-1 rounded-xl border-slate-200 text-sm" placeholder="Contoh: ganti switch keyboard dan cleaning"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-500">Kondisi Akhir</label>
                    <select name="condition_after" class="w-full mt-1 rounded-xl border-slate-200 text-sm" required>
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak ringan</option>
                        <option value="rusak_berat">Rusak berat</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="dihapus">Dihapus</option>
                        <option value="diganti">Diganti</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">Biaya</label>
                    <input type="number" min="0" name="cost" value="0" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div class="rounded-xl bg-amber-50 border border-amber-200 p-3">
                <p class="text-xs font-bold text-amber-800 mb-2">BHP yang dipakai</p>
                @for($i = 0; $i < 3; $i++)
                    <div class="grid grid-cols-3 gap-2 mb-2 last:mb-0">
                        <select name="bhp_stock_id[]" class="col-span-2 rounded-xl border-amber-200 text-xs">
                            <option value="">Tidak pakai BHP</option>
                            @foreach($stocks as $stock)
                                <option value="{{ $stock['id'] }}">{{ $stock['item_name'] }} — stok {{ $stock['current_stock'] }} {{ $stock['unit'] }}</option>
                            @endforeach
                        </select>
                        <input type="number" min="1" name="bhp_quantity[]" class="rounded-xl border-amber-200 text-xs" placeholder="Qty">
                    </div>
                @endfor
                <p class="text-[0.68rem] text-amber-700 mt-2">Saat form disimpan, stok BHP otomatis berkurang sesuai qty.</p>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500">Catatan</label>
                <textarea name="notes" rows="2" class="w-full mt-1 rounded-xl border-slate-200 text-sm"></textarea>
            </div>
            <button class="w-full rounded-xl bg-indigo-600 text-white text-sm font-semibold py-2.5 hover:bg-indigo-700">Simpan Maintenance</button>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden xl:col-span-2">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800">Riwayat Maintenance</p>
                <p class="text-xs text-slate-400">{{ count($logs) }} log maintenance</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Cari aset/masalah" class="rounded-xl border-slate-200 text-sm">
                <select name="status" class="rounded-xl border-slate-200 text-sm">
                    <option value="">Semua</option>
                    <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button class="rounded-xl bg-slate-900 text-white text-sm font-semibold px-4">Filter</button>
            </form>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-mono text-xs font-bold bg-slate-100 px-2 py-0.5 rounded-md">{{ $log['asset_code'] }}</span>
                                <span class="text-sm font-bold text-slate-800">{{ $log['item_name'] }}</span>
                                <span class="badge {{ $statusClass[$log['status']] ?? 'badge-draft' }} text-xs">{{ str_replace('_', ' ', $log['status']) }}</span>
                                <span class="badge {{ $conditionClass[$log['condition_after']] ?? 'badge-draft' }} text-xs">{{ str_replace('_', ' ', $log['condition_after']) }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mb-2">{{ $log['maintenance_date'] }} · {{ $log['performed_by_name'] }} · {{ $log['room_name'] ?? 'tanpa ruangan' }}</p>
                            @if($log['issue_description'])
                                <p class="text-sm text-slate-600"><span class="font-semibold">Masalah:</span> {{ $log['issue_description'] }}</p>
                            @endif
                            @if($log['action_taken'])
                                <p class="text-sm text-slate-600"><span class="font-semibold">Tindakan:</span> {{ $log['action_taken'] }}</p>
                            @endif
                            @if(!empty($log['bhp_usages']))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($log['bhp_usages'] as $usage)
                                        <span class="inline-flex items-center px-2 py-0.5 text-[0.68rem] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
                                            {{ $usage['item_name'] }} -{{ $usage['quantity'] }} {{ $usage['unit'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500 lg:text-right whitespace-nowrap">
                            Biaya<br>
                            <span class="text-sm font-bold text-slate-900">Rp {{ number_format($log['cost'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-sm text-slate-400">Belum ada log maintenance.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection