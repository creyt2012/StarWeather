<script setup>
import { computed } from 'vue';

const props = defineProps({
    vessel: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close']);

// Simulated extra technical data for AIS HUD
const techSpecs = computed(() => {
    return {
        mmsi: 'MMSI_' + Math.floor(Math.random() * 900000 + 100000),
        deadweight: (Math.random() * 200000 + 50000).toLocaleString() + ' DWT',
        flag: ['PANAMA', 'LIBERIA', 'MARSHALL_ISLANDS', 'VIETNAM'][Math.floor(Math.random() * 4)],
        eta: '2026-02-' + (Math.floor(Math.random() * 5) + 20) + ' 08:30 UTC',
        strategic_importance: Math.random() > 0.7 ? 'HIGH_PRIORITY' : 'ROUTINE_TRADE'
    };
});

const cargoStatus = computed(() => {
    if (props.vessel.type === 'TANKER') return { label: 'CRUDE_OIL', progress: 85, color: 'bg-orange-500' };
    if (props.vessel.type === 'CONTAINER') return { label: 'COMMERCIAL_GOODS', progress: 92, color: 'bg-vibrant-blue' };
    return { label: 'BULK_CARGO', progress: 60, color: 'bg-yellow-500' };
});
</script>

<template>
    <div class="ais-vessel-hud bg-black/90 backdrop-blur-2xl border border-teal-500/50 shadow-[0_0_50px_rgba(45,212,191,0.2)] flex flex-col max-h-[85vh]">
        <!-- Header -->
        <div class="p-5 border-b border-teal-500/20 bg-teal-500/10 flex justify-between items-center relative z-10">
            <div>
                <p class="text-[8px] font-black text-teal-400 uppercase tracking-[0.4em] mb-1">Maritime_Asset_Intel</p>
                <h3 class="text-lg font-black uppercase tracking-tighter italic leading-none">{{ vessel.name }}</h3>
            </div>
            <button @click="$emit('close')" class="text-white/40 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6">
            <!-- Tactical Profile -->
            <div class="grid grid-cols-2 gap-px bg-white/5 border border-white/10">
                <div class="p-4 bg-[#0a0a0f]">
                    <p class="text-[8px] font-black text-white/20 uppercase mb-1">IDENT_MMSI</p>
                    <p class="text-sm font-black text-teal-400 font-mono">{{ techSpecs.mmsi }}</p>
                </div>
                <div class="p-4 bg-[#0a0a0f]">
                    <p class="text-[8px] font-black text-white/20 uppercase mb-1">REGISTRY_FLAG</p>
                    <p class="text-sm font-black text-white italic truncate">{{ techSpecs.flag }}</p>
                </div>
            </div>

            <!-- Operational Status -->
            <div class="space-y-4">
                <h4 class="text-[10px] font-black text-white/40 uppercase tracking-widest border-l-2 border-teal-500 pl-3">Operational_Manifest</h4>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-end border-b border-white/5 pb-2">
                        <span class="text-[9px] text-white/30 font-bold uppercase">Status</span>
                        <span class="text-sm font-black text-vibrant-green italic">{{ vessel.status }}</span>
                    </div>
                    <div class="flex justify-between items-end border-b border-white/5 pb-2">
                        <span class="text-[9px] text-white/30 font-bold uppercase">Deadweight</span>
                        <span class="text-sm font-black text-white">{{ techSpecs.deadweight }}</span>
                    </div>
                    <div class="flex justify-between items-end border-b border-white/5 pb-2">
                        <span class="text-[9px] text-white/30 font-bold uppercase">Schedule_ETA</span>
                        <span class="text-sm font-black text-yellow-500">{{ techSpecs.eta }}</span>
                    </div>
                </div>
            </div>

            <!-- Cargo Intelligence -->
            <div class="space-y-3">
                <h4 class="text-[10px] font-black text-white/40 uppercase tracking-widest border-l-2 border-teal-500 pl-3">Strategic_Cargo_Impact</h4>
                <div class="p-4 bg-teal-500/5 border border-teal-500/20 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <p class="text-[8px] font-black text-teal-400 uppercase tracking-widest">Cargo_Type</p>
                            <p class="text-xs font-black text-white uppercase italic">{{ cargoStatus.label }}</p>
                        </div>
                        <span class="text-sm font-black text-white">{{ cargoStatus.progress }}%</span>
                    </div>
                    <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full shadow-[0_0_10px_currentColor]" :class="cargoStatus.color" :style="{ width: cargoStatus.progress + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- Importance Alert -->
            <div v-if="techSpecs.strategic_importance === 'HIGH_PRIORITY'" class="p-3 bg-teal-500/10 border border-teal-500/30 rounded-lg animate-pulse text-center">
                <p class="text-[8px] font-black text-teal-400 uppercase tracking-widest">⚓ STRATEGIC_ASSET_WATCH_ACTIVE</p>
            </div>

            <div class="pt-4 space-y-3">
                <button class="w-full py-4 bg-teal-500 text-black text-[10px] font-black uppercase tracking-[0.3em] hover:bg-teal-400 transition-all flex items-center justify-center space-x-3">
                    <span class="text-lg">🛰️</span>
                    <span>SATELLITE_SURVEILLANCE_LOCK</span>
                </button>
                <button class="w-full py-3 bg-white/5 text-white/40 text-[9px] font-black uppercase tracking-[0.3em] hover:bg-white/10 transition-all border border-white/5">
                    REQUEST_PORT_LOGS
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 2px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(45, 212, 191, 0.2);
}
</style>
