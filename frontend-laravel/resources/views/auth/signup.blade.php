<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Labventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        @keyframes slideInLeft {
            from { transform: translateX(-32px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes blob1 {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(50px,-40px) scale(1.12); }
            66%      { transform: translate(-25px,30px) scale(0.92); }
        }
        .panel-left  { animation: slideInLeft 0.55s cubic-bezier(0.22,1,0.36,1) forwards; }
        .panel-right { animation: fadeIn 0.8s ease forwards; }
        .blob-1 { animation: blob1 12s ease-in-out infinite; }
        .panel-divider {
            position: absolute; right: 0; top: 8%; height: 84%; width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(99,102,241,0.45), transparent);
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 lg:p-8">

    <div class="w-full max-w-5xl min-h-[540px] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

        {{-- Left --}}
        <div class="panel-left relative lg:w-[42%] bg-[#F8FAFC] px-8 py-12 lg:px-12 flex flex-col justify-center">
            <div class="panel-divider hidden lg:block"></div>

            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-10">
                <svg width="38" height="38" viewBox="0 0 40 40" fill="none">
                    <rect x="8" y="22" width="24" height="10" fill="#4F46E5" rx="2"/>
                    <ellipse cx="20" cy="22" rx="12" ry="4" fill="#818CF8"/>
                    <ellipse cx="20" cy="32" rx="12" ry="4" fill="#4338CA"/>
                    <path d="M15 7 L13 22 L27 22 L25 7" fill="#C7D2FE" stroke="#6366F1" stroke-width="1.5" stroke-linejoin="round"/>
                    <rect x="14" y="4" width="12" height="4" rx="2" fill="#6366F1"/>
                    <path d="M14 17 Q20 20 26 17 L27 22 L13 22 Z" fill="#6366F1" opacity="0.55"/>
                </svg>
                <span class="text-xl font-bold text-slate-900 tracking-tight">Labventory</span>
            </div>

            <h1 class="text-[2rem] font-bold text-slate-900 leading-tight mb-2">Create Account</h1>
            <p class="text-sm text-slate-500 mb-8 leading-relaxed">Account registration for Labventory System.</p>

            {{-- Info box --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-indigo-800 mb-1">Fitur dalam Pengembangan</p>
                        <p class="text-sm text-indigo-600 leading-relaxed">
                            Sign Up sedang dalam pengembangan. Akun saat ini dikelola oleh Administrator.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center gap-2 w-full py-3.5 rounded-xl font-semibold text-white text-sm bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Sign In
            </a>

            <p class="text-center text-[0.7rem] text-slate-400 mt-6">© 2025 Labventory · All rights reserved</p>
        </div>

        {{-- Right --}}
        <div class="panel-right hidden lg:flex relative flex-1 bg-[#0F172A] items-center justify-center overflow-hidden">
            <div class="blob-1 absolute w-[420px] h-[420px] rounded-full pointer-events-none"
                 style="background:radial-gradient(circle at center,rgba(99,102,241,0.35),transparent 65%);top:-80px;right:-60px;"></div>
            <div class="absolute w-[300px] h-[300px] rounded-full pointer-events-none"
                 style="background:radial-gradient(circle at center,rgba(124,58,237,0.2),transparent 65%);bottom:-60px;left:-30px;"></div>

            <div class="relative z-10 text-center px-10 max-w-md">
                <div class="w-20 h-20 rounded-2xl bg-white/10 flex items-center justify-center mx-auto mb-6 border border-white/10">
                    <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-3">Manajemen Akun</h2>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Akun pengguna dibuat dan dikelola oleh Administrator melalui panel manajemen pengguna.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
