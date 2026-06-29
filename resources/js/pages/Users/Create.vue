<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'User Management', href: '/users' },
            { title: 'Tambah User', href: '/users/create' },
        ],
    },
});

const props = defineProps<{
    roles: any[]
}>();

const roleOptions = computed(() => {
    return props.roles.map(role => ({
        value: role.name,
        label: role.name
    }));
});

const form = useForm({
    name: '', 
    email: '', 
    password: '', 
    roles: []
});

const submit = () => form.post('/users');
</script>

<template>

    <Head title="Tambah User" />
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div
            class="max-w-2xl mx-auto w-full bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold mb-6 text-gray-800 dark:text-gray-200">Tambah User Baru</h2>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama</label>
                    <input v-model="form.name" type="text"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2.5 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.name }">
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input v-model="form.email" type="email"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2.5 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.email }">
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                    <input v-model="form.password" type="password"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 p-2.5 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': form.errors.password }">
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Role</label>
                    <Multiselect v-model="form.roles" :options="roleOptions" searchable mode="tags" :close-on-select="false" :create-option="true"
                        placeholder="Pilih satu atau beberapa role..." class="multiselect-custom" />
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <Link href="/users" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900">Batal
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
<style>
/* Kostumisasi warna tag penanda multiselect agar senada dengan tema aplikasi */
.multiselect-custom {
    --ms-bg: #1f2937;
  --ms-border: #4b5563;
  --ms-font-color: #f9fafb;
  
  /* Dropdown list */
  --ms-dropdown-bg: #1f2937;
  --ms-dropdown-border: #4b5563;
  
  /* Options */
  --ms-option-bg-pointed: #374151;
  --ms-option-color-pointed: #ffffff;
  --ms-option-bg-selected: #059669;
  --ms-option-bg-selected-pointed: #047857;
  
  /* Search input */
  --ms-search-bg: #1f2937;
}
</style>