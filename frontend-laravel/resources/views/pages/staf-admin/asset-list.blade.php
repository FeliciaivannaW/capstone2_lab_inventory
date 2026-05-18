@extends('layouts.app')
@section('title', 'Siklus Barang')
@section('content')
<style>
    .filter-bar { display:flex; gap:12px; align-items:flex-end; margin-top:16px; flex-wrap:wrap; }
    .filter-group { display:flex; flex-direction:column; gap:4px; }
    .filter-group label { font-size:12px; color:#666; font-weight:600; }
    .filter-group input, .filter-group select { padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:14px; min-width:150px; }
    .btn-filter { padding:8px 18px; border:none; border-radius:6px; cursor:pointer; font-size:14px; font-weight:500; background:#3b82f6; color:white; }
    .btn-reset { padding:8px 18px; border:none; border-radius:6px; font-size:14px; background:#e5e7eb; color:#374151; text-decoration:none; display:inline-block; }
    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-warning { background:#fef3c7; color:#92400e; }
    .badge-info { background:#dbeafe; color:#1e40af; }
    .badge-danger { background:#fee2e2; color:#991b1b; }
    .btn-timeline { display:inline-block; padding:5px 12px; background:#8b5cf6; color:white; text-decoration:none; border-radius:6px; font-size:13px; }
    .btn-timeline:hover { background:#7c3aed; }
</style>

<div class="header">
    <h1>🔄 Pelacakan Siklus Barang</h1>
    <p>Lihat riwayat lengkap siklus hidup setiap aset inventaris.</p>
    <form method="GET" action="{{ route('staf-admin.asset-list') }}" class="filter-bar">
        <div class="filter-group">
            <label>Cari Aset</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Kode, nama...">
        </div>
        <div class="filter-group">
            <label>Kondisi</label>
            <select name="condition">
                <option value="">Semua</option>
                <option value="baik" {{ ($filters['condition'] ?? '') == 'baik' ? 'selected' : '' }}>Baik</option>
                <option value="rusak_ringan" {{ ($filters['condition'] ?? '') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                <option value="rusak_berat" {{ ($filters['condition'] ?? '') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                <option value="maintenance" {{ ($filters['condition'] ?? '') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="dihapus" {{ ($filters['condition'] ?? '') == 'dihapus' ? 'selected' : '' }}>Dihapus</option>
                <option value="diganti" {{ ($filters['condition'] ?? '') == 'diganti' ? 'selected' : '' }}>Diganti</option>
            </select>
        </div>
        <button type="submit" class="btn-filter">🔍 Filter</button>
        <a href="{{ route('staf-admin.asset-list') }}" class="btn-reset">Reset</a>
    </form>
</div>

<div class="section">
    @if(empty($assets))
        <div class="empty">
            <p style="text-align:center; font-size:16px;">📭 Belum ada data aset inventaris.</p>
            <p style="text-align:center; color:#999; font-size:13px;">Data aset akan muncul setelah barang diterima dan dicatat.</p>
        </div>
    @else
        <table>
            <thead><tr><th>No</th><th>Kode Aset</th><th>Nama</th><th>Kategori</th><th>Kondisi</th><th>Status</th><th>Tgl Terima</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($assets as $i => $asset)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $asset['asset_code'] }}</strong></td>
                        <td>{{ $asset['item_name'] }}</td>
                        <td>{{ $asset['category_name'] ?? '-' }}</td>
                        <td>
                            @php
                                $cc = ['baik'=>'success','rusak_ringan'=>'warning','rusak_berat'=>'danger','maintenance'=>'info','dihapus'=>'danger','diganti'=>'info'];
                            @endphp
                            <span class="badge badge-{{ $cc[$asset['asset_condition']] ?? 'info' }}">{{ str_replace('_',' ',ucfirst($asset['asset_condition'])) }}</span>
                        </td>
                        <td>{{ ucfirst(str_replace('_',' ',$asset['status'])) }}</td>
                        <td>{{ $asset['received_date'] ? date('d/m/Y', strtotime($asset['received_date'])) : '-' }}</td>
                        <td>
                            <a href="{{ route('staf-admin.asset-timeline', $asset['id']) }}" class="btn-timeline">📜 Timeline</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
