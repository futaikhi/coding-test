<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Category Management', href: '/categories' }],
    },
});

const props = defineProps<{
    categories: any[];
    filters: { search?: string }
}>();

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get('/categories', { search: search.value }, { preserveState: true, replace: true });
};

const deleteCategory = (id: string) => {
    if (confirm('Hapus kategori ini? Seluruh material di dalam kategori ini akan ikut terdampak.')) {
        router.delete(`/categories/${id}`);
    }
};
</script>

<template>
    <Head title="Manajemen Kategori Barang" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Category Management</h2>
            </div>
            <Link href="/categories/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition">
                + Tambah Kategori
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white p-4 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <div class="max-w-md flex gap-2">
                <input 
                    v-model="search" 
                    type="text" 
                    @keyup.enter="handleSearch"
                    placeholder="Cari nama kategori..." 
                    class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                />
                <button @click="handleSearch" class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-sm px-4 rounded-lg transition cursor-pointer">Cari</button>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama Kategori</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-40">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Atribut Kustom</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">{{ category.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="category.is_active" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/30">
                                    Aktif
                                </span>
                                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200/30">
                                    Non-Aktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col text-xs space-y-0.5 text-gray-500 dark:text-gray-400">
                                    <div><span class="font-medium text-gray-400">Rak:</span> {{ category.attributes?.rack || '-' }}</div>
                                    <div><span class="font-medium text-gray-400">Suhu:</span> {{ category.attributes?.temperature || '-' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/categories/${category.id}/edit`" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Edit</Link>
                                <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="categories?.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">Belum ada data kategori.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>