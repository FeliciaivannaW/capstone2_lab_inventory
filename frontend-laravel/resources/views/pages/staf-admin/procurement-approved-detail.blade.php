@extends('layouts.app')
@section('title', 'Detail Draf Disetujui')
@section('content')
<style>
    .info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; margin-bottom:20px; }
    .info-card { padding:14px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; }
    .info-card label { font-size:12px; color:#64748b; font-weight:600; display:block; margin-bottom:4px; }
    .info-card span { font-size:15px; font-weight:500; color:#1e293b; }
    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge-success { background:#dcfce7; color:#166534; }
    .badge-danger { background:#fee2e2; color:#991b1b; }
    .badge-info { background:#dbeafe; color:#1e40af; }
    .btn-action { display:inline-block; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none; cursor:pointer; border:none; }
    .btn-primary { background:#3b82f6; color:white; }
    .btn-secondary { background:#6b7280; color:white; }
    .btn-success { background:#22c55e; color:white; }
    .actions-bar { display:flex; justify-content:space-between; align-items:center; padding:16px; background:#f8fafc; border-radius:8px; margin-top:16px; }
</style>

<div class="header">
    <h1>{{ $draft['title'] }}</h1>
    <p>Detail draf pengadaan yang sudah difinalisasi oleh Kaprodi</p>
</div>

<div class="section">
    <h3>📄 Informasi Draf</h3>
    <div class="info-grid">
        <div class="info-card"><label>Status</label><span class="badge badge-success">🔒 Finalized</span></div>
        <div class="info-card"><label>Laboratorium</label><span>{{ $draft['lab_name'] }}</span></div>
        <div class="info-card"><label>Tahun Anggaran</label><span>{{ $draft['budget_year'] }}</span></div>
        <div class="info-card"><label>Dibuat oleh</label><span>{{ $draft['created_by_name'] }}</span></div>
        <div class="info-card"><label>Difinalisasi oleh</label><span>{{ $draft['finalized_by_name'] ?? '-' }}</span></div>
        <div class="info-card"><label>Tanggal Finalisasi</label><span>{{ $draft['finalized_at'] ? date('d/m/Y H:i', strtotime($draft['finalized_at'])) : '-' }}</span></div>
    </div>
    @if($draft['notes'])
        <div class="info-card" style="margin-bottom:16px;"><label>Catatan</label><span>{{ $draft['notes'] }}</span></div>
    @endif
</div>

<div class="section">
    <h3><span class="badge badge-success">✓</span> Item Disetujui ({{ count($approvedItems) }})</h3>
    @if(empty($approvedItems))
        <div class="empty">Tidak ada item yang disetujui dalam draf ini.</div>
    @else
        <table>
            <thead><tr><th>No</th><th>Nama Barang</th><th>Tipe</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th><th>Link</th><th>Catatan</th></tr></thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($approvedItems as $i => $item)
                    @php $sub = $item['estimated_price'] * $item['quantity']; $total += $sub; @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $item['item_name'] }}</strong></td>
                        <td><span class="badge badge-info">{{ ucfirst($item['item_type']) }}</span></td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>Rp {{ number_format($item['estimated_price'],0,',','.') }}</td>
                        <td><strong>Rp {{ number_format($sub,0,',','.') }}</strong></td>
                        <td>@if($item['purchase_link'])<a href="{{ $item['purchase_link'] }}" target="_blank" style="color:#3b82f6;">🔗 Buka</a>@else - @endif</td>
                        <td>{{ $item['review_note'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr style="background:#f0fdf4;"><td colspan="5" style="text-align:right;font-weight:bold;">Total:</td><td colspan="3" style="font-weight:bold;font-size:16px;">Rp {{ number_format($total,0,',','.') }}</td></tr></tfoot>
        </table>
    @endif
</div>

@if(!empty($rejectedItems))
<div class="section">
    <h3><span class="badge badge-danger">✗</span> Item Ditolak ({{ count($rejectedItems) }})</h3>
    <table>
        <thead><tr><th>No</th><th>Nama Barang</th><th>Tipe</th><th>Jumlah</th><th>Harga</th><th>Alasan</th></tr></thead>
        <tbody>
            @foreach($rejectedItems as $i => $item)
                <tr style="opacity:0.7;">
                    <td>{{ $i+1 }}</td><td><s>{{ $item['item_name'] }}</s></td>
                    <td>{{ ucfirst($item['item_type']) }}</td><td>{{ $item['quantity'] }}</td>
                    <td>Rp {{ number_format($item['estimated_price'],0,',','.') }}</td>
                    <td style="color:#dc2626;">{{ $item['review_note'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="section">
    <div class="actions-bar">
        <div>
            @if(!empty($approvedItems))
                <a href="{{ route('staf-admin.goods-receipt', $draft['id']) }}" class="btn-action btn-success">📦 Input Penerimaan Barang</a>
            @endif
        </div>
        <a href="{{ route('staf-admin.procurement-approved') }}" class="btn-action btn-secondary">← Kembali</a>
    </div>
</div>
@endsection
