<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import axios from 'axios';

const props = defineProps<{
    material: {
        id: string;
        category_id: string;
        code: string;
        name: string;
        description: string;
        published_at: string;
        document_path: string;
    }
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Material Inventory', href: '/materials' },
            { title: 'Edit Material', href: '#' },
        ],
    },
});

// FUNGSI REMOTE FETCHING (PENGGANTI SELECT2 AJAX)
const fetchCategories = async (query: string) => {
    // Tembak endpoint API backend dengan parameter 'q'
    const response = await axios.get(`/api/categories-search?q=${query}`);
    return response.data; // Mengembalikan array [{value, label}, ...]
};

// Helper untuk memformat value datetime-local dari DB agar pas masuk ke tag input HTML
const formatForInput = (dateTimeStr: string) => {
    if (!dateTimeStr) return '';
    return dateTimeStr.substring(0, 10); // Memotong string menjadi bentuk YYYY-MM-DDTHH:MM
};

const form = useForm({
    _method: 'PUT',
    category_id: props.material.category_id,
    code: props.material.code,
    name: props.material.name,
    description: props.material.description,
    published_at: formatForInput(props.material.published_at),
    document: null as File | null, // Biarkan null, hanya diisi jika user ingin mengganti file PDF lama
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.document = target.files[0];
    }
};

const submit = () => {
    form.post(`/materials/${props.material.id}`, {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="`Edit Material: ${material.name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-3xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Edit Data Material</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Biarkan kolom dokumen kosong jika tidak ingin mengganti file PDF yang sudah ada.</p>
            
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Kode Material</label>
                        <input v-model="form.code" type="text" readonly class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" :class="{'border-red-500': form.errors.code}">
                        <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Material</label>
                        <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" :class="{'border-red-500': form.errors.name}">
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Kategori Relasi</label>
                        <Multiselect v-model="form.category_id" :options="fetchCategories" :delay="300" :searchable="true" class="multiselect-premium"/>
                        <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Tanggal Rilis / Terbit</label>
                        <VueDatePicker 
                            v-model="form.published_at" 
                            model-type="yyyy-MM-dd"
                            :time-config="{ enableTimePicker: false }"
                            format="yyyy-MM-dd"
                            auto-apply
                            class="datepicker-premium"
                            :dark="true"
                        />
                        <p v-if="form.errors.published_at" class="text-red-500 text-xs mt-1">{{ form.errors.published_at }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Deskripsi / Keterangan Spesifikasi</label>
                    <textarea v-model="form.description" rows="3" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Ganti Dokumen Lampiran <span class="text-gray-400 font-normal lowercase">(kosongkan jika tidak diganti)</span></label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-950 hover:bg-gray-100 dark:border-gray-800 dark:hover:border-gray-700 transition" :class="{'border-red-500': form.errors.document}">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Klik untuk mengganti PDF</span></p>
                                <p class="text-xs text-gray-400 mt-1">{{ form.document ? `File Baru: ${form.document.name}` : 'Aplikasi tetap mempertahankan file PDF lama Anda di sistem.' }}</p>
                            </div>
                            <input type="file" accept="application/pdf" class="hidden" @change="handleFileUpload" />
                        </label>
                    </div>
                    <p v-if="form.errors.document" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.document }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/materials" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Batal</Link>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition cursor-pointer">
                        Perbarui Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>