<script setup>
import { Head } from '@inertiajs/vue3';
import Globe from '@/Components/Globe.vue';
import { ref, onMounted } from 'vue';

const metrics = ref({
    cloud_coverage: 0,
    rain_intensity: 0,
    risk_score: 0,
    risk_level: 'LOADING...',
    captured_at: null,
    image_url: null
});

const satellites = ref([]);

onMounted(() => {
    // 1. Initial Data Load
    fetchLatestMetrics();
    fetchLiveSatellites();

    // 2. Setup Real-time Listeners
    if (window.Echo) {
        window.Echo.channel('weather.live')
            .listen('.weather.updated', (e) => {
                console.log('Weather Update:', e);
                metrics.value = e.data;
            });

        window.Echo.channel('satellites.live')
            .listen('.satellite.updated', (e) => {
                // Update or add satellite to list
                const index = satellites.value.findIndex(s => s.id === e.data.id);
                if (index !== -1) {
                    satellites.value[index] = e.data;
                } else {
                    satellites.value.push(e.data);
                }
            });
    }
});

const fetchLatestMetrics = async () => {
    try {
        const response = await fetch('/api/v1/weather/latest', {
            headers: { 'X-API-KEY': 'vetinh_dev_key_123' } // Using the seeded key
        });
        const json = await response.json();
        if (json.status === 'success') {
            metrics.value = {
                ...json.data,
                risk_score: json.data.risk.score,
                risk_level: json.data.risk.level
            };
        }
    } catch (e) {
        console.error('Failed to fetch metrics:', e);
    }
};

const fetchLiveSatellites = async () => {
    try {
        const response = await fetch('/api/v1/satellites/live', {
            headers: { 'X-API-KEY': 'vetinh_dev_key_123' }
        });
        const json = await response.json();
        if (json.status === 'success') {
            satellites.value = json.data.map(s => ({
                id: s.id,
                name: s.name,
                ...s.last_track
            }));
        }
    } catch (e) {
        console.error('Failed to fetch satellites:', e);
    }
};
</script>

<template>
    <Head title="Live Earth Platform" />

    <div class="flex h-screen bg-space-dark text-white overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 glass m-4 mr-0 p-6 flex flex-col space-y-8 z-20">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-vibrant-blue rounded-lg flex items-center justify-center glow-blue">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-xl font-outfit font-bold glow-text tracking-tight uppercase italic">Vetinh</h1>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-white/5 text-vibrant-blue font-semibold border-l-2 border-vibrant-blue">
                    <span>Live Monitor</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-white/40 hover:text-white transition">
                    <span>Analytics Hub</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-white/5 text-white/40 hover:text-white transition">
                    <span>API Console</span>
                </a>
            </nav>

            <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                <p class="text-[10px] text-white/40 uppercase tracking-widest mb-2 font-bold">System Pulse</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-green-400">WebSocket Connected</span>
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                </div>
            </div>
        </aside>

        <!-- Main Content (Globe) -->
        <main class="flex-1 relative">
            <Globe :satellites="satellites" />

            <!-- Floating Metrics -->
            <div class="absolute top-8 right-8 w-80 space-y-4 z-20">
                <div class="glass p-5 space-y-4 border-t-2 border-vibrant-blue">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-white/60 uppercase">Cloud Coverage</h3>
                        <span class="text-sm font-mono text-vibrant-blue">{{ metrics.cloud_coverage }}%</span>
                    </div>
                    <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-vibrant-blue transition-all duration-1000" :style="{ width: metrics.cloud_coverage + '%' }"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-white/30">
                        <span>Source: Himawari-9</span>
                        <span>{{ metrics.captured_at }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="glass p-4 text-center group hover:border-vibrant-purple transition">
                        <p class="text-[9px] text-white/40 uppercase tracking-widest mb-1 font-bold">Storm Risk</p>
                        <p class="text-2xl font-bold font-outfit text-white group-hover:text-vibrant-purple transition">{{ metrics.risk_score }}</p>
                    </div>
                    <div class="glass p-4 text-center">
                        <p class="text-[9px] text-white/40 uppercase tracking-widest mb-1 font-bold">Severity</p>
                        <p :class="{
                            'text-green-400': metrics.risk_level === 'LOW',
                            'text-yellow-400': metrics.risk_level === 'MEDIUM',
                            'text-orange-500': metrics.risk_level === 'HIGH',
                            'text-red-500': metrics.risk_level === 'CRITICAL'
                        }" class="text-xs font-black uppercase tracking-widest mt-2">{{ metrics.risk_level }}</p>
                    </div>
                </div>

                <div class="glass p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-black text-white/40 uppercase tracking-widest">Live Satellites</h3>
                        <span class="px-2 py-0.5 bg-vibrant-blue/20 text-vibrant-blue text-[9px] rounded font-bold uppercase">{{ satellites.length }} Active</span>
                    </div>
                    <div class="space-y-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="sat in satellites" :key="sat.id" class="p-3 bg-white/5 rounded-lg border border-white/5 flex flex-col space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white/80 tracking-tight">{{ sat.name }}</span>
                                <span class="text-[9px] font-mono text-white/30">ID: {{ sat.norad_id }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[9px] font-mono text-white/50">
                                <div>LAT: {{ sat.latitude }}</div>
                                <div>LNG: {{ sat.longitude }}</div>
                                <div>ALT: {{ Math.round(sat.altitude) }}km</div>
                                <div class="text-vibrant-blue">VEL: {{ Math.round(sat.velocity) }}km/s</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional Overlay (Vietnam) -->
            <div class="absolute bottom-8 right-8 z-20 glass p-2 w-48 overflow-hidden group">
                <p class="text-[8px] text-white/40 uppercase font-black text-center mb-2">Vietnam Infrared (H9)</p>
                <img :src="metrics.image_url || 'https://via.placeholder.com/200x300/16161a/4f46e5?text=WAITING+FOR+H9'" 
                     class="w-full h-auto rounded-lg grayscale group-hover:grayscale-0 transition duration-500" 
                     alt="Vietnam Weather">
            </div>
        </main>
    </div>
</template>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 2px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(79, 70, 229, 0.3);
}
</style>
