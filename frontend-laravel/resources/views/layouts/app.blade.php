<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Lab Inventory')</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #222;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #1f2937;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            padding: 24px 18px;
        }

        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #374151;
            color: white;
        }

        .main {
            margin-left: 240px;
            padding: 28px;
        }

        .header {
            background: white;
            padding: 20px 24px;
            border-radius: 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .header p {
            margin: 8px 0 0;
            color: #666;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 24px;
        }

        .card, .section {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }

        .card h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        .number {
            margin-top: 12px;
            font-size: 30px;
            font-weight: bold;
        }

        .status-success {
            color: #16a34a;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9fafb;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 999px;
            background: #e5e7eb;
            font-size: 12px;
        }

        .badge-lab {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .empty {
            padding: 18px;
            background: #f9fafb;
            border: 1px dashed #ccc;
            border-radius: 10px;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Lab Inventory</h2>
        @php
            $authUser = session('auth_user');
            $role = $authUser['role'] ?? null;
        @endphp

        <div style="font-size: 13px; color: #d1d5db; margin-bottom: 18px; line-height: 1.5;">
            <strong>{{ $authUser['name'] ?? 'User' }}</strong><br>
            {{ $role ?? '-' }}
        </div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('laboratories') }}" class="{{ request()->routeIs('laboratories') ? 'active' : '' }}">
            Laboratorium
        </a>

        <a href="{{ route('rooms') }}" class="{{ request()->routeIs('rooms') ? 'active' : '' }}">
            Ruangan
        </a>

        <a href="{{ route('inventory') }}" class="{{ request()->routeIs('inventory') ? 'active' : '' }}">
            Inventaris
        </a>

        <a href="{{ route('bhp') }}" class="{{ request()->routeIs('bhp') ? 'active' : '' }}">
            BHP
        </a>

        <a href="{{ route('procurement') }}" class="{{ request()->routeIs('procurement') ? 'active' : '' }}">
            Pengadaan
        </a>

        <a href="{{ route('maintenance') }}" class="{{ request()->routeIs('maintenance') ? 'active' : '' }}">
            Maintenance
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 20px;">
            @csrf
            <button type="submit" style="
                width: 100%;
                padding: 10px 12px;
                background: #991b1b;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
            ">
                Logout
            </button>
        </form>
    </div>

    <div class="main">
        @yield('content')
    </div>

</body>
</html>