<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Role Management',
                href: '/roles', // Menggunakan path statis
            },
        ],
    },
});

defineProps({
    roles: Array as () => any[]
});

const deleteRole = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus role ini?')) {
        // Menggunakan template literal untuk path dinamis tanpa route()
        router.delete(`/roles/${id}`);
    }
};
</script>

<template>
    <Head title="Role Management" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        
        <div class="flex justify-between items-center mb-2">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">Daftar Roles</h2>
            <Link href="/roles/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                + Tambah Role
            </Link>
        </div>

        <div class="relative flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:bg-gray-900 dark:border-sidebar-border">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(role, index) in roles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ role.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-3">
                                <Link :href="`/roles/${role.id}/edit`" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Edit
                                </Link>
                                <button @click="deleteRole(role.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <tr v-if="roles?.length === 0">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                Belum ada data role.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>