<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// Tangkap props statistik yang dikirim dari web.php
defineProps<{
    totalUsers: number;
    totalRoles: number;
    totalMaterials: number;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            
            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users Registered</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalUsers }}</h3>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <Link href="/users" class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400">Lihat semua user →</Link>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total System Roles</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalRoles }}</h3>
                    </div>
                    <div class="rounded-lg bg-purple-50 p-3 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <Link href="/roles" class="text-xs font-semibold text-purple-600 hover:underline dark:text-purple-400">Kelola hak akses →</Link>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inventory Items</p>
                        <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ totalMaterials }}</h3>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-3 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Menunggu modul inventaris...</span>
                </div>
            </div>

        </div>

        <div class="relative min-h-[50vh] flex-1 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 md:min-h-min">
            <div class="max-w-2xl">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Selamat Datang di Sistem Manajemen Aplikasi</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Aplikasi ini dikembangkan menggunakan kombinasi teknologi berkinerja tinggi: 
                    <span class="font-semibold text-blue-600">Laravel 13</span> pada sisi backend, 
                    <span class="font-semibold text-emerald-600">Vue 3 (Inertia.js)</span> pada sisi frontend, 
                    serta menggunakan arsitektur <span class="font-semibold text-purple-600">TypeScript</span> yang kuat dan aman.
                </p>

                <div class="mt-6 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Petunjuk Singkat Penggunaan</h4>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>Gunakan menu <span class="font-semibold">Role Management</span> untuk menambah, mengubah, atau menghapus tingkatan hak akses sistem.</li>
                        <li>Menu <span class="font-semibold">User Management</span> (Khusus Administrator) digunakan untuk memetakan akun pegawai beserta hak aksesnya secara fleksibel (Multi-Role).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>