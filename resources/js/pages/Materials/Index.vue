<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Material Inventory', href: '/materials' }],
    },
});

// Tangkap props yang dilempar oleh MaterialController
const props = defineProps<{
    materials: any[];
    filters: {
        search?: string;
        category_id?: string;
        sort_by?: string;
        sort_order?: string;
    }
}>();

const isExporting = ref(false);
const exportMessage = ref('');

const handleAsyncExport = () => {
    isExporting.value = true;
    exportMessage.value = 'Mengirim kriteria filter ke antrean server...';

    // REVISI: Kirim objek data filter yang sedang aktif di UI saat ini ke Backend
    axios.post('/materials/export', {
        search: search.value,
        category_id: categoryId.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value
    })
        .then(() => {
            exportMessage.value = 'Mengekspor data sesuai filter pilihan Anda, mohon tunggu...';

            // Jalankan polling deteksi berkala setiap 2 detik
            const checkInterval = setInterval(() => {
                axios.get('/materials/export/check')
                    .then((res) => {
                        if (res.data.ready) {
                            clearInterval(checkInterval);
                            isExporting.value = false;
                            exportMessage.value = '';

                            // Download file hasil filter kustom
                            window.location.href = res.data.download_url;
                        }
                    });
            }, 2000);
        })
        .catch(() => {
            isExporting.value = false;
            exportMessage.value = 'Gagal memproses ekspor berfilter.';
        });
};

// Definisikan state kendali alur kerja import wizard
const importStep = ref<'idle' | 'mapping' | 'importing'>('idle');
const csvHeaders = ref<string[]>([]);
const tempFilePath = ref('');

// Objek untuk menampung kecocokan mapping pilihan user
const columnMapping = ref({
    name: '',
    category: '',
    published_at: '',
    description: ''
});

// State monitoring real-time live progress bar
const progressData = ref({
    status: 'idle',
    current: 0,
    total: 0,
    percentage: 0,
    errors: [] as string[]
});

// Fungsi Tahap 1: Upload File CSV & Tangkap Header
const handleCsvUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const formData = new FormData();
        formData.append('file', target.files[0]);

        axios.post('/materials/import/upload', formData)
            .then((res) => {
                csvHeaders.value = res.data.headers;
                tempFilePath.value = res.data.file_path;
                importStep.value = 'mapping'; // Alihkan screen ke fase pilih kolom
            })
            .catch(() => alert('File harus berformat .csv murni!'));
    }
};

// Fungsi Tahap 2: Eksekusi Queue & Jalankan Live Polling Progress Bar
const startImportProcessing = () => {
    importStep.value = 'importing';

    axios.post('/materials/import/process', {
        file_path: tempFilePath.value,
        mapping: columnMapping.value
    })
        .then(() => {
            // Jalankan sistem polling interval deteksi setiap 1 detik
            const pollInterval = setInterval(() => {
                axios.get('/materials/import/progress')
                    .then((res) => {
                        progressData.value = res.data;

                        // Hentikan polling jika status di backend telah selesai (completed)
                        if (res.data.status === 'completed') {
                            clearInterval(pollInterval);
                            importStep.value = 'idle';
                            alert('Selamat! Seluruh proses asinkronus import data selesai dimasukkan.');
                            // Refresh data halaman tabel depan inertia tanpa reload browser
                            router.reload({ only: ['materials'] });
                        }
                    });
            }, 1000);
        });
};

const closeImportWizard = () => {
    importStep.value = 'idle';
    progressData.value = { status: 'idle', current: 0, total: 0, percentage: 0, errors: [] };
};

// Inisialisasi state reactive untuk form filter berdasarkan data filter sebelumnya (preserve state)
const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

// Fungsi untuk mengeksekusi pencarian dan filter ke server
const handleFilter = () => {
    router.get('/materials', {
        search: search.value,
        category_id: categoryId.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value
    }, {
        preserveState: true, // Menjaga input agar tidak ter-reset setelah halaman memuat data baru
        replace: true       // Mengganti history URL agar tidak menumpuk saat user klik bolak-balik
    });
};

// Fungsi reset filter ke kondisi awal
const resetFilter = () => {
    search.value = '';
    categoryId.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    handleFilter();
};

// Fungsi hapus data (Soft Delete)
const deleteMaterial = (id: string) => {
    if (confirm('Apakah Anda yakin ingin memindahkan material ini ke tong sampah (Soft Delete)?')) {
        router.delete(`/materials/${id}`);
    }
};

// Helper format tanggal agar tampil rapi (Contoh: 29 Juni 2026)
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

// FUNGSI REMOTE FETCHING (PENGGANTI SELECT2 AJAX)
const fetchCategories = async (query: string) => {
    // Tembak endpoint API backend dengan parameter 'q'
    const response = await axios.get(`/api/categories-search?q=${query}`);
    return response.data; // Mengembalikan array [{value, label}, ...]
};
</script>

