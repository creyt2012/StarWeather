<script setup>
import { Head } from '@inertiajs/vue3';
import Globe from '@/Components/Globe.vue';
import { ref, onMounted } from 'vue';

const metrics = ref({
    cloud_coverage: 45.2,
    rain_intensity: 1.2,
    risk_score: 15,
    risk_level: 'LOW'
});

const activeSatellites = ref([
    { id: 'HIMAWARI-9', status: 'ACTIVE' },
    { id: 'SENTINEL-2', status: 'IDLE' },
    { id: 'ISS', status: 'TRACKING' }
]);
</script>

<template>
    <Head title="Real-time Weather Intelligence" />

    <div class="flex h-screen bg-space-dark text-white overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 glass m-4 mr-0 p-6 flex flex-col space-y-8">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-vibrant-blue rounded-lg flex items-center justify-center glow-blue">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-xl font-outfit font-bold glow-text tracking-tight">StarWeather</h1>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-white/5 text-vibrant-blue font-semibold">
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-white/60 hover:text-white transition">
                    <span>Weather Layers</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-white/60 hover:text-white transition">
                    <span>Satellites</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-white/60 hover:text-white transition">
                    <span>Risk Alerts</span>
                </a>
            </nav>

            <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                <p class="text-[10px] text-white/40 uppercase tracking-tighter mb-2">Current Plan</p>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-vibrant-purple">ENTERPRISE</span>
                    <span class="text-xs text-white/30 italic">Active</span>
                </div>
            </div>
        </aside>

        <!-- Main Content (Globe) -->
        <main class="flex-1 relative">
            <Globe />

            <!-- Floating Metrics -->
            <div class="absolute top-8 right-8 w-80 space-y-4">
                <div class="glass p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-white/60">Global Cloud Coverage</h3>
                        <span class="text-xs text-green-400 font-mono">{{ metrics.cloud_coverage }}%</span>
                    </div>
                    <div class="h-1 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-vibrant-blue glow-blue" :style="{ width: metrics.cloud_coverage + '%' }"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="glass p-4 text-center">
                        <p class="text-[10px] text-white/40 uppercase mb-1">Risk Index</p>
                        <p class="text-xl font-bold font-outfit text-yellow-500">{{ metrics.risk_score }}</p>
                    </div>
                    <div class="glass p-4 text-center">
                        <p class="text-[10px] text-white/40 uppercase mb-1">Severity</p>
                        <p class="text-xl font-bold font-outfit text-green-500 uppercase text-xs">{{ metrics.risk_level }}</p>
                    </div>
                </div>

                <div class="glass p-5">
                    <h3 class="text-xs font-bold text-white/40 uppercase tracking-widest mb-4">Tracking Satellites</h3>
                    <div class="space-y-3">
                        <div v-for="sat in activeSatellites" :key="sat.id" class="flex items-center justify-between">
                            <span class="text-xs font-mono text-white/80">{{ sat.id }}</span>
                            <span :class="sat.status === 'ACTIVE' ? 'text-green-400' : 'text-white/20'" class="text-[10px] font-bold">
                                {{ sat.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
