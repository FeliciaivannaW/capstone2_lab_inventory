@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="header">
        <h1>Dashboard</h1>
        <p>Sistem digitalisasi inventaris laboratorium dan barang habis pakai.</p>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Status Backend</h3>
            <div class="number status-success">
                {{ $health['status'] ?? 'error' }}
            </div>
        </div>

        <div class="card">
            <h3>Total Role</h3>
            <div class="number">{{ count($roles) }}</div>
        </div>

        <div class="card">
            <h3>Total Ruangan</h3>
            <div class="number">{{ count($rooms) }}</div>
        </div>

        <div class="card">
            <h3>Total Laboratorium</h3>
            <div class="number">{{ count($laboratories) }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Daftar Laboratorium</h2>

        <table>
            <thead>
                <tr>
                    <th>Kode Lab</th>
                    <th>Nama Lab</th>
                    <th>Kode Ruangan</th>
                    <th>Nama Ruangan</th>
                    <th>Gedung</th>
                    <th>Lantai</th>
                    <th>Kepala Lab</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laboratories as $lab)
                    <tr>
                        <td><strong>{{ $lab['code'] }}</strong></td>
                        <td>{{ $lab['name'] }}</td>
                        <td>{{ $lab['room_code'] }}</td>
                        <td>{{ $lab['room_name'] }}</td>
                        <td>{{ $lab['building_name'] }}</td>
                        <td>{{ $lab['floor_name'] }}</td>
                        <td>{{ $lab['head_name'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection