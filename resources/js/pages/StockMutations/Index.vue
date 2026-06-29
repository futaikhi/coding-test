<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';
import axios from 'axios';
import { parseAuditLog } from '@/utils/auditParser';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Stock Mutations', href: '/stock-mutations' }],
    },
});

const props = defineProps<{
    mutations: any[];
    materials: any[];
    filters: {
        search?: string;
        type?: string;
        material_id?: string;
        sort_by?: string;
        sort_order?: string;
    }
    audit_logs: any[];
}>();

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const materialId = ref(props.filters.material_id || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortOrder = ref(props.filters.sort_order || 'desc');

const handleFilter = () => {
    router.get('/stock-mutations', {
        search: search.value,
        type: type.value,
        material_id: materialId.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    type.value = '';
    materialId.value = '';
    sortBy.value = 'created_at';
    sortOrder.value = 'desc';
    handleFilter();
};

const deleteMutation = (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus log mutasi ini (Soft Delete)?')) {
        router.delete(`/stock-mutations/${id}`);
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const fetchMaterials = async (query: string) => {
    const response = await axios.get(`/api/materials-search?q=${query}`);
    return response.data;
};

const isExporting = ref(false);
const importStep = ref<'idle' | 'mapping' | 'importing'>('idle');
const csvHeaders = ref<string[]>([]);
const tempFilePath = ref('');

const columnMapping = ref({
    material_identifier: '',
    type: '',
    quantity: '',
    note: ''
});

const progressData = ref({
    status: 'idle', current: 0, total: 0, percentage: 0, errors: [] as string[]
});

// Handler Ekspor Asinkronus Berfilter
const handleAsyncExport = () => {
    isExporting.value = true;
    axios.post('/stock-mutations/export', {
        search: search.value,
        type: type.value,
        material_id: materialId.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value
    }).then(() => {
        const checkInterval = setInterval(() => {
            axios.get('/stock-mutations/export/check').then((res) => {
                if (res.data.ready) {
                    clearInterval(checkInterval);
                    isExporting.value = false;
                    window.location.href = res.data.download_url;
                }
            });
        }, 2000);
    });
};

// Handler Impor Data Wizard Dinamis
const handleCsvUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const formData = new FormData();
        formData.append('file', target.files[0]);
        axios.post('/stock-mutations/import/upload', formData).then((res) => {
            csvHeaders.value = res.data.headers;
            tempFilePath.value = res.data.file_path;
            importStep.value = 'mapping';
        }).catch(() => alert('File harus berformat .csv murni!'));;
    }
};

const startImportProcessing = () => {
    importStep.value = 'importing';
    axios.post('/stock-mutations/import/process', {
        file_path: tempFilePath.value,
        mapping: columnMapping.value
    }).then(() => {
        const pollInterval = setInterval(() => {
            axios.get('/stock-mutations/import/progress').then((res) => {
                progressData.value = res.data;
                if (res.data.status === 'completed') {
                    clearInterval(pollInterval);
                    importStep.value = 'idle';
                    router.reload({ only: ['stock-mutations'] });
                    alert('Impor logs mutasi selesai diproses server!');
                }
            });
        }, 1000);
    });
};

const closeImportWizard = () => {
    importStep.value = 'idle';
    progressData.value = { status: 'idle', current: 0, total: 0, percentage: 0, errors: [] };
};

const formatAuditDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>

    <Head title="Histori Mutasi Stok" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Stock Mutations Log</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau arus logistik barang keluar dan masuk
                    gudang secara real-time.</p>
            </div>
            <div class="flex items-center gap-2">
                <label
                    class="cursor-pointer border border-blue-600 text-blue-600 dark:text-blue-400 hover:bg-blue-600 hover:text-white text-xs font-semibold py-2.5 px-3 rounded-lg transition">
                    <span>Import Logs</span>
                    <input type="file" accept=".csv" class="hidden" @change="handleCsvUpload"
                        :disabled="importStep !== 'idle'" />
                </label>

                <button @click="handleAsyncExport" :disabled="isExporting"
                    class="border border-emerald-600 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white text-xs font-semibold py-2.5 px-3 rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer">
                    {{ isExporting ? 'Exporting...' : 'Export Excel' }}
                </button>

                <Link href="/stock-mutations/create"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 px-3 rounded-lg shadow-sm transition">
                    + Catat Mutasi
                </Link>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200/80 bg-white p-4 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Cari
                        Catatan</label>
                    <input v-model="search" type="text" @keyup.enter="handleFilter" placeholder="Ketik deskripsi..."
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tipe
                        Arus</label>
                    <select v-model="type" @change="handleFilter"
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <option value="">Semua Pergerakan</option>
                        <option value="in">Barang Masuk (In)</option>
                        <option value="out">Barang Keluar (Out)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Filter
                        Barang</label>
                    <Multiselect v-model="materialId" @change="handleFilter" :options="fetchMaterials" :delay="300"
                        :searchable="true" placeholder="Pilih kategori barang..." class="multiselect-premium" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Urutan
                        Kuantitas</label>
                    <div class="flex gap-2">
                        <select v-model="sortBy" @change="handleFilter"
                            class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            <option value="created_at">Waktu Transaksi</option>
                            <option value="quantity">Jumlah Kuantitas</option>
                        </select>
                        <select v-model="sortOrder" @change="handleFilter"
                            class="rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            <option value="desc">Besar-Kecil</option>
                            <option value="asc">Kecil-Besar</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button @click="handleFilter"
                        class="flex-1 bg-gray-100 dark:bg-gray-800 hover:bg-blue-600 hover:text-white text-gray-700 dark:text-gray-300 font-semibold text-sm py-2 rounded-lg transition shadow-sm cursor-pointer">Saring</button>
                    <button @click="resetFilter"
                        class="bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-500 text-sm py-2 px-3 rounded-lg transition cursor-pointer">✕</button>
                </div>
            </div>
        </div>
        <div v-if="importStep !== 'idle'"
            class="rounded-xl border-2 border-dashed border-blue-500/40 bg-blue-50/5 p-5 dark:bg-blue-950/5 shadow-sm mt-4">
            <div class="flex justify-between items-center mb-3">
                <h3
                    class="text-sm font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 flex items-center gap-2">
                    <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-ping"></span>
                    Asynchronous CSV Import Engine
                </h3>
                <button @click="closeImportWizard" class="text-xs text-gray-400 hover:text-gray-600 cursor-pointer">
                    Batal & Tutup✕</button>
            </div>

            <div v-if="importStep === 'mapping'" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Identitas Material Code/Name
                            (*Wajib)</label>
                        <select v-model="columnMapping.material_identifier"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Jenis Arus In/Out
                            (*Wajib)</label>
                        <select v-model="columnMapping.type"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Jumlah Vol/Qty
                            (*Wajib)</label>
                        <select v-model="columnMapping.quantity"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-400 mb-1">Keterangan / Alasan
                            Note</label>
                        <select v-model="columnMapping.note"
                            class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2">
                            <option value="">-- Pilih Kolom CSV --</option>
                            <option v-for="(h, idx) in csvHeaders" :key="idx" :value="idx">{{ h }}</option>
                        </select>
                    </div>
                </div>

                <button @click="startImportProcessing"
                    :disabled="columnMapping.material_identifier === '' || columnMapping.type === '' || columnMapping.quantity === ''"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded-lg disabled:opacity-40 shadow-sm cursor-pointer">
                    Jalankan Impor Log Mutasi →
                </button>
            </div>

            <div v-if="importStep === 'importing'" class="space-y-3">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500">Memproses Records: <b>{{ progressData.current }}</b> / {{
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
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Waktu
                                Record</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama Barang
                            </th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-36">Tipe
                                Arus</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-36">Jumlah
                                Vol</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                Keterangan/Note</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="mut in mutations" :key="mut.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-400">{{
                                formatDate(mut.created_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                {{ mut.material?.name || 'Material Hilang/Terhapus' }}
                                <span class="block text-xs font-mono font-normal text-gray-400 mt-0.5">{{
                                    mut.material?.code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="mut.type === 'in'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/30">
                                    ▲ MASUK (IN)
                                </span>
                                <span v-else
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/30">
                                    ▼ KELUAR (OUT)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold"
                                :class="mut.type === 'in' ? 'text-emerald-600' : 'text-amber-600'">
                                {{ mut.type === 'in' ? '+' : '-' }} {{ mut.quantity }} pcs
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ mut.note
                                || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/stock-mutations/${mut.id}/edit`"
                                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Edit Note</Link>
                                <button @click="deleteMutation(mut.id)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="mutations?.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                Belum ada rekaman sirkulasi mutasi barang.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div
        class="mt-8 rounded-xl border border-gray-200/60 bg-white p-5 dark:border-gray-800/60 dark:bg-gray-900 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <span class="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">Jejak Audit Aktivitas
                Modul</h3>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-800">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-left text-xs">
                <thead class="bg-gray-50/70 dark:bg-gray-800/40 text-gray-400 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-2.5 w-36">Date</th>
                        <th class="px-4 py-2.5 w-44">User / Actor</th>
                        <th class="px-4 py-2.5 w-28">Action</th>
                        <th class="px-4 py-2.5">Note (Apa Yang Terjadi)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-600 dark:text-gray-400 font-mono">
                    <tr v-for="log in audit_logs" :key="log.id"
                        class="hover:bg-gray-50/40 dark:hover:bg-gray-800/10 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-400">{{ formatAuditDate(log.created_at) }}</td>
                        <td
                            class="px-4 py-3 whitespace-nowrap font-sans font-semibold text-gray-800 dark:text-gray-200">
                            {{ log.user ? log.user.name : 'System Worker' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide" :class="{
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400': log.event === 'created' || log.event === 'imported',
                                'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400': log.event === 'updated',
                                'bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-400': log.event === 'exported',
                                'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400': log.event === 'deleted',
                            }">
                                {{ log.event }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-sans text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ parseAuditLog(log) }}
                        </td>
                    </tr>
                    <tr v-if="audit_logs?.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400 font-sans">Belum ada jejak aktivitas
                            transaksi pada modul ini.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>