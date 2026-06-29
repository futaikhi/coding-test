<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps<{ role: { id: number; name: string; } }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Role Management', href: '/roles' },
            { title: 'Edit Role', href: '#' },
        ],
    },
});

const form = useForm({ name: props.role.name });
const submit = () => form.put(`/roles/${props.role.id}`);
</script>

<template>
    <Head :title="`Edit Role: ${role.name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Edit Role</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Mengubah nama role akan berdampak pada seluruh user dengan role ini.</p>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Role</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition"
                        :class="{'border-red-500 focus:border-red-500': form.errors.name}"
                    >
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1.5">{{ form.errors.name }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/roles" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Perbarui Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>