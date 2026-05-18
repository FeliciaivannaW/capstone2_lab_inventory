@extends('layouts.app')

@section('title', $isEdit ? 'Edit Draf Pengadaan' : 'Buat Draf Pengadaan')

@section('content')
    <div class="header">
        <h1>{{ $isEdit ? 'Edit Draf Pengadaan' : 'Buat Draf Pengadaan' }}</h1>
        <p>{{ $isEdit ? 'Perbarui informasi draf pengadaan' : 'Siapkan draf baru untuk tahun anggaran' }} {{ now()->year }}</p>
    </div>

    <x-form.container :action="$formAction" :method="$formMethod">
        <!-- Informasi Draf Section -->
        <x-form.section title="Informasi Draf" description="Isi data dasar untuk draf pengadaan">
            <x-form.field 
                name="title" 
                label="Judul Draf" 
                type="text"
                placeholder="Contoh: Pengadaan Lab Komputer Tahun 2024"
                value="{{ $draft['title'] ?? '' }}"
                required
            />

            @if($authUser['role'] === 'staf_administrasi')
                <x-form.field 
                    name="lab_id" 
                    label="Laboratorium" 
                    type="select"
                    :options="$laboratories"
                    value="{{ $draft['lab_id'] ?? '' }}"
                    required
                />
            @else
                <div style="padding: 10px; background: #f9f9f9; border-radius: 4px;">
                    <strong>Laboratorium:</strong> {{ $authUser['laboratory_name'] ?? 'Tidak ada' }}
                    <input type="hidden" name="lab_id" value="{{ $authUser['lab_id'] ?? '' }}" />
                </div>
            @endif

            <x-form.field 
                name="budget_year" 
                label="Tahun Anggaran" 
                type="number"
                value="{{ $draft['budget_year'] ?? now()->year }}"
                required
            />

            <x-form.field 
                name="notes" 
                label="Catatan (Opsional)" 
                type="textarea"
                placeholder="Catatan atau penjelasan tambahan untuk draf ini..."
                value="{{ $draft['notes'] ?? '' }}"
            />
        </x-form.section>

        <!-- Items Section -->
        @if($isEdit)
            <x-form.items-table 
                :items="$draft['items'] ?? []"
                :canEdit="!$draft['is_locked'] && $draft['status'] === 'draft'"
            />
        @endif

        <!-- Submit Actions -->
        <x-form.actions 
            submitLabel="{{ $isEdit ? 'Perbarui Draf' : 'Buat Draf' }}"
            cancelUrl="{{ route('procurement') }}"
            :showDelete="$isEdit"
            deleteUrl="{{ $isEdit ? route('procurement.destroy', $draft['id'] ?? '') : '' }}"
            deleteTitle="Hapus Draf Pengadaan?"
            deleteMessage="Tindakan ini tidak dapat dibatalkan. Semua data draft akan dihapus secara permanen."
        />
    </x-form.container>

    @if($isEdit && !$draft['is_locked'])
        <!-- Modal untuk Add Item -->
        <div id="addItemModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                <h3>Tambah Item Pengadaan</h3>
                
                <form id="addItemForm" onsubmit="handleAddItem(event)">
                    <div class="form-group">
                        <label for="itemName">Nama Barang *</label>
                        <input type="text" id="itemName" name="item_name" required placeholder="Nama barang yang akan dibeli" />
                    </div>

                    <div class="form-group small-2">
                        <div>
                            <label for="itemType">Tipe *</label>
                            <select id="itemType" name="item_type" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="inventory">Inventaris (Aset Tetap)</option>
                                <option value="bhp">BHP (Bahan Habis Pakai)</option>
                            </select>
                        </div>
                        <div>
                            <label for="quantity">Jumlah *</label>
                            <input type="number" id="quantity" name="quantity" min="1" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estimatedPrice">Harga Perkiraan (Rp) *</label>
                        <input type="number" id="estimatedPrice" name="estimated_price" min="0" step="0.01" required />
                    </div>

                    <div class="form-group">
                        <label for="purchaseLink">Link Pembelian</label>
                        <input type="url" id="purchaseLink" name="purchase_link" placeholder="https://..." />
                    </div>

                    <div style="text-align: right; margin-top: 20px;">
                        <button type="button" onclick="closeAddItemModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                            Batal
                        </button>
                        <button type="submit" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Tambah Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showAddItemModal() {
                document.getElementById('addItemModal').style.display = 'block';
            }

            function closeAddItemModal() {
                document.getElementById('addItemModal').style.display = 'none';
                document.getElementById('addItemForm').reset();
            }

            function handleAddItem(event) {
                event.preventDefault();
                const formData = new FormData(document.getElementById('addItemForm'));
                const draftId = {{ $draft['id'] ?? 0 }};

                fetch(`/api/procurement/${draftId}/items`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Item berhasil ditambahkan');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Gagal menambah item'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });

                closeAddItemModal();
            }

            function deleteItem(itemId) {
                if (!confirm('Yakin ingin menghapus item ini?')) return;

                const draftId = {{ $draft['id'] ?? 0 }};
                fetch(`/api/procurement/${draftId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Item berhasil dihapus');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Gagal menghapus item'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }

            // Close modal when clicking outside
            document.getElementById('addItemModal').addEventListener('click', function(e) {
                if (e.target === this) closeAddItemModal();
            });
        </script>
    @endif

    <style>
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group.small-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group.small-2 > div {
            display: flex;
            flex-direction: column;
        }
    </style>
@endsection
