<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
</script>

<template>
    <Head title="Selamat Datang - StockMaster Inventory" />
    
    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-[#0a0a0a] dark:text-gray-100 font-sans transition-colors duration-300">
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between border-b border-gray-200/60 dark:border-gray-800/60">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-600/20">
                    S
                </div>
                <span class="text-lg font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent">
                    StockMaster
                </span>
            </div>
            
            <nav class="flex items-center gap-4">
                <Link v-if="$page.props.auth.user" :href="dashboard()"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-600/10 hover:bg-blue-700 hover:shadow-lg transition">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()"
                        class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                        Log in
                    </Link>
                </template>
            </nav>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 mb-6">
                    <span class="flex h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Coding Test Project - Ready to Review
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white leading-tight mb-6">
                    Sistem Manajemen <br class="hidden sm:inline"/>
                    <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Inventaris & Material</span> Gudang
                </h1>
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed mb-8">
                    Aplikasi terintegrasi untuk pengelolaan data stok, pelacakan logistik material, dan penanganan dokumen lampiran. Dibangun dengan fokus penuh pada aspek keamanan, integritas riwayat transaksi, serta performa tinggi.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="dashboard()"
                        class="w-full sm:w-auto text-center rounded-xl bg-blue-600 px-8 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition">
                        Masuk ke Dashboard →
                    </Link>
                    <template v-else>
                        <Link :href="login()"
                            class="w-full sm:w-auto text-center rounded-xl bg-blue-600 px-8 py-3.5 text-base font-semibold text-white shadow-xl shadow-blue-600/20 hover:bg-blue-700 transition">
                            Mulai Demo Aplikasi
                        </Link>
                    </template>
                </div>
            </div>

            <div class="mt-24">
                <div class="text-center mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Arsitektur & Fitur Utama Aplikasi</h2>
                    <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mt-2">Seluruh fitur dibangun sesuai instruksi teknis pengujian.</p>
                </div>
                
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-200/60 dark:border-gray-800/60 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">RBAC Authentication</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Pembatasan hak akses multi-role (Spatie) yang mengamankan menu Administrator dan Staff secara ketat baik di sisi server maupun client.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-200/60 dark:border-gray-800/60 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-purple-50 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Audit Trail (History)</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Mencatat histori perubahan log secara instan. Menjamin data transaksional masa lalu tidak terpengaruh jika data master diubah.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-200/60 dark:border-gray-800/60 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Asynchronous Excel Engine</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Ekspor dan impor data Excel berkapasitas besar dengan fitur pemetaan kolom dinamis, berjalan asinkronus via Laravel Queue.
                        </p>
                    </div>

                    <div class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-200/60 dark:border-gray-800/60 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Advanced Relational Guard</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Validasi dokumen lampiran PDF super ketat (100 kb - 500 kb) serta penanganan relasi tabel database yang kompleks dan terstruktur.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-20 border-t border-gray-200/60 dark:border-gray-800/60 pt-16 text-center">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-6">Sistem ini dibangun menggunakan ekosistem modern</h4>
                <div class="flex flex-wrap items-center justify-center gap-6 text-sm font-semibold text-gray-600 dark:text-gray-400">
                    <span class="px-4 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">Laravel 13</span>
                    <span class="px-4 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">Vue 3 (Inertia.js)</span>
                    <span class="px-4 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">TypeScript</span>
                    <span class="px-4 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">Tailwind CSS</span>
                </div>
            </div>
        </main>

        <footer class="w-full border-t border-gray-200/60 dark:border-gray-800/60 mt-12 py-6 text-center text-xs text-gray-400 dark:text-gray-600">
            &copy; 2026 StockMaster Management. Project Ujian Teknis Full-Stack Developer.
        </footer>
    </div>
</template>