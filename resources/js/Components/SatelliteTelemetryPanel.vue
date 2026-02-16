<script setup>
import { computed } from 'vue';

const props = defineProps({
    satellite: {
        type: Object,
        required: true
    }
});

const formattedVelocity = computed(() => {
    return props.satellite.velocity ? props.satellite.velocity.toFixed(3) : '---';
});

const formattedAltitude = computed(() => {
    return props.satellite.altitude ? Math.round(props.satellite.altitude).toLocaleString() : '---';
});

const formattedPeriod = computed(() => {
    return props.satellite.period ? props.satellite.period.toFixed(1) : '---';
});
</script>

<template>
    <div class="telemetry-panel p-6 bg-black/40 backdrop-blur-xl border border-vibrant-blue/20 rounded-2xl shadow-2xl animate-in zoom-in duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-1.5 h-1.5 rounded-full bg-vibrant-blue animate-pulse"></div>
                <h3 class="text-[10px] font-black text-vibrant-blue tracking-[0.3em] uppercase italic">Orbital_Telemetry</h3>
            </div>
            <span class="text-[8px] font-mono text-white/20 tracking-widest uppercase">ID: {{ satellite.norad_id || 'X-IDENT' }}</span>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Velocity Gauge -->
            <div class="relative p-4 bg-white/[0.02] border border-white/5 rounded-xl hover:border-vibrant-blue/30 transition-all group">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[9px] font-black text-white/40 uppercase tracking-widest">Velocity</span>
                    <span class="text-[9px] font-mono text-vibrant-blue italic">REL_TO_GROUND</span>
                </div>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black text-white group-hover:text-vibrant-blue transition-colors">{{ formattedVelocity }}</span>
                    <span class="text-[10px] font-bold text-white/20 uppercase">KM/S</span>
                </div>
                <!-- Mini Progress Bar -->
                <div class="w-full h-1 bg-white/5 rounded-full mt-4 overflow-hidden relative">
                    <div class="h-full bg-vibrant-blue transition-all duration-1000" :style="{ width: (satellite.velocity / 30 * 100) + '%' }"></div>
                    <div class="absolute inset-x-0 bottom-0 h-[2px] bg-white/10 opacity-20"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Altitude -->
                <div class="flex flex-col p-4 bg-white/[0.02] border border-white/5 rounded-xl">
                    <span class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-2">Altitude</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="text-xl font-black text-white">{{ formattedAltitude }}</span>
                        <span class="text-[8px] font-bold text-white/20 uppercase">KM</span>
                    </div>
                </div>

                <!-- Orbital Period -->
                <div class="flex flex-col p-4 bg-white/[0.02] border border-white/5 rounded-xl">
                    <span class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-2">Period</span>
                    <div class="flex items-baseline space-x-1">
                        <span class="text-xl font-black text-white">{{ formattedPeriod }}</span>
                        <span class="text-[8px] font-bold text-white/20 uppercase">MIN</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Tag -->
        <div class="mt-6 pt-4 border-t border-white/5 flex justify-between items-center">
            <div class="flex flex-col">
                <span class="text-[7px] font-black text-white/20 uppercase tracking-[0.2em]">Data_Provenance</span>
                <span class="text-[8px] font-mono text-vibrant-blue/60 uppercase">CELESTRAK / SGP4_CORE</span>
            </div>
            <div class="flex space-x-1">
                <div v-for="i in 3" :key="i" class="w-1 h-3 bg-vibrant-blue/20 rounded-[0.5px]"></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.telemetry-panel {
    background-image: radial-gradient(circle at 0% 0%, rgba(79, 70, 229, 0.05) 0%, transparent 50%);
}
</style>
