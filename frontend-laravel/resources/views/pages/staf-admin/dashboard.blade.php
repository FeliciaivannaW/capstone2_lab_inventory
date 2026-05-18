@extends('layouts.app')
@section('title', 'Dashboard Statistik')
@section('content')
<style>
    .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
    .stat-card { background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #3b82f6; }
    .stat-card.green { border-left-color:#22c55e; }
    .stat-card.yellow { border-left-color:#f59e0b; }
    .stat-card.red { border-left-color:#ef4444; }
    .stat-card.purple { border-left-color:#8b5cf6; }
    .stat-card h4 { margin:0 0 4px; font-size:13px; color:#64748b; font-weight:600; }
    .stat-card .number { font-size:28px; font-weight:700; color:#1e293b; }
    .stat-card .sub { font-size:12px; color:#94a3b8; margin-top:4px; }
    .two-col { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .chart-section { background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:20px; }
    .chart-section h3 { margin:0 0 16px; font-size:16px; }
    .mini-table { width:100%; font-size:13px; }
    .mini-table th { background:#f8fafc; padding:8px 12px; text-align:left; font-size:12px; color:#64748b; }
    .mini-table td { padding:8px 12px; border-bottom:1px solid #f1f5f9; }
    .bar { display:inline-block; height:18px; border-radius:4px; min-width:4px; }
    .bar-blue { background:#3b82f6; }
    .bar-green { background:#22c55e; }
    .bar-yellow { background:#f59e0b; }
    .bar-red { background:#ef4444; }
    @media(max-width:900px) { .stats-grid { grid-template-columns:repeat(2,1fr); } .two-col { grid-template-columns:1fr; } }
</style>

<div class="header">
    <h1>📊 Dashboard Statistik</h1>
    <p>Ringkasan data inventaris, stok BHP, dan pengadaan laboratorium.</p>
</div>

@php
    $inv = $stats['inventory'] ?? [];
    $bhp = $stats['bhp'] ?? [];
    $proc = $stats['procurement'] ?? [];
    $recv = $stats['reception'] ?? [];
    $maint = $stats['maintenance'] ?? [];
    $bhpCat = $stats['bhpByCategory'] ?? [];
@endphp

<div class="stats-grid">
    <div class="stat-card green">
        <h4>Total Inventaris Aktif</h4>
        <div class="number">{{ $inv['active'] ?? 0 }}</div>
        <div class="sub">dari {{ $inv['total'] ?? 0 }} total aset</div>
    </div>
    <div class="stat-card yellow">
        <h4>Dalam Maintenance</h4>
        <div class="number">{{ $inv['in_maintenance'] ?? 0 }}</div>
        <div class="sub">{{ $inv['disposed'] ?? 0 }} dihapus, {{ $inv['replaced'] ?? 0 }} diganti</div>
    </div>
    <div class="stat-card">
        <h4>Stok BHP</h4>
        <div class="number">{{ $bhp['total_items'] ?? 0 }}</div>
        <div class="sub">{{ $bhp['low_stock_count'] ?? 0 }} hampir habis</div>
    </div>
    <div class="stat-card purple">
        <h4>Draf Pengadaan</h4>
        <div class="number">{{ $proc['total_drafts'] ?? 0 }}</div>
        <div class="sub">{{ $proc['finalized_count'] ?? 0 }} finalized</div>
    </div>
</div>

<div class="two-col">
    <div class="chart-section">
        <h3>📦 Status Inventaris</h3>
        <table class="mini-table">
            <tr><td>Kondisi Baik</td><td><span class="bar bar-green" style="width:{{ min(($inv['condition_good'] ?? 0)*3, 200) }}px"></span></td><td><strong>{{ $inv['condition_good'] ?? 0 }}</strong></td></tr>
            <tr><td>Rusak Ringan</td><td><span class="bar bar-yellow" style="width:{{ min(($inv['condition_light_damage'] ?? 0)*3, 200) }}px"></span></td><td><strong>{{ $inv['condition_light_damage'] ?? 0 }}</strong></td></tr>
            <tr><td>Rusak Berat</td><td><span class="bar bar-red" style="width:{{ min(($inv['condition_heavy_damage'] ?? 0)*3, 200) }}px"></span></td><td><strong>{{ $inv['condition_heavy_damage'] ?? 0 }}</strong></td></tr>
            <tr><td>Sudah Label</td><td><span class="bar bar-green" style="width:{{ min(($inv['labeled'] ?? 0)*3, 200) }}px"></span></td><td><strong>{{ $inv['labeled'] ?? 0 }}</strong></td></tr>
            <tr><td>Belum Label</td><td><span class="bar bar-yellow" style="width:{{ min(($inv['unlabeled'] ?? 0)*3, 200) }}px"></span></td><td><strong>{{ $inv['unlabeled'] ?? 0 }}</strong></td></tr>
        </table>
    </div>

    <div class="chart-section">
        <h3>🛒 Status Pengadaan</h3>
        <table class="mini-table">
            <tr><td>Draft</td><td><span class="bar bar-blue" style="width:{{ min(($proc['draft_count'] ?? 0)*8, 200) }}px"></span></td><td><strong>{{ $proc['draft_count'] ?? 0 }}</strong></td></tr>
            <tr><td>Submitted</td><td><span class="bar bar-yellow" style="width:{{ min(($proc['submitted_count'] ?? 0)*8, 200) }}px"></span></td><td><strong>{{ $proc['submitted_count'] ?? 0 }}</strong></td></tr>
            <tr><td>Finalized</td><td><span class="bar bar-green" style="width:{{ min(($proc['finalized_count'] ?? 0)*8, 200) }}px"></span></td><td><strong>{{ $proc['finalized_count'] ?? 0 }}</strong></td></tr>
            <tr><td>Barang Diterima</td><td><span class="bar bar-green" style="width:{{ min(($recv['received_items'] ?? 0)*8, 200) }}px"></span></td><td><strong>{{ $recv['received_items'] ?? 0 }}</strong></td></tr>
            <tr><td>Belum Diterima</td><td><span class="bar bar-red" style="width:{{ min(($recv['pending_items'] ?? 0)*8, 200) }}px"></span></td><td><strong>{{ $recv['pending_items'] ?? 0 }}</strong></td></tr>
        </table>
    </div>
</div>

<div class="two-col">
    <div class="chart-section">
        <h3>📋 Stok BHP per Kategori</h3>
        @if(empty($bhpCat))
            <p style="color:#94a3b8;">Belum ada data BHP.</p>
        @else
            <table class="mini-table">
                <thead><tr><th>Kategori</th><th>Item</th><th>Total Stok</th><th>Stok Rendah</th></tr></thead>
                <tbody>
                    @foreach($bhpCat as $cat)
                        <tr>
                            <td>{{ $cat['category_name'] ?? 'Tanpa Kategori' }}</td>
                            <td>{{ $cat['item_count'] }}</td>
                            <td>{{ $cat['total_stock'] }}</td>
                            <td>@if($cat['low_stock'] > 0)<span style="color:#ef4444; font-weight:600;">⚠ {{ $cat['low_stock'] }}</span>@else <span style="color:#22c55e;">✓</span> @endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="chart-section">
        <h3>🔧 Maintenance</h3>
        <table class="mini-table">
            <tr><td>Planned</td><td><strong>{{ $maint['planned'] ?? 0 }}</strong></td></tr>
            <tr><td>In Progress</td><td><strong>{{ $maint['in_progress'] ?? 0 }}</strong></td></tr>
            <tr><td>Done</td><td><strong>{{ $maint['done'] ?? 0 }}</strong></td></tr>
            <tr><td>Cancelled</td><td><strong>{{ $maint['cancelled'] ?? 0 }}</strong></td></tr>
        </table>
    </div>
</div>
@endsection
