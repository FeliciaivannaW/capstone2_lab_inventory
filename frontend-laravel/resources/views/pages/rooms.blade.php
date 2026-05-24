@extends('layouts.app')

@section('title', 'Ruangan')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Ruangan</h1>
    <p class="text-sm text-slate-500 mt-1">Daftar seluruh ruangan berdasarkan denah Gedung GWM lantai 8.</p>
</div>

<div class="glass-card rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <p class="text-sm font-semibold text-slate-700">{{ count($rooms) }} ruangan</p>
    </div>
    @if(empty($rooms))
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm text-slate-400">Belum ada data ruangan</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="lv-table">
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
                            <td>
                                <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $room['code'] }}
                                </span>
                            </td>
                            <td class="font-semibold text-slate-800">{{ $room['name'] }}</td>
                            <td>
                                @if($room['room_type'] === 'laboratory')
                                    <span class="badge badge-active text-xs">Laboratory</span>
                                @else
                                    <span class="badge badge-draft text-xs">{{ ucfirst($room['room_type']) }}</span>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $room['building_name'] }}</td>
                            <td class="text-slate-500">{{ $room['floor_name'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
