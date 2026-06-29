<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Category Management', href: '/categories' },
            { title: 'Tambah Kategori', href: '#' },
        ],
    },
});

const form = useForm({
    name: '',
    is_active: true, // Default nilai Boolean: true
    attributes: {
        rack: '',        // Sub-field JSON
        temperature: '', // Sub-field JSON
    }
});

const submit = () => {
    form.post('/categories');
};
</script>

<template>
    <Head title="Tambah Kategori Baru" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Tambah Kategori Baru</h2>
            
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Kategori</label>
                    <input v-model="form.name" type="text" placeholder="Contoh: Logam Berat" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" :class="{'border-red-500': form.errors.name}">
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-950 p-3 rounded-lg border border-gray-200 dark:border-gray-800/60">
                    <input 
                        v-model="form.is_active" 
                        type="checkbox" 
                        id="is_active"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500/20 dark:bg-gray-900 dark:border-gray-700"
                    >
                    <label for="is_active" class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                        Aktifkan Kategori Ini <span class="text-xs font-normal text-gray-400 block mt-0.5">(Kategori yang tidak aktif tidak akan muncul di opsi inventaris material)</span>
                    </label>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800/60 pt-4 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Spesifikasi Tambahan</h4>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Penempatan Lokasi Rak</label>
                            <input v-model="form.attributes.rack" type="text" placeholder="Contoh: RAK-A1" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Standarisasi Suhu Ruang</label>
                            <input v-model="form.attributes.temperature" type="text" placeholder="Contoh: 24°C / Dingin" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/categories" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>