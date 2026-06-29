<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

defineOptions({
    layout: { breadcrumbs: [{ title: 'System Audit Trails', href: '#' }] },
});

const props = defineProps<{ logs: any; filters: { event?: string, module?: string } }>();

const eventFilter = ref(props.filters.event || '');
const moduleFilter = ref(props.filters.module || ''); // State baru

const handleFilter = () => {
    router.get('/audit-logs', {
        event: eventFilter.value,
        module: moduleFilter.value
    }, { preserveState: true, replace: true });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const simplifyModelName = (modelPath: string) => {
    if (!modelPath) return 'System';
    return modelPath.replace('App\\Models\\', '');
};

// TRIK UTAMA: Hanya ambil nama field/kolom yang berubah, sembunyikan isinya!
const formatChangedColumns = (log: any) => {
    if (log.event === 'updated' && log.new_values) {
        // Ambil nama properti objek JSON yang mengalami perubahan nilai
        const fields = Object.keys(log.new_values).filter(k => !['updated_at', 'created_at'].includes(k));
        return `Mengubah spesifikasi pada kolom: [ ${fields.join(', ')} ]`;
    }
    if (log.event === 'created') return 'Membuat data inventaris baru ke sistem.';
    if (log.event === 'deleted') return 'Menghapus record data dari sistem.';
    if (log.event === 'imported') return log.new_values?.summary || 'Berhasil memproses dokumen impor CSV.';
    if (log.event === 'exported') return log.new_values?.info || 'Mengekspor data ke file Excel/CSV.';
    return 'Aktivitas sistem internal.';
};
</script>

<template>

    <Head title="System Audit Trails" />

    <div class="flex h-full flex-1 flex-col gap-4 p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Audit Trails (Library Engine)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Riwayat sirkulasi data terpusat menggunakan
                    library terenkripsi.</p>
            </div>

            <div class="flex gap-2">
                <select v-model="moduleFilter" @change="handleFilter"
                    class="rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2 text-xs text-gray-700 dark:text-gray-300">
                    <option value="">Semua Modul</option>
                    <option value="material">Modul Material</option>
                    <option value="category">Modul Kategori</option>
                    <option value="mutation">Modul Mutasi Stok</option>
                </select>

                <select v-model="eventFilter" @change="handleFilter"
                    class="rounded-lg border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-2 text-xs text-gray-700 dark:text-gray-300">
                    <option value="">Semua Aksi</option>
                    <option value="created">Penyimpanan Baru</option>
                    <option value="updated">Perubahan Data</option>
                    <option value="deleted">Penghapusan</option>
                    <option value="imported">Proses Impor</option>
                    <option value="exported">Proses Ekspor</option>
                </select>
            </div>
        </div>

        <div
            class="rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm flex-1 overflow-y-auto">
            <div class="relative border-l border-gray-200 dark:border-gray-800 ml-3 space-y-5">

                <div v-for="log in logs.data" :key="log.id" class="relative pl-6 group">
                    <span
                        class="absolute -left-3 top-1 flex h-6 w-6 items-center justify-center rounded-full border shadow-sm text-[10px] font-bold uppercase"
                        :class="{
                            'bg-emerald-50 text-emerald-600 border-emerald-200': log.event === 'created' || log.event === 'imported',
                            'bg-blue-50 text-blue-600 border-blue-200': log.event === 'updated',
                            'bg-purple-50 text-purple-600 border-purple-200': log.event === 'exported',
                            'bg-red-50 text-red-600 border-red-200': log.event === 'deleted',
                        }">
                        {{ log.event.substring(0, 1) }}
                    </span>

                    <div
                        class="bg-gray-50/50 dark:bg-gray-950/40 border border-gray-100 dark:border-gray-850 p-4 rounded-xl shadow-sm">
                        <div class="flex justify-between items-center mb-1">
                            <div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white mr-2">
                                    {{ log.user ? log.user.name : 'System Worker' }}
                                </span>
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide border bg-white dark:bg-gray-900">
                                    {{ log.event }}
                                </span>
                                <span class="text-xs text-gray-400 ml-2">Modul:</span>
                                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 ml-1">
                                    {{ simplifyModelName(log.auditable_type) }}
                                </span>
                            </div>
                            <span class="text-xs font-mono text-gray-400">{{ formatDate(log.created_at) }}</span>
                        </div>

                        <p
                            class="text-xs text-gray-600 dark:text-gray-400 mt-2 font-mono bg-white dark:bg-gray-900/60 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                            {{ formatChangedColumns(log) }}
                        </p>
                    </div>

                </div>

                <div v-if="logs.data?.length === 0" class="text-center py-12 text-sm text-gray-400">
                    Belum ada jejak audit yang terekam.
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center border-t border-gray-100 dark:border-gray-800 pt-4"
                v-if="logs.links?.length > 3">
                <span class="text-xs text-gray-400">Halaman {{ logs.current_page }}</span>
                <div class="flex gap-2">
                    <a v-if="logs.prev_page_url" :href="logs.prev_page_url"
                        class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-800 rounded-md font-semibold">← Prev</a>
                    <a v-if="logs.next_page_url" :href="logs.next_page_url"
                        class="px-3 py-1.5 text-xs bg-gray-100 dark:bg-gray-800 rounded-md font-semibold">Next →</a>
                </div>
            </div>

        </div>
    </div>
</template>