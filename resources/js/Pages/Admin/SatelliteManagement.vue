<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    satellites: Array
});

const editingSatellite = ref(null);
const showAddModal = ref(false);

const form = useForm({
    name: '',
    norad_id: '',
    type: 'COMMUNICATION',
    status: 'ACTIVE',
    tle_line1: '',
    tle_line2: ''
});

const editForm = useForm({
    id: null,
    name: '',
    type: '',
    status: '',
    tle_line1: '',
    tle_line2: ''
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

// Helper for UI
const route = window.route;
</script>

<template>
    <Head title="Command Center - Orbital Management" />
    
    <div class="min-h-screen bg-[#050508] text-white p-8 font-inter">
        <!-- Header -->
        <div class="max-w-7xl mx-auto flex justify-between items-end mb-12">
            <div>
                <p class="text-vibrant-blue font-black tracking-[0.2em] text-xs uppercase mb-2">System Administration</p>
                <h1 class="text-5xl font-black font-outfit uppercase tracking-tighter italic">Command Center</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="/" class="px-6 py-3 border border-white/10 hover:bg-white/5 transition uppercase text-[10px] font-black tracking-widest">Back to Mission Control</a>
                <button @click="showAddModal = true" class="px-6 py-3 bg-vibrant-blue text-black hover:bg-white transition uppercase text-[10px] font-black tracking-widest">Deploy New Asset</button>
            </div>
        </div>

        <!-- Inventory Grid -->
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="sat in satellites" :key="sat.id" 
                class="group relative bg-white/[0.02] border border-white/5 p-6 hover:border-vibrant-blue/30 transition-all duration-500 overflow-hidden">
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-[9px] text-white/30 uppercase font-black mb-1">NORAD #{{ sat.norad_id }}</p>
                            <h3 class="text-xl font-black uppercase italic tracking-tight">{{ sat.name }}</h3>
                        </div>
                        <span :class="{
                            'bg-vibrant-green/10 text-vibrant-green border-vibrant-green/20': sat.status === 'ACTIVE',
                            'bg-red-500/10 text-red-500 border-red-500/20': sat.status !== 'ACTIVE'
                        }" class="px-2 py-0.5 border text-[8px] font-black uppercase tracking-widest">
                            {{ sat.status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-[8px] text-white/20 uppercase font-bold">Category</p>
                            <p class="text-xs font-bold text-white/70">{{ sat.type }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] text-white/20 uppercase font-bold">Last Update</p>
                            <p class="text-xs font-bold text-white/70">REALTIME</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-4 border-t border-white/5">
                        <button @click="openEdit(sat)" class="flex-1 py-2 text-[8px] font-black uppercase tracking-widest bg-white/5 hover:bg-white/10 transition">Configure</button>
                        <button @click="deleteSat(sat.id)" class="px-4 py-2 text-[8px] font-black uppercase tracking-widest text-red-500 hover:bg-red-500/10 transition">X</button>
                    </div>
                </div>

                <!-- Abstract background glow -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-vibrant-blue/5 blur-[60px] group-hover:bg-vibrant-blue/10 transition duration-1000"></div>
            </div>
        </div>

        <!-- Modals would go here (Add/Edit) - Simplified for brevity in this step -->
        <div v-if="editingSatellite" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-[#08080C] border border-white/10 p-8">
                <h2 class="text-3xl font-black uppercase italic mb-6">Configure Asset: {{ editingSatellite.name }}</h2>
                <form @submit.prevent="submitUpdate" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">Satellite Name</label>
                            <input v-model="editForm.name" type="text" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">Type</label>
                            <select v-model="editForm.type" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition uppercase">
                                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[9px] uppercase font-black text-white/40">Status</label>
                        <select v-model="editForm.status" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition uppercase">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">TLE Line 1</label>
                            <textarea v-model="editForm.tle_line1" rows="2" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-[10px] font-mono focus:border-vibrant-blue outline-none transition"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">TLE Line 2</label>
                            <textarea v-model="editForm.tle_line2" rows="2" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-[10px] font-mono focus:border-vibrant-blue outline-none transition"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 pt-4">
                        <button type="button" @click="editingSatellite = null" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white/5">Cancel</button>
                        <button type="submit" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest bg-vibrant-blue text-black hover:bg-white">Commit Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-[#08080C] border border-white/10 p-8 shadow-2xl">
                <h2 class="text-3xl font-black uppercase italic mb-6">Deploy New Orbital Asset</h2>
                <form @submit.prevent="submitAdd" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">Satellite Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g. SENTINEL-6" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">NORAD ID</label>
                            <input v-model="form.norad_id" type="text" placeholder="46829" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">Type</label>
                            <select v-model="form.type" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition uppercase">
                                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">Status</label>
                            <select v-model="form.status" class="w-full bg-white/5 border border-white/10 px-4 py-3 text-sm focus:border-vibrant-blue outline-none transition uppercase">
                                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 mt-4 border-t border-white/5">
                        <p class="text-[8px] text-white/20 uppercase font-black tracking-widest mb-2">Orbital Parameters (Optional TLE Override)</p>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">TLE Line 1</label>
                            <textarea v-model="form.tle_line1" rows="2" placeholder="1 25544U 98067A   24047.882..." class="w-full bg-white/5 border border-white/10 px-4 py-3 text-[10px] font-mono focus:border-vibrant-blue outline-none transition"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] uppercase font-black text-white/40">TLE Line 2</label>
                            <textarea v-model="form.tle_line2" rows="2" placeholder="2 25544  51.6424  93.4478..." class="w-full bg-white/5 border border-white/10 px-4 py-3 text-[10px] font-mono focus:border-vibrant-blue outline-none transition"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 pt-4">
                        <button type="button" @click="showAddModal = false" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white/5">Abort</button>
                        <button type="submit" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest bg-vibrant-blue text-black hover:bg-white shadow-[0_0_20px_rgba(30,58,138,0.5)]">Authorize Deployment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.font-inter { font-family: 'Inter', sans-serif; }
.text-vibrant-blue { color: #0088ff; }
.bg-vibrant-blue { background-color: #0088ff; }
.border-vibrant-blue { border-color: #0088ff; }
.text-vibrant-green { color: #00ffaa; }
.border-vibrant-green { border-color: #00ffaa; }
</style>
