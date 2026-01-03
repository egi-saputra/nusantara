<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { UserIcon, ClipboardDocumentListIcon, AcademicCapIcon, CheckBadgeIcon, UserGroupIcon } from '@heroicons/vue/24/solid';

const page = usePage();
const userName = page.props.auth.user.name || 'User';
</script>

<template>

    <Head title="Dashboard" />

    <UserLayout>
        <!-- Welcome Section -->
        <div
            class="bg-gradient-to-r mb-6 from-blue-500 to-indigo-600 text-white rounded-lg shadow-lg p-6 flex flex-col sm:flex-row items-center gap-4">
            <UserIcon class="w-12 h-12 text-white" />
            <div>
                <h1 class="text-2xl font-bold">Selamat datang, {{ userName }}! 👋</h1>
                <p class="text-white/90">Semoga harimu tetap produktif dan menyenangkan!</p>
            </div>
        </div>

        <!-- Cards Section -->
        <div class="grid mb-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Admin -->
            <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                <UserIcon class="w-10 h-10 text-red-500" />
                <div>
                    <p class="text-gray-500">Admin</p>
                    <h3 class="text-xl font-bold">{{ page.props.usersCount.admin }}</h3>
                </div>
            </div>

            <!-- Proktor -->
            <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                <UserGroupIcon class="w-10 h-10 text-purple-500" />
                <div>
                    <p class="text-gray-500">Proktor</p>
                    <h3 class="text-xl font-bold">{{ page.props.usersCount.proktor }}</h3>
                </div>
            </div>

            <!-- Guru -->
            <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                <AcademicCapIcon class="w-10 h-10 text-green-500" />
                <div>
                    <p class="text-gray-500">Guru</p>
                    <h3 class="text-xl font-bold">{{ page.props.usersCount.guru }}</h3>
                </div>
            </div>

            <!-- Siswa -->
            <div class="bg-white rounded-lg shadow p-5 flex items-center gap-4 hover:shadow-lg transition">
                <ClipboardDocumentListIcon class="w-10 h-10 text-blue-500" />
                <div>
                    <p class="text-gray-500">Siswa</p>
                    <h3 class="text-xl font-bold">{{ page.props.usersCount.siswa }}</h3>
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
    </UserLayout>
</template>
