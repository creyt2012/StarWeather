<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    apiKeys: Array,
    tenants: Array
});

const showAddKey = ref(false);
const editingKey = ref(null);

const form = useForm({
    name: '',
    tenant_id: '',
    rate_limit: 60,
    monthly_quota: 10000,
});

const submitAdd = () => {
    form.post(route('admin.apikeys.store'), {
        onSuccess: () => {
            showAddKey.value = false;
            form.reset();
        }
    });
};

const deleteKey = (id) => {
    if (confirm('Are you sure you want to revoke this intelligence key? Access will be terminated immediately.')) {
        useForm({}).delete(route('admin.apikeys.destroy', id));
    }
};

</script>

<template>
    <AdminLayout>
        <template #header>API_INTELLIGENCE_CONTROL</template>
        <Head title="API Management - Mission Control" />

        <div class="space-y-8">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-vibrant-blue font-black tracking-[0.4em] text-[10px] uppercase mb-2">Gatekeeper_Protocol</p>
                    <h3 class="text-4xl font-black font-outfit uppercase tracking-tighter italic leading-none">KEY_REGISTRY</h3>
                </div>
                <button @click="showAddKey = true" class="px-8 py-4 bg-vibrant-blue text-black hover:bg-white transition uppercase text-[10px] font-black tracking-[0.3em] shadow-[0_0_30px_rgba(0,136,255,0.3)]">Provision_New_Key</button>
            </div>

            <!-- Key List -->
            <div class="bg-[#08080C] border border-white/5 rounded-xl overflow-hidden shadow-2xl">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/[0.02] border-b border-white/5">
                            <th class="p-6 text-[10px] font-black uppercase tracking-[0.3em] text-white/30">Identifier</th>
                            <th class="p-6 text-[10px] font-black uppercase tracking-[0.3em] text-white/30">Intelligence_Key</th>
                            <th class="p-6 text-[10px] font-black uppercase tracking-[0.3em] text-white/30">Rate_Limit</th>
                            <th class="p-6 text-[10px] font-black uppercase tracking-[0.3em] text-white/30">Quota_Usage</th>
                            <th class="p-6 text-[10px] font-black uppercase tracking-[0.3em] text-white/30 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="apiKey in apiKeys" :key="apiKey.id" class="hover:bg-white/[0.01] transition-colors group">
                            <td class="p-6">
                                <p class="text-sm font-black uppercase text-white group-hover:text-vibrant-blue transition-colors">{{ apiKey.name }}</p>
                                <p class="text-[9px] font-mono text-white/20 uppercase tracking-widest mt-1">Tenant_ID: {{ apiKey.tenant_id }}</p>
                            </td>
                            <td class="p-6">
                                <code class="text-[10px] font-mono bg-white/5 px-3 py-1 text-vibrant-blue/70 border border-vibrant-blue/20">{{ apiKey.key.substring(0, 16) }}...</code>
                            </td>
                            <td class="p-6">
                                <span class="text-xs font-bold text-white/60">{{ apiKey.rate_limit }} <span class="text-[9px] text-white/20">RPM</span></span>
                            </td>
                            <td class="p-6 w-64">
                                <div class="space-y-2 text-right">
                                    <div class="flex justify-between text-[9px] font-black tracking-widest mb-1 uppercase">
                                        <span class="text-white/40">Consumption</span>
                                        <span class="text-vibrant-blue">{{ ((apiKey.usage_count / apiKey.monthly_quota) * 100).toFixed(1) }}%</span>
                                    </div>
                                    <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                                        <div :style="{ width: (apiKey.usage_count / apiKey.monthly_quota) * 100 + '%' }" class="h-full bg-vibrant-blue shadow-[0_0_10px_#0088ff]"></div>
                                    </div>
                                    <p class="text-[8px] font-mono text-white/20 tracking-tighter mt-1 uppercase">{{ apiKey.usage_count }} / {{ apiKey.monthly_quota }} REQUESTS</p>
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <button @click="deleteKey(apiKey.id)" class="px-4 py-2 text-[9px] font-black text-red-500/40 hover:text-red-500 hover:bg-red-500/10 transition-all font-mono uppercase tracking-widest">Revoke</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddKey" class="fixed inset-0 bg-black/95 backdrop-blur-xl z-[100] flex items-center justify-center p-6">
            <div class="w-full max-w-xl bg-[#08080C] border border-white/10 rounded-2xl overflow-hidden shadow-[0_0_100px_rgba(30,58,138,0.3)]">
                <div class="p-8 bg-vibrant-blue/5 border-b border-white/10 flex justify-between items-center">
                    <h2 class="text-3xl font-black uppercase italic tracking-tighter">PROVISION_API_ACCESS</h2>
                    <button @click="showAddKey = false" class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all font-mono text-xs">ESC</button>
                </div>
                <form @submit.prevent="submitAdd" class="p-10 space-y-8">
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Key_Identifier</label>
                        <input v-model="form.name" type="text" placeholder="e.g. SKY-NET-DATA-FEED" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Associate_Tenant</label>
                        <select v-model="form.tenant_id" class="w-full bg-[#101015] border border-white/10 px-5 py-4 text-xs font-bold focus:border-vibrant-blue outline-none uppercase cursor-pointer hover:bg-white/5 transition">
                            <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                         <div class="space-y-1">
                            <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Requests_Per_Min</label>
                            <input v-model.number="form.rate_limit" type="number" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] uppercase font-black text-white/40 tracking-widest">Monthly_Quota</label>
                            <input v-model.number="form.monthly_quota" type="number" class="w-full bg-white/5 border border-white/10 px-5 py-4 text-sm font-bold focus:border-vibrant-blue outline-none transition" />
                        </div>
                    </div>
                    <div class="pt-6">
                         <button type="submit" class="w-full py-6 bg-vibrant-blue text-black text-xs font-black uppercase tracking-[0.5em] hover:bg-white transition-all transform shadow-[0_15px_40px_rgba(0,136,255,0.4)]">VALIDATE_AND_PROVISION</button>
                    </div>
                </form>
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
