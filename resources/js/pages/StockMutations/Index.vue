<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';
import axios from 'axios';

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
</script>

<template>
    <Head title="Histori Mutasi Stok" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Stock Mutations Log</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau arus logistik barang keluar dan masuk gudang secara real-time.</p>
            </div>
            <Link href="/stock-mutations/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition">
                + Catat Mutasi
            </Link>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white p-4 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Cari Catatan</label>
                    <input v-model="search" type="text" @keyup.enter="handleFilter" placeholder="Ketik deskripsi..." class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Tipe Arus</label>
                    <select v-model="type" @change="handleFilter" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                        <option value="">Semua Pergerakan</option>
                        <option value="in">Barang Masuk (In)</option>
                        <option value="out">Barang Keluar (Out)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Filter Barang</label>
                    <Multiselect v-model="materialId" @change="handleFilter" :options="fetchMaterials" :delay="300" :searchable="true"
                            placeholder="Pilih kategori barang..." class="multiselect-premium" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Urutan Kuantitas</label>
                    <div class="flex gap-2">
                        <select v-model="sortBy" @change="handleFilter" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            <option value="created_at">Waktu Transaksi</option>
                            <option value="quantity">Jumlah Kuantitas</option>
                        </select>
                        <select v-model="sortOrder" @change="handleFilter" class="rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600">
                            <option value="desc">Besar-Kecil</option>
                            <option value="asc">Kecil-Besar</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button @click="handleFilter" class="flex-1 bg-gray-100 dark:bg-gray-800 hover:bg-blue-600 hover:text-white text-gray-700 dark:text-gray-300 font-semibold text-sm py-2 rounded-lg transition shadow-sm cursor-pointer">Saring</button>
                    <button @click="resetFilter" class="bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 text-gray-500 text-sm py-2 px-3 rounded-lg transition cursor-pointer">✕</button>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white dark:border-gray-800/80 dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Waktu Record</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-36">Tipe Arus</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-36">Jumlah Vol</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Keterangan/Note</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                        <tr v-for="mut in mutations" :key="mut.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-400">{{ formatDate(mut.created_at) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                {{ mut.material?.name || 'Material Hilang/Terhapus' }}
                                <span class="block text-xs font-mono font-normal text-gray-400 mt-0.5">{{ mut.material?.code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span v-if="mut.type === 'in'" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/30">
                                    ▲ MASUK (IN)
                                </span>
                                <span v-else class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/30">
                                    ▼ KELUAR (OUT)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" :class="mut.type === 'in' ? 'text-emerald-600' : 'text-amber-600'">
                                {{ mut.type === 'in' ? '+' : '-' }} {{ mut.quantity }} pcs
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ mut.note || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium space-x-3">
                                <Link :href="`/stock-mutations/${mut.id}/edit`" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Edit Note</Link>
                                <button @click="deleteMutation(mut.id)" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                        <tr v-if="mutations?.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500 text-sm">Belum ada rekaman sirkulasi mutasi barang.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>