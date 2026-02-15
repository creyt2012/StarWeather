<script setup>
import { onMounted, ref, onUnmounted } from 'vue';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';

const props = defineProps({
    satellites: {
        type: Array,
        default: () => []
    }
});

const globeContainer = ref(null);
let scene, camera, renderer, globe, controls;

onMounted(() => {
    initScene();
    animate();
});

onUnmounted(() => {
    if (renderer) renderer.dispose();
});

const initScene = () => {
    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 2.5;

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(globeContainer.value.clientWidth, globeContainer.value.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    globeContainer.value.appendChild(renderer.domElement);

    // Light
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
    const sunLight = new THREE.DirectionalLight(0xffffff, 1);
    sunLight.position.set(5, 3, 5);
    scene.add(sunLight);

    // Globe
    const geometry = new THREE.SphereGeometry(1, 64, 64);
    const textureLoader = new THREE.TextureLoader();
    
    // Using a placeholder earth texture if local assets don't exist
    const material = new THREE.MeshPhongMaterial({
        map: textureLoader.load('https://unpkg.com/three-globe/example/img/earth-blue-marble.jpg'),
        bumpMap: textureLoader.load('https://unpkg.com/three-globe/example/img/earth-topology.png'),
        bumpScale: 0.05,
        specularMap: textureLoader.load('https://unpkg.com/three-globe/example/img/earth-water-mask.png'),
        specular: new THREE.Color('grey')
    });
    
    globe = new THREE.Mesh(geometry, material);
    scene.add(globe);

    // Controls
    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.5;

    window.addEventListener('resize', onWindowResize);
};

const onWindowResize = () => {
    camera.aspect = globeContainer.value.clientWidth / globeContainer.value.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(globeContainer.value.clientWidth, globeContainer.value.clientHeight);
};

const animate = () => {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
};
</script>

<template>
    <div ref="globeContainer" class="w-full h-full relative cursor-grab active:cursor-grabbing">
        <div class="absolute bottom-4 left-4 z-10 pointer-events-none">
            <div class="flex items-center space-x-2 text-xs text-white/50 uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span>Live Satellite Engine Active</span>
            </div>
        </div>
    </div>
</template>
