<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const emit = defineEmits(['close']);

const systemLogs = ref([
    { id: 1, type: 'INFO', text: 'NEURAL_COMMAND_INITIALIZED', time: '0.001ms' },
    { id: 2, type: 'WARN', text: 'SOLAR_FLARE_DETECTED_SECTOR_7', time: '1.2s' },
    { id: 3, type: 'INTEL', text: 'ANALYZING_MAGNETOSPHERE_RESONANCE', time: '2.5s' }
]);

const aiStatus = ref('LISTENING');

// Waveform simulation
const bars = ref(Array.from({ length: 40 }, () => 10 + Math.random() * 30));
let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        bars.value = bars.value.map(() => 5 + Math.random() * 45);
        if (Math.random() > 0.9) {
            const types = ['INFO', 'WARN', 'INTEL', 'CRITICAL'];
            const messsages = ['SYNCING_PLANETARY_CORE', 'DEBRIS_COLLISION_ALERT', 'ADS_B_TRAFFIC_PEAK', 'CABLE_INTEGRITY_VERIFIED'];
            systemLogs.value.unshift({
                id: Date.now(),
                type: types[Math.floor(Math.random() * types.length)],
                text: messsages[Math.floor(Math.random() * messsages.length)],
                time: 'NOW'
            });
            if (systemLogs.value.length > 8) systemLogs.value.pop();
        }
    }, 100);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <div class="cyber-command-hud bg-black/80 backdrop-blur-3xl border border-vibrant-blue/30 shadow-[0_0_100px_rgba(0,136,255,0.1)] flex flex-col h-[400px]">
        <!-- Header -->
        <div class="p-4 border-b border-vibrant-blue/20 flex justify-between items-center bg-vibrant-blue/5">
            <div class="flex items-center space-x-3">
                <div class="relative w-3 h-3">
                    <div class="absolute inset-0 bg-vibrant-blue animate-ping opacity-50 rounded-full"></div>
                    <div class="absolute inset-0 bg-vibrant-blue rounded-full"></div>
                </div>
                <div>
                    <p class="text-[7px] font-black text-vibrant-blue uppercase tracking-[0.3em]">Neural_Command_v4.2</p>
                    <p class="text-[10px] font-black text-white italic uppercase">{{ aiStatus }}</p>
                </div>
            </div>
            <button @click="$emit('close')" class="text-white/20 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="flex-1 p-5 flex flex-col space-y-5 overflow-hidden">
            <!-- Waveform Visualizer -->
            <div class="flex items-end justify-between h-16 px-4 bg-vibrant-blue/5 border border-vibrant-blue/10 rounded-sm">
                <div v-for="(h, i) in bars" :key="i" 
                     class="w-1 bg-vibrant-blue transition-all duration-100"
                     :style="{ height: h + '%', opacity: 0.3 + (h/100) }">
                </div>
            </div>

            <!-- Strategic Intelligence Logs -->
            <div class="flex-1 bg-black/40 border border-white/5 rounded p-3 overflow-hidden flex flex-col">
                <p class="text-[7px] font-black text-white/20 uppercase tracking-widest mb-2 italic">Realtime_Intelligence_Feed</p>
                <div class="space-y-2 overflow-y-auto custom-scrollbar pr-2">
                    <div v-for="log in systemLogs" :key="log.id" class="flex items-start space-x-3 text-[9px] font-mono leading-tight">
                        <span :class="{
                            'text-vibrant-blue': log.type === 'INFO',
                            'text-yellow-500': log.type === 'WARN',
                            'text-purple-500': log.type === 'INTEL',
                            'text-red-500': log.type === 'CRITICAL'
                        }" class="font-black">[{{ log.type }}]</span>
                        <span class="text-white/60 uppercase truncate">{{ log.text }}</span>
                        <span class="text-white/20 ml-auto whitespace-nowrap">{{ log.time }}</span>
                    </div>
                </div>
            </div>

            <!-- Command Controls -->
            <div class="grid grid-cols-2 gap-3">
                <button class="py-2 bg-vibrant-blue/10 border border-vibrant-blue/30 text-vibrant-blue text-[8px] font-black uppercase tracking-widest hover:bg-vibrant-blue/20 transition-all">
                    INIT_PLANETARY_SCAN
                </button>
                <button class="py-2 bg-white/5 border border-white/10 text-white/40 text-[8px] font-black uppercase tracking-widest hover:bg-white/10 transition-all">
                    GENERATE_BRIEFING
                </button>
            </div>
        </div>
        
        <!-- Bottom Decoration -->
        <div class="h-1 bg-gradient-to-r from-transparent via-vibrant-blue/50 to-transparent"></div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 1px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 136, 255, 0.2);
}
</style>
