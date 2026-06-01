<div class="mt-6 border-t border-slate-100 pt-6">
    <template x-if="items.length === 0">
        <div class="flex flex-col items-center justify-center py-10 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
            <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-medium text-slate-500">Belum ada item.</p>
            @if($canEdit)
                <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Item" untuk menambahkan.</p>
            @endif
        </div>
    </template>

    <template x-if="items.length > 0">
        <div class="overflow-y-auto max-h-96 rounded-xl border border-slate-200 shadow-inner">
            <table class="lv-table w-full relative">
                <thead class="sticky top-0 bg-slate-50 z-10 shadow-sm">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Harga Perkiraan</th>
                        <th>Link Pembelian</th>
                        <th>Status</th>
                        @if($canEdit)
                            <th class="text-center w-24">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.temp_id || item.id || index">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="text-center text-slate-400 font-mono text-xs" x-text="index + 1"></td>
                            <td>
                                <div class="font-semibold text-slate-800" x-text="item.item_name"></div>
                                <template x-if="item.item_description">
                                    <div class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="item.item_description" :title="item.item_description"></div>
                                </template>
                                <template x-if="item.replacement_asset_name || item.replacement_asset_code">
                                    <div class="text-[0.7rem] text-amber-700 mt-1 flex items-center gap-1 font-medium bg-amber-50/80 inline-flex px-1.5 py-0.5 rounded border border-amber-100/50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span x-text="'Ganti: ' + (item.replacement_asset_name || item.replacement_asset_code)"></span>
                                    </div>
                                </template>
                            </td>
                            <td class="text-slate-600 text-sm" x-text="item.item_type === 'inventory' ? 'Inventaris' : 'BHP'"></td>
                            <td class="text-slate-600 font-medium" x-text="item.quantity"></td>
                            <td class="text-slate-600 font-medium" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.estimated_price)"></td>
                            <td>
                                <template x-if="item.purchase_link">
                                    <a :href="item.purchase_link" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                        Lihat
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </template>
                                <template x-if="!item.purchase_link">
                                    <span class="text-slate-400">-</span>
                                </template>
                            </td>
                            <td>
                                <span class="badge" 
                                      :class="{
                                          'badge-pending': (item.review_status || 'pending') === 'pending',
                                          'badge-approved': item.review_status === 'approved',
                                          'badge-rejected': item.review_status === 'rejected'
                                      }"
                                      x-text="(item.review_status || 'pending').charAt(0).toUpperCase() + (item.review_status || 'pending').slice(1)">
                                </span>
                            </td>
                            @if($canEdit)
                                <td class="text-center">
                                    <template x-if="(item.review_status || 'pending') === 'pending'">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="editItem(index)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click="deleteItem(index)" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="(item.review_status || 'pending') === 'rejected'">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="deleteItem(index)" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Item (Ditolak - Tidak Bisa Diedit)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="(item.review_status || 'pending') === 'approved'">
                                        <span class="inline-flex items-center justify-center gap-1 text-xs font-medium text-slate-400" title="Item telah disetujui, tidak dapat diubah">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                        </span>
                                    </template>
                                </td>
                            @endif
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </template>
</div>
