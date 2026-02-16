<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    stats: Object,
    recent_keys: Array
});

</script>

<template>
    <AdminLayout>
        <template #header>INTELLIGENCE_OVERVIEW</template>
        <Head title="Admin Dashboard - Mission Control" />

        <div class="space-y-10">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="(val, label) in stats" :key="label" 
                    class="bg-[#08080C] border border-white/5 p-8 relative overflow-hidden group hover:border-vibrant-blue/50 transition-all duration-500">
                    <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.3em] mb-2">{{ label.replace(/_/g, ' ') }}</p>
                    <h3 class="text-4xl font-black font-outfit text-white group-hover:text-vibrant-blue transition-colors">{{ val }}</h3>
                    
                    <!-- Decorative HUD Corner -->
                    <div class="absolute top-0 right-0 w-8 h-8 opacity-20 group-hover:opacity-100 transition-opacity">
                        <div class="absolute top-2 right-2 w-4 h-[1px] bg-vibrant-blue"></div>
                        <div class="absolute top-2 right-2 w-[1px] h-4 bg-vibrant-blue"></div>
                    </div>
                </div>
            </div>

            <!-- Usage & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Usage Meter -->
                <div class="lg:col-span-2 bg-[#08080C] border border-white/5 p-8 rounded-xl relative overflow-hidden">
                    <div class="flex justify-between items-end mb-8">
                        <div>
                            <p class="text-[10px] font-black text-vibrant-blue uppercase tracking-[0.4em] mb-1">Global_Resource_Consumption</p>
                            <h3 class="text-2xl font-black uppercase italic tracking-tighter">DATA_THROUGHPUT_MAP</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-mono text-white/40 tracking-widest">PEAK: 12.4 GB/s</span>
                        </div>
                    </div>

                    <!-- Mock Chart area -->
                    <div class="h-64 border border-white/5 bg-white/[0.01] rounded-lg flex items-end p-6 space-x-2">
                        <div v-for="i in 20" :key="i" 
                            :style="{ height: Math.random() * 80 + 20 + '%' }"
                            class="flex-1 bg-vibrant-blue/20 hover:bg-vibrant-blue transition-all duration-300 relative group">
                            <div class="absolute inset-x-0 top-0 h-1 bg-vibrant-blue hidden group-hover:block blur-[5px]"></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Keys -->
                <div class="bg-[#08080C] border border-white/5 p-8 rounded-xl flex flex-col">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] mb-8 border-b border-white/5 pb-4">RECENT_KEY_PROVISIONING</h3>
                    <div class="flex-1 space-y-4">
                        <div v-for="key in recent_keys" :key="key.id" class="flex justify-between items-center p-4 bg-white/[0.02] border border-white/5 group hover:border-vibrant-blue/30 transition-all cursor-crosshair">
                            <div>
                                <p class="text-[10px] font-bold text-white/80 uppercase">{{ key.name }}</p>
                                <p class="text-[8px] font-mono text-white/20 tracking-tighter">{{ key.key.substring(0, 12) }}...</p>
                            </div>
                            <span :class="key.is_active ? 'text-vibrant-green' : 'text-red-500'" class="text-[9px] font-black tracking-widest uppercase">
                                {{ key.is_active ? 'ACTIVE' : 'REVOKED' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.font-outfit { font-family: 'Outfit', sans-serif; }
.text-vibrant-blue { color: #0088ff; }
.bg-vibrant-blue { background-color: #0088ff; }
.text-vibrant-green { color: #00ffaa; }
</style>
