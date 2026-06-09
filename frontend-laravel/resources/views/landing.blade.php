<!DOCTYPE html>
<html lang="id" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Inventory System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            background-color: transparent;
            color: #f8fafc;
        }
        
        /* Real Lab Background with Parallax Layer */
        #parallax-bg {
            position: fixed;
            top: -5%;
            left: -5%;
            width: 110vw;
            height: 110vh;
            background-image: url('{{ asset("images/lab_bg.png") }}');
            background-size: cover;
            background-position: center;
            z-index: 0;
            transition: transform 0.1s ease-out;
            pointer-events: none;
        }
        
        /* Overlay to darken the background so text is readable */
        #bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.8));
            z-index: 1;
            pointer-events: none;
        }

        #webgl-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 2;
            pointer-events: none;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .card-glass {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/loaders/DRACOLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/examples/js/controls/OrbitControls.js"></script>
</head>
<body class="antialiased min-h-screen flex flex-col relative text-slate-100">

    <!-- Background Elements -->
    <div id="parallax-bg"></div>
    <div id="bg-overlay"></div>
    <div id="webgl-container"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-panel">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-xl shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-2xl tracking-tight">LabInventory<span class="text-indigo-400">.</span></span>
                </div>
                <div>
                    @if (session()->has('auth_token'))
                        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium transition-all shadow-[0_0_15px_rgba(79,70,229,0.4)] hover:shadow-[0_0_25px_rgba(79,70,229,0.6)] relative z-50">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-full bg-white text-slate-900 hover:bg-slate-200 font-semibold transition-all relative z-50">Masuk</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center pt-28 pb-12 px-4 sm:px-6 lg:px-8 relative" style="z-index: 10;">
        <div class="text-center max-w-5xl mx-auto flex flex-col items-center">
            
            <!-- Voxel Dog Component -->
            <div class="-mb-6 md:-mb-8 relative z-20 pointer-events-auto">
                <x-voxel-dog />
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 leading-tight drop-shadow-lg">
                Kelola Laboratorium Anda dengan <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400">Lebih Cerdas</span>
            </h1>
            <p class="text-base md:text-lg text-slate-200 mb-6 max-w-4xl mx-auto leading-relaxed drop-shadow-md">
                Tingkatkan efisiensi pengelolaan aset, pengadaan material, hingga pencatatan perawatan inventaris dalam satu platform digital yang seamless.
            </p>
            <div class="flex flex-col sm:flex-row gap-5 justify-center">
                @if (session()->has('auth_token'))
                    <a href="{{ route('dashboard') }}" class="px-8 py-4 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-lg transition-all transform hover:-translate-y-1 shadow-[0_10px_20px_rgba(79,70,229,0.3)] border border-indigo-500">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-lg transition-all transform hover:-translate-y-1 shadow-[0_10px_20px_rgba(79,70,229,0.3)] border border-indigo-500">
                        Mulai Sekarang
                    </a>
                @endif
                <a href="#features" class="px-8 py-4 rounded-full bg-slate-800/60 hover:bg-slate-700/80 border border-slate-500 text-white font-semibold text-lg transition-all backdrop-blur-md shadow-lg relative z-50">
                    Pelajari Fitur
                </a>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="py-24 relative" style="z-index: 10;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-bold mb-6 drop-shadow-md">Fitur Utama</h2>
                <p class="text-slate-300 max-w-2xl mx-auto text-lg drop-shadow-sm">Platform kami dirancang khusus untuk memenuhi kompleksitas manajemen laboratorium modern dengan antarmuka yang intuitif.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card-glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group shadow-xl relative z-20">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-500/30 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-300 border border-indigo-400/40">
                        <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Asset Tracking</h3>
                    <p class="text-slate-300 leading-relaxed text-lg">
                        Pantau setiap aset laboratorium secara real-time. Ketahui lokasi, status ketersediaan, dan riwayat penggunaan peralatan dengan data yang akurat.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="card-glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group shadow-xl relative z-20">
                    <div class="w-16 h-16 rounded-2xl bg-purple-500/30 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-300 border border-purple-400/40">
                        <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Procurement</h3>
                    <p class="text-slate-300 leading-relaxed text-lg">
                        Sistem pengadaan terintegrasi. Ajukan, setujui, dan lacak status pemesanan barang baru maupun bahan habis pakai (BHP) dengan transparan.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="card-glass p-8 rounded-3xl hover:-translate-y-2 transition-all duration-300 group shadow-xl relative z-20">
                    <div class="w-16 h-16 rounded-2xl bg-pink-500/30 flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-300 border border-pink-400/40">
                        <svg class="w-8 h-8 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Maintenance Log</h3>
                    <p class="text-slate-300 leading-relaxed text-lg">
                        Catat dan jadwalkan perawatan peralatan secara teratur. Hindari kerusakan tak terduga dengan histori perbaikan yang jelas dan tertata.
                    </p>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="py-10 text-center text-slate-400 glass-panel border-t-0 mt-12 relative" style="z-index: 10;">
        <p class="font-medium">&copy; {{ date('Y') }} Lab Inventory System. All rights reserved.</p>
    </footer>

    <!-- Parallax Background & Three.js Implementation -->
    <script>
        // Parallax Effect for the Background Image
        const bgElement = document.getElementById('parallax-bg');
        
        let mouseX = 0;
        let mouseY = 0;
        const windowHalfX = window.innerWidth / 2;
        const windowHalfY = window.innerHeight / 2;

        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX - windowHalfX);
            mouseY = (event.clientY - windowHalfY);
            
            // Background Image Parallax
            // Moving in opposite direction of mouse
            const xOffset = -(mouseX * 0.02);
            const yOffset = -(mouseY * 0.02);
            
            if(bgElement) {
                bgElement.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
            }
        });

        // Inisialisasi Three.js untuk Efek Partikel/Overlay Data Node
        const container = document.getElementById('webgl-container');
        const scene = new THREE.Scene();

        const camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 1, 1000);
        camera.position.z = 200;

        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); 
        container.appendChild(renderer.domElement);

        const particleCount = 200;
        const particles = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const velocities = [];

        for (let i = 0; i < particleCount; i++) {
            positions[i * 3] = (Math.random() - 0.5) * 600;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 600;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 600;

            velocities.push({
                x: (Math.random() - 0.5) * 0.3,
                y: (Math.random() - 0.5) * 0.3,
                z: (Math.random() - 0.5) * 0.3
            });
        }

        particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const particleMaterial = new THREE.PointsMaterial({
            color: 0x818cf8, // Indigo color
            size: 2.5,
            transparent: true,
            opacity: 0.6,
            sizeAttenuation: true
        });

        const particleSystem = new THREE.Points(particles, particleMaterial);
        scene.add(particleSystem);

        const lineMaterial = new THREE.LineBasicMaterial({
            color: 0x818cf8,
            transparent: true,
            opacity: 0.1
        });

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        let lineMesh; 
        let targetX = 0;
        let targetY = 0;

        function animate() {
            requestAnimationFrame(animate);

            particleSystem.rotation.y += 0.0005;
            particleSystem.rotation.x += 0.0002;

            // Parallax pergerakan kamera Three.js mengikuti mouse
            targetX = mouseX * 0.08;
            targetY = mouseY * 0.08;
            
            camera.position.x += (targetX - camera.position.x) * 0.02;
            camera.position.y += (-targetY - camera.position.y) * 0.02;
            camera.lookAt(scene.position);

            const positionsAttr = particleSystem.geometry.attributes.position;
            
            for (let i = 0; i < particleCount; i++) {
                positionsAttr.array[i * 3] += velocities[i].x;
                positionsAttr.array[i * 3 + 1] += velocities[i].y;
                positionsAttr.array[i * 3 + 2] += velocities[i].z;

                if (Math.abs(positionsAttr.array[i * 3]) > 300) velocities[i].x *= -1;
                if (Math.abs(positionsAttr.array[i * 3 + 1]) > 300) velocities[i].y *= -1;
                if (Math.abs(positionsAttr.array[i * 3 + 2]) > 300) velocities[i].z *= -1;
            }
            positionsAttr.needsUpdate = true;

            const linePositions = [];
            
            for (let i = 0; i < particleCount; i++) {
                for (let j = i + 1; j < particleCount; j++) {
                    const dx = positionsAttr.array[i * 3] - positionsAttr.array[j * 3];
                    const dy = positionsAttr.array[i * 3 + 1] - positionsAttr.array[j * 3 + 1];
                    const dz = positionsAttr.array[i * 3 + 2] - positionsAttr.array[j * 3 + 2];
                    const distSq = dx * dx + dy * dy + dz * dz;

                    if (distSq < 4000) {
                        linePositions.push(
                            positionsAttr.array[i * 3], positionsAttr.array[i * 3 + 1], positionsAttr.array[i * 3 + 2],
                            positionsAttr.array[j * 3], positionsAttr.array[j * 3 + 1], positionsAttr.array[j * 3 + 2]
                        );
                    }
                }
            }

            if (lineMesh) {
                scene.remove(lineMesh);
                lineMesh.geometry.dispose();
            }

            if(linePositions.length > 0) {
                const linesGeometry = new THREE.BufferGeometry();
                linesGeometry.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
                lineMesh = new THREE.LineSegments(linesGeometry, lineMaterial);
                scene.add(lineMesh);
            }

            renderer.render(scene, camera);
        }

        animate();
    </script>
</body>
</html>
