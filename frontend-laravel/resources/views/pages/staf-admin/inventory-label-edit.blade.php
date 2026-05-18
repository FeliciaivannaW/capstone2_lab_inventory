@extends('layouts.app')
@section('title', 'Edit Label Inventaris')
@section('content')
<style>
    .form-card { background:white; padding:24px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); max-width:600px; }
    .form-group { margin-bottom:16px; }
    .form-group label { display:block; font-weight:600; margin-bottom:6px; font-size:14px; color:#374151; }
    .form-group input, .form-group textarea { width:100%; padding:10px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; box-sizing:border-box; }
    .form-group input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
    .info-row { display:flex; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
    .info-item { flex:1; min-width:140px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; }
    .info-item label { font-size:11px; color:#64748b; font-weight:600; display:block; margin-bottom:2px; }
    .info-item span { font-size:14px; font-weight:500; }
    .btn-submit { padding:10px 24px; background:#3b82f6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
    .btn-submit:hover { background:#2563eb; }
    .btn-back { padding:10px 24px; background:#e5e7eb; color:#374151; border:none; border-radius:8px; font-size:14px; text-decoration:none; display:inline-block; }
    .current-photo { max-width:200px; border-radius:8px; border:1px solid #e5e7eb; margin-top:8px; }
    .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
    .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .file-hint { font-size:12px; color:#64748b; margin-top:4px; }
</style>

<div class="header">
    <h1>✏️ Edit Label Inventaris</h1>
    <p>Update nomor label dan foto QR/Barcode untuk aset <strong>{{ $asset['asset_code'] }}</strong></p>
</div>

@if(session('error'))
    <div class="alert alert-error">✗ {{ session('error') }}</div>
@endif

<div class="section">
    <div class="info-row">
        <div class="info-item"><label>Kode Aset</label><span>{{ $asset['asset_code'] }}</span></div>
        <div class="info-item"><label>Nama</label><span>{{ $asset['item_name'] }}</span></div>
        <div class="info-item"><label>Kategori</label><span>{{ $asset['category_name'] ?? '-' }}</span></div>
        <div class="info-item"><label>Ruangan</label><span>{{ $asset['room_name'] ?? '-' }}</span></div>
        <div class="info-item"><label>Kondisi</label><span>{{ str_replace('_',' ',ucfirst($asset['asset_condition'])) }}</span></div>
        <div class="info-item"><label>Status</label><span>{{ ucfirst($asset['status']) }}</span></div>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('staf-admin.inventory-label.update', $asset['id']) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="label_number">Nomor Label *</label>
                <input type="text" id="label_number" name="label_number" value="{{ $asset['label_number'] ?? '' }}" required placeholder="Contoh: LAB-PROG1-PC-001">
            </div>

            <div class="form-group">
                <label for="qr_photo">Foto QR/Barcode</label>
                @if($asset['photo_url'])
                    <p style="font-size:13px; color:#64748b;">Foto saat ini:</p>
                    <img src="{{ $asset['photo_url'] }}" alt="Current QR" class="current-photo">
                @endif
                <input type="file" id="qr_photo" name="qr_photo" accept="image/jpeg,image/png,image/webp" style="margin-top:8px;">
                <p class="file-hint">Format: JPEG, PNG, WEBP. Maks 2MB. Kosongkan jika tidak ingin mengubah foto.</p>
            </div>

            <div style="display:flex; gap:12px; margin-top:20px;">
                <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
                <a href="{{ route('staf-admin.inventory-label') }}" class="btn-back">← Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection
