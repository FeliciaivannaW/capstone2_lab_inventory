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
{{-- Alpine.js Component for Form State --}}
<div x-data="procurementForm({ 
    draft: {{ json_encode($draft ?? ['id' => 0, 'items' => []]) }} 
})">
    
    {{-- Main form --}}
    <x-form.container :action="$formAction" :method="$formMethod">
        
        {{-- Hidden input for items JSON --}}
        <input type="hidden" name="items_json" :value="JSON.stringify(items)">

        {{-- Section: Info Draf --}}
        <div class="glass-card rounded-2xl mb-5">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Informasi Draf</h2>
                <p class="text-xs text-slate-400 mt-0.5">Isi data dasar untuk draf pengadaan</p>
            </div>
            <div class="p-6 space-y-5">
                <x-form.field name="title" label="Judul Draf" type="text" placeholder="Contoh: Pengadaan Lab Komputer Tahun 2025" :value="$draft['title'] ?? ''" required />

                @if($authUser['role'] === 'staf_administrasi')
                    <x-form.field name="lab_id" label="Laboratorium" type="select" :options="$laboratories" :value="$draft['lab_id'] ?? ''" required />
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

                <x-form.field name="budget_year" label="Tahun Anggaran" type="number" :value="$draft['budget_year'] ?? now()->year" required />
                <x-form.field name="notes" label="Catatan (Opsional)" type="textarea" placeholder="Catatan atau penjelasan tambahan untuk draf ini..." :value="$draft['notes'] ?? ''" />
            </div>
        </div>

        {{-- Section: Items (edit mode only) --}}
        @if($isEdit)
            <div class="glass-card rounded-2xl mb-5">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Daftar Item Pengadaan</h2>
                        <p class="text-xs text-slate-400 mt-0.5" x-text="items.length + ' item'"></p>
                    </div>
                    @if(!($draft['is_locked'] ?? false) && ($draft['status'] ?? '') === 'draft')
                        <button type="button" @click="openItemModal()"
                                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Item
                        </button>
                    @endif
                </div>
                
                {{-- Alpine state drives this table now --}}
                <x-form.items-table :items="[]" :canEdit="!($draft['is_locked'] ?? false) && ($draft['status'] ?? '') === 'draft'" />
            </div>
        @endif

        {{-- Actions --}}
        <div class="glass-card rounded-2xl p-5 flex items-center justify-between gap-4">
            <button type="button" @click="requestCancel()"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Batal
            </button>
            <button type="submit" @click="isSubmitting = true"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $isEdit ? 'Lanjutkan (Review & Kirim)' : 'Lanjutkan (Tambah Item)' }}
            </button>
        </div>

    </x-form.container>

    {{-- Add/Edit Item Modal --}}
    @if($isEdit && !($draft['is_locked'] ?? false))
        <template x-teleport="body">
            <div x-show="itemModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-0">
                <!-- Backdrop -->
                <div x-show="itemModalOpen" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="itemModalOpen = false"></div>
                     
                <!-- Modal Content -->
                <div x-show="itemModalOpen" 
                     x-transition:enter="transition ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="transition ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg m-auto flex flex-col max-h-[90vh]">
                     
                    <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                        <h3 class="text-lg font-bold text-slate-900 mb-6" x-text="editIndex === null ? 'Tambah Item Pengadaan' : 'Edit Item Pengadaan'"></h3>

                        <form id="itemForm" @submit.prevent="saveItem()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                            <input type="text" x-model="activeItem.item_name" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" placeholder="Nama barang yang akan dibeli" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi / Spesifikasi</label>
                            <textarea x-model="activeItem.item_description" 
                                      x-init="$watch('itemModalOpen', val => { if(val) { $nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' }) } }); $watch('activeItem.item_description', val => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                      @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                      class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors resize-none overflow-hidden" 
                                      placeholder="Tuliskan spesifikasi lengkap atau detail..." rows="2"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                                <select x-model="activeItem.item_type" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" required>
                                    <option value="inventory">Inventaris (Aset Tetap)</option>
                                    <option value="bhp">BHP (Bahan Habis Pakai)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                                <input type="number" x-model.number="activeItem.quantity" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" min="1" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Perkiraan (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" x-model.number="activeItem.estimated_price" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" min="0" step="1000" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Link Pembelian</label>
                            <input type="url" x-model="activeItem.purchase_link" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors" placeholder="https://...">
                        </div>

                        </form>
                    </div>
                    
                    <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex items-center gap-3">
                        <button type="button" @click="itemModalOpen = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">Batal</button>
                        <button type="submit" form="itemForm" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
                            <span x-text="editIndex === null ? 'Tambah Item' : 'Simpan Perubahan'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- Cancel Confirmation Modal --}}
    <template x-teleport="body">
        <div x-show="cancelModalOpen" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-0">
            <div x-show="cancelModalOpen" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="cancelModalOpen = false"></div>
                 
            <div x-show="cancelModalOpen" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center m-auto">
                 
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                
                <h3 class="text-lg font-bold text-slate-900 mb-2">Buang Perubahan?</h3>
                <p class="text-sm text-slate-500 mb-6">Anda memiliki perubahan pada draf yang belum disimpan. Yakin ingin membuang perubahan ini dan kembali?</p>
                
                <div class="flex items-center gap-3">
                    <button type="button" @click="cancelModalOpen = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Tetap di Sini</button>
                    <a href="{{ route('procurement') }}" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-xl transition-colors">Buang & Kembali</a>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('procurementForm', ({ draft }) => ({
            items: [],
            initialItemsStr: '',
            initialFormDataStr: '',
            isSubmitting: false,
            
            // Modal states
            itemModalOpen: false,
            cancelModalOpen: false,
            
            // Edit states
            editIndex: null,
            activeItem: {
                item_name: '',
                item_description: '',
                item_type: 'inventory',
                quantity: 1,
                estimated_price: 0,
                purchase_link: ''
            },
            
            init() {
                // Initialize items from draft
                if (draft && draft.items) {
                    this.items = JSON.parse(JSON.stringify(draft.items));
                    this.initialItemsStr = JSON.stringify(this.items);
                }
                
                // Track initial form fields state
                this.$nextTick(() => {
                    const form = this.$root.querySelector('form');
                    if (form) {
                        const fd = new FormData(form);
                        fd.delete('items_json');
                        fd.delete('_token');
                        this.initialFormDataStr = new URLSearchParams(fd).toString();
                    }
                });
            },
            
            get isFormModified() {
                const form = this.$root.querySelector('form');
                if (!form) return false;
                
                const fd = new FormData(form);
                fd.delete('items_json');
                fd.delete('_token');
                
                return new URLSearchParams(fd).toString() !== this.initialFormDataStr;
            },

            get isItemsModified() {
                return JSON.stringify(this.items) !== this.initialItemsStr;
            },
            
            openItemModal(index = null) {
                if (index !== null) {
                    this.editIndex = index;
                    this.activeItem = JSON.parse(JSON.stringify(this.items[index]));
                } else {
                    this.editIndex = null;
                    this.activeItem = {
                        item_name: '',
                        item_description: '',
                        item_type: 'inventory',
                        quantity: 1,
                        estimated_price: 0,
                        purchase_link: ''
                    };
                }
                this.itemModalOpen = true;
            },
            
            editItem(index) {
                this.openItemModal(index);
            },
            
            deleteItem(index) {
                if (confirm('Yakin ingin menghapus item ini dari draf?')) {
                    this.items.splice(index, 1);
                }
            },
            
            saveItem() {
                // Basic validation
                if (!this.activeItem.item_name || !this.activeItem.quantity || !this.activeItem.estimated_price) {
                    alert('Mohon lengkapi data item');
                    return;
                }
                
                if (this.editIndex !== null) {
                    // Update existing
                    this.items[this.editIndex] = { 
                        ...this.items[this.editIndex], 
                        ...this.activeItem 
                    };
                } else {
                    // Add new
                    this.items.push({
                        ...this.activeItem,
                        temp_id: 'temp_' + Date.now(),
                        review_status: 'pending'
                    });
                }
                
                this.itemModalOpen = false;
            },
            
            requestCancel() {
                if (this.isItemsModified || this.isFormModified) {
                    this.cancelModalOpen = true;
                } else {
                    window.location.href = "{{ route('procurement') }}";
                }
            }
        }));
    });
</script>
@endpush
@endsection
