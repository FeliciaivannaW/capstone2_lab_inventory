@extends('layouts.app')

@section('title', 'Pengadaan')

@section('content')
    <style>
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-link {
            color: #0066cc;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="header">
        <h1>Pengadaan</h1>
        <p>Riwayat draf pengadaan aset dan BHP dengan status review dan finalisasi.</p>
    </div>

    <div class="section">
        @if(empty($drafts))
            <div class="empty">
                Tidak ada draf pengadaan.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Draf</th>
                        <th>Lab</th>
                        <th>Tahun Anggaran</th>
                        <th>Pembuat</th>
                        <th>Status</th>
                        <th>Item (Pending/Setuju/Tolak)</th>
                        <th>Terkunci</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($drafts as $index => $draft)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $draft['title'] }}</strong></td>
                            <td>{{ $draft['lab_name'] }}</td>
                            <td>{{ $draft['budget_year'] }}</td>
                            <td>{{ $draft['created_by_name'] }}</td>
                            <td>
                                <span class="badge badge-{{ 
                                    $draft['status'] === 'draft' ? 'secondary' : 
                                    ($draft['status'] === 'submitted' ? 'info' : 
                                    ($draft['status'] === 'finalized' ? 'success' : 'danger'))
                                }}">
                                    {{ ucfirst($draft['status']) }}
                                </span>
                            </td>
                            <td>
                                {{ $draft['pending_count'] ?? 0 }}/{{ $draft['approved_count'] ?? 0 }}/{{ $draft['rejected_count'] ?? 0 }}
                            </td>
                            <td>
                                @if($draft['is_locked'])
                                    <span style="color: red;">🔒 Ya</span>
                                @else
                                    <span style="color: green;">🔓 Tidak</span>
                                @endif
                            </td>
                            <td>{{ date('d/m/Y H:i', strtotime($draft['created_at'])) }}</td>
                            <td>
                                <a href="{{ route('procurement.show', $draft['id']) }}" class="btn-link">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection