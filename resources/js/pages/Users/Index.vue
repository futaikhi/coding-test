<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'User Management', href: '/users' }],
    },
});

defineProps<{ users: any[] }>();

const deleteUser = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="User Management" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">User Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola akun pengguna beserta otorisasi multi-role.</p>
            </div>
            <Link href="/users/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm shadow-blue-600/10 transition">
                + Tambah User
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama Akun</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Hak Akses (Roles)</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="role in user.roles" :key="role.id" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/30 dark:border-blue-800/30">
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/users/${user.id}/edit`" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Edit</Link>
                                <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="users?.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                Belum ada user terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>