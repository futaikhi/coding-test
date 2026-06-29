<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Role Management', href: '/roles' }],
    },
});

defineProps<{ roles: any[] }>();

const deleteRole = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus role ini?')) {
        router.delete(`/roles/${id}`);
    }
};
</script>

<template>
    <Head title="Role Management" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Role Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola tingkatan hak akses sistem.</p>
            </div>
            <Link href="/roles/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm shadow-blue-600/10 transition">
                + Tambah Role
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-20">No</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama Role</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="(role, index) in roles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ role.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/roles/${role.id}/edit`" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Edit</Link>
                                <button @click="deleteRole(role.id)" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="roles?.length === 0">
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                Belum ada data role di sistem.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>