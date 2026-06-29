<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{
    mutation: {
        id: string;
        material: { code: string; name: string; };
        type: string;
        quantity: number;
        note: string;
    }
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Stock Mutations', href: '/stock-mutations' },
            { title: 'Edit Note', href: '#' },
        ],
    },
});

const form = useForm({
    note: props.mutation.note || ''
});

const submit = () => form.put(`/stock-mutations/${props.mutation.id}`);
</script>

<template>
    <Head title="Edit Catatan Mutasi" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Edit Catatan Mutasi</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Volume kuantitas dan jenis pergerakan stok terkunci permanen demi alasan validitas audit data logistik.</p>
            
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid gap-4 md:grid-cols-3 bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border border-gray-200 dark:border-gray-800/60">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Barang</span>
                        <span class="text-sm font-semibold">{{ mutation.material?.name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jenis Arus</span>
                        <span class="text-sm font-bold" :class="mutation.type === 'in' ? 'text-emerald-600' : 'text-amber-600'">
                            {{ mutation.type === 'in' ? '▲ Masuk' : '▼ Keluar' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Volume Kuantitas</span>
                        <span class="text-sm font-bold">{{ mutation.quantity }} pcs</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Perbarui Keterangan / Alasan Mutasi</label>
                    <textarea v-model="form.note" rows="4" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" placeholder="Tulis perubahan catatan logs di sini..."></textarea>
                    <p v-if="form.errors.note" class="text-red-500 text-xs mt-1">{{ form.errors.note }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/stock-mutations" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Perbarui Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>