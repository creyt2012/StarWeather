<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stations: Array
});

const isModalOpen = ref(false);
const editingStation = ref(null);

const form = useForm({
    name: '',
    code: '',
    latitude: 0,
    longitude: 0,
    elevation_m: 0,
    frequency_band: 'S-band',
    coverage_radius_km: 250,
    status: 'operational',
    parameters: '{}'
});

const openCreateModal = () => {
    editingStation.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (station) => {
    editingStation.value = station;
    form.name = station.name;
    form.code = station.code;
    form.latitude = station.latitude;
    form.longitude = station.longitude;
    form.elevation_m = station.elevation_m;
    form.frequency_band = station.frequency_band;
    form.coverage_radius_km = station.coverage_radius_km;
    form.status = station.status;
    form.parameters = station.parameters ? JSON.stringify(station.parameters, null, 2) : '{}';
    isModalOpen.value = true;
};

const submit = () => {
    // Parse JSON parameters back before sending
    let parsedParams = null;
    try {
        parsedParams = JSON.parse(form.parameters);
    } catch(e) {
        alert("Invalid JSON format in parameters");
        return;
    }

    const payload = form.transform((data) => ({
        ...data,
        parameters: parsedParams
    }));

    if (editingStation.value) {
        payload.put(route('admin.radar-stations.update', editingStation.value.id), {
            onSuccess: () => isModalOpen.value = false
        });
    } else {
        payload.post(route('admin.radar-stations.store'), {
            onSuccess: () => isModalOpen.value = false
        });
    }
};

const deleteStation = (id) => {
    if (confirm('AUTHORIZATION REQUIRED: Permanently decommission this weather radar station?')) {
        form.delete(route('admin.radar-stations.destroy', id));
    }
};
</script>

<template>
    <Head title="Weather Radar Systems" />

    <AdminLayout>
        <template #header>Weather_Radar_Registry</template>

        <div class="space-y-8">
            <!-- Header Section -->
            <div class="flex justify-between items-end">
                <div>
                    <h3 class="text-2xl font-black uppercase tracking-tighter italic">DOPPLER_RADAR_NODES</h3>
                    <p class="text-xs text-white/40 uppercase tracking-widest mt-1">Tropospheric Scan & Precipitation Tracking</p>
                </div>
                <button @click="openCreateModal" class="px-8 py-3 bg-vibrant-blue text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(0,136,255,0.4)] hover:scale-105 transition-all">
                    DEPLOY_NEW_RADAR
                </button>
            </div>

            <!-- Stations Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="station in stations" :key="station.id" 
                    class="bg-[#050508] border border-white/5 p-6 relative group overflow-hidden">
                    
                    <div class="absolute top-0 right-0 p-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEditModal(station)" class="p-2 bg-white/5 hover:bg-vibrant-blue/20 text-white/40 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button @click="deleteStation(station.id)" class="p-2 bg-white/5 hover:bg-red-500/20 text-white/40 hover:text-red-500 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>

                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-vibrant-blue/10 border border-vibrant-blue/20">
                            <!-- Radar Icon -->
                            <svg class="w-6 h-6 text-vibrant-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-dasharray="2 4"/>
                            </svg>
                        </div>
                        <div :class="station.status === 'operational' ? 'text-vibrant-green bg-vibrant-green/10' : (station.status === 'maintenance' ? 'text-yellow-500 bg-yellow-500/10' : 'text-red-500 bg-red-500/10')" 
                            class="px-3 py-1 text-[8px] font-black uppercase tracking-widest border border-current">
                            {{ station.status }}
                        </div>
                    </div>

                    <h4 class="text-lg font-black uppercase tracking-tight mb-1">{{ station.name }}</h4>
                    <p class="text-[10px] font-mono text-white/30 tracking-widest uppercase mb-4">{{ station.code }} - {{ station.frequency_band }}</p>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-[10px] font-mono border-t border-white/5 pt-4">
                            <span class="text-white/20">COORDINATES</span>
                            <span class="text-vibrant-blue">{{ Number(station.latitude).toFixed(4) }}, {{ Number(station.longitude).toFixed(4) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-mono">
                            <span class="text-white/20">ELEVATION</span>
                            <span>{{ station.elevation_m }}m MSL</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-mono">
                            <span class="text-white/20">SWEEP RADIUS</span>
                            <span class="text-vibrant-blue">{{ station.coverage_radius_km }}km (Doppler)</span>
                        </div>
                    </div>

                    <!-- Aesthetics -->
                    <div class="mt-6 h-1 w-full bg-white/5 overflow-hidden">
                        <div class="h-full bg-vibrant-blue animate-pulse" :style="{ width: station.status === 'operational' ? '100%' : '30%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRUD Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-[#020205]/90 backdrop-blur-md">
            <div class="bg-[#08080c] border border-white/10 p-10 max-w-2xl w-full relative shadow-[0_0_100px_rgba(0,136,255,0.1)] custom-scrollbar" style="max-height: 90vh; overflow-y: auto;">
                <button @click="isModalOpen = false" class="absolute top-6 right-6 text-white/20 hover:text-white uppercase text-[10px] font-black tracking-widest">CLOSE [X]</button>
                
                <h3 class="text-xl font-black uppercase tracking-tighter italic mb-8">
                    {{ editingStation ? 'UPDATE_RADAR_CALIBRATION' : 'INITIALIZE_NEW_RADAR' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Radar Facility Name</label>
                            <input v-model="form.name" type="text" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">WMO/ICAO Code</label>
                            <input v-model="form.code" type="text" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all uppercase" :disabled="!!editingStation" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Latitude</label>
                            <input v-model="form.latitude" type="number" step="any" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Longitude</label>
                            <input v-model="form.longitude" type="number" step="any" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Elevation (m)</label>
                            <input v-model="form.elevation_m" type="number" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Frequency Band</label>
                            <select v-model="form.frequency_band" class="w-full bg-[#0a0a0f] border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all">
                                <option value="S-band">S-BAND (Severe Weather)</option>
                                <option value="C-band">C-BAND (Medium Range)</option>
                                <option value="X-band">X-BAND (High Res/Local)</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Coverage (Km)</label>
                            <input v-model="form.coverage_radius_km" type="number" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Operational Status</label>
                        <select v-model="form.status" class="w-full bg-[#0a0a0f] border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all">
                            <option value="operational">OPERATIONAL (TRANSMITTING)</option>
                            <option value="maintenance">MAINTENANCE (CALIBRATING)</option>
                            <option value="offline">OFFLINE (DEACTIVATED)</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Technical Parameters (JSON)</label>
                        <textarea v-model="form.parameters" rows="4" class="w-full bg-[#050508] border border-white/10 px-4 py-3 text-[10px] font-mono focus:border-vibrant-blue outline-none transition-all" placeholder='{"beam_width": 1.0, "rpm": 3}'></textarea>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full py-4 bg-vibrant-blue text-white text-[10px] font-black uppercase tracking-[0.3em] font-outfit shadow-[0_0_20px_rgba(0,136,255,0.3)] hover:scale-[1.02] transition-all disabled:opacity-50">
                        {{ editingStation ? 'COMMIT_HARDWARE_CHANGES' : 'EXECUTE_RADAR_DEPLOYMENT' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
