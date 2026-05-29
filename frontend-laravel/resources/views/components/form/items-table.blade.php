<div class="form-items-section">
    <h3>Daftar Item</h3>

    @if(empty($items))
        <div class="empty" style="padding: 18px; background: #f9fafb; border: 1px dashed #ccc; border-radius: 10px; color: #666; text-align: center;">
            Belum ada item. Klik tombol "Tambah Item" untuk menambahkan.
        </div>
    @else
        <table class="items-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Tipe</th>
                    <th>Jumlah</th>
                    <th>Harga Perkiraan</th>
                    <th>Link Pembelian</th>
                    <th>Status</th>
                    @if($canEdit)
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item['item_name'] }}</strong></td>
                        <td>{{ ucfirst($item['item_type']) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>Rp {{ number_format($item['estimated_price'], 0, ',', '.') }}</td>
                        <td>
                            @if($item['purchase_link'])
                                <a href="{{ $item['purchase_link'] }}" target="_blank" class="btn-link">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ 
                                $item['review_status'] === 'pending' ? 'warning' : 
                                ($item['review_status'] === 'approved' ? 'success' : 'danger')
                            }}">
                                {{ ucfirst($item['review_status'] ?? 'pending') }}
                            </span>
                        </td>
                        @if($canEdit && $item['review_status'] === 'pending')
                            <td>
                                <button type="button" class="btn-sm" onclick="deleteItem({{ $item['id'] }})" style="color: red;">Hapus</button>
                            </td>
                        @elseif($canEdit)
                            <td><em style="color: #999;">Terkunci</em></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


</div>

<style>
    .form-items-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .items-table th {
        background: #f3f4f6;
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    .items-table tr:hover {
        background: #f9fafb;
    }

    .btn-link {
        color: #0066cc;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
        background: none;
        border: none;
        cursor: pointer;
    }

    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 0.85em;
        font-weight: bold;
        color: white;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #333;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        text-decoration: none;
    }

    .btn-primary {
        background-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }
</style>
