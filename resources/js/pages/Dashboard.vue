<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Layers, Package, ArrowUpRight, ArrowDownLeft, Clock } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard Analytics', href: '#' }],
    },
});

// Tangkap data statistik riil kiriman dari backend controller
defineProps<{
    stats: {
        total_materials: number;
        total_categories: number;
        total_incoming: number;
        total_outgoing: number;
    },
    recent_mutations: any[]
}>();

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dashboard Ringkasan Logistik" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Gudang Utama Dashboard</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ringkasan indikator performa volume logistik dan sirkulasi inventaris secara real-time.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <div class="rounded-xl border border-gray-200/80 bg-white p-5 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Jenis Material</p>
                    <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ stats.total_materials }} <span class="text-xs font-normal text-gray-400">Items</span></h3>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-lg text-blue-600 dark:text-blue-400">
                    <Package class="h-6 w-6" />
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Kategori Aktif</p>
                    <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ stats.total_categories }} <span class="text-xs font-normal text-gray-400">Grup</span></h3>
                </div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <Layers class="h-6 w-6" />
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Volume Barang Masuk</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">+ {{ stats.total_incoming }} <span class="text-xs font-normal text-gray-400">Pcs</span></h3>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                    <ArrowUpRight class="h-6 w-6" />
                </div>
            </div>

            <div class="rounded-xl border border-gray-200/80 bg-white p-5 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Volume Barang Keluar</p>
                    <h3 class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">- {{ stats.total_outgoing }} <span class="text-xs font-normal text-gray-400">Pcs</span></h3>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-lg text-amber-600 dark:text-amber-400">
                    <ArrowDownLeft class="h-6 w-6" />
                </div>
            </div>
            
        </div>

        <div class="grid gap-6 lg:grid-cols6 flex-1">
            
            <div class="lg:col-span-2 rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <Clock class="h-4 w-4 text-gray-400" />
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Aktivitas Arus Sirkulasi Barang Terbaru</h3>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-left">
                        <thead class="bg-gray-50/60 dark:bg-gray-800/30 text-xs font-bold text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Material / Barang</th>
                                <th class="px-5 py-3 w-28">Tipe Arus</th>
                                <th class="px-5 py-3 text-right w-28">Volume</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm text-gray-700 dark:text-gray-300">
                            <tr v-for="mut in recent_mutations" :key="mut.id" class="hover:bg-gray-50/40 dark:hover:bg-gray-800/10 transition">
                                <td class="px-5 py-3.5 text-xs font-mono text-gray-400">{{ formatDate(mut.created_at) }}</td>
                                <td class="px-5 py-3.5 font-semibold text-gray-900 dark:text-white">
                                    {{ mut.material?.name || 'Material Terhapus' }}
                                    <span class="block text-xs font-mono font-normal text-gray-400 mt-0.5">{{ mut.material?.code }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span v-if="mut.type === 'in'" class="inline-block px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/20">
                                        MASUK
                                    </span>
                                    <span v-else class="inline-block px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/20">
                                        KELUAR
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold" :class="mut.type === 'in' ? 'text-emerald-600' : 'text-amber-600'">
                                    {{ mut.type === 'in' ? '+' : '-' }} {{ mut.quantity }} pcs
                                </td>
                            </tr>
                            <tr v-if="recent_mutations?.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-xs">Belum ada aktivitas mutasi barang yang tercatat hari ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>