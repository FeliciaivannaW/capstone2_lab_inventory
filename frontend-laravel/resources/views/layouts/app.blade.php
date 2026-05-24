<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Labventory') — Labventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        navy: { DEFAULT: '#0F172A', 800: '#1E293B', 700: '#334155', 600: '#475569' },
                    },
                    boxShadow: {
                        'glass': '0 4px 24px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.9)',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.1/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }

        @keyframes slideInContent {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .content-animate { animation: slideInContent 0.35s ease forwards; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }

        /* Sidebar nav link hover */
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            font-size: 0.8125rem; font-weight: 500;
            color: #94A3B8;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }
        .nav-link:hover { background: rgba(255,255,255,0.07); color: #E2E8F0; }
        .nav-link.active {
            background: rgba(99,102,241,0.18);
            color: #A5B4FC;
        }
        .nav-link.active svg { color: #6366F1; }
        .nav-link svg { width:16px; height:16px; flex-shrink:0; transition: color 0.15s; }

        /* Section label in sidebar */
        .nav-section {
            font-size: 0.625rem; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: #475569;
            padding: 0 12px; margin: 16px 0 4px;
        }

        /* Sidebar collapse transition */
        .sidebar-label { transition: opacity 0.2s, width 0.2s; white-space: nowrap; overflow: hidden; }
        .sidebar-collapsed .sidebar-label { opacity: 0; width: 0; }
        .sidebar-collapsed .nav-section { opacity: 0; }
        .sidebar-collapsed { width: 64px !important; }
        .sidebar-collapsed .brand-name { opacity: 0; width: 0; }

        /* Table styles */
        .lv-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        .lv-table th { background: #F8FAFC; padding: 11px 16px; text-align: left; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: #64748B; border-bottom: 1.5px solid #E2E8F0; }
        .lv-table td { padding: 13px 16px; border-bottom: 1px solid #F1F5F9; color: #334155; vertical-align: middle; }
        .lv-table tbody tr:hover { background: #F8FAFC; }
        .lv-table tbody tr:last-child td { border-bottom: none; }

        /* Status badges */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:999px; font-size:0.7rem; font-weight:600; }
        .badge-draft     { background:#F1F5F9; color:#64748B; }
        .badge-submitted { background:#EFF6FF; color:#3B82F6; }
        .badge-approved  { background:#ECFDF5; color:#10B981; }
        .badge-rejected  { background:#FEF2F2; color:#EF4444; }
        .badge-finalized { background:#F0FDF4; color:#16A34A; }
        .badge-pending   { background:#FFFBEB; color:#D97706; }
        .badge-active    { background:#EEF2FF; color:#6366F1; }

        .glass-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 4px 24px rgba(15,23,42,0.06), inset 0 1px 0 rgba(255,255,255,0.9);
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen"
      x-data="{
          sidebarOpen: true,
          userMenuOpen: false,
      }">

    {{-- ───── SIDEBAR ───── --}}
    @php
        $authUser = session('auth_user');
        $role = $authUser['role'] ?? null;
        $userName = $authUser['name'] ?? 'User';

        $roleLabels = [
            'admin'               => 'Administrator',
            'kepala_laboratorium' => 'Kepala Laboratorium',
            'ketua_program_studi' => 'Ketua Program Studi',
            'staf_administrasi'   => 'Staf Administrasi',
            'staf_laboratorium'   => 'Staf Laboratorium',
        ];
        $roleLabel = $roleLabels[$role] ?? $role;

        $initials = collect(explode(' ', $userName))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
    @endphp

    <aside :class="sidebarOpen ? 'w-60' : 'w-16 sidebar-collapsed'"
           class="fixed left-0 top-0 h-screen bg-[#0F172A] flex flex-col z-40 overflow-hidden transition-all duration-250 ease-out border-r border-[#1E293B]">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-[#1E293B]">
            <div class="flex-shrink-0">
                <svg width="30" height="30" viewBox="0 0 40 40" fill="none">
                    <rect x="8" y="22" width="24" height="10" fill="#4F46E5" rx="2"/>
                    <ellipse cx="20" cy="22" rx="12" ry="4" fill="#818CF8"/>
                    <ellipse cx="20" cy="32" rx="12" ry="4" fill="#4338CA"/>
                    <path d="M15 7 L13 22 L27 22 L25 7" fill="#C7D2FE" stroke="#6366F1" stroke-width="1.5" stroke-linejoin="round"/>
                    <rect x="14" y="4" width="12" height="4" rx="2" fill="#6366F1"/>
                    <path d="M14 17 Q20 20 26 17 L27 22 L13 22 Z" fill="#6366F1" opacity="0.55"/>
                </svg>
            </div>
            <span class="brand-name sidebar-label text-white font-bold text-base tracking-tight">Labventory</span>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="sidebar-label">Dashboard</span>
            </a>

            {{-- Admin --}}
            @if(in_array($role, ['administrator', 'admin']))
                <div class="nav-section sidebar-label">Manajemen</div>
                <a href="{{ route('users') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-label">Manajemen User</span>
                </a>
                <a href="{{ route('laboratories') }}" class="nav-link {{ request()->routeIs('laboratories') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span class="sidebar-label">Laboratorium</span>
                </a>
                <a href="{{ route('rooms') }}" class="nav-link {{ request()->routeIs('rooms') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span class="sidebar-label">Ruangan</span>
                </a>
            @endif

            {{-- Kepala Lab + Ketua Prodi --}}
            @if(in_array($role, ['kepala_laboratorium', 'ketua_program_studi']))
                <div class="nav-section sidebar-label">Pengadaan</div>
                <a href="{{ route('procurement') }}" class="nav-link {{ request()->routeIs('procurement*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="sidebar-label">Draf Pengadaan</span>
                </a>
                <a href="{{ route('inventory') }}" class="nav-link {{ request()->routeIs('inventory') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="sidebar-label">Katalog Inventaris</span>
                </a>
            @endif

            {{-- Staf Lab --}}
            @if($role === 'staf_laboratorium')
                <div class="nav-section sidebar-label">Laboratorium</div>
                <a href="{{ route('bhp') }}" class="nav-link {{ request()->routeIs('bhp') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span class="sidebar-label">Kelola Stok BHP</span>
                </a>
                <a href="{{ route('maintenance') }}" class="nav-link {{ request()->routeIs('maintenance') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="sidebar-label">Log Maintenance</span>
                </a>
                <a href="{{ route('inventory') }}" class="nav-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="sidebar-label">Update Kondisi Aset</span>
                </a>
            @endif

            {{-- Staf Administrasi --}}
            @if($role === 'staf_administrasi')
                {{-- ── TUGAS UTAMA (3 fitur sesuai requirement) ── --}}
                <div class="nav-section sidebar-label">Tugas Utama</div>

                {{-- Fitur 1 --}}
                <a href="{{ route('staf-admin.procurement-approved') }}" class="nav-link {{ request()->routeIs('staf-admin.procurement-approved*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-label">Draf Disetujui</span>
                </a>

                {{-- Fitur 3 --}}
                <a href="{{ route('staf-admin.goods-receipt-index') }}" class="nav-link {{ request()->routeIs('staf-admin.goods-receipt*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                    </svg>
                    <span class="sidebar-label">Penerimaan Barang</span>
                </a>

                {{-- Fitur 2 --}}
                <a href="{{ route('staf-admin.inventory-label') }}" class="nav-link {{ request()->routeIs('staf-admin.inventory-label*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="sidebar-label">Update Label & Foto</span>
                </a>

                {{-- ── PELENGKAP (informasi tambahan) ── --}}
                <div class="nav-section sidebar-label">Lainnya</div>

                <a href="{{ route('staf-admin.dashboard') }}" class="nav-link {{ request()->routeIs('staf-admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="sidebar-label">Statistik</span>
                </a>
                <a href="{{ route('staf-admin.asset-list') }}" class="nav-link {{ request()->routeIs('staf-admin.asset*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="sidebar-label">Pelacakan Siklus</span>
                </a>
                <a href="{{ route('staf-admin.inventaris') }}" class="nav-link {{ request()->routeIs('staf-admin.inventaris') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="sidebar-label">Semua Inventaris</span>
                </a>
            @endif
        </nav>

        {{-- User section + Logout --}}
        <div class="border-t border-[#1E293B] p-3">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                    {{ $initials }}
                </div>
                <div class="sidebar-label overflow-hidden">
                    <p class="text-white text-xs font-semibold truncate">{{ $userName }}</p>
                    <p class="text-slate-500 text-[0.65rem] truncate">{{ $roleLabel }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit"
                        class="nav-link w-full text-red-400 hover:text-red-300 hover:bg-red-500/10 justify-start">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-label">Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ───── TOP HEADER ───── --}}
    <header :class="sidebarOpen ? 'left-60' : 'left-16'"
            class="fixed top-0 right-0 h-14 bg-white/80 backdrop-blur-md border-b border-slate-200 z-30 flex items-center px-6 gap-4 transition-all duration-250">

        {{-- Sidebar toggle --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg p-1.5 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-1.5 text-sm text-slate-500 min-w-0">
            <span class="text-slate-400 text-xs">Labventory</span>
            <span class="text-slate-300">/</span>
            <span class="font-semibold text-slate-700 truncate">@yield('title', 'Dashboard')</span>
        </div>

        <div class="flex-1"></div>

        {{-- Role badge --}}
        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-[0.68rem] font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100">
            {{ $roleLabel }}
        </span>

        {{-- User avatar --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false"
                    class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white hover:ring-2 hover:ring-indigo-300 transition-all">
                {{ $initials }}
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-10 w-52 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50"
                 style="display:none;">
                <div class="px-4 py-2.5 border-b border-slate-100">
                    <p class="text-xs font-semibold text-slate-800">{{ $userName }}</p>
                    <p class="text-[0.68rem] text-slate-500 mt-0.5">{{ $roleLabel }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ───── MAIN CONTENT ───── --}}
    <main :class="sidebarOpen ? 'ml-60' : 'ml-16'"
          class="min-h-screen pt-14 transition-all duration-250">
        <div class="content-animate p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>