@extends('layouts.app')

@section('title', 'Draf Pengadaan Disetujui')

@section('content')
    <style>
        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .filter-bar .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-bar label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-width: 160px;
        }

        .filter-bar button {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-filter {
            background: #3b82f6;
            color: white;
        }

        .btn-filter:hover {
            background: #2563eb;
        }

        .btn-reset {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-reset:hover {
            background: #d1d5db;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .stats-row {
            display: flex;
            gap: 8px;
            font-size: 13px;
        }

        .btn-detail {
            display: inline-block;
            padding: 6px 14px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-detail:hover {
            background: #2563eb;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>

    <div class="header">
        <h1>📋 Draf Pengadaan yang Disetujui</h1>
        <p>Draf pengadaan yang sudah difinalisasi oleh Ketua Program Studi.</p>

        <form method="GET" action="{{ route('staf-admin.procurement-approved') }}" class="filter-bar">
            <div class="filter-group">
                <label>Tahun Anggaran</label>
                <select name="budget_year">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ ($filters['budget_year'] ?? '') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="filter-group">
                <label>Cari Judul / Lab</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ketik kata kunci...">
            </div>

            <button type="submit" class="btn-filter">🔍 Filter</button>
            <a href="{{ route('staf-admin.procurement-approved') }}" class="btn-reset" style="text-decoration:none; display:inline-block;">Reset</a>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    <div class="section">
        @if(empty($drafts))
            <div class="empty">
                <p style="text-align: center; font-size: 16px;">📭 Belum ada draf pengadaan yang difinalisasi.</p>
                <p style="text-align: center; color: #999; font-size: 13px;">Draf akan muncul di sini setelah Kaprodi menfinalisasi draf dari Kepala Laboratorium.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Draf</th>
                        <th>Laboratorium</th>
                        <th>Tahun</th>
                        <th>Pembuat</th>
                        <th>Difinalisasi</th>
                        <th>Item (Setuju / Tolak)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drafts as $index => $draft)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $draft['title'] }}</strong></td>
                            <td>
                                <span class="badge badge-info">{{ $draft['lab_code'] ?? '' }}</span>
                                {{ $draft['lab_name'] }}
                            </td>
                            <td>{{ $draft['budget_year'] }}</td>
                            <td>{{ $draft['created_by_name'] }}</td>
                            <td>
                                @if($draft['finalized_at'])
                                    {{ date('d/m/Y', strtotime($draft['finalized_at'])) }}
                                    <br><small style="color:#666;">oleh {{ $draft['finalized_by_name'] ?? '-' }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="stats-row">
                                    <span class="badge badge-success">✓ {{ $draft['approved_count'] ?? 0 }}</span>
                                    <span class="badge badge-danger">✗ {{ $draft['rejected_count'] ?? 0 }}</span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('staf-admin.procurement-approved.detail', $draft['id']) }}" class="btn-detail">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
