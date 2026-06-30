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
}>();
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
    </div>
</template>