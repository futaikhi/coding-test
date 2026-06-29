<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'User Management', href: '/users' }],
    },
});

defineProps<{ users: any[] }>();

const deleteUser = (id: number) => {
    if (confirm('Yakin ingin hapus user ini?')) {
        router.delete(`/users/${id}`);
    }
};
</script>

<template>
    <Head title="User Management" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold">Daftar Users</h2>
            <Link href="/users/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Tambah User</Link>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-6 py-4 text-sm">{{ user.name }}</td>
                        <td class="px-6 py-4 text-sm">{{ user.email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span v-for="role in user.roles" :key="role.id" class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs ml-2">{{ role.name }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <Link :href="`/users/${user.id}/edit`" class="text-indigo-600">Edit</Link>
                            <button @click="deleteUser(user.id)" class="text-red-600">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>