<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Labventory') — Labventory</title>
    <link rel="icon" type="image/svg+xml"
        href='data:image/svg+xml;utf8,
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
            <text y="50" font-size="48">🧪</text>
        </svg>'>
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
    <script>
        window.tablePaginationData = (totalItems) => ({
            currentPage: 1,
            perPage: 10,
            totalItems: totalItems,
            filteredTotalItems: totalItems,
            sortField: '',
            sortAsc: true,
            indexMap: {},
            updateCounter: 0,
            filters: {},
            searchQuery: '',
            get totalPages() { return Math.ceil(this.filteredTotalItems / this.perPage); },
            getPages() { return Array.from({length: this.totalPages}, (_, i) => i + 1); },
            showRow(index) {
                const _ = this.updateCounter;
                let finalIndex = index;
                if (this.indexMap && this.indexMap[index] !== undefined) {
                    finalIndex = this.indexMap[index];
                }
                if (finalIndex === -1) return false;
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + parseInt(this.perPage);
                return finalIndex >= start && finalIndex < end;
            },
            sortBy(field, thEl) {
                const table = thEl.closest('table');
                
                if (this.sortField === field) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortField = field;
                    this.sortAsc = true;
                }
                
                this.applyFiltersAndSorting(table);
            },
            setFilter(column, value) {
                this.filters[column] = value;
                this.currentPage = 1;
                this.applyFiltersAndSorting();
            },
            resetFilters() {
                Object.keys(this.filters).forEach(k => this.filters[k] = '');
                this.currentPage = 1;
                this.applyFiltersAndSorting();
            },
            applyFiltersAndSorting(targetTable = null) {
                let table = targetTable;
                if (!table) {
                    const container = this.$el.closest('[x-data]') || this.$el;
                    table = container.querySelector('table.lv-table');
                }
                if (table) {
                    window.applyTableFiltersAndSorting(table, this);
                }
            }
        });

        window.getSortValue = (cell) => {
            if (!cell) return '';
            if (cell.dataset.sortValue) return cell.dataset.sortValue;
            
            const text = cell.innerText.trim();
            if (!text) return '';
            
            // Try date parse
            if (text.match(/^[0-9]{1,2}[-\/\s][A-Za-z0-9]{3,}[-\/\s][0-9]{2,4}/) || text.match(/^[0-9]{4}[-\/\s][0-9]{2}[-\/\s][0-9]{2}/)) {
                const parsedDate = Date.parse(text);
                if (!isNaN(parsedDate)) {
                    return parsedDate;
                }
            }
            
            // Numeric check
            const cleaned = text.replace(/[^0-9.,-]/g, '').replace(',', '.');
            if (cleaned !== '' && !isNaN(cleaned) && text.match(/^\s*[-+]?[0-9]/)) {
                return parseFloat(cleaned);
            }
            
            return text.toLowerCase();
        };

        window.applyTableFiltersAndSorting = (table, alpineData) => {
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const groups = [];
            let currentGroup = null;
            
            rows.forEach(row => {
                if (row.querySelector('td[colspan]') && (row.innerText.includes('Belum ada') || row.innerText.includes('Tidak ada') || row.innerText.includes('No data') || row.innerText.includes('Belum memilih'))) {
                    return;
                }
                const isDetail = row.querySelector('td[colspan]');
                if (!isDetail) {
                    currentGroup = { main: row, details: [], matches: true };
                    groups.push(currentGroup);
                } else {
                    if (currentGroup) {
                        currentGroup.details.push(row);
                    } else {
                        groups.push({ main: row, details: [], matches: true });
                    }
                }
            });
            
            // Filter step using AND logic on data attributes and search query
            let visibleCount = 0;
            const searchLower = (alpineData.searchQuery || '').toLowerCase();
            
            groups.forEach(group => {
                let matches = true;
                for (const [key, filterValue] of Object.entries(alpineData.filters)) {
                    if (filterValue !== undefined && filterValue !== '') {
                        const attrName = 'filter' + key.charAt(0).toUpperCase() + key.slice(1);
                        const rowValue = group.main.dataset[attrName];
                        if (rowValue !== undefined && rowValue !== filterValue) {
                            matches = false;
                            break;
                        }
                    }
                }
                
                if (matches && searchLower) {
                    const textContent = group.main.innerText.toLowerCase();
                    if (!textContent.includes(searchLower)) {
                        matches = false;
                    }
                }
                
                group.matches = matches;
                if (matches) visibleCount++;
            });
            
            const visibleGroups = groups.filter(g => g.matches);
            const hiddenGroups = groups.filter(g => !g.matches);
            
            // Sort step on visible subset
            if (alpineData.sortField) {
                const ths = Array.from(table.querySelectorAll('thead th'));
                let actualColIndex = ths.findIndex(th => th.getAttribute('field') === alpineData.sortField || th.dataset.sortField === alpineData.sortField);
                
                if (actualColIndex === -1) {
                    ths.forEach((th, idx) => {
                        const clickAttr = th.getAttribute('@click') || th.getAttribute('x-on:click') || '';
                        if (clickAttr.includes(alpineData.sortField)) {
                            actualColIndex = idx;
                        }
                    });
                }
                
                if (actualColIndex !== -1) {
                    const direction = alpineData.sortAsc ? 'asc' : 'desc';
                    visibleGroups.sort((a, b) => {
                        const cellA = a.main.cells[actualColIndex];
                        const cellB = b.main.cells[actualColIndex];
                        
                        const valA = window.getSortValue(cellA);
                        const valB = window.getSortValue(cellB);
                        
                        if (valA === valB) return 0;
                        
                        if (direction === 'asc') {
                            if (typeof valA === 'number' && typeof valB === 'number') {
                                return valA - valB;
                            }
                            return valA.toString().localeCompare(valB.toString(), undefined, {numeric: true, sensitivity: 'base'});
                        } else {
                            if (typeof valA === 'number' && typeof valB === 'number') {
                                return valB - valA;
                            }
                            return valB.toString().localeCompare(valA.toString(), undefined, {numeric: true, sensitivity: 'base'});
                        }
                    });
                }
            }
            
            // Re-append to DOM in order
            visibleGroups.forEach(group => {
                tbody.appendChild(group.main);
                group.details.forEach(detail => tbody.appendChild(detail));
            });
            hiddenGroups.forEach(group => {
                tbody.appendChild(group.main);
                group.details.forEach(detail => tbody.appendChild(detail));
            });
            
            // Re-map the indices
            const newIndexMap = {};
            hiddenGroups.forEach(group => {
                const origIndex = parseInt(group.main.dataset.originalIndex);
                if (!isNaN(origIndex)) {
                    newIndexMap[origIndex] = -1;
                }
            });
            visibleGroups.forEach((group, newIndex) => {
                const origIndex = parseInt(group.main.dataset.originalIndex);
                if (!isNaN(origIndex)) {
                    newIndexMap[origIndex] = newIndex;
                }
            });
            
            alpineData.indexMap = newIndexMap;
            alpineData.filteredTotalItems = visibleGroups.length;
            alpineData.updateCounter = (alpineData.updateCounter || 0) + 1;
        };

        // Initialize original index markings on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            const initOriginalIndices = () => {
                const tables = document.querySelectorAll('table.lv-table');
                tables.forEach(table => {
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    
                    let mainIndex = 0;
                    let currentMainIndex = 0;
                    
                    rows.forEach(row => {
                        if (row.querySelector('td[colspan]') && (row.innerText.includes('Belum ada') || row.innerText.includes('Tidak ada') || row.innerText.includes('No data') || row.innerText.includes('Belum memilih'))) {
                            return;
                        }
                        const isDetail = row.querySelector('td[colspan]');
                        if (!isDetail) {
                            currentMainIndex = mainIndex;
                            row.dataset.originalIndex = currentMainIndex;
                            mainIndex++;
                        } else {
                            row.dataset.originalIndex = currentMainIndex;
                        }
                    });
                });
            };
            initOriginalIndices();
            
            // Re-run index init when Alpine updates
            if (window.Alpine) {
                window.Alpine.nextTick(() => {
                    initOriginalIndices();
                });
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('tablePagination', (totalItems) => window.tablePaginationData(totalItems));
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }

        /* Fix missing border, padding, and focus states on inputs using border-slate-200 and border-amber-200 */
        input.border-slate-200, select.border-slate-200, textarea.border-slate-200 {
            border-width: 1px;
            border-style: solid;
            padding: 0.55rem 0.875rem;
            outline: none;
            transition: all 0.15s ease-in-out;
        }
        input.border-slate-200:focus, select.border-slate-200:focus, textarea.border-slate-200:focus {
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        input.border-amber-200, select.border-amber-200, textarea.border-amber-200 {
            border-width: 1px;
            border-style: solid;
            padding: 0.45rem 0.75rem;
            outline: none;
            transition: all 0.15s ease-in-out;
        }
        input.border-amber-200:focus, select.border-amber-200:focus, textarea.border-amber-200:focus {
            border-color: #D97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
        }

        [x-cloak] {
        display: none !important;
        }

        
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
            padding: 0 12px;
        }
        .nav-section.sidebar-label {
            margin-top: 16px !important;
            margin-bottom: 4px !important;
        }
        .nav-section.sidebar-label:first-of-type {
            margin-top: 32px !important;
            margin-bottom: 8px !important;
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
        .badge-critical  { background:#FFF7ED; color:#EA580C; }
        .badge-empty     { background:#FEF2F2; color:#DC2626; border: 1px solid #FECACA; }

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
            'administrator'       => 'Administrator',
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
            <div class="w-9 h-9 rounded-xl bg-indigo-500/15 border border-indigo-400/30 flex items-center justify-center text-xl flex-shrink-0">
                🧪
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

            {{-- Staf Laboratorium --}}
            @if($role === 'staf_laboratorium')
                <div class="nav-section sidebar-label">Laboratorium</div>

                <a href="{{ route('rooms') }}" class="nav-link {{ request()->routeIs('rooms') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span class="sidebar-label">Ruangan</span>
                </a>

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

                <a href="{{ route('inventory') }}" class="nav-link {{ request()->routeIs('inventory') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="sidebar-label">Update Kondisi Aset</span>
                </a>

                <a href="{{ route('inventory.history') }}" class="nav-link {{ request()->routeIs('inventory.history') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-label">History Kondisi</span>
                </a>
            @endif

            {{-- Staf Administrasi --}}
            @if($role === 'staf_administrasi')
                <div class="nav-section sidebar-label">Operasi Utama</div>

                {{-- Penerimaan Logistik (Alur Tahap 1 - 3) --}}
                <a href="{{ route('staf-admin.procurement-approved') }}" class="nav-link {{ request()->routeIs('staf-admin.procurement-approved*', 'staf-admin.goods-receipt*', 'staf-admin.inventory-label*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-3l-2 3h-6l-2-3H4"/>
                    </svg>
                    <span class="sidebar-label">Penerimaan Logistik</span>
                </a>

                <div class="nav-section sidebar-label">Inventaris</div>

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
        
        {{-- Flash Messages --}}
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 class="fixed top-20 right-6 z-[999] p-4 rounded-xl shadow-lg border backdrop-blur-md max-w-sm flex items-start gap-3
                        {{ session('success') ? 'bg-emerald-50/90 border-emerald-200' : 'bg-red-50/90 border-red-200' }}"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4">
                <div class="flex-shrink-0 mt-0.5">
                    @if(session('success'))
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold {{ session('success') ? 'text-emerald-800' : 'text-red-800' }}">
                        {{ session('success') ? 'Berhasil' : 'Gagal' }}
                    </p>
                    <p class="text-xs mt-1 {{ session('success') ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ session('success') ?? session('error') }}
                    </p>
                </div>
                <button @click="show = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        <div class="content-animate p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    @stack('modals')
    @stack('scripts')
</body>
</html>