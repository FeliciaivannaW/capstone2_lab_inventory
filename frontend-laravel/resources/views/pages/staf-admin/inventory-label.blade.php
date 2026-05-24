@extends('layouts.app')

@section('title', 'Update Label & Foto')

@section('content')

@include('components.staf-admin.workflow-strip', ['active' => 'label'])

<div x-data="labelDrawerApp()">

    <div class="mb-6 flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Update Label & Foto QR</h1>
            <p class="text-sm text-slate-500 mt-1">
                Langkah terakhir: berikan nomor label dan unggah foto QR/Barcode untuk aset yang sudah diterima.
            </p>
        </div>
        @php
            $totalAssets = count($assets ?? []);
            $labeledCount = collect($assets ?? [])->filter(fn($a) => !empty($a['label_number']))->count();
            $unlabeledCount = $totalAssets - $labeledCount;
        @endphp
        <div class="flex gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                {{ $labeledCount }} sudah label
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                {{ $unlabeledCount }} belum label
            </span>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('staf-admin.inventory-label') }}"
          class="glass-card rounded-2xl px-5 py-4 mb-5 flex flex-wrap items-end gap-4">
        <div class="flex-[2] min-w-[180px]">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Cari Aset</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode, label, nama…"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status Label</label>
            <select name="label_status"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                <option value="">Semua</option>
                <option value="labeled"   {{ ($filters['label_status'] ?? '') == 'labeled'   ? 'selected' : '' }}>Sudah Label</option>
                <option value="unlabeled" {{ ($filters['label_status'] ?? '') == 'unlabeled' ? 'selected' : '' }}>Belum Label</option>
            </select>
        </div>
        <div class="flex-1 min-w-[140px]">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Status</label>
            <select name="status"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                <option value="">Semua</option>
                <option value="available"   {{ ($filters['status'] ?? '') == 'available'   ? 'selected' : '' }}>Available</option>
                <option value="in_use"      {{ ($filters['status'] ?? '') == 'in_use'      ? 'selected' : '' }}>In Use</option>
                <option value="maintenance" {{ ($filters['status'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-wider mb-1.5 block">Asal Draf</label>
            <select name="source_draft"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                <option value="">Semua Draf</option>
                @foreach($draftOptions ?? [] as $d)
                    <option value="{{ $d['id'] }}" {{ ($filters['source_draft'] ?? '') == $d['id'] ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Str::limit($d['title'], 40) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
            <a href="{{ route('staf-admin.inventory-label') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-sm font-semibold text-slate-700">{{ count($assets ?? []) }} aset ditemukan</p>
        </div>

        @if(empty($assets))
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-sm font-medium text-slate-400">Belum ada data aset inventaris</p>
                <p class="text-xs text-slate-400 mt-1">Data aset muncul setelah barang diterima dan dicatat sistem.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="lv-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Aset</th>
                            <th>Nama</th>
                            <th>Asal Draf</th>
                            <th>Ruangan</th>
                            <th>Label</th>
                            <th>QR/Foto</th>
                            <th>Kondisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $i => $asset)
                            @php
                                $condMap = [
                                    'baik'         => 'badge-approved',
                                    'rusak_ringan' => 'badge-pending',
                                    'rusak_berat'  => 'badge-rejected',
                                    'maintenance'  => 'badge-active',
                                    'dihapus'      => 'badge-rejected',
                                    'diganti'      => 'badge-active',
                                ];
                                $condClass = $condMap[$asset['asset_condition']] ?? 'badge-draft';
                                $assetJson = htmlspecialchars(json_encode([
                                    'id'             => $asset['id'],
                                    'asset_code'     => $asset['asset_code'] ?? '',
                                    'item_name'      => $asset['item_name'] ?? '',
                                    'category_name'  => $asset['category_name'] ?? '—',
                                    'room_name'      => $asset['room_name'] ?? '—',
                                    'label_number'   => $asset['label_number'] ?? '',
                                    'photo_url'      => $asset['photo_url'] ?? '',
                                    'asset_condition'=> $asset['asset_condition'] ?? 'baik',
                                ]), ENT_QUOTES, 'UTF-8');
                            @endphp
                            <tr>
                                <td class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                                <td>
                                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $asset['asset_code'] }}
                                    </span>
                                </td>
                                <td class="font-semibold text-slate-800">
                                    {{ $asset['item_name'] }}
                                    @if($asset['category_name'] ?? null)
                                        <p class="text-[0.65rem] text-slate-400 font-normal">{{ $asset['category_name'] }}</p>
                                    @endif
                                </td>
                                <td>
                                    @if($asset['source_draft'] ?? null)
                                        <a href="{{ route('staf-admin.procurement-approved.detail', $asset['source_draft']['id']) }}"
                                           class="inline-flex items-center gap-1 text-xs text-violet-600 hover:text-violet-800 hover:underline font-semibold">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ \Illuminate\Support\Str::limit($asset['source_draft']['title'], 24) }}
                                        </a>
                                    @else
                                        <span class="text-slate-300 text-xs italic">—</span>
                                    @endif
                                </td>
                                <td class="text-slate-500 text-xs">{{ $asset['room_name'] ?? '—' }}</td>
                                <td>
                                    @if($asset['label_number'])
                                        <span class="badge badge-approved text-xs">{{ $asset['label_number'] }}</span>
                                    @else
                                        <span class="badge badge-pending text-xs">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asset['photo_url'] ?? null)
                                        <img src="{{ $asset['photo_url'] }}" alt="QR"
                                             class="w-10 h-10 object-cover rounded-lg border border-slate-200 cursor-pointer hover:scale-110 transition-transform">
                                    @else
                                        <div class="w-10 h-10 rounded-lg border-2 border-dashed border-slate-200 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $condClass }} text-xs">
                                        {{ str_replace('_', ' ', ucfirst($asset['asset_condition'])) }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button"
                                            onclick='openDrawer(@json($asset))'
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg border border-amber-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Update
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ───── SIDE DRAWER ───── --}}
    {{-- Backdrop --}}
    <div x-show="drawerOpen" x-transition.opacity
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40"
         @click="closeDrawer()"
         style="display:none;"></div>

    {{-- Drawer panel --}}
    <aside x-show="drawerOpen"
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed right-0 top-0 h-screen w-full sm:w-[460px] bg-white shadow-2xl z-50 flex flex-col"
           style="display:none;">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-3 flex-shrink-0">
            <div class="min-w-0">
                <p class="text-[0.68rem] font-bold text-amber-600 uppercase tracking-wider">Update Label & QR</p>
                <h2 class="text-lg font-bold text-slate-900 truncate mt-0.5" x-text="asset.item_name"></h2>
                <p class="text-xs text-slate-500 mt-1">
                    <span class="font-mono font-bold text-slate-700" x-text="asset.asset_code"></span>
                    · <span x-text="asset.room_name"></span>
                </p>
            </div>
            <button @click="closeDrawer()"
                    class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg p-1.5 transition-colors flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <form :action="formAction" method="POST" enctype="multipart/form-data"
              class="flex-1 overflow-y-auto px-6 py-5 space-y-5"
              @submit="loading = true">
            @csrf
            @method('PUT')

            {{-- Asset info chip --}}
            <div class="bg-slate-50 rounded-xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500">Kategori</p>
                    <p class="text-sm font-semibold text-slate-800 truncate" x-text="asset.category_name"></p>
                </div>
                <div>
                    <span class="badge badge-active text-xs" x-text="(asset.asset_condition || '').replace('_',' ')"></span>
                </div>
            </div>

            {{-- Label input --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                    Nomor Label <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 font-mono text-sm">#</span>
                    <input type="text" name="label_number" x-model="form.label_number" required
                           placeholder="contoh: LAB-COMNET-001"
                           class="w-full pl-8 pr-3 py-2.5 text-sm font-mono border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                </div>
                <p class="text-[0.65rem] text-slate-400 mt-1.5">
                    Format anjuran: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded">LAB-[KODE]-[NO]</code>
                </p>
            </div>

            {{-- Drag-drop upload --}}
            <div>
                <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5 block">
                    Foto QR / Barcode
                </label>
                <div
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="handleDrop($event)"
                    @click="$refs.fileInput.click()"
                    :class="dragging ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 bg-slate-50'"
                    class="relative border-2 border-dashed rounded-xl p-5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/40 transition-all">

                    {{-- Preview --}}
                    <template x-if="previewUrl">
                        <div class="space-y-3">
                            <img :src="previewUrl" class="mx-auto max-h-44 rounded-lg shadow-md border border-slate-200">
                            <div class="flex items-center justify-center gap-2">
                                <span class="inline-flex items-center gap-1 text-[0.65rem] font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Scan to Verify
                                </span>
                                <button type="button" @click.stop="clearPreview()"
                                        class="text-[0.65rem] font-semibold text-red-500 hover:text-red-700">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Placeholder --}}
                    <template x-if="!previewUrl">
                        <div class="text-center py-3">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-700">Upload foto QR/Barcode</p>
                            <p class="text-xs text-slate-400 mt-0.5">atau drag & drop di sini</p>
                            <p class="text-[0.6rem] text-slate-400 mt-2">JPG, PNG, WEBP · max 2MB</p>
                        </div>
                    </template>

                    <input type="file" name="qr_photo" accept="image/*" x-ref="fileInput"
                           class="hidden" @change="handleFile($event.target.files[0])">
                </div>
            </div>

            {{-- Existing photo (if any) --}}
            <template x-if="asset.photo_url && !previewUrl">
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center gap-3">
                    <img :src="asset.photo_url" class="w-12 h-12 rounded-lg object-cover border border-emerald-300">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-emerald-700">Foto saat ini tersimpan</p>
                        <p class="text-[0.65rem] text-emerald-600 mt-0.5">Upload baru akan menggantikan foto ini.</p>
                    </div>
                </div>
            </template>

            <div class="h-2"></div>
        </form>

        {{-- Footer actions --}}
        <div class="px-6 py-4 border-t border-slate-100 flex gap-3 flex-shrink-0 bg-slate-50/60">
            <button @click="closeDrawer()" type="button"
                    class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 transition-colors">
                Batal
            </button>
            <button @click="submitForm()" type="button" :disabled="loading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl transition-colors inline-flex items-center justify-center gap-2 shadow-sm">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="loading ? 'Menyimpan…' : 'Simpan Perubahan'"></span>
            </button>
        </div>
    </aside>

