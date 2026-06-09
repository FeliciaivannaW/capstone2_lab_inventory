<div id="voxel-dog-container" class="relative w-56 h-56 md:w-64 md:h-64 mx-auto cursor-grab active:cursor-grabbing">
    <!-- Spinner -->
    <div id="dog-spinner" class="absolute inset-0 flex items-center justify-center">
        <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dogContainer = document.getElementById('voxel-dog-container');
    const dogSpinner = document.getElementById('dog-spinner');

    if (!dogContainer || typeof THREE === 'undefined') return;

    const scW = dogContainer.clientWidth;
    const scH = dogContainer.clientHeight;

    const dogRenderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true
    });
    dogRenderer.setPixelRatio(window.devicePixelRatio);
    dogRenderer.setSize(scW, scH);
    dogRenderer.outputEncoding = THREE.sRGBEncoding;
    dogContainer.appendChild(dogRenderer.domElement);

    const dogScene = new THREE.Scene();

    const target = new THREE.Vector3(-0.5, 1.2, 0);
    const initialCameraPosition = new THREE.Vector3(
        20 * Math.sin(0.2 * Math.PI),
        10,
        20 * Math.cos(0.2 * Math.PI)
    );

    const scale = scH * 0.005 + 4.8;
    const dogCamera = new THREE.OrthographicCamera(
        -scale, scale, scale, -scale, 0.01, 50000
    );
    dogCamera.position.copy(initialCameraPosition);
    dogCamera.lookAt(target);

    const ambientLight = new THREE.AmbientLight(0xcccccc, Math.PI);
    dogScene.add(ambientLight);

    const controls = new THREE.OrbitControls(dogCamera, dogRenderer.domElement);
    controls.autoRotate = true;
    controls.target = target;

    const urlDogGLB = '{{ asset("dog.glb") }}';
    const loader = new THREE.GLTFLoader();
    
    const dracoLoader = new THREE.DRACOLoader();
    dracoLoader.setDecoderConfig({ type: 'js' });
    dracoLoader.setDecoderPath('https://www.gstatic.com/draco/versioned/decoders/1.4.1/');
    loader.setDRACOLoader(dracoLoader);
    
    loader.load(urlDogGLB, (gltf) => {
        const obj = gltf.scene;
        obj.name = 'dog';
        obj.position.y = 0;
        obj.position.x = 0;
        dogScene.add(obj);

        if (dogSpinner) {
            dogSpinner.style.display = 'none';
        }

        let frame = 0;
        function easeOutCirc(x) {
            return Math.sqrt(1 - Math.pow(x - 1, 4));
        }

        function animateDog() {
            requestAnimationFrame(animateDog);

            frame = frame <= 100 ? frame + 1 : frame;

            if (frame <= 100) {
                const p = initialCameraPosition;
                const rotSpeed = -easeOutCirc(frame / 120) * Math.PI * 20;

                dogCamera.position.y = 10;
                dogCamera.position.x = p.x * Math.cos(rotSpeed) + p.z * Math.sin(rotSpeed);
                dogCamera.position.z = p.z * Math.cos(rotSpeed) - p.x * Math.sin(rotSpeed);
                dogCamera.lookAt(target);
            } else {
                controls.update();
            }

            dogRenderer.render(dogScene, dogCamera);
        }
        animateDog();
    }, undefined, (error) => {
        console.error('Error loading Voxel Dog:', error);
        if (dogSpinner) dogSpinner.style.display = 'none';
        dogContainer.innerHTML += '<p class="text-sm text-red-400 absolute bottom-0 left-0 right-0 text-center">Gagal memuat model 3D.</p>';
    });

    window.addEventListener('resize', () => {
        if(dogContainer && dogRenderer) {
            const newW = dogContainer.clientWidth;
            const newH = dogContainer.clientHeight;
            dogRenderer.setSize(newW, newH);
            
            const newScale = newH * 0.005 + 4.8;
            dogCamera.left = -newScale;
            dogCamera.right = newScale;
            dogCamera.top = newScale;
            dogCamera.bottom = -newScale;
            dogCamera.updateProjectionMatrix();
        }
    });
});
</script>
