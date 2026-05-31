@extends('layouts.app')

@section('title', $isEdit ? 'Edit Draf Pengadaan' : 'Buat Draf Pengadaan')

@section('content')
@php
    $authUser = session('auth_user');
@endphp

{{-- Back --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('procurement') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar
    </a>
</div>

{{-- Step indicator --}}
<div class="flex items-center gap-0 mb-8">
    @php
        $steps = [
            ['label' => 'Info Dasar',    'desc' => 'Judul & tahun anggaran'],
            ['label' => 'Tambah Item',   'desc' => 'Daftar barang pengadaan'],
            ['label' => 'Review & Kirim','desc' => 'Finalisasi draf'],
        ];
        $currentStep = $isEdit ? 2 : 1;
    @endphp
    @foreach($steps as $i => $step)
        @php $sNum = $i + 1; $done = $sNum < $currentStep; $active = $sNum === $currentStep; @endphp
        <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                    {{ $done ? 'bg-indigo-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 'bg-slate-200 text-slate-500') }}">
                    @if($done)
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        {{ $sNum }}
                    @endif
                </div>
                <div class="hidden sm:block">
                    <p class="text-xs font-bold {{ $active ? 'text-slate-900' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                    <p class="text-[0.65rem] text-slate-400">{{ $step['desc'] }}</p>
                </div>
            </div>
            @if($i < count($steps) - 1)
                <div class="flex-1 h-px mx-4 {{ $done ? 'bg-indigo-300' : 'bg-slate-200' }}"></div>
            @endif
        </div>
    @endforeach
</div>

{{-- Main form --}}
<x-form.container :action="$formAction" :method="$formMethod">

    {{-- Section: Info Draf --}}
    <div class="glass-card rounded-2xl mb-5">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Informasi Draf</h2>
            <p class="text-xs text-slate-400 mt-0.5">Isi data dasar untuk draf pengadaan</p>
        </div>
        <div class="p-6 space-y-5">
            <x-form.field
                name="title"
                label="Judul Draf"
                type="text"
                placeholder="Contoh: Pengadaan Lab Komputer Tahun 2025"
                :value="$draft['title'] ?? ''"
                required
            />

            @if($authUser['role'] === 'staf_administrasi')
                <x-form.field
                    name="lab_id"
                    label="Laboratorium"
                    type="select"
                    :options="$laboratories"
                    :value="$draft['lab_id'] ?? ''"
                    required
                />
            @else
                <div class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-slate-400 font-medium">Laboratorium</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $authUser['laboratory_name'] ?? 'Tidak ada' }}</p>
                    </div>
                    <input type="hidden" name="lab_id" value="{{ $authUser['lab_id'] ?? '' }}">
                </div>
            @endif

            <x-form.field
                name="budget_year"
                label="Tahun Anggaran"
                type="number"
                :value="$draft['budget_year'] ?? now()->year"
                required
            />

            <x-form.field
                name="notes"
                label="Catatan (Opsional)"
                type="textarea"
                placeholder="Catatan atau penjelasan tambahan untuk draf ini..."
                :value="$draft['notes'] ?? ''"
            />
        </div>
    </div>

    {{-- Section: Items (edit mode only) --}}
    @if($isEdit)
        <div class="glass-card rounded-2xl mb-5">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Item Pengadaan</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ count($draft['items'] ?? []) }} item</p>
                </div>
                @if(!$draft['is_locked'] && $draft['status'] === 'draft')
                    <button type="button" onclick="openAddItemModal()"
                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Item
                    </button>
                @endif
            </div>
            <x-form.items-table
                :items="$draft['items'] ?? []"
                :canEdit="!$draft['is_locked'] && $draft['status'] === 'draft'"
            />
        </div>
    @endif

    {{-- Actions --}}
    <div class="glass-card rounded-2xl p-5 flex items-center justify-between gap-4">
        <a href="{{ route('procurement') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
            Batal
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $isEdit ? 'Perbarui Draf' : 'Buat Draf' }}
        </button>
    </div>

</x-form.container>

{{-- Add Item Modal --}}
@if($isEdit && !$draft['is_locked'])
@push('modals')
    <div x-data="{ open: false, loading: false }" id="addItemModalWrap">
        <div x-show="open" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-0">
            <!-- Backdrop -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
                 
            <!-- Modal Content -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                 
                <h3 class="text-lg font-bold text-slate-900 mb-6">Tambah Item Pengadaan</h3>

                <form id="addItemForm" @submit.prevent class="space-y-4">
                    <x-form.field name="item_name" label="Nama Barang" placeholder="Nama barang yang akan dibeli" required />
                    
                    <div class="grid grid-cols-2 gap-4">
                        <x-form.field name="item_type" label="Tipe" type="select" :options="['inventory' => 'Inventaris (Aset Tetap)', 'bhp' => 'BHP (Bahan Habis Pakai)']" required />
                        <x-form.field name="quantity" label="Jumlah" type="number" min="1" required />
                    </div>

                    <x-form.field name="estimated_price" label="Harga Perkiraan (Rp)" type="number" min="0" step="1000" required />
                    <x-form.field name="purchase_link" label="Link Pembelian" type="url" placeholder="https://..." />

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100 mt-6">
                        <button type="button" @click="open = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" :disabled="loading" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 rounded-xl transition-colors shadow-sm">
                            <span x-show="!loading">Tambah Item</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                Menambahkan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openAddItemModal() {
                document.getElementById('addItemModalWrap')._x_dataStack[0].open = true;
            }

            document.getElementById('addItemForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const wrap = document.getElementById('addItemModalWrap')._x_dataStack[0];
                wrap.loading = true;

                const formData = new FormData(this);
                const draftId = {{ $draft['id'] ?? 0 }};
                const payload = Object.fromEntries(formData);
                if (payload.purchase_link === '') {
                    delete payload.purchase_link;
                }

                try {
                    const res = await fetch(`/api/procurement/${draftId}/items`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.status === 'success') location.reload();
                    else {
                        let errorMsg = data.message || 'Gagal menambah item';
                        if (data.errors) {
                            errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                        }
                        alert('Error: ' + errorMsg);
                    }
                } catch(err) {
                    alert('Terjadi kesalahan');
                }
                wrap.loading = false;
                wrap.open = false;
            });

            async function deleteItem(itemId) {
                if (!confirm('Yakin ingin menghapus item ini?')) return;
                const draftId = {{ $draft['id'] ?? 0 }};
                try {
                    const res = await fetch(`/api/procurement/${draftId}/items/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                    const data = await res.json();
                    if (data.status === 'success') location.reload();
                    else alert('Error: ' + (data.message || 'Gagal'));
                } catch(err) {
                    alert('Terjadi kesalahan');
                }
            }
        </script>
    </div>
@endpush
@endif
@endsection