</div>

@push('scripts')
<script>
function labelDrawerApp() {
    return {
        drawerOpen: false,
        loading: false,
        dragging: false,
        previewUrl: null,
        formAction: '',
        asset: {
            id: null,
            asset_code: '',
            item_name: '',
            category_name: '',
            room_name: '',
            label_number: '',
            photo_url: '',
            asset_condition: 'baik',
        },
        form: {
            label_number: '',
        },

        openDrawer(asset) {
            this.asset = {
                id:              asset.id,
                asset_code:      asset.asset_code || '',
                item_name:       asset.item_name || '',
                category_name:   asset.category_name || '—',
                room_name:       asset.room_name || '—',
                label_number:    asset.label_number || '',
                photo_url:       asset.photo_url || '',
                asset_condition: asset.asset_condition || 'baik',
            };
            this.form.label_number = this.asset.label_number;
            this.previewUrl = null;
            this.formAction = `{{ url('staf-admin/inventory-label') }}/${asset.id}`;
            this.drawerOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.previewUrl = null;
            this.dragging = false;
            document.body.style.overflow = '';
        },

        handleDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files[0];
            if (file) {
                this.handleFile(file);
                // Sync ke input
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.fileInput.files = dt.files;
            }
        },

        handleFile(file) {
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB');
                return;
            }
            this.previewUrl = URL.createObjectURL(file);
        },

        clearPreview() {
            this.previewUrl = null;
            this.$refs.fileInput.value = '';
        },

        submitForm() {
            if (!this.form.label_number) {
                alert('Nomor label wajib diisi');
                return;
            }
            this.loading = true;
            // Trigger native form submission
            const form = this.$el.querySelector('form');
            form.submit();
        }
    }
}

// Bridge untuk vanilla onclick → Alpine
function openDrawer(asset) {
    const root = document.querySelector('[x-data="labelDrawerApp()"]');
    if (root && root._x_dataStack) {
        root._x_dataStack[0].openDrawer(asset);
    }
}
</script>
@endpush
@endsection
