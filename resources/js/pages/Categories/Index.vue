<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

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

// State kendali ekspor-impor
const isExporting = ref(false);
const importStep = ref<'idle' | 'mapping' | 'importing'>('idle');
const csvHeaders = ref<string[]>([]);
const tempFilePath = ref('');

const columnMapping = ref({
    name: '',
    is_active: '',
    rack: '',
    temperature: ''
});

const progressData = ref({
    status: 'idle', current: 0, total: 0, percentage: 0, errors: [] as string[]
});

// Logic Ekspor Berfilter
const handleAsyncExport = () => {
    isExporting.value = true;
    axios.post('/categories/export', { search: search.value })
        .then(() => {
            const checkInterval = setInterval(() => {
                axios.get('/categories/export/check').then((res) => {
                    if (res.data.ready) {
                        clearInterval(checkInterval);
                        isExporting.value = false;
                        window.location.href = res.data.download_url;
                    }
                });
            }, 2000);
        });
};

// Logic Impor Data Wizard
const handleCsvUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const formData = new FormData();
        formData.append('file', target.files[0]);
        axios.post('/categories/import/upload', formData).then((res) => {
            csvHeaders.value = res.data.headers;
            tempFilePath.value = res.data.file_path;
            importStep.value = 'mapping';
        }).catch(() => alert('File harus berformat .csv murni!'));;
    }
};

const startImportProcessing = () => {
    importStep.value = 'importing';
    axios.post('/categories/import/process', {
        file_path: tempFilePath.value,
        mapping: columnMapping.value
    }).then(() => {
        const pollInterval = setInterval(() => {
            axios.get('/categories/import/progress').then((res) => {
                progressData.value = res.data;
                if (res.data.status === 'completed') {
                    clearInterval(pollInterval);
                    importStep.value = 'idle';
                    router.reload({ only: ['categories'] });
                }
            });
        }, 1000);
    });
};

const closeImportWizard = () => {
    importStep.value = 'idle';
    progressData.value = { status: 'idle', current: 0, total: 0, percentage: 0, errors: [] };
};
</script>

<template>

    <Head title="Manajemen Kategori Barang" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Category Management</h2>
            </div>
            <div class="flex items-center gap-2">
                <label
                    class="cursor-pointer border border-blue-600 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition">
                    <span>Import CSV</span>
                    <input type="file" accept=".csv" class="hidden" @change="handleCsvUpload"
                        :disabled="importStep !== 'idle'" />
                </label>

                <button @click="handleAsyncExport" :disabled="isExporting"
                    class="inline-flex items-center gap-1.5 border border-emerald-600 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white text-xs font-semibold py-2.5 px-3 rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer">
                    {{ isExporting ? 'Exporting...' : 'Export Excel' }}
                </button>

                <Link href="/categories/create"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 px-3 rounded-lg shadow-sm transition">
                    + Tambah Kategori
                </Link>
            </div>
        </div>

    <div
            class="rounded-xl border border-gray-200/80 bg-white p-4 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <div class="max-w-md flex gap-2">
                <input v-model="search" type="text" @keyup.enter="handleSearch" placeholder="Cari nama kategori..."
                    class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" />
                <button @click="handleSearch"
                    class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-sm px-4 rounded-lg transition cursor-pointer">Cari</button>
            </div>
        </div>
        <div v-if="importStep !== 'idle'"
            class="rounded-xl border-2 border-dashed border-blue-500/40 bg-blue-50/5 p-5 dark:bg-blue-950/5 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-ping"></span>
                    Asynchronous CSV Import Engine
                </h3>
                <button @click="closeImportWizard" class="text-xs text-gray-400 hover:text-gray-600 cursor-pointer">Batal & Tutup ✕</button>
            </div>

            <div v-if="importStep === 'mapping'" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Nama Kategori (*Wajib)</label>
                        <select v-model="columnMapping.name"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Status Aktif (Boolean)</label>
                        <select v-model="columnMapping.is_active"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Lokasi Rak (JSON)</label>
                        <select v-model="columnMapping.rack"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Suhu Ruang (JSON)</label>
                        <select v-model="columnMapping.temperature"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                </div>

                <button @click="startImportProcessing" :disabled="columnMapping.name === ''"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded-lg disabled:opacity-40 shadow-sm cursor-pointer">
                    Jalankan Impor Kategori →
                </button>
            </div>

            <div v-if="importStep === 'importing'" class="space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500">Memproses Kategori: <b>{{ progressData.current }}</b> / {{
                        progressData.total }} baris</span>
                    <span class="text-blue-600 font-bold">{{ progressData.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full transition-all duration-300"
                        :style="{ width: progressData.percentage + '%' }"></div>
                </div>

                <div v-if="progressData.errors.length > 0"
                    class="bg-red-50 dark:bg-red-950/20 border border-red-200/40 rounded-lg p-3 max-h-24 overflow-y-auto">
                    <ul class="text-[10px] list-disc list-inside text-red-500 font-mono space-y-0.5">
                        <li v-for="(err, idx) in progressData.errors" :key="idx">{{ err }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div
            class="rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama
                                Kategori</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-40">Status
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Atribut
                                Kustom</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="category in categories" :key="category.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                {{ category.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="category.is_active"
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/30">
                                    Aktif
                                </span>
                                <span v-else
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 border border-red-200/30">
                                    Non-Aktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col text-xs space-y-0.5 text-gray-500 dark:text-gray-400">
                                    <div><span class="font-medium text-gray-400">Rak:</span> {{
                                        category.attributes?.rack || '-' }}</div>
                                    <div><span class="font-medium text-gray-400">Suhu:</span> {{
                                        category.attributes?.temperature || '-' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/categories/${category.id}/edit`"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Edit</Link>
                                <button @click="deleteCategory(category.id)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="categories?.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                Belum ada data kategori.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>