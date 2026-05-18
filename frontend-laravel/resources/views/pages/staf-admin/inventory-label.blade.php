@extends('layouts.app')
@section('title', 'Label Inventaris')
@section('content')
<style>
    .filter-bar { display:flex; gap:12px; align-items:flex-end; margin-top:16px; flex-wrap:wrap; }
    .filter-group { display:flex; flex-direction:column; gap:4px; }
    .filter-group label { font-size:12px; color:#666; font-weight:600; }
    .filter-group input, .filter-group select { padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; min-width:150px; }
    .btn-filter { padding:8px 18px; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:500; background:#3b82f6; color:white; }
    .btn-reset { padding:8px 18px; border:none; border-radius:6px; cursor:pointer; font-size:14px; background:#e5e7eb; color:#374151; text-decoration:none; display:inline-block; }
    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-info { background:#dbeafe; color:#1e40af; }
    .badge-danger { background:#fee2e2; color:#991b1b; }
    .btn-edit { display:inline-block; padding:5px 12px; background:#f59e0b; color:white; text-decoration:none; border-radius:6px; font-size:13px; }
    .btn-edit:hover { background:#d97706; }
    .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
    .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .qr-thumb { width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb; }
</style>

<div class="header">
    <h1>🏷️ Label Inventaris</h1>
    <p>Update nomor label dan foto QR/Barcode untuk setiap aset inventaris.</p>
    <form method="GET" action="{{ route('staf-admin.inventory-label') }}" class="filter-bar">
        <div class="filter-group">
            <label>Cari Aset</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode, label, nama...">
        </div>
        <div class="filter-group">
            <label>Status Label</label>
            <select name="label_status">
                <option value="">Semua</option>
                <option value="labeled" {{ ($filters['label_status'] ?? '') == 'labeled' ? 'selected' : '' }}>Sudah Label</option>
                <option value="unlabeled" {{ ($filters['label_status'] ?? '') == 'unlabeled' ? 'selected' : '' }}>Belum Label</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Kondisi</label>
            <select name="status">
                <option value="">Semua</option>
                <option value="available" {{ ($filters['status'] ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="in_use" {{ ($filters['status'] ?? '') == 'in_use' ? 'selected' : '' }}>In Use</option>
                <option value="maintenance" {{ ($filters['status'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
        </div>
        <button type="submit" class="btn-filter">🔍 Filter</button>
        <a href="{{ route('staf-admin.inventory-label') }}" class="btn-reset">Reset</a>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error">✗ {{ session('error') }}</div>
@endif

<div class="section">
    @if(empty($assets))
        <div class="empty">
            <p style="text-align:center; font-size:16px;">📭 Belum ada data aset inventaris.</p>
            <p style="text-align:center; color:#999; font-size:13px;">Data aset akan muncul setelah barang diterima dan dicatat oleh sistem.</p>
        </div>
    @else
        <table>
            <thead><tr><th>No</th><th>Kode Aset</th><th>Nama</th><th>Kategori</th><th>Ruangan</th><th>Label</th><th>QR/Foto</th><th>Kondisi</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($assets as $i => $asset)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $asset['asset_code'] }}</strong></td>
                        <td>{{ $asset['item_name'] }}</td>
                        <td>{{ $asset['category_name'] ?? '-' }}</td>
                        <td>{{ $asset['room_name'] ?? '-' }}</td>
                        <td>
                            @if($asset['label_number'])
                                <span class="badge badge-success">{{ $asset['label_number'] }}</span>
                            @else
                                <span class="badge badge-warning">Belum</span>
                            @endif
                        </td>
                        <td>
                            @if($asset['photo_url'])
                                <img src="{{ $asset['photo_url'] }}" alt="QR" class="qr-thumb">
                            @else
                                <span style="color:#999;">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $condColors = ['baik'=>'success','rusak_ringan'=>'warning','rusak_berat'=>'danger','maintenance'=>'info','dihapus'=>'danger','diganti'=>'info'];
                                $c = $condColors[$asset['asset_condition']] ?? 'info';
                            @endphp
                            <span class="badge badge-{{ $c }}">{{ str_replace('_',' ',ucfirst($asset['asset_condition'])) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('staf-admin.inventory-label.edit', $asset['id']) }}" class="btn-edit">✏️ Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
