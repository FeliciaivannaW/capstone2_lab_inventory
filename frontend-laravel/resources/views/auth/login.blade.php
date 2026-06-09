<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Labventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        navy: '#0F172A',
                        indigo: { 400: '#818CF8', 500: '#6366F1', 600: '#4F46E5', 700: '#4338CA' },
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }

        /* Page load animations */
        @keyframes slideInLeft {
            from { transform: translateX(-32px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .panel-left  { animation: slideInLeft 0.55s cubic-bezier(0.22,1,0.36,1) forwards; }
        .panel-right { animation: fadeIn 0.8s ease forwards; }

        /* Animated mesh blobs */
        @keyframes blob1 {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(50px,-40px) scale(1.12); }
            66%      { transform: translate(-25px,30px) scale(0.92); }
        }
        @keyframes blob2 {
            0%,100% { transform: translate(0,0) scale(1); }
            40%      { transform: translate(-40px,50px) scale(1.08); }
            70%      { transform: translate(35px,-20px) scale(0.88); }
        }
        @keyframes blob3 {
            0%,100% { transform: translate(0,0) scale(1); }
            50%      { transform: translate(25px,35px) scale(1.18); }
        }
        .blob-1 { animation: blob1 12s ease-in-out infinite; }
        .blob-2 { animation: blob2 16s ease-in-out infinite; }
        .blob-3 { animation: blob3 10s ease-in-out infinite; }

        /* Floating label inputs */
        .field-wrap { position: relative; }
        .field-wrap input {
            width: 100%;
            padding: 1.15rem 1rem 0.45rem 1rem;
            background: #fff;
            border: 1.5px solid #E2E8F0;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            color: #0F172A;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .field-wrap input:focus {
            border-color: #6366F1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.14);
        }
        .field-wrap label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.875rem;
            color: #94A3B8;
            pointer-events: none;
            transition: all 0.18s ease;
        }
        .field-wrap input:focus + label,
        .field-wrap input:not(:placeholder-shown) + label {
            top: 0.6rem;
            transform: none;
            font-size: 0.68rem;
            font-weight: 600;
            color: #6366F1;
            letter-spacing: 0.02em;
        }

        /* Button shimmer on hover */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .btn-sign-in {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 50%, #6366F1 100%);
            background-size: 200% auto;
            transition: background-position 0.4s ease, transform 0.15s ease, box-shadow 0.2s ease;
        }
        .btn-sign-in:hover:not(:disabled) {
            animation: shimmer 1.4s linear infinite;
            box-shadow: 0 8px 24px rgba(99,102,241,0.4);
            transform: translateY(-1px);
        }
        .btn-sign-in:active:not(:disabled) { transform: translateY(0); }

        /* Thin indigo divider between panels */
        .panel-divider {
            position: absolute;
            right: 0; top: 8%; height: 84%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(99,102,241,0.45), transparent);
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 lg:p-8"
      x-data="{ loading: false, showPass: false }">

    <div class="w-full max-w-5xl min-h-[620px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

        {{-- ───────────── LEFT: Form Panel ───────────── --}}
        <div class="panel-left relative lg:w-[42%] bg-[#F8FAFC] px-8 py-12 lg:px-12 flex flex-col justify-center">
            <div class="panel-divider hidden lg:block"></div>

            {{-- Back to Home --}}
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-indigo-600 transition-colors mb-10 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>

            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-10">
                <svg width="38" height="38" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Database cylinder -->
                    <rect x="8" y="22" width="24" height="10" fill="#4F46E5" rx="2"/>
                    <ellipse cx="20" cy="22" rx="12" ry="4" fill="#818CF8"/>
                    <ellipse cx="20" cy="32" rx="12" ry="4" fill="#4338CA"/>
                    <!-- Flask body -->
                    <path d="M15 7 L13 22 L27 22 L25 7" fill="#C7D2FE" stroke="#6366F1" stroke-width="1.5" stroke-linejoin="round"/>
                    <!-- Flask mouth -->
                    <rect x="14" y="4" width="12" height="4" rx="2" fill="#6366F1"/>
                    <!-- Liquid inside flask -->
                    <path d="M14 17 Q20 20 26 17 L27 22 L13 22 Z" fill="#6366F1" opacity="0.55"/>
                    <circle cx="17" cy="14" r="1.5" fill="#6366F1" opacity="0.35"/>
                    <circle cx="23" cy="12" r="1"   fill="#6366F1" opacity="0.35"/>
                </svg>
                <span class="text-xl font-bold text-slate-900 tracking-tight">Labventory</span>
            </div>

            {{-- Heading --}}
            <h1 class="text-[2rem] font-bold text-slate-900 leading-tight mb-1">Welcome back</h1>
            <p class="text-sm text-slate-500 mb-7 leading-relaxed">Sign in to your workspace to manage lab assets and procurement.</p>

            {{-- Alerts --}}
            @if(session('error'))
                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('login.process') }}" @submit="loading = true" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div class="field-wrap">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder=" " required>
                    <label for="email">Email address</label>
                </div>

                {{-- Password --}}
                <div class="field-wrap relative">
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" placeholder=" " required style="padding-right:3rem;">
                    <label for="password">Password</label>
                    <button type="button" @click="showPass = !showPass"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-500 transition-colors p-1">
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>

                {{-- Forgot password --}}
                <div class="flex justify-end -mt-1">
                    <a href="{{ route('forgot.password') }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                        Forgot password?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit" :disabled="loading"
                        class="btn-sign-in w-full py-3.5 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2 mt-1"
                        :class="loading ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'">
                    <template x-if="!loading">
                        <span class="flex items-center gap-2">
                            Sign In
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </template>
                    <template x-if="loading">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Signing in...
                        </span>
                    </template>
                </button>
            </form>

            {{-- Demo accounts --}}
            <div class="mt-6 bg-white border border-slate-200 rounded-2xl p-4">
                <p class="text-[0.68rem] font-semibold text-slate-400 uppercase tracking-widest mb-2.5">Demo Accounts</p>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500"><span class="font-semibold text-slate-700 w-24">Admin</span><span>admin@example.com / password</span></div>
                    <div class="flex justify-between text-slate-500"><span class="font-semibold text-slate-700 w-24">Ka. Lab</span><span>kalab@example.com / password</span></div>
                    <div class="flex justify-between text-slate-500"><span class="font-semibold text-slate-700 w-24">Ka. Prodi</span><span>kaprodi@example.com / password</span></div>
                    <div class="flex justify-between text-slate-500"><span class="font-semibold text-slate-700 w-24">Staf Admin</span><span>stafadmin@example.com / password</span></div>
                    <div class="flex justify-between text-slate-500"><span class="font-semibold text-slate-700 w-24">Staf Lab</span><span>staflab@example.com / password</span></div>
                </div>
            </div>

            <p class="text-center text-[0.7rem] text-slate-400 mt-5">© 2026 Labventory · All rights reserved</p>
        </div>

        {{-- ───────────── RIGHT: Branding Panel ───────────── --}}
        <div class="panel-right hidden lg:flex relative flex-1 bg-[#0F172A] items-center justify-center overflow-hidden">

            {{-- Animated mesh blobs --}}
            <div class="blob-1 absolute w-[420px] h-[420px] rounded-full pointer-events-none"
                 style="background:radial-gradient(circle at center,rgba(99,102,241,0.35),transparent 65%);top:-80px;right:-60px;"></div>
            <div class="blob-2 absolute w-[360px] h-[360px] rounded-full pointer-events-none"
                 style="background:radial-gradient(circle at center,rgba(124,58,237,0.28),transparent 65%);bottom:-70px;left:-40px;"></div>
            <div class="blob-3 absolute w-[280px] h-[280px] rounded-full pointer-events-none"
                 style="background:radial-gradient(circle at center,rgba(129,140,248,0.2),transparent 65%);top:40%;left:35%;"></div>

            {{-- Content --}}
            <div class="relative z-10 text-center px-10 max-w-lg">

                {{-- Lab illustration --}}
                <div class="flex justify-center mb-8">
                    <svg width="290" height="195" viewBox="0 0 290 195" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Lab bench surface -->
                        <rect x="18" y="138" width="254" height="11" rx="3" fill="#1E293B" stroke="#334155" stroke-width="1"/>
                        <rect x="28" y="149" width="8" height="38" rx="2" fill="#1E293B" stroke="#334155" stroke-width="1"/>
                        <rect x="254" y="149" width="8" height="38" rx="2" fill="#1E293B" stroke="#334155" stroke-width="1"/>

                        <!-- Monitor frame -->
                        <rect x="85" y="74" width="120" height="62" rx="5" fill="#1E293B" stroke="#334155" stroke-width="1.5"/>
                        <rect x="89" y="78" width="112" height="54" rx="3" fill="#0A0F1E"/>
                        <!-- Monitor stand -->
                        <rect x="135" y="136" width="20" height="4" rx="1" fill="#334155"/>
                        <rect x="128" y="138" width="34" height="2.5" rx="1" fill="#334155"/>

                        <!-- Chart bars on screen -->
                        <rect x="97"  y="117" width="9" height="12" rx="1" fill="#6366F1" opacity="0.75"/>
                        <rect x="110" y="110" width="9" height="19" rx="1" fill="#6366F1"/>
                        <rect x="123" y="100" width="9" height="29" rx="1" fill="#818CF8"/>
                        <rect x="136" y="112" width="9" height="17" rx="1" fill="#6366F1" opacity="0.7"/>
                        <rect x="149" y="96"  width="9" height="21" rx="1" fill="#A5B4FC"/>
                        <rect x="162" y="105" width="9" height="24" rx="1" fill="#6366F1" opacity="0.6"/>
                        <!-- Grid lines -->
                        <line x1="93" y1="118" x2="177" y2="118" stroke="#1E293B" stroke-width="1"/>
                        <line x1="93" y1="110" x2="177" y2="110" stroke="#1E293B" stroke-width="1"/>
                        <line x1="93" y1="102" x2="177" y2="102" stroke="#1E293B" stroke-width="1"/>

                        <!-- Flask left -->
                        <path d="M45 84 L40 128 Q38 133 44 136 L68 136 Q74 133 72 128 L67 84 Z"
                              fill="#0F172A" stroke="#6366F1" stroke-width="1.5" stroke-linejoin="round"/>
                        <!-- Liquid in flask -->
                        <path d="M42 124 Q56 130 70 124 L72 128 Q74 133 68 136 L44 136 Q38 133 40 128 Z"
                              fill="#6366F1" opacity="0.65"/>
                        <!-- Flask neck -->
                        <rect x="48" y="77" width="16" height="8" rx="3" fill="#1E293B" stroke="#6366F1" stroke-width="1.5"/>
                        <!-- Bubbles -->
                        <circle cx="52" cy="112" r="3" fill="#818CF8" opacity="0.45"/>
                        <circle cx="62" cy="104" r="2" fill="#818CF8" opacity="0.45"/>

                        <!-- Inventory boxes right -->
                        <rect x="208" y="98"  width="40" height="28" rx="3" fill="#1E293B" stroke="#334155" stroke-width="1.5"/>
                        <rect x="212" y="103" width="40" height="28" rx="3" fill="#1E293B" stroke="#6366F1" stroke-width="1" opacity="0.5"/>
                        <rect x="216" y="108" width="40" height="28" rx="3" fill="#1E293B" stroke="#6366F1" stroke-width="1.5"/>
                        <rect x="224" y="119" width="22" height="3"  rx="1" fill="#6366F1" opacity="0.6"/>
                        <rect x="224" y="125" width="15" height="2"  rx="1" fill="#6366F1" opacity="0.35"/>

                        <!-- Floating accent dots -->
                        <circle cx="52"  cy="55" r="4" fill="#6366F1" opacity="0.35"/>
                        <circle cx="238" cy="60" r="3" fill="#818CF8" opacity="0.35"/>
                        <circle cx="200" cy="75" r="2" fill="#A5B4FC" opacity="0.4"/>
                    </svg>
                </div>

                {{-- Heading --}}
                <h2 class="text-3xl font-bold text-white mb-3 leading-tight">
                    Smart <span class="text-indigo-400 underline decoration-indigo-500/40 underline-offset-4">Lab</span> Operations
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
                    Digitize your laboratory assets, track procurement cycles, and manage consumables — all in one place.
                </p>

                {{-- Feature pills --}}
                <div class="flex items-center justify-center gap-3 flex-wrap">
                    @foreach([['🧪','Asset Tracking'],['📦','Procurement'],['🔧','Maintenance Logs']] as $pill)
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-medium text-white border border-white/10"
                             style="background:rgba(255,255,255,0.06);backdrop-filter:blur(8px);">
                            <span>{{ $pill[0] }}</span> {{ $pill[1] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</body>
</html>
