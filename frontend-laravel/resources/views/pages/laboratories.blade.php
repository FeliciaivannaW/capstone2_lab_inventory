@extends('layouts.app')

@section('title', 'Laboratorium')

@section('content')
    <div class="header">
        <h1>Laboratorium</h1>
        <p>Daftar laboratorium dari denah Gedung GWM lantai 8.</p>
    </div>

    <div class="section">
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