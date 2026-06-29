<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

// Definisikan props untuk menerima data role yang sedang diedit
const props = defineProps<{
    role: {
        id: number;
        name: string;
    }
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Role Management', href: '/roles' },
        ],
    },
});

const form = useForm({
    name: props.role.name,
});

const submit = () => {
    form.put(`/roles/${props.role.id}`);
};
</script>

<template>
    <Head title="Edit Role" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-200">Edit Role: {{ role.name }}</h2>
            
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Role</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2.5 text-gray-900 dark:text-gray-100"
                        :class="{'border-red-500': form.errors.name}"
                    >
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <Link href="/roles" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>