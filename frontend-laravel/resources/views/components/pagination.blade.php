@props(['total'])

<div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
    <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500">Tampilkan</span>
        <select x-model="perPage" @change="currentPage = 1" class="rounded-xl border-slate-200 text-xs px-2.5 py-1 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
        </select>
        <span class="text-xs text-slate-500">data per halaman</span>
    </div>
    
    <div class="text-xs text-slate-500">
        Menampilkan <span class="font-semibold text-slate-700" x-text="totalItems === 0 ? 0 : Math.min((currentPage - 1) * perPage + 1, totalItems)"></span>
        sampai <span class="font-semibold text-slate-700" x-text="Math.min(currentPage * perPage, totalItems)"></span>
        dari <span class="font-semibold text-slate-700" x-text="totalItems"></span> data
    </div>
    
    <div class="flex items-center gap-1.5" x-show="totalPages > 1" x-cloak>
        <button type="button" @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Sebelumnya
        </button>
        
        <template x-for="page in getPages()" :key="page">
            <button type="button" @click="currentPage = page" :class="currentPage === page ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'" class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors">
                <span x-text="page"></span>
            </button>
        </template>
        
        <button type="button" @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Berikutnya
        </button>
    </div>
</div>
