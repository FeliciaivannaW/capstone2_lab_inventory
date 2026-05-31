<div class="mt-6 border-t border-slate-100 pt-6">
    @if(empty($items))
        <div class="flex flex-col items-center justify-center py-10 bg-slate-50 border border-dashed border-slate-200 rounded-xl">
            <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-medium text-slate-500">Belum ada item.</p>
            @if($canEdit)
                <p class="text-xs text-slate-400 mt-1">Klik tombol "Tambah Item" untuk menambahkan.</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="lv-table w-full">
                <thead>
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Harga Perkiraan</th>
                        <th>Link Pembelian</th>
                        <th>Status</th>
                        @if($canEdit)
                            <th class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td class="text-center text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="font-semibold text-slate-800">{{ $item['item_name'] }}</td>
                            <td class="text-slate-600 text-sm">{{ $item['item_type'] === 'inventory' ? 'Inventaris' : 'BHP' }}</td>
                            <td class="text-slate-600 font-medium">{{ $item['quantity'] }}</td>
                            <td class="text-slate-600 font-medium">Rp {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                            <td>
                                @if($item['purchase_link'])
                                    <a href="{{ $item['purchase_link'] }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                        Lihat
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($item['review_status'] ?? 'pending') {
                                        'pending' => 'badge-pending',
                                        'approved' => 'badge-approved',
                                        'rejected' => 'badge-rejected',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($item['review_status'] ?? 'pending') }}
                                </span>
                            </td>
                            @if($canEdit)
                                <td class="text-center">
                                    @if($item['review_status'] === 'pending')
                                        <button type="button" onclick="deleteItem({{ $item['id'] }})" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                            Terkunci
                                        </span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
