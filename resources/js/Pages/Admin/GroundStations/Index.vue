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
    type: 'weather_station',
    status: 'online'
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
    form.type = station.type;
    form.status = station.status;
    isModalOpen.value = true;
};

const submit = () => {
    if (editingStation.value) {
        form.put(route('admin.ground-stations.update', editingStation.value.id), {
            onSuccess: () => isModalOpen.value = false
        });
    } else {
        form.post(route('admin.ground-stations.store'), {
            onSuccess: () => isModalOpen.value = false
        });
    }
};

const deleteStation = (id) => {
    if (confirm('Are you sure you want to decommission this station?')) {
        form.delete(route('admin.ground-stations.destroy', id));
    }
};
</script>

<template>
    <Head title="Ground Station Management" />

    <AdminLayout>
        <template #header>Ground_Station_Registry</template>

        <div class="space-y-8">
            <!-- Header Section -->
            <div class="flex justify-between items-end">
                <div>
                    <h3 class="text-2xl font-black uppercase tracking-tighter italic">EARTH_SEGMENT_NODES</h3>
                    <p class="text-xs text-white/40 uppercase tracking-widest mt-1">Telemetry Reception & IoT Mesh Control</p>
                </div>
                <button @click="openCreateModal" class="px-8 py-3 bg-vibrant-blue text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(0,136,255,0.4)] hover:scale-105 transition-all">
                    DEPLOY_NEW_STATION
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
                            <svg class="w-6 h-6 text-vibrant-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div :class="station.status === 'online' ? 'text-vibrant-green bg-vibrant-green/10' : 'text-red-500 bg-red-500/10'" 
                            class="px-3 py-1 text-[8px] font-black uppercase tracking-widest border border-current">
                            {{ station.status }}
                        </div>
                    </div>

                    <h4 class="text-lg font-black uppercase tracking-tight mb-1">{{ station.name }}</h4>
                    <p class="text-[10px] font-mono text-white/30 tracking-widest uppercase mb-4">{{ station.code }}</p>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-[10px] font-mono border-t border-white/5 pt-4">
                            <span class="text-white/20">COORDINATES</span>
                            <span class="text-vibrant-blue">{{ station.latitude.toFixed(4) }}, {{ station.longitude.toFixed(4) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-mono">
                            <span class="text-white/20">LATEST_SIGNAL</span>
                            <span>{{ station.latest_metric ? new Date(station.latest_metric.captured_at).toLocaleTimeString() : 'NO_SIGNAL' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px] font-mono">
                            <span class="text-white/20">TEMPERATURE</span>
                            <span class="text-vibrant-blue">{{ station.latest_metric?.temperature?.toFixed(1) || '--' }}°C</span>
                        </div>
                    </div>

                    <!-- Aesthetics -->
                    <div class="mt-6 h-1 w-full bg-white/5 overflow-hidden">
                        <div class="h-full bg-vibrant-blue animate-pulse" :style="{ width: '60%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRUD Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-[#020205]/90 backdrop-blur-md">
            <div class="bg-[#08080c] border border-white/10 p-10 max-w-2xl w-full relative shadow-[0_0_100px_rgba(0,136,255,0.1)]">
                <button @click="isModalOpen = false" class="absolute top-6 right-6 text-white/20 hover:text-white uppercase text-[10px] font-black tracking-widest">CLOSE [X]</button>
                
                <h3 class="text-xl font-black uppercase tracking-tighter italic mb-8">
                    {{ editingStation ? 'UPDATE_STATION_PARAMETERS' : 'INITIALIZE_NEW_STATION' }}
                </h3>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Station Name</label>
                            <input v-model="form.name" type="text" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Unique Code</label>
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

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Node Type</label>
                            <select v-model="form.type" class="w-full bg-[#0a0a0f] border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all">
                                <option value="weather_station">WEATHER_STATION</option>
                                <option value="iot_sensor">IOT_SENSOR</option>
                                <option value="gateway">GATEWAY_NODE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-white/30 uppercase tracking-widest">Deployment Status</label>
                            <select v-model="form.status" class="w-full bg-[#0a0a0f] border border-white/10 px-4 py-3 text-xs font-mono focus:border-vibrant-blue outline-none transition-all">
                                <option value="online">OPERATIONAL (ONLINE)</option>
                                <option value="offline">MAINTENANCE (OFFLINE)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full py-4 bg-vibrant-blue text-white text-[10px] font-black uppercase tracking-[0.3em] font-outfit shadow-[0_0_20px_rgba(0,136,255,0.3)] hover:scale-[1.02] transition-all disabled:opacity-50">
                        {{ editingStation ? 'COMMIT_CHANGES' : 'EXECUTE_DEPLOYMENT' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
