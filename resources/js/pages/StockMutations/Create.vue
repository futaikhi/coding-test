<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';
import axios from 'axios';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Stock Mutations', href: '/stock-mutations' },
            { title: 'Catat Mutasi', href: '#' },
        ],
    },
});

const props = defineProps<{ materials: any[] }>();

const fetchMaterials = async (query: string) => {
    const response = await axios.get(`/api/materials-search?q=${query}`);
    return response.data;
};

const form = useForm({
    material_id: '',
    type: 'in', // Default: barang masuk
    quantity: 1,
    note: ''
});

const submit = () => form.post('/stock-mutations');
</script>

<template>
    <Head title="Pencatatan Mutasi Stok Baru" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Catat Sirkulasi Barang Gudang</h2>
            
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pilih Barang/Material</label>
                    <Multiselect v-model="form.material_id" :options="fetchMaterials" :delay="300" :searchable="true" placeholder="Ketik kode atau nama material..." class="multiselect-premium"/>
                    <p v-if="form.errors.material_id" class="text-red-500 text-xs mt-1">{{ form.errors.material_id }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Jenis Arus Pergerakan</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-800 cursor-pointer bg-gray-50 dark:bg-gray-950" :class="{'border-emerald-500 ring-2 ring-emerald-500/10': form.type === 'in'}">
                            <input v-model="form.type" type="radio" value="in" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">▲ Barang Masuk (IN)</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-800 cursor-pointer bg-gray-50 dark:bg-gray-950" :class="{'border-amber-500 ring-2 ring-amber-500/10': form.type === 'out'}">
                            <input v-model="form.type" type="radio" value="out" class="text-amber-600 focus:ring-amber-500">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">▼ Barang Keluar (OUT)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Jumlah Kuantitas (Volume)</label>
                    <input v-model="form.quantity" type="number" min="1" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100" :class="{'border-red-500': form.errors.quantity}">
                    <p v-if="form.errors.quantity" class="text-red-500 text-xs mt-1">{{ form.errors.quantity }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Keterangan / Alasan Mutasi</label>
                    <textarea v-model="form.note" rows="3" placeholder="Contoh: Restock supplier utama / Pengiriman ke vendor B..." class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/stock-mutations" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Simpan Catatan Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>