<template>

    <Head title="Manajemen Material Inventaris" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Material Inventory</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data fisik barang, klasifikasi
                    kategori, dan dokumen PDF lampiran.</p>
            </div>
            <div class="flex items-center gap-3">
                <label
                    class="cursor-pointer border border-blue-600 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition">
                    <span>Import CSV</span>
                    <input type="file" accept=".csv" class="hidden" @change="handleCsvUpload"
                        :disabled="importStep !== 'idle'" />
                </label>
                <button @click="handleAsyncExport" :disabled="isExporting"
                    class="inline-flex items-center gap-2 border border-emerald-600 dark:border-emerald-500 hover:bg-emerald-600 text-emerald-600 dark:text-emerald-400 hover:text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <svg v-if="isExporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ isExporting ? 'Exporting...' : 'Export Excel' }}
                </button>
                <Link href="/materials/create"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm shadow-blue-600/10 transition">
                    + Tambah Material
                </Link>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200/80 bg-white p-4 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <div class="lg:col-span-2">
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Cari
                        Kode / Nama</label>
                    <input v-model="search" type="text" @keyup.enter="handleFilter"
                        placeholder="Ketik lalu tekan enter..."
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" />
                </div>

                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Kategori</label>
                    <Multiselect v-model="categoryId" @change="handleFilter" :options="fetchCategories" :delay="300"
                        :searchable="true" placeholder="Pilih kategori barang..." class="multiselect-premium" />
                </div>

                <div>
                    <label
                        class="block text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">Urutkan
                        Berdasarkan</label>
                    <div class="flex gap-2">
                        <select v-model="sortBy" @change="handleFilter"
                            class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                            <option value="created_at">Tgl Input</option>
                            <option value="name">Nama Barang</option>
                            <option value="code">Kode Barang</option>
                            <option value="published_at">Tgl Rilis</option>
                        </select>
                        <select v-model="sortOrder" @change="handleFilter"
                            class="rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                            <option value="desc">Z-A</option>
                            <option value="asc">A-Z</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button @click="handleFilter"
                        class="flex-1 bg-gray-100 dark:bg-gray-800 hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white dark:hover:text-white text-gray-700 dark:text-gray-300 font-semibold text-sm py-2 px-3 rounded-lg transition shadow-sm cursor-pointer">
                        Saring
                    </button>
                    <button @click="resetFilter"
                        class="bg-gray-50 dark:bg-gray-950 hover:bg-gray-200 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-800 text-gray-500 text-sm py-2 px-2 rounded-lg transition cursor-pointer"
                        title="Reset Filter">
                        ✕
                    </button>
                </div>
            </div>
        </div>
        <div v-if="importStep !== 'idle'"
            class="rounded-xl border-2 border-dashed border-blue-500/40 bg-blue-50/5 p-6 dark:bg-blue-950/5 shadow-sm mb-4">
            <div class="flex justify-between items-center mb-4">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-ping"></span>
                    Asynchronous CSV Import Engine
                </h3>
                <button @click="closeImportWizard" class="text-xs text-gray-400 hover:text-gray-600 cursor-pointer">Batal & Tutup
                    ✕</button>
            </div>

            <div v-if="importStep === 'mapping'" class="space-y-4">
                <p class="text-xs text-gray-500">Sistem mendeteksi kolom file Anda. Silakan cocokkan field database
                    (Kiri) dengan Kolom file CSV Anda (Kanan):</p>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Nama Material (*Wajib)</label>
                        <select v-model="columnMapping.name"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Nama Kategori (*Wajib)</label>
                        <select v-model="columnMapping.category"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Tanggal Rilis</label>
                        <select v-model="columnMapping.published_at"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Deskripsi Spesifikasi</label>
                        <select v-model="columnMapping.description"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                </div>

                <button @click="startImportProcessing" :disabled="columnMapping.name === '' || columnMapping.category === ''"
                    class="mt-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded-lg disabled:opacity-40 cursor-pointer">
                    Mulai Proses Antrean Impor →
                </button>
            </div>

            <div v-if="importStep === 'importing'" class="space-y-4">
                <div class="flex justify-between items-center text-xs font-semibold">
                    <span class="text-gray-500">Memproses: <span class="text-blue-600 font-bold">{{ progressData.current
                    }}</span> / {{ progressData.total }} baris data</span>
                    <span class="text-blue-600">{{ progressData.percentage }}% Selesai</span>
                </div>

                <div class="w-full bg-gray-200 dark:bg-gray-800 h-3 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-full transition-all duration-300"
                        :style="{ width: progressData.percentage + '%' }"></div>
                </div>

                <div v-if="progressData.errors.length > 0"
                    class="bg-red-50 dark:bg-red-950/20 border border-red-200/40 rounded-lg p-3 max-h-32 overflow-y-auto">
                    <h5 class="text-xs font-bold text-red-600 mb-1">Peringatan Log Validasi Gagal ({{
                        progressData.errors.length }}):</h5>
                    <ul class="text-[11px] list-disc list-inside text-red-500 font-mono space-y-0.5">
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
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-32">Kode
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama
                                Material</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Kategori
                                Relasi</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-40">
                                Tanggal Rilis</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-32">File
                                PDF</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="material in materials" :key="material.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ material.code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                <div>{{ material.name }}</div>
                                <span class="text-xs font-normal text-gray-400 line-clamp-1 max-w-xs">{{
                                    material.description || 'Tidak ada deskripsi' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 text-xs font-medium">
                                    {{ material.category?.name || 'Kategori Terhapus' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(material.published_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a v-if="material.document_path" :href="`/storage/${material.document_path}`"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-semibold underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Lihat PDF
                                </a>
                                <span v-else class="text-xs text-gray-400">Tidak ada file</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/materials/${material.id}/edit`"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Edit</Link>
                                <button @click="deleteMaterial(material.id)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="materials?.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                Tidak ada data material yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>