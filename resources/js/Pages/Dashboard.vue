<script setup>
import { Head } from '@inertiajs/vue3';
import Globe from '@/Components/Globe.vue';
import { ref, onMounted, computed } from 'vue';

const metrics = ref({
    cloud_coverage: 0,
    cloud_density: 0,
    rain_intensity: 0,
    pressure: 0,
    risk_score: 0,
    risk_level: 'LOADING...',
    confidence_score: 0,
    provenance: null,
    captured_at: null,
    image_url: null
});

const selectedLocation = ref(null); // { lat, lng, temp, pressure, humidity, windSpeed, windDir }

const satellites = ref([]);
const activeLayers = ref(['COMMUNICATION', 'NAVIGATION', 'STATION', 'SCIENTIFIC', 'WEATHER', 'SPACE_DEBRIS']);

const filteredSatellites = computed(() => {
    return satellites.value.filter(s => activeLayers.value.includes(s.type));
});

const toggleLayer = (layer) => {
    if (activeLayers.value.includes(layer)) {
        activeLayers.value = activeLayers.value.filter(l => l !== layer);
    } else {
        activeLayers.value.push(layer);
    }
};

onMounted(() => {
    fetchLatestMetrics();
    fetchLiveSatellites();

    if (window.Echo) {
        window.Echo.channel('weather.live')
            .listen('.weather.updated', (e) => {
                metrics.value = e.data;
            });

        window.Echo.channel('satellites.live')
            .listen('.satellite.updated', (e) => {
                const index = satellites.value.findIndex(s => s.id === e.data.id);
                if (index !== -1) {
                    satellites.value[index] = { ...satellites.value[index], ...e.data };
                } else {
                    satellites.value.push(e.data);
                }
            });
    }
});

const fetchLatestMetrics = async () => {
    try {
        const response = await fetch('/api/v1/weather/latest', {
            headers: { 'X-API-KEY': 'vetinh_dev_key_123' }
        });
        const json = await response.json();
        if (json.status === 'success') {
            metrics.value = {
                ...json.data,
                risk_score: json.data.risk.score,
                risk_level: json.data.risk.level,
                confidence_score: json.data.risk.confidence
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
                ...s,
                ...s.last_track
            }));
        }
    } catch (e) {
        console.error('Failed to fetch satellites:', e);
    }
};

const handleSurfaceClick = async (data) => {
    selectedLocation.value = { ...data, history: [] };
    
    try {
        const response = await fetch(`/api/v1/weather/history?lat=${data.lat}&lng=${data.lng}`, {
            headers: { 'X-API-KEY': 'vetinh_dev_key_123' }
        });
        const json = await response.json();
        if (json.status === 'success') {
            selectedLocation.value.history = json.data;
        }
    } catch (e) {
        console.error('Failed to fetch location history:', e);
    }
};
</script>

<template>
    <Head title="Vetinh | Orbital Intelligence" />

    <div class="fixed inset-0 bg-[#05050a] text-white flex overflow-hidden">
        <!-- 1. Left Control Panel -->
        <aside class="w-72 glass m-6 rounded-2xl p-6 flex flex-col space-y-8 z-30 pointer-events-auto">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-vibrant-blue rounded-xl flex items-center justify-center glow-blue">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black italic tracking-tighter uppercase leading-none">Vetinh</h1>
                    <p class="text-[9px] text-white/30 uppercase tracking-[0.3em] font-bold">Orbital Explorer</p>
                </div>
            </div>

            <!-- Satellite Layers -->
            <div class="space-y-4">
                <h3 class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] mb-4">Satellite Layers</h3>
                <div class="space-y-2">
                    <button v-for="layer in ['COMMUNICATION', 'NAVIGATION', 'STATION', 'SCIENTIFIC', 'WEATHER', 'SPACE_DEBRIS']" 
                            :key="layer"
                            @click="toggleLayer(layer)"
                            class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-300 border"
                            :class="activeLayers.includes(layer) ? 'bg-white/5 border-white/10' : 'bg-transparent border-transparent opacity-40 grayscale'">
                        <div class="flex items-center space-x-3">
                            <span class="w-2 h-2 rounded-full" :class="{
                                'bg-vibrant-blue': layer === 'COMMUNICATION',
                                'bg-vibrant-green': layer === 'NAVIGATION',
                                'bg-white': layer === 'STATION',
                                'bg-vibrant-purple': layer === 'SCIENTIFIC',
                                'bg-[#10b981]': layer === 'WEATHER',
                                'bg-vibrant-orange': layer === 'SPACE_DEBRIS'
                            }"></span>
                            <span class="text-[11px] font-bold tracking-tight text-white/80 uppercase">{{ layer.toLowerCase().replace('_', ' ') }}</span>
                        </div>
                        <span class="text-[10px] font-mono text-white/30">{{ filteredSatellites.filter(s => s.type === layer).length }}</span>
                    </button>
                </div>
            </div>

            <div class="flex-1"></div>

            <!-- System Info -->
            <div class="p-4 bg-white/5 rounded-2xl border border-white/5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-white/40 uppercase">Global Health</span>
                    <span class="text-[10px] text-vibrant-green font-bold">OPTIMAL</span>
                </div>
                <div class="h-1 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-vibrant-green w-[92%]"></div>
                </div>
            </div>

                <!-- Sidebar Footer -->
                <div class="p-6 border-t border-white/5 space-y-3">
                    <a href="/admin/satellites" class="flex items-center justify-between group p-3 border border-white/10 hover:border-vibrant-blue/50 transition bg-white/[0.02]">
                        <span class="text-[10px] font-black tracking-widest uppercase text-white/40 group-hover:text-vibrant-blue transition">Admin Panel</span>
                        <div class="w-1.5 h-1.5 rounded-full bg-vibrant-blue shadow-[0_0_8px_rgba(0,136,255,0.8)]"></div>
                    </a>
                    
                    <div class="flex items-center justify-between p-3 bg-white/[0.02] border border-white/5">
                        <div class="flex flex-col">
                            <span class="text-[7px] text-white/20 uppercase font-black">System Status</span>
                            <span class="text-[10px] font-bold text-vibrant-green tracking-tighter uppercase">Optimal</span>
                        </div>
                        <div class="h-1 w-12 bg-white/5 overflow-hidden">
                            <div class="h-full bg-vibrant-green w-3/4 animate-pulse"></div>
                        </div>
                    </div>
                </div>
        </aside>

        <!-- 2. Central Globe Area -->
        <main class="flex-1 relative">
            <Globe :satellites="filteredSatellites" :weatherMetrics="metrics" @surface-click="handleSurfaceClick" />

            <!-- Top Search / HUD -->
            <div class="absolute top-8 left-1/2 -translate-x-1/2 w-[400px] z-30">
                <div class="glass flex items-center p-1 rounded-2xl border-white/10 group">
                    <div class="p-3 text-white/40">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Search Orbitals (Landsat, ISS, Himawari...)" 
                           class="flex-1 bg-transparent border-none text-xs focus:ring-0 text-white placeholder:text-white/20 font-bold uppercase tracking-tight">
                    <div class="pr-2">
                        <kbd class="px-2 py-1 bg-white/5 rounded-lg text-[10px] font-mono text-white/40">CMD+K</kbd>
                    </div>
                </div>
            </div>

            <!-- Right Analytics Panel -->
            <div class="absolute top-8 right-8 w-80 space-y-4 z-30">
                <!-- Weather Fusion Hud -->
                <div class="glass p-6 rounded-2xl border-t-2 border-vibrant-blue relative overflow-hidden group">
                    <div class="relative z-10 space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[10px] font-black text-white/50 uppercase tracking-[0.2em]">Regional Scan</h2>
                            <div class="px-2 py-0.5 bg-vibrant-blue/20 text-vibrant-blue text-[8px] rounded font-black">H9-LIVE</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-[9px] text-white/30 uppercase font-black">Cloud Coverage</p>
                                <p class="text-2xl font-black font-outfit">{{ metrics.cloud_coverage }}<span class="text-xs text-white/20 ml-0.5">%</span></p>
                            </div>
                            <div class="space-y-1 text-right">
                                <p class="text-[9px] text-white/30 uppercase font-black">Risk Score</p>
                                <p :class="{
                                    'text-vibrant-green': metrics.risk_level === 'LOW',
                                    'text-yellow-400': metrics.risk_level === 'MEDIUM',
                                    'text-vibrant-orange': metrics.risk_level === 'HIGH',
                                    'text-red-500': metrics.risk_level === 'CRITICAL'
                                }" class="text-2xl font-black font-outfit">{{ metrics.risk_score }}</p>
                            </div>
                        </div>

                        <!-- Enterprise Row 2 -->
                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-white/5">
                            <div class="space-y-0.5">
                                <p class="text-[8px] text-white/20 uppercase font-bold">Pressure</p>
                                <p class="text-xs font-bold text-white/80">{{ metrics.pressure }}<span class="text-[8px] ml-0.5 text-white/20">hPa</span></p>
                            </div>
                            <div class="space-y-0.5 text-center">
                                <p class="text-[8px] text-white/20 uppercase font-bold">Density</p>
                                <p class="text-xs font-bold text-white/80">{{ Math.round(metrics.cloud_density) }}%</p>
                            </div>
                            <div class="space-y-0.5 text-right">
                                <p class="text-[8px] text-white/20 uppercase font-bold">Confidence</p>
                                <p class="text-xs font-bold text-vibrant-blue">{{ metrics.confidence_score }}%</p>
                            </div>
                        </div>

                        <!-- Provenance Footer -->
                        <div v-if="metrics.provenance" class="flex items-center justify-between pt-2">
                             <div class="flex items-center space-x-1">
                                 <span class="text-[7px] text-white/20 uppercase">Sensor:</span>
                                 <span class="text-[7px] font-black text-white/40 uppercase">{{ metrics.provenance.sensor }}</span>
                             </div>
                             <div class="flex items-center space-x-1">
                                 <span class="text-[7px] text-white/20 uppercase">Source:</span>
                                 <span class="text-[7px] font-black text-white/40 uppercase">{{ metrics.source }}</span>
                             </div>
                        </div>
                    </div>
                    <!-- Subtle Glow in background -->
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-vibrant-blue/10 blur-3xl group-hover:bg-vibrant-blue/20 transition duration-1000"></div>
                </div>

                <!-- Vietnam Micro-Map Overlay -->
                <div class="glass p-2 rounded-2xl border border-white/5 group relative">
                    <img :src="metrics.image_url || 'https://via.placeholder.com/400x600/05050a/4f46e5?text=WAITING+FOR+H9'" 
                         class="w-full h-48 object-cover rounded-xl grayscale group-hover:grayscale-0 transition duration-700" 
                         alt="Satellite Scan">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#05050a]/80 to-transparent flex flex-col justify-end p-4">
                        <p class="text-[9px] font-black text-white uppercase tracking-widest text-center">Pacific/Vietnam Sector</p>
                    </div>
                </div>

                <!-- Legend Overlay -->
                <div class="glass rounded-2xl overflow-hidden border border-white/10">
                    <div class="flex items-center justify-between p-4 bg-white/5 border-b border-white/5">
                        <div class="flex items-center space-x-2">
                            <div class="p-1.5 bg-white/10 rounded-full">
                                <svg class="w-3.5 h-3.5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-white/90">Legend</span>
                        </div>
                        <svg class="w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div class="p-5 space-y-6">
                        <!-- CATEGORIES -->
                        <div class="space-y-3">
                            <h3 class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Categories</h3>
                            <div class="space-y-2.5">
                                <button v-for="cat in [
                                    { id: 'COMMUNICATION', label: 'Communication', color: 'bg-[#0ea5e9]' },
                                    { id: 'NAVIGATION', label: 'GPS Navigation', color: 'bg-[#22c55e]' },
                                    { id: 'SCIENTIFIC', label: 'Scientific', color: 'bg-[#a855f7]' },
                                    { id: 'SPACE_DEBRIS', label: 'Space Debris', color: 'bg-[#f97316]' }
                                ]" :key="cat.id" @click="toggleLayer(cat.id)" 
                                   class="flex items-center space-x-3 group transition w-full"
                                   :class="activeLayers.includes(cat.id) ? 'opacity-100' : 'opacity-30 grayscale cursor-pointer'">
                                    <span :class="cat.color" class="w-2.5 h-2.5 rounded-full shadow-[0_0_8px_currentColor]"></span>
                                    <span class="text-[11px] font-bold text-white/70 group-hover:text-white">{{ cat.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="h-px bg-white/5 mx-[-1.25rem]"></div>

                        <!-- ORBIT TYPES -->
                        <div class="space-y-3">
                            <h3 class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Orbit Types</h3>
                            <div class="space-y-2">
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-[10px] font-black text-white/80 w-8">LEO</span>
                                    <span class="text-[10px] font-bold text-white/30">0-2k km</span>
                                </div>
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-[10px] font-black text-white/80 w-8">MEO</span>
                                    <span class="text-[10px] font-bold text-white/30">2-36k km</span>
                                </div>
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-[10px] font-black text-white/80 w-8">GEO</span>
                                    <span class="text-[10px] font-bold text-white/30">36k km</span>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-white/5 mx-[-1.25rem]"></div>

                        <!-- KEYBOARD -->
                        <div class="space-y-3">
                            <h3 class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Keyboard</h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-2">
                                        <kbd class="px-2 py-0.5 bg-white/5 rounded text-[10px] font-mono text-white/60 border border-white/5">1-4</kbd>
                                        <span class="text-[10px] font-bold text-white/40 group-hover:text-white/60">Views</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-2">
                                        <kbd class="px-2 py-0.5 bg-white/5 rounded text-[10px] font-mono text-white/60 border border-white/5">+/-</kbd>
                                        <span class="text-[10px] font-bold text-white/40 group-hover:text-white/60">Zoom</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-2">
                                        <kbd class="px-2 py-0.5 bg-white/5 rounded text-[10px] font-mono text-white/60 border border-white/5">R</kbd>
                                        <span class="text-[10px] font-bold text-white/40 group-hover:text-white/60">Rotate</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-2">
                                        <kbd class="px-2 py-0.5 bg-white/5 rounded text-[10px] font-mono text-white/60 border border-white/5">Esc</kbd>
                                        <span class="text-[10px] font-bold text-white/40 group-hover:text-white/60">Deselect</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Time Control Panel -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30">
                <div class="glass px-8 py-3 rounded-full flex items-center space-x-8 border-white/10">
                    <button class="text-white/40 hover:text-white transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.5 3.5v13l9-6.5-9-6.5z"/></svg>
                    </button>
                    <div class="flex flex-col items-center">
                        <p class="text-[8px] text-white/30 uppercase font-black tracking-widest leading-none mb-1">Time Speed</p>
                        <div class="flex items-center space-x-3">
                            <span class="text-[10px] font-mono text-vibrant-blue font-bold tracking-tighter uppercase italic">10X Realtime</span>
                            <div class="w-32 h-1 bg-white/10 rounded-full relative">
                                <div class="absolute left-0 top-0 h-full w-[60%] bg-vibrant-blue shadow-[0_0_10px_#4f46e5] rounded-full"></div>
                                <div class="absolute left-[60%] top-1/2 -translate-y-1/2 w-2 h-2 bg-white rounded-full shadow-lg"></div>
                            </div>
                        </div>
                    </div>
                    <button class="text-white/40 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>
        </main>

        <!-- 3. Professional Weather Detail Drawer (Premium Overhaul) -->
        <transition name="slide-right">
            <aside v-if="selectedLocation" 
                   class="fixed top-6 bottom-6 right-6 w-[420px] backdrop-blur-3xl bg-black/40 z-50 rounded-[2.5rem] border border-white/10 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.6)] flex flex-col overflow-hidden animate-in slide-in-from-right duration-700 ease-out">
                
                <!-- Header: Premium Identity -->
                <div class="p-8 pb-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-vibrant-blue to-indigo-600 flex items-center justify-center shadow-lg shadow-vibrant-blue/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2 2 2 0 012 2v.656c0 .53.21 1.039.586 1.414l.439.439M9.172 9.172a4 4 0 015.656 0M9 10a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <h2 class="text-2xl font-black tracking-tight text-white leading-tight">LOCATION <span class="text-vibrant-blue italic">INTEL</span></h2>
                            <div class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-vibrant-green animate-pulse"></span>
                                <span class="text-[10px] text-white/40 uppercase font-black tracking-[0.2em]">Deep Scan Active</span>
                            </div>
                        </div>
                    </div>
                    <button @click="selectedLocation = null" class="group p-3 rounded-full hover:bg-white/10 transition-all duration-300">
                        <svg class="w-5 h-5 text-white/30 group-hover:text-white group-hover:rotate-90 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-8 pb-8 space-y-8 no-scrollbar">
                    
                    <!-- Coordinates Card -->
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-vibrant-blue/20 to-transparent rounded-3xl blur opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        <div class="relative p-5 bg-white/5 rounded-3xl border border-white/5 flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-[9px] text-vibrant-blue font-black uppercase tracking-widest">Global Coordinates</p>
                                <div class="flex items-center space-x-3">
                                    <span class="text-lg font-mono font-bold text-white">{{ selectedLocation.lat.toFixed(4) }}°N</span>
                                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                    <span class="text-lg font-mono font-bold text-white">{{ selectedLocation.lng.toFixed(4) }}°E</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-vibrant-blue/10 border border-vibrant-blue/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-vibrant-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Core Metrics: Hyper-Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Temp Card -->
                        <div class="p-6 rounded-[2rem] bg-gradient-to-br from-white/5 to-transparent border border-white/10 space-y-4 hover:border-vibrant-blue/40 transition-all duration-500">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] text-white/30 uppercase font-black tracking-widest">Air Temp</span>
                                <svg class="w-4 h-4 text-vibrant-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div class="flex items-end space-x-1">
                                <span class="text-4xl font-black text-white leading-none">{{ selectedLocation.temp }}°</span>
                                <span class="text-sm font-bold text-white/30 mb-1">C</span>
                            </div>
                            <p class="text-[10px] text-vibrant-green font-bold">Stable Trend ↗ +0.4</p>
                        </div>
                        
                        <!-- Wind Card -->
                        <div class="p-6 rounded-[2rem] bg-gradient-to-br from-white/5 to-transparent border border-white/10 space-y-4 hover:border-vibrant-green/40 transition-all duration-500">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] text-white/30 uppercase font-black tracking-widest">Wind Speed</span>
                                <svg class="w-4 h-4 text-vibrant-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </div>
                            <div class="flex items-end space-x-1">
                                <span class="text-4xl font-black text-white leading-none text-vibrant-green">{{ selectedLocation.windSpeed }}</span>
                                <span class="text-sm font-bold text-white/30 mb-1">km/h</span>
                            </div>
                            <p class="text-[10px] text-white/40 font-bold uppercase tracking-tight">Gusts: {{ selectedLocation.windGusts }} km/h</p>
                        </div>
                    </div>

                    <!-- Secondary Intelligence Layout -->
                    <div class="space-y-4">
                        <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em] pl-2">Atmospheric Multi-Scanner</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(val, label) in { 
                                'Pressure': selectedLocation.pressure + ' hPa',
                                'Humidity': selectedLocation.humidity + '%',
                                'Visibility': selectedLocation.visibility + ' km',
                                'UV Index': selectedLocation.uvIndex,
                                'Precip': selectedLocation.precip + ' mm',
                                'Clouds': selectedLocation.clouds + '%'
                            }" :key="label" 
                                 class="p-4 bg-white/[0.03] border border-white/5 rounded-2xl flex flex-col space-y-1 hover:bg-white/[0.06] transition duration-300">
                                <span class="text-[8px] text-white/40 uppercase font-bold tracking-tight">{{ label }}</span>
                                <span class="text-xs font-mono font-bold text-white">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Forecast: Premium Timeline -->
                    <div class="space-y-4 mt-8">
                        <div class="flex items-center justify-between pl-2">
                            <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">7-Day Predictive Scan</h3>
                            <span class="px-2 py-0.5 bg-indigo-500/10 border border-indigo-500/20 rounded text-[7px] text-indigo-400 font-black uppercase">Alpha AI</span>
                        </div>
                        <div class="p-5 bg-white/[0.03] border border-white/5 rounded-[2rem] flex justify-between items-center group/forecast overflow-x-auto no-scrollbar">
                            <div v-for="day in ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']" :key="day" 
                                 class="flex flex-col items-center space-y-3 min-w-[40px] px-2 py-3 rounded-xl hover:bg-white/5 transition-all duration-500 group/day cursor-default">
                                <span class="text-[8px] font-black text-white/20 group-hover/day:text-white/60 transition-colors">{{ day }}</span>
                                <div class="w-8 h-8 rounded-full bg-vibrant-blue/10 flex items-center justify-center text-vibrant-blue group-hover/day:scale-110 group-hover/day:bg-vibrant-blue transition-all duration-300 group-hover/day:text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-white">{{ Math.round(selectedLocation.temp + (Math.random() * 4 - 2)) }}°</span>
                            </div>
                        </div>
                    </div>

                    <!-- Chart: High-Fidelity Logic -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pl-2">
                            <h3 class="text-[10px] font-black text-white/20 uppercase tracking-[0.3em]">24H Dynamic Pressure</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-1 h-1 rounded-full bg-vibrant-blue shadow-[0_0_5px_#4f46e5]"></div>
                                <span class="text-[7px] text-vibrant-blue font-black uppercase tracking-widest">Live Feed</span>
                            </div>
                        </div>
                        <div class="h-28 flex items-end justify-between p-6 bg-white/[0.03] border border-white/5 rounded-[2.5rem] relative group/chart">
                            <div v-for="(point, idx) in (selectedLocation.history || Array(24).fill({pressure: 1013}))" :key="idx" 
                                 class="w-1.5 bg-vibrant-blue/20 rounded-full hover:bg-vibrant-blue transition-all duration-500 cursor-pointer relative group/bar"
                                 :style="{ height: (point.pressure ? (point.pressure - 980) / 60 * 100 : 20) + '%' }">
                                 <!-- Bar Tooltip -->
                                 <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover/bar:block backdrop-blur-md bg-white/10 border border-white/10 px-2 py-1 rounded-lg text-[8px] text-white font-mono whitespace-nowrap z-50">
                                     {{ point.time || '00:00' }}: {{ point.pressure?.toFixed(1) }} hPa
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Area -->
                    <div class="pt-4 space-y-4">
                        <div class="p-5 bg-gradient-to-r from-vibrant-blue/10 to-indigo-500/10 rounded-3xl border border-white/10 flex items-center space-x-4">
                            <div class="w-8 h-8 rounded-full bg-vibrant-green/10 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full bg-vibrant-green shadow-[0_0_8px_#10b981]"></div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-white uppercase tracking-widest leading-none">Intelligence Status: OPTIMAL</span>
                                <span class="text-[7px] text-white/30 uppercase mt-1 tracking-tighter">Sensor Fusion Verified @ Station Intel-09</span>
                            </div>
                        </div>
                        <button class="w-full relative group overflow-hidden py-5 rounded-[2rem] bg-vibrant-blue text-white shadow-[0_20px_40px_-10px_rgba(79,70,229,0.4)] transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                            <span class="relative text-[10px] font-black uppercase tracking-[0.3em]">Generate Global PDF Intel</span>
                        </button>
                    </div>

                </div>
            </aside>
        </transition>
    </div>
</template>

<style>
/* Custom animations for the UI overhaul */
@keyframes pulse-vibrant {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
}

.glow-blue {
    box-shadow: 0 0 25px rgba(79, 70, 229, 0.4);
}
</style>
