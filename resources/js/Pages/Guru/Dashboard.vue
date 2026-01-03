<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { UserIcon, ClipboardDocumentListIcon, AcademicCapIcon, CheckBadgeIcon } from '@heroicons/vue/24/solid';

const page = usePage();
const userName = page.props.auth.user.name || 'User';
</script>

<template>

    <Head title="Dashboard" />

    <UserLayout>
        <div class="p-6 max-w-7xl mx-auto space-y-6">
            <!-- Welcome Section -->
            <div
                class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg shadow-lg p-6 flex items-center gap-4">
                <UserIcon class="w-12 h-12 text-white" />
                <div>
                    <h1 class="text-2xl font-bold">Selamat datang, {{ userName }}!</h1>
                    <p class="text-white/90">Semoga harimu menyenangkan dan produktif!</p>
                </div>
            </div>

            <!-- Cards Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                    <ClipboardDocumentListIcon class="w-10 h-10 text-blue-500" />
                    <div>
                        <p class="text-gray-500">Jumlah Peserta</p>
                        <h3 class="text-xl font-bold">120</h3>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                    <AcademicCapIcon class="w-10 h-10 text-green-500" />
                    <div>
                        <p class="text-gray-500">Ujian Aktif</p>
                        <h3 class="text-xl font-bold">5</h3>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                    <CheckBadgeIcon class="w-10 h-10 text-yellow-500" />
                    <div>
                        <p class="text-gray-500">Nilai Tertinggi</p>
                        <h3 class="text-xl font-bold">98%</h3>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                    <UserIcon class="w-10 h-10 text-purple-500" />
                    <div>
                        <p class="text-gray-500">Admin Online</p>
                        <h3 class="text-xl font-bold">3</h3>
                    </div>
                </div>
            </div>

            <!-- Informasi tambahan / widget -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-semibold text-lg text-gray-700 mb-4">Aktivitas Terbaru</h2>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2">
                        <CheckBadgeIcon class="w-5 h-5 text-green-500" />
                        Peserta baru mendaftar 3 menit yang lalu
                    </li>
                    <li class="flex items-center gap-2">
                        <ClipboardDocumentListIcon class="w-5 h-5 text-blue-500" />
                        Ujian baru ditambahkan
                    </li>
                    <li class="flex items-center gap-2">
                        <AcademicCapIcon class="w-5 h-5 text-yellow-500" />
                        Nilai ujian diperbarui
                    </li>
                </ul>
            </div>
        </div>
    </UserLayout>
</template>
