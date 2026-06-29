<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Role Management', href: '/roles' },
            { title: 'Tambah Role', href: '#' },
        ],
    },
});

const form = useForm({ name: '' });
const submit = () => form.post('/roles');
</script>

<template>
    <Head title="Tambah Role" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Tambah Role Baru</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Pastikan nama role unik dan merepresentasikan hak akses.</p>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Role</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                        :class="{'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.name}"
                        placeholder="Contoh: Staff Gudang"
                    >
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">{{ form.errors.name }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/roles" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Simpan Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>