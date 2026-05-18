@extends('layouts.app')

@section('title', 'Detail Pengadaan')

@section('content')
    <div class="header">
        <h1>{{ $draft['title'] }}</h1>
        <p>Tahun Anggaran: <strong>{{ $draft['budget_year'] }}</strong> | Lab: <strong>{{ $draft['lab_name'] }}</strong></p>
    </div>

    <div class="section">
        <h3>Informasi Draf</h3>
        <div class="info-grid">
            <div class="info-item">
                <label>Status:</label>
                <span class="badge badge-{{ 
                    $draft['status'] === 'draft' ? 'secondary' : 
                    ($draft['status'] === 'submitted' ? 'info' : 
                    ($draft['status'] === 'finalized' ? 'success' : 'danger'))
                }}">
                    {{ ucfirst($draft['status']) }}
                </span>
            </div>
            <div class="info-item">
                <label>Terkunci:</label>
                <span>{{ $draft['is_locked'] ? '🔒 Ya' : '🔓 Tidak' }}</span>
            </div>
            <div class="info-item">
                <label>Dibuat oleh:</label>
                <span>{{ $draft['created_by_name'] }} ({{ date('d/m/Y H:i', strtotime($draft['created_at'])) }})</span>
            </div>
            @if($draft['finalized_by_name'])
                <div class="info-item">
                    <label>Difinalisasi oleh:</label>
                    <span>{{ $draft['finalized_by_name'] }} ({{ date('d/m/Y H:i', strtotime($draft['finalized_at'])) }})</span>
                </div>
            @endif
        </div>

        @if($draft['notes'])
            <div class="info-item">
                <label>Catatan:</label>
                <p>{{ $draft['notes'] }}</p>
            </div>
        @endif
    </div>

    <div class="section">
        <h3>Daftar Item Pengadaan</h3>

        @if(empty($draft['items']))
            <div class="empty">
                Tidak ada item dalam draf ini.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Harga Perkiraan</th>
                        <th>Status Review</th>
                        <th>Direview oleh</th>
                        <th>Tanggal Review</th>
                        <th>Catatan Reviewer</th>
                        <th>Link Pembelian</th>
                        <th>Barang Pengganti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($draft['items'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item['item_name'] }}</strong></td>
                            <td>{{ ucfirst($item['item_type']) }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>Rp {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ 
                                    $item['review_status'] === 'pending' ? 'warning' : 
                                    ($item['review_status'] === 'approved' ? 'success' : 'danger')
                                }}">
                                    {{ ucfirst($item['review_status']) }}
                                </span>
                            </td>
                            <td>{{ $item['reviewed_by_name'] ?? '-' }}</td>
                            <td>{{ $item['reviewed_at'] ? date('d/m/Y H:i', strtotime($item['reviewed_at'])) : '-' }}</td>
                            <td>{{ $item['review_note'] ?? '-' }}</td>
                            <td>
                                @if($item['purchase_link'])
                                    <a href="{{ $item['purchase_link'] }}" target="_blank" class="btn-link">Lihat</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $item['replacement_asset_code'] ?? '-' }}</td>
                            @if(!$draft['is_locked'] && $draft['status'] !== 'finalized' && $item['review_status'] === 'pending')
                                <td>
                                    <button onclick="showReviewModal({{ $item['id'] }}, '{{ addslashes($item['item_name']) }}')" class="btn-link" style="color: blue;">Review</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!$draft['is_locked'] && $draft['status'] !== 'finalized')
                <div class="section-actions" style="margin-top: 20px;">
                    <h4>Aksi Review Item</h4>
                    <p><em>Gunakan tombol "Review" pada kolom aksi untuk menyetujui atau menolak item pengadaan.</em></p>
                </div>
            @endif
        @endif
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
            <h3>Review Item Pengadaan</h3>
            <p id="itemNameDisplay"></p>
            
            <form id="reviewForm" onsubmit="handleReview(event)">
                <div style="margin-bottom: 15px;">
                    <label for="reviewStatus" style="display: block; font-weight: bold; margin-bottom: 5px;">Status Review:</label>
                    <select id="reviewStatus" name="review_status" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="">-- Pilih Status --</option>
                        <option value="approved">✓ Setujui</option>
                        <option value="rejected">✗ Tolak</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="reviewNote" style="display: block; font-weight: bold; margin-bottom: 5px;">Catatan (opsional):</label>
                    <textarea id="reviewNote" name="review_note" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-height: 80px;"></textarea>
                </div>

                <div style="text-align: right;">
                    <button type="button" onclick="closeReviewModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Batal</button>
                    <button type="submit" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Simpan Review</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentItemId = null;
        let currentDraftId = {{ $draft['id'] }};

        function showReviewModal(itemId, itemName) {
            currentItemId = itemId;
            document.getElementById('itemNameDisplay').textContent = 'Barang: ' + itemName;
            document.getElementById('reviewModal').style.display = 'block';
            document.getElementById('reviewStatus').value = '';
            document.getElementById('reviewNote').value = '';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
            currentItemId = null;
        }

        function handleReview(event) {
            event.preventDefault();

            const reviewStatus = document.getElementById('reviewStatus').value;
            const reviewNote = document.getElementById('reviewNote').value;

            if (!reviewStatus) {
                alert('Pilih status review terlebih dahulu');
                return;
            }

            // Send review to backend
            fetch('/api/procurement/drafts/' + currentDraftId + '/items/' + currentItemId + '/review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    review_status: reviewStatus,
                    review_note: reviewNote
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Review berhasil disimpan');
                    // Reload halaman untuk update data
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Gagal menyimpan review'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim review');
            });

            closeReviewModal();
        }

        // Close modal when clicking outside
        document.getElementById('reviewModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeReviewModal();
            }
        });
    </script>

    <div class="section">
        <div class="section-actions">
            @if(!$draft['is_locked'] && $draft['status'] !== 'finalized')
                <button class="btn btn-primary" onclick="handleFinalize()">
                    Finalisasi Draf
                </button>
            @else
                <span class="badge badge-secondary">Draf sudah terkunci</span>
            @endif
            <a href="{{ route('procurement') }}" class="btn btn-secondary">Kembali ke Riwayat</a>
        </div>
    </div>

    <!-- Finalize Confirmation Modal -->
    <div id="finalizeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
            <h3>Finalisasi Draf Pengadaan</h3>
            <p>Apakah Anda yakin ingin menfinalisasi draf ini? <strong>Setelah difinalisasi, draf akan terkunci dan tidak bisa diubah lagi.</strong></p>
            
            <div style="text-align: right;">
                <button type="button" onclick="closeFinalizeModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Batal</button>
                <button type="button" onclick="confirmFinalize()" style="padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Ya, Finalisasi</button>
            </div>
        </div>
    </div>

    <script>
        function handleFinalize() {
            document.getElementById('finalizeModal').style.display = 'block';
        }

        function closeFinalizeModal() {
            document.getElementById('finalizeModal').style.display = 'none';
        }

        function confirmFinalize() {
            const draftId = {{ $draft['id'] }};

            fetch('/api/procurement/drafts/' + draftId + '/finalize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Draf berhasil difinalisasi dan terkunci');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Gagal menfinalisasi draf'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menfinalisasi draf');
            });

            closeFinalizeModal();
        }

        // Close modal when clicking outside
        document.getElementById('finalizeModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeFinalizeModal();
            }
        });
    </script>
@endsection

<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-item {
        padding: 10px;
        background-color: #f5f5f5;
        border-radius: 4px;
    }

    .info-item label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }

    .section-actions {
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 4px;
    }

    .btn {
        display: inline-block;
        padding: 8px 16px;
        margin-right: 10px;
        border-radius: 4px;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid #ccc;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 0.85em;
        font-weight: bold;
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    .badge-info {
        background-color: #17a2b8;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #333;
    }
</style>
