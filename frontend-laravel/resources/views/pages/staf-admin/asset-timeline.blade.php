@extends('layouts.app')
@section('title', 'Timeline Aset')
@section('content')
<style>
    .asset-header { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .asset-info { flex:1; min-width:140px; padding:14px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0; }
    .asset-info label { font-size:11px; color:#64748b; font-weight:600; display:block; margin-bottom:2px; }
    .asset-info span { font-size:14px; font-weight:500; color:#1e293b; }
    .timeline { position:relative; padding-left:40px; margin-top:20px; }
    .timeline::before { content:''; position:absolute; left:16px; top:0; bottom:0; width:3px; background:#e2e8f0; border-radius:2px; }
    .timeline-item { position:relative; margin-bottom:24px; padding:16px 20px; background:white; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #3b82f6; }
    .timeline-item.procurement { border-left-color:#8b5cf6; }
    .timeline-item.receipt { border-left-color:#22c55e; }
    .timeline-item.condition_change { border-left-color:#f59e0b; }
    .timeline-item.maintenance { border-left-color:#3b82f6; }
    .timeline-item.disposal { border-left-color:#ef4444; }
    .timeline-dot { position:absolute; left:-32px; top:18px; width:12px; height:12px; border-radius:50%; border:3px solid white; box-shadow:0 0 0 2px #3b82f6; }
    .timeline-item.procurement .timeline-dot { background:#8b5cf6; box-shadow:0 0 0 2px #8b5cf6; }
    .timeline-item.receipt .timeline-dot { background:#22c55e; box-shadow:0 0 0 2px #22c55e; }
    .timeline-item.condition_change .timeline-dot { background:#f59e0b; box-shadow:0 0 0 2px #f59e0b; }
    .timeline-item.maintenance .timeline-dot { background:#3b82f6; box-shadow:0 0 0 2px #3b82f6; }
    .timeline-item.disposal .timeline-dot { background:#ef4444; box-shadow:0 0 0 2px #ef4444; }
    .timeline-title { font-size:15px; font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:8px; }
    .timeline-date { font-size:12px; color:#94a3b8; margin-bottom:6px; }
    .timeline-desc { font-size:14px; color:#475569; margin-bottom:4px; }
    .timeline-detail { font-size:13px; color:#64748b; font-style:italic; }
    .timeline-meta { font-size:12px; color:#94a3b8; margin-top:6px; }
    .type-icon { font-size:18px; }
    .badge { display:inline-block; padding:3px 8px; border-radius:999px; font-size:11px; font-weight:600; }
    .badge-purple { background:#ede9fe; color:#6d28d9; }
    .badge-green { background:#dcfce7; color:#166534; }
    .badge-yellow { background:#fef3c7; color:#92400e; }
    .badge-blue { background:#dbeafe; color:#1e40af; }
    .badge-red { background:#fee2e2; color:#991b1b; }
    .btn-back { display:inline-block; padding:8px 18px; background:#6b7280; color:white; text-decoration:none; border-radius:6px; font-size:14px; margin-top:12px; }
</style>

<div class="header">
    <h1>📜 Timeline Siklus Barang</h1>
    <p>Riwayat lengkap dari pengadaan hingga penghapusan</p>
</div>

<div class="section">
    <div class="asset-header">
        <div class="asset-info"><label>Kode Aset</label><span>{{ $asset['asset_code'] }}</span></div>
        <div class="asset-info"><label>Nama</label><span>{{ $asset['item_name'] }}</span></div>
        <div class="asset-info"><label>Kategori</label><span>{{ $asset['category_name'] ?? '-' }}</span></div>
        <div class="asset-info"><label>Kondisi</label><span>{{ str_replace('_',' ',ucfirst($asset['asset_condition'])) }}</span></div>
        <div class="asset-info"><label>Status</label><span>{{ ucfirst(str_replace('_',' ',$asset['status'])) }}</span></div>
        <div class="asset-info"><label>Ruangan</label><span>{{ $asset['room_name'] ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h3>🕐 Timeline Siklus Hidup</h3>

    @if(empty($timeline))
        <div class="empty">
            <p style="text-align:center;">Belum ada riwayat siklus untuk aset ini.</p>
            <p style="text-align:center; color:#999; font-size:13px;">Timeline akan terisi otomatis saat ada aktivitas pengadaan, penerimaan, maintenance, atau penghapusan.</p>
        </div>
    @else
        <div class="timeline">
            @foreach($timeline as $event)
                @php
                    $icons = ['procurement'=>'🛒','receipt'=>'📦','condition_change'=>'🔄','maintenance'=>'🔧','disposal'=>'🗑️'];
                    $badgeClass = ['procurement'=>'purple','receipt'=>'green','condition_change'=>'yellow','maintenance'=>'blue','disposal'=>'red'];
                    $icon = $icons[$event['type']] ?? '📌';
                    $bc = $badgeClass[$event['type']] ?? 'blue';
                @endphp
                <div class="timeline-item {{ $event['type'] }}">
                    <div class="timeline-dot"></div>
                    <div class="timeline-title">
                        <span class="type-icon">{{ $icon }}</span>
                        {{ $event['title'] }}
                        <span class="badge badge-{{ $bc }}">{{ ucfirst(str_replace('_',' ',$event['status'] ?? '')) }}</span>
                    </div>
                    <div class="timeline-date">
                        {{ $event['date'] ? date('d M Y, H:i', strtotime($event['date'])) : 'Tanggal tidak tersedia' }}
                    </div>
                    <div class="timeline-desc">{{ $event['description'] ?? '' }}</div>
                    @if($event['detail'])
                        <div class="timeline-detail">{{ $event['detail'] }}</div>
                    @endif
                    <div class="timeline-meta">
                        👤 {{ $event['user'] ?? '-' }}
                        @if(isset($event['cost']) && $event['cost'])
                            &nbsp;|&nbsp; 💰 Rp {{ number_format($event['cost'], 0, ',', '.') }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="section">
    <a href="{{ route('staf-admin.asset-list') }}" class="btn-back">← Kembali ke Daftar Aset</a>
</div>
@endsection
