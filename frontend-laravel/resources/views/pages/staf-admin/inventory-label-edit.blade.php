@extends('layouts.app')

@section('title', 'Edit Label Inventaris')

@section('content')

{{-- Back --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('staf-admin.inventory-label') }}"
       class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Daftar Label
    </a>
</div>

<div class="mb-7">
    <h1 class="text-2xl font-bold text-slate-900">Edit Label Inventaris</h1>
    <p class="text-sm text-slate-500 mt-1">Update nomor label dan foto QR/Barcode untuk aset
        <span class="font-semibold text-indigo-600">{{ $asset['asset_code'] }}</span>
    </p>
</div>

{{-- Error --}}
@if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Asset info --}}
    <div class="space-y-4">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Info Aset</p>
            <div class="space-y-3">
                @php
                    $infoRows = [
                        ['Kode Aset',  $asset['asset_code']],
                        ['Nama',       $asset['item_name']],
                        ['Kategori',   $asset['category_name'] ?? '—'],
                        ['Ruangan',    $asset['room_name'] ?? '—'],
                        ['Status',     ucfirst($asset['status'] ?? '—')],
                    ];
                    $condMap = ['baik'=>'badge-approved','rusak_ringan'=>'badge-pending','rusak_berat'=>'badge-rejected','maintenance'=>'badge-active','dihapus'=>'badge-rejected','diganti'=>'badge-draft'];
                @endphp
                @foreach($infoRows as [$label, $value])
                    <div>
                        <p class="text-[0.68rem] font-semibold text-slate-400 mb-0.5">{{ $label }}</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $value }}</p>
                    </div>
                @endforeach
                <div>
                    <p class="text-[0.68rem] font-semibold text-slate-400 mb-0.5">Kondisi</p>
                    <span class="badge {{ $condMap[$asset['asset_condition']] ?? 'badge-draft' }} text-xs">
                        {{ str_replace('_', ' ', ucfirst($asset['asset_condition'])) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Current QR photo --}}
        @if($asset['photo_url'] ?? null)
            <div class="glass-card rounded-2xl p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Foto QR Saat Ini</p>
                <img src="{{ $asset['photo_url'] }}" alt="QR Code"
                     class="w-full max-w-[160px] rounded-xl border border-slate-200 mx-auto block">
                <p class="text-center text-xs text-slate-400 mt-2">Scan to Verify</p>
            </div>
        @endif
    </div>

    {{-- Form --}}
    <div class="lg:col-span-2">
        <form method="POST" action="{{ route('staf-admin.inventory-label.update', $asset['id']) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="glass-card rounded-2xl p-6 space-y-5">

                {{-- Label number --}}
                <div>
                    <label for="label_number" class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">
                        Nomor Label <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="label_number" name="label_number"
                           value="{{ $asset['label_number'] ?? '' }}" required
                           placeholder="Contoh: LAB-PROG1-PC-001"
                           class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-mono">
                </div>

                {{-- QR upload --}}
                <div x-data="{ dragging: false, preview: null }">
                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2 block">
                        Foto QR / Barcode
                    </label>

                    <div
                        @dragover.prevent="dragging = true"
                        @dragleave="dragging = false"
                        @drop.prevent="
                            dragging = false;
                            const f = $event.dataTransfer.files[0];
                            if(f) { preview = URL.createObjectURL(f); $refs.qrInput.files = $event.dataTransfer.files; }
                        "
                        :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-slate-300 bg-slate-50 hover:border-indigo-300 hover:bg-slate-100'"
                        class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer"
                        @click="$refs.qrInput.click()">

                        <input type="file" x-ref="qrInput" name="qr_photo" accept="image/jpeg,image/png,image/webp"
                               class="sr-only"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                        <template x-if="!preview">
                            <div>
                                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center mx-auto mb-3 shadow-sm">
                                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 mb-1">Drag & drop foto atau klik untuk pilih</p>
                                <p class="text-xs text-slate-400">JPEG, PNG, WEBP · Maks 2MB</p>
                                <p class="text-[0.68rem] text-indigo-500 font-semibold mt-2">Scan to Verify</p>
                            </div>
                        </template>

                        <template x-if="preview">
                            <div>
                                <img :src="preview" class="w-32 h-32 object-cover rounded-xl mx-auto mb-3 border border-slate-200">
                                <p class="text-xs text-slate-500">Klik untuk ganti foto</p>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Kosongkan jika tidak ingin mengubah foto.</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('staf-admin.inventory-label') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
