@extends('layouts.app')

@section('title', 'Ruangan')

@section('content')
    <div class="header">
        <h1>Ruangan</h1>
        <p>Daftar seluruh ruangan berdasarkan denah Gedung GWM lantai 8.</p>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Kode Ruangan</th>
                    <th>Nama Ruangan</th>
                    <th>Tipe</th>
                    <th>Gedung</th>
                    <th>Lantai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                    <tr>
                        <td><strong>{{ $room['code'] }}</strong></td>
                        <td>{{ $room['name'] }}</td>
                        <td>
                            @if($room['room_type'] === 'laboratory')
                                <span class="badge badge-lab">{{ $room['room_type'] }}</span>
                            @else
                                <span class="badge">{{ $room['room_type'] }}</span>
                            @endif
                        </td>
                        <td>{{ $room['building_name'] }}</td>
                        <td>{{ $room['floor_name'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection