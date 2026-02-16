<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    satellites: Array
});

const editingSatellite = ref(null);
const selectedSatellite = ref(null);
const showAddModal = ref(false);
const mapContainer = ref(null);
let map = null;
let orbitLayer = null;

const selectSatellite = async (sat) => {
    selectedSatellite.value = sat;
    await nextTick();
    initMap();
};

const initMap = () => {
    if (map) {
        map.remove();
        map = null;
    }

    if (!mapContainer.value) return;

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([0, 0], 2);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    // Mock orbital path
    const points = [];
    for (let i = 0; i <= 360; i += 10) {
        points.push([
            30 * Math.sin((i * Math.PI) / 180),
            i - 180
        ]);
    }

    orbitLayer = L.polyline(points, {
        color: '#0088ff',
        weight: 1,
        dashArray: '5, 10',
        opacity: 0.5
    }).addTo(map);

    L.circleMarker([0, 0], {
        radius: 6,
        fillColor: '#0088ff',
        color: '#fff',
        weight: 2,
        fillOpacity: 1
    }).addTo(map);
};

const form = useForm({
    name: '',
    norad_id: '',
    type: 'COMMUNICATION',
    status: 'ACTIVE',
    tle_line1: '',
    tle_line2: '',
    data_source: '',
    source_url: '',
    dataset_name: '',
    priority: 0,
    api_config: {}
});

const editForm = useForm({
    id: null,
    name: '',
    type: '',
    status: '',
    tle_line1: '',
    tle_line2: '',
    data_source: '',
    source_url: '',
    dataset_name: '',
    priority: 0,
    api_config: {}
});

const submitAdd = () => {
    form.post(route('admin.satellites.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const openEdit = (sat) => {
    editingSatellite.value = sat;
    editForm.id = sat.id;
    editForm.name = sat.name;
    editForm.type = sat.type;
    editForm.status = sat.status;
    editForm.tle_line1 = sat.tle_line1;
    editForm.tle_line2 = sat.tle_line2;
    editForm.data_source = sat.data_source || '';
    editForm.source_url = sat.source_url || '';
    editForm.dataset_name = sat.dataset_name || '';
    editForm.priority = sat.priority || 0;
    editForm.api_config = sat.api_config || {};
};

const submitUpdate = () => {
    editForm.put(route('admin.satellites.update', editForm.id), {
        onSuccess: () => editingSatellite.value = null
    });
};

const deleteSat = (id) => {
    if (confirm('Are you sure you want to remove this orbital asset?')) {
        useForm({}).delete(route('admin.satellites.destroy', id));
    }
};

const types = ['COMMUNICATION', 'NAVIGATION', 'OBSERVATION', 'SCIENTIFIC', 'SPACE_DEBRIS', 'STATION'];
const statuses = ['ACTIVE', 'INACTIVE', 'DECOMMISSIONED'];

const route = window.route;
</script>

<template>
    <AdminLayout>
        <template #header>SATELLITE_ASSET_INVENTORY</template>
        <Head title="Mission Control - Orbital Assets" />
        
        <div class="relative min-h-[calc(100vh-120px)]">
            <!-- Header actions -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-3xl font-black font-outfit uppercase tracking-tighter italic">ORBITAL_FLEET_STATUS</h3>
                    <div class="flex items-center space-x-4 mt-2">
                        <div class="flex items-center space-x-1.5 pt-1">
                            <div class="w-2 h-2 rounded-full bg-vibrant-green animate-pulse"></div>
                            <span class="text-[9px] font-black uppercase text-white/40 tracking-widest">Global_Signal_Stability: 98.4%</span>
                        </div>
                    </div>
                </div>
                <button @click="showAddModal = true" class="px-8 py-4 bg-vibrant-blue text-black hover:bg-white transition-all uppercase text-[10px] font-black tracking-[0.3em] shadow-[0_0_30px_rgba(0,136,255,0.3)]">Deploy_New_Asset</button>
            </div>

            <!-- Enhanced Inventory Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="sat in satellites" :key="sat.id" 
                    @click="selectSatellite(sat)"
                    :class="selectedSatellite?.id === sat.id ? 'border-vibrant-blue shadow-[0_0_30px_rgba(0,136,255,0.15)] bg-vibrant-blue/5' : 'border-white/5 hover:border-vibrant-blue/30 bg-[#08080C]'"
                    class="group relative border p-6 transition-all duration-500 cursor-pointer overflow-hidden">
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 border border-white/10 rounded-lg flex items-center justify-center bg-white/[0.02] group-hover:border-vibrant-blue/50 transition-colors">
                                <svg class="w-5 h-5 text-white/20 group-hover:text-vibrant-blue transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-[8px] font-black uppercase tracking-[0.3em] px-2 py-0.5 border border-white/10 text-white/30 rounded">{{ sat.type }}</span>
                        </div>

                        <h3 class="text-lg font-black uppercase italic tracking-tight text-white group-hover:text-vibrant-blue transition-colors mb-4">{{ sat.name }}</h3>
                        
                        <div class="grid grid-cols-2 gap-2 mb-6 text-[9px] font-mono">
                            <div class="p-2 bg-white/[0.02] border border-white/5">
                                <p class="text-white/20 uppercase mb-0.5">Altitude</p>
                                <p class="text-white/80">{{ sat.telemetry.altitude }}</p>
                            </div>
                            <div class="p-2 bg-white/[0.02] border border-white/5">
                                <p class="text-white/20 uppercase mb-0.5">Signal</p>
                                <p class="text-vibrant-green">{{ sat.telemetry.signal }}</p>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-[8px] font-black uppercase tracking-widest pt-2 border-t border-white/5">
                            <span class="text-white/20">Status:</span>
                            <span :class="sat.status === 'ACTIVE' ? 'text-vibrant-green' : 'text-red-500'">{{ sat.status }}</span>
                        </div>
                    </div>

                    <!-- Scanline pulse -->
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-vibrant-blue/5 to-transparent h-12 -translate-y-full group-hover:animate-scan-fast pointer-events-none opacity-50"></div>
                </div>
            </div>

            <!-- Mission Intelligence Drawer -->
            <transition name="drawer">
                <div v-if="selectedSatellite" class="fixed top-0 right-0 w-[450px] h-screen bg-[#040408] border-l border-white/10 z-[100] shadow-[-20px_0_50px_rgba(0,0,0,1)] flex flex-col">
                    <!-- Drawer Header -->
                    <div class="p-8 border-b border-white/10 flex justify-between items-center bg-vibrant-blue/[0.03]">
                        <div>
                            <p class="text-[9px] font-black text-vibrant-blue uppercase tracking-[0.5em] mb-1">Asset_Deep_Intelligence</p>
                            <h2 class="text-2xl font-black uppercase italic tracking-tighter">{{ selectedSatellite.name }}</h2>
                        </div>
                        <button @click="selectedSatellite = null" class="w-10 h-10 border border-white/10 hover:bg-white/10 flex items-center justify-center transition-all">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-8 space-y-10">
                        <!-- Ground Track Map -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-white/40">LIVE_GROUND_TRACK</h4>
                                <span class="px-2 py-0.5 bg-red-500/10 text-red-500 text-[8px] font-black uppercase inline-flex items-center">
                                    <div class="w-1 h-1 bg-red-500 rounded-full mr-1.5 animate-pulse"></div>
                                    REC_01_NAV
                                </span>
                            </div>
                            <div class="h-48 bg-black border border-white/10 relative overflow-hidden group">
                                <div ref="mapContainer" class="w-full h-full z-0 grayscale invert opacity-80 brightness-[0.7]"></div>
                                <!-- HUD Overlay for map -->
                                <div class="absolute top-2 left-2 z-10 pointer-events-none space-y-1">
                                    <p class="text-[8px] font-mono text-vibrant-blue">LAT: 12.4592</p>
                                    <p class="text-[8px] font-mono text-vibrant-blue">LNG: 107.2910</p>
                                </div>
                            </div>
                        </div>

                        <!-- Telemetry Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(val, key) in { Altitude: selectedSatellite.telemetry.altitude, Velocity: selectedSatellite.telemetry.velocity, Signal: selectedSatellite.telemetry.signal, Temp: selectedSatellite.telemetry.temp }" :key="key" 
                                class="p-4 border border-white/5 bg-white/[0.01]">
                                <p class="text-[8px] font-black text-white/30 uppercase tracking-[.3em] mb-1">{{ key }}</p>
                                <p class="text-sm font-black text-white tabular-nums">{{ val }}</p>
                            </div>
                        </div>

                        <!-- TLE Analysis -->
                        <div class="space-y-4">
                             <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-white/40">TLE_PROPAGATION_STRING</h4>
                             <div class="bg-black p-4 border border-white/10 font-mono text-[10px] leading-relaxed space-y-1 group">
                                <p class="text-white/80 break-all"><span class="text-vibrant-blue font-bold">1_</span>{{ selectedSatellite.tle_line1 }}</p>
                                <p class="text-white/80 break-all"><span class="text-vibrant-blue font-bold">2_</span>{{ selectedSatellite.tle_line2 }}</p>
                                <div class="mt-4 pt-4 border-t border-white/5 grid grid-cols-2 gap-2 text-[8px] uppercase font-black tracking-widest opacity-40 group-hover:opacity-100 transition-opacity">
                                    <p>EPOCH: 24047.8174</p>
                                    <p>INCLINATION: 53.21</p>
                                    <p>RAAN: 247.19</p>
                                    <p>MEAN_ANOM: 112.92</p>
                                </div>
                             </div>
                        </div>

                        <!-- Sensor Registry -->
                        <div class="space-y-4">
                             <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-white/40">SENSOR_UNIT_REGISTRY</h4>
                             <div class="space-y-2">
                                <div v-for="sensor in selectedSatellite.telemetry.sensors" :key="sensor.name" class="flex justify-between items-center p-3 border border-white/5 bg-white/[0.01]">
                                    <span class="text-[9px] font-bold text-white/60 tracking-wider">{{ sensor.name }}</span>
                                    <span :class="sensor.status === 'ONLINE' ? 'text-vibrant-green' : 'text-white/20'" class="text-[8px] font-black uppercase tracking-widest italic">{{ sensor.status }}</span>
                                </div>
                             </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-6 space-y-3">
                            <button @click="openEdit(selectedSatellite)" class="w-full py-4 bg-white text-black text-[10px] font-black uppercase tracking-[.3em] hover:bg-vibrant-blue transition-all">Reconfigure_Mission</button>
                            <button @click="deleteSat(selectedSatellite.id)" class="w-full py-3 border border-red-500/20 text-red-500/60 hover:text-red-500 hover:bg-red-500/10 text-[9px] font-black uppercase tracking-[.2em] transition-all">Deactivate_Asset</button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Edit Modal (Mission Config) -->
        <div v-if="editingSatellite" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[100] flex items-center justify-center p-6">
            <div class="w-full max-w-4xl bg-[#08080C] border border-white/10 rounded-2xl overflow-hidden shadow-[0_0_100px_rgba(0,0,0,1)]">
                <div class="p-8 bg-vibrant-blue/5 border-b border-white/10 flex justify-between items-center">
                    <h2 class="text-4xl font-black uppercase italic tracking-tighter">ASSET_MISSION_CONFIG: <span class="text-vibrant-blue">{{ editForm.name }}</span></h2>
                    <button @click="editingSatellite = null" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all font-mono">ESC</button>
                </div>
                
                <form @submit.prevent="submitUpdate" class="p-10 grid grid-cols-2 gap-10 max-h-[80vh] overflow-y-auto custom-scrollbar">
                    <!-- Core Identity -->
                    <div class="space-y-8">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-vibrant-blue mb-4">Core_Telemetry</h3>
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Satellite Designation</label>
                                <input v-model="editForm.name" type="text" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition transition-all" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Operation_Type</label>
                                    <select v-model="editForm.type" class="w-full bg-[#101015] border border-white/10 px-5 py-4 text-xs font-bold focus:border-vibrant-blue outline-none uppercase cursor-pointer hover:bg-white/5 transition">
                                        <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Mission_Status</label>
                                    <select v-model="editForm.status" class="w-full bg-[#101015] border border-white/10 px-5 py-4 text-xs font-bold focus:border-vibrant-blue outline-none uppercase cursor-pointer hover:bg-white/5 transition">
                                        <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-vibrant-blue pt-4">Orbital_Parameters</h3>
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">TLE_DATA_STR_1</label>
                                <textarea v-model="editForm.tle_line1" rows="2" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-[11px] font-mono focus:border-vibrant-blue outline-none transition resize-none"></textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">TLE_DATA_STR_2</label>
                                <textarea v-model="editForm.tle_line2" rows="2" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-[11px] font-mono focus:border-vibrant-blue outline-none transition resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Mission Config -->
                    <div class="space-y-8 bg-white/[0.01] p-8 border border-white/5 rounded-2xl">
                        <h3 class="text-[11px] font-black uppercase tracking-[0.5em] text-vibrant-blue">Intelligence_Sourcing</h3>
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Primary_Data_Origin</label>
                                <input v-model="editForm.data_source" type="text" placeholder="e.g. CELESTRAK, NOAA, JAXA" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Source_Payload_URL</label>
                                <input v-model="editForm.source_url" type="text" placeholder="https://..." class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-mono focus:border-vibrant-blue outline-none transition" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Dataset_Identifier</label>
                                    <input v-model="editForm.dataset_name" type="text" placeholder="gp-weather-v1" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-mono focus:border-vibrant-blue outline-none transition" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Mission_Priority</label>
                                    <input v-model.number="editForm.priority" type="number" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                                </div>
                            </div>
                        </div>

                        <div class="pt-10 flex flex-col space-y-4">
                            <button type="submit" class="w-full py-6 bg-vibrant-blue text-black text-xs font-black uppercase tracking-[0.5em] hover:bg-white hover:scale-[1.02] transition-all transform shadow-[0_15px_40px_rgba(0,136,255,0.4)]">INITIALIZE_RECONFIGURATION</button>
                            <button type="button" @click="editingSatellite = null" class="w-full py-4 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">Abort_Action</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Modal (Same styling architecture) -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[100] flex items-center justify-center p-6">
            <div class="w-full max-w-4xl bg-[#08080C] border border-white/10 rounded-2xl overflow-hidden shadow-[0_0_100px_rgba(0,0,0,1)]">
                <div class="p-8 bg-vibrant-blue/5 border-b border-white/10 flex justify-between items-center">
                    <h2 class="text-4xl font-black uppercase italic tracking-tighter text-white">DEPLOY_NEW_MISSION_ASSET</h2>
                    <button @click="showAddModal = false" class="w-12 h-12 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all font-mono">ESC</button>
                </div>
                
                <form @submit.prevent="submitAdd" class="p-10 grid grid-cols-2 gap-10 max-h-[80vh] overflow-y-auto custom-scrollbar">
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Satellite Designation</label>
                                <input v-model="form.name" type="text" placeholder="Satellite Name" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">NORAD_TRACKING_ID</label>
                                    <input v-model="form.norad_id" type="text" placeholder="NORAD ID" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-mono focus:border-vibrant-blue outline-none transition" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Operation_Type</label>
                                    <select v-model="form.type" class="w-full bg-[#101015] border border-white/10 px-5 py-4 text-xs font-bold focus:border-vibrant-blue outline-none uppercase transition">
                                        <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 pt-6 border-t border-white/5">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">TLE_DATA_STR_1</label>
                                <textarea v-model="form.tle_line1" rows="2" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-[11px] font-mono focus:border-vibrant-blue outline-none transition resize-none"></textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">TLE_DATA_STR_2</label>
                                <textarea v-model="form.tle_line2" rows="2" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-[11px] font-mono focus:border-vibrant-blue outline-none transition resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8 bg-white/[0.01] p-8 border border-white/5 rounded-2xl">
                         <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Data_Origin</label>
                                <input v-model="form.data_source" type="text" placeholder="e.g. CELESTRAK" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Dataset_Identifier</label>
                                <input v-model="form.dataset_name" type="text" placeholder="weather-main-v1" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-mono focus:border-vibrant-blue outline-none transition" />
                            </div>
                        </div>

                        <div class="pt-20 flex flex-col space-y-4">
                            <button type="submit" class="w-full py-6 bg-vibrant-blue text-black text-xs font-black uppercase tracking-[0.5em] hover:bg-white hover:scale-[1.02] transition-all transform shadow-[0_15px_40px_rgba(0,136,255,0.4)]">AUTHORIZE_DEPLOYMENT</button>
                            <button type="button" @click="showAddModal = false" class="w-full py-4 border border-white/10 text-[10px] font-black uppercase tracking-widest text-white/40 hover:text-white transition-colors">Cancel_Mission</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </AdminLayout>
</template>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.font-inter { font-family: 'Inter', sans-serif; }
.text-vibrant-blue { color: #0088ff; }
.bg-vibrant-blue { background-color: #0088ff; }
.border-vibrant-blue { border-color: #0088ff; }
.text-vibrant-green { color: #00ffaa; }
.bg-vibrant-green { background-color: #00ffaa; }
.border-vibrant-green { border-color: #00ffaa; }

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 136, 255, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 136, 255, 0.5);
}

/* Animations */
@keyframes scan-fast {
    from { transform: translateY(-100%); }
    to { transform: translateY(300%); }
}

.animate-scan-fast {
    animation: scan-fast 2s linear infinite;
}

/* Drawer Transitions */
.drawer-enter-active, .drawer-leave-active {
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.drawer-enter-from, .drawer-leave-to {
    transform: translateX(100%);
}
</style>
