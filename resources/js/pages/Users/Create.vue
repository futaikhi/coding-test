<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'User Management', href: '/users' },
            { title: 'Tambah User', href: '#' },
        ],
    },
});

const props = defineProps<{ roles: any[] }>();

const roleOptions = computed(() => {
    return props.roles.map(role => ({ value: role.name, label: role.name }));
});

const form = useForm({
    name: '', email: '', password: '', roles: [] as string[]
});

const submit = () => form.post('/users');
</script>

<template>
    <Head title="Tambah User" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="max-w-2xl mx-auto w-full rounded-xl border border-gray-200/80 bg-white p-6 dark:border-gray-800/80 dark:bg-gray-900 shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Tambah User Baru</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Buat akun kredensial pegawai baru dan tentukan hak aksesnya.</p>
            
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Nama Lengkap</label>
                    <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" :class="{'border-red-500': form.errors.name}">
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Alamat Email</label>
                    <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" :class="{'border-red-500': form.errors.email}">
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Kata Sandi (Password)</label>
                    <input v-model="form.password" type="password" class="w-full rounded-lg border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 p-2.5 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition" :class="{'border-red-500': form.errors.password}">
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pilih Multi-Role</label>
                    <Multiselect
                        v-model="form.roles"
                        :options="roleOptions"
                        searchable
                        mode="tags"
                        :close-on-select="false"
                        placeholder="Klik untuk memilih role..."
                        class="multiselect-premium"
                    />
                    <p v-if="form.errors.roles" class="text-red-500 text-xs mt-1">{{ form.errors.roles }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800/60">
                    <Link href="/users" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">Batal</Link>
                    <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50 cursor-pointer">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>