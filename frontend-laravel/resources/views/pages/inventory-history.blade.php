@extends('layouts.app')

@section('title', 'History Kondisi Aset')

@section('content')
<div x-data="{ 
    activeModal: false, 
    activeLog: null,
    formatCondition(cond) {
        const labels = {
            'baik': 'Baik',
            'rusak_ringan': 'Rusak Ringan',
            'rusak_berat': 'Rusak Berat',
            'maintenance': 'Maintenance',
            'dihapus': 'Dihapus',
            'diganti': 'Diganti'
        };
        return labels[cond] || (cond ? cond.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '-');
    },
    condClass(cond) {
        const classes = {
            'baik': 'badge-approved',
            'rusak_ringan': 'badge-pending',
            'rusak_berat': 'badge-rejected',
            'maintenance': 'badge-active',
            'dihapus': 'badge-rejected',
            'diganti': 'badge-draft'
        };
        return classes[cond] || 'badge-draft';
    },
    formatDate(str) {
        if (!str) return '-';
        const d = new Date(str);
        const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' };
        return d.toLocaleDateString('id-ID', options).replace(/\./g, ':');
    }
}">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">History Kondisi Aset</h1>
        <p class="text-sm text-slate-500 mt-1">
            Riwayat perubahan kondisi aset, termasuk aset yang dihapus atau diganti.
        </p>
    </div>

    @php
        $history = $history ?? [];

        $conditionLabels = [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            'maintenance' => 'Maintenance',
            'dihapus' => 'Dihapus',
            'diganti' => 'Diganti',
        ];

        $conditionClasses = [
            'baik' => 'badge-approved',
            'rusak_ringan' => 'badge-pending',
            'rusak_berat' => 'badge-rejected',
            'maintenance' => 'badge-active',
            'dihapus' => 'badge-rejected',
            'diganti' => 'badge-draft',
        ];

        $formatCondition = function ($condition) use ($conditionLabels) {
            return $conditionLabels[$condition] ?? ucfirst(str_replace('_', ' ', $condition ?? '-'));
        };
    @endphp

    <div class="glass-card rounded-2xl p-5 mb-6">
        <form method="GET" action="{{ route('inventory.history') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-7">
                <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">
                    Cari History
                </label>
                <div class="relative">
                    <input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Kode aset, label, nama barang, catatan..."
                        class="w-full rounded-xl border-slate-200 text-sm pr-9"
                    >

                    @if(request()->filled('search'))
                        <a
                            href="{{ route('inventory.history', request()->except('search')) }}"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg leading-none"
                            title="Hapus pencarian"
                        >
                            ×
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">
                    Kondisi Baru
                </label>
                <select
                    name="condition"
                    class="w-full rounded-xl border-slate-200 text-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('condition') === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ request('condition') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('condition') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="maintenance" {{ request('condition') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="dihapus" {{ request('condition') === 'dihapus' ? 'selected' : '' }}>Dihapus</option>
                    <option value="diganti" {{ request('condition') === 'diganti' ? 'selected' : '' }}>Diganti</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex gap-2">
                <button class="flex-1 rounded-xl bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 hover:bg-indigo-700">
                    Filter
                </button>

                <a href="{{ route('inventory.history') }}"
                   class="rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold px-4 py-2.5 hover:bg-slate-200 text-center flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-sm font-bold text-slate-800">{{ count($history) }} history ditemukan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="lv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aset</th>
                        <th>Perubahan Kondisi</th>
                        <th>Diubah Oleh</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($history as $index => $row)
                        @php
                            $oldCondition = $row['old_condition'] ?? '-';
                            $newCondition = $row['new_condition'] ?? '-';

                            $oldClass = $conditionClasses[$oldCondition] ?? 'badge-draft';
                            $newClass = $conditionClasses[$newCondition] ?? 'badge-draft';
                        @endphp

                        <tr @click="activeLog = {{ json_encode($row) }}; activeModal = true" class="cursor-pointer hover:bg-slate-50 transition-colors">
                            <td class="text-slate-500">{{ $index + 1 }}</td>

                            <td>
                                <div class="font-mono text-xs font-bold bg-slate-100 px-2 py-1 rounded-md inline-block mb-1">
                                    {{ $row['asset_code'] ?? '-' }}
                                </div>

                                <div class="font-semibold text-slate-800">
                                    {{ $row['item_name'] ?? '-' }}
                                </div>
                            </td>

                            <td>
                                @if($oldCondition === $newCondition)
                                    <div class="flex items-center gap-2">
                                        <span class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">Tercatat</span>
                                        <span class="badge {{ $newClass }} text-xs">
                                            {{ $formatCondition($newCondition) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="badge {{ $oldClass }} text-xs">
                                            {{ $formatCondition($oldCondition) }}
                                        </span>
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        <span class="badge {{ $newClass }} text-xs">
                                            {{ $formatCondition($newCondition) }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="text-slate-600">
                                {{ $row['updated_by_name'] ?? '-' }}
                            </td>

                            <td class="text-slate-500">
                                @if(!empty($row['updated_at']))
                                    {{ \Carbon\Carbon::parse($row['updated_at'])->format('d M Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-10">
                                Belum ada history kondisi aset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail -->
    <template x-teleport="body">
        <div x-show="activeModal" 
             class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden" @click.away="activeModal = false">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Detail History Kondisi
                    </h3>
                    <button @click="activeModal = false" class="text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <template x-if="activeLog">
                        <div class="space-y-6">
                            <!-- Info Aset -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-400 uppercase mb-1.5">Aset</p>
                                        <p class="font-bold text-slate-800 text-sm leading-snug mb-1.5" x-text="activeLog.item_name || '-'"></p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-[0.7rem] font-bold bg-white px-2 py-1 rounded-md border border-slate-200 whitespace-nowrap" x-text="activeLog.asset_code || '-'"></span>
                                            <template x-if="activeLog.label_number">
                                                <span class="font-mono text-[0.7rem] text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 whitespace-nowrap" x-text="activeLog.label_number"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Ruangan</p>
                                        <template x-if="activeLog.room_name">
                                            <div>
                                                <p class="font-semibold text-slate-800" x-text="activeLog.room_name"></p>
                                                <p class="text-xs text-slate-500 font-mono" x-text="activeLog.room_code || '-'"></p>
                                            </div>
                                        </template>
                                        <template x-if="!activeLog.room_name">
                                            <p class="text-sm italic text-slate-400">Belum ada ruangan</p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Perubahan Kondisi -->
                            <template x-if="activeLog.old_condition !== activeLog.new_condition">
                                <div class="grid grid-cols-[1fr_auto_1fr] gap-4 items-center">
                                    <div class="border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center text-center relative overflow-hidden bg-white">
                                        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-transparent opacity-50"></div>
                                        <p class="text-xs font-bold text-slate-400 uppercase mb-2 relative z-10">Kondisi Lama</p>
                                        <span :class="['badge relative z-10', condClass(activeLog.old_condition)]" x-text="formatCondition(activeLog.old_condition)"></span>
                                    </div>
                                    
                                    <div class="flex items-center justify-center z-20 relative">
                                        <div class="bg-white rounded-full w-10 h-10 shadow-sm border border-slate-100 text-slate-400 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center text-center relative overflow-hidden bg-white">
                                        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-transparent opacity-50"></div>
                                        <p class="text-xs font-bold text-slate-400 uppercase mb-2 relative z-10">Kondisi Baru</p>
                                        <span :class="['badge relative z-10', condClass(activeLog.new_condition)]" x-text="formatCondition(activeLog.new_condition)"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- Kondisi Tetap -->
                            <template x-if="activeLog.old_condition === activeLog.new_condition">
                                <div class="border border-slate-100 rounded-xl p-4 flex flex-col items-center justify-center text-center relative overflow-hidden bg-white">
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-transparent opacity-50"></div>
                                    <p class="text-xs font-bold text-slate-400 uppercase mb-2 relative z-10">Kondisi Tercatat</p>
                                    <span :class="['badge relative z-10', condClass(activeLog.new_condition)]" x-text="formatCondition(activeLog.new_condition)"></span>
                                </div>
                            </template>

                            <!-- Info Lanjutan -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Diubah Oleh</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                            <span x-text="(activeLog.updated_by_name || '?').charAt(0).toUpperCase()"></span>
                                        </div>
                                        <p class="font-medium text-slate-800" x-text="activeLog.updated_by_name || '-'"></p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Waktu Perubahan</p>
                                    <p class="font-medium text-slate-800 mt-1" x-text="formatDate(activeLog.updated_at)"></p>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase mb-1">Catatan Perubahan</p>
                                <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-600 italic border border-slate-100 whitespace-pre-line" x-text="activeLog.note || 'Tidak ada catatan'"></div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button @click="activeModal = false" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-300 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection