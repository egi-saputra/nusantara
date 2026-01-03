<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount } from 'vue'
import { ArrowLeftIcon, PlayCircleIcon } from '@heroicons/vue/24/solid'

defineProps({
    soal: Object,
    jumlahSoal: Number,
})

/**
 * NON-TURBO / ANTI-CHEAT SCRIPT
 * (dipindahkan dari Blade tanpa perubahan logika)
 */
const beforeUnloadHandler = (e) => {
    e.preventDefault()
    e.returnValue = 'Jangan menutup atau merefresh halaman saat ujian!'
    return e.returnValue
}

const keydownHandler = (e) => {
    if (
        (e.ctrlKey || e.metaKey) &&
        ['c', 'v', 'x', 'r', 't', 'n', 'w'].includes(e.key.toLowerCase())
    ) {
        e.preventDefault()
        alert('Shortcut dinonaktifkan selama ujian!')
    }

    if (e.key === 'F5') {
        e.preventDefault()
        alert('Reload diblokir!')
    }

    if (e.key === 'Escape') {
        e.preventDefault()
    }
}

const contextMenuHandler = (e) => {
    e.preventDefault()
}

onMounted(() => {
    window.addEventListener('beforeunload', beforeUnloadHandler)
    document.addEventListener('keydown', keydownHandler)
    document.addEventListener('contextmenu', contextMenuHandler)
})

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beforeUnloadHandler)
    document.removeEventListener('keydown', keydownHandler)
    document.removeEventListener('contextmenu', contextMenuHandler)
})
</script>

<template>
    <AppLayout>

        <Head title="Informasi Ujian" />

        <!-- Header -->
        <template #header>
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                Informasi Ujian
            </h2>
        </template>

        <div class="md:max-w-4xl mx-auto md:mt-10 md:px-4">
            <div class="bg-white shadow-sm md:shadow-2xl rounded-lg md:rounded-3xl
                       p-5 md:p-8 border border-gray-300">

                <!-- Title -->
                <h3 class="text-xl md:text-3xl font-extrabold text-gray-700
                           mb-6 text-center tracking-tight">
                    Informasi Quiz / Soal Ujian
                </h3>

                <!-- TOKEN -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100
                           border border-blue-200 rounded md:rounded-2xl p-5
                           mb-8 text-center shadow-sm">
                    <p class="text-xs text-blue-600 font-semibold uppercase tracking-widest">
                        Token Ujian
                    </p>
                    <p class="text-3xl md:text-4xl font-extrabold text-blue-700
                               mt-2 tracking-widest select-all">
                        {{ soal.token }}
                    </p>
                </div>

                <!-- INFO GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5 mb-6">

                    <div class="info-card">
                        <p class="info-label">Quiz Title</p>
                        <p class="info-value">
                            {{ soal.title ?? 'Tidak ada' }}
                        </p>
                    </div>

                    <div class="info-card">
                        <p class="info-label">Mata Pelajaran</p>
                        <p class="info-value">
                            {{ soal.mapel?.mapel ?? 'Tidak ada' }}
                        </p>
                    </div>

                    <div class="info-card">
                        <p class="info-label">Unit Kelas</p>
                        <p class="info-value">
                            {{ soal.kelas ?? '-' }}
                        </p>
                    </div>

                    <div class="info-card">
                        <p class="info-label">Jumlah Soal</p>
                        <p class="info-value">
                            {{ jumlahSoal }} Soal
                        </p>
                    </div>

                    <div class="info-card">
                        <p class="info-label">Durasi Ujian</p>
                        <p class="info-value">
                            {{ soal.waktu }} menit
                        </p>
                    </div>

                </div>

                <!-- WARNING -->
                <div class="bg-red-50 border border-red-200 rounded-lg md:rounded-2xl
                           p-5 mb-8 shadow-sm">
                    <h4 class="font-bold text-red-700 mb-3 flex items-center gap-2">
                        ⚠️ Perhatian
                    </h4>

                    <ul class="list-disc list-inside text-red-600 text-sm
                               space-y-1 leading-relaxed">
                        <li>Kerjakan ujian sesuai waktu yang ditentukan.</li>
                        <li>Waktu dimulai setelah klik tombol <b>Kerjakan</b>.</li>
                        <li>Ujian akan tertutup otomatis saat waktu habis.</li>
                        <li>Dilarang membuka tab baru atau menutup halaman.</li>
                        <li>Dilarang refresh, back, copy–paste, screenshot.</li>
                        <li>Dilarang menggunakan shortcut atau developer tools.</li>
                    </ul>

                    <p class="mt-3 text-red-700 text-sm font-medium">
                        Pelanggaran dapat menyebabkan ujian batal atau token diperbarui otomatis.
                    </p>
                </div>

                <!-- ACTION -->
                <div class="flex flex-col sm:flex-row gap-3
                           justify-between items-stretch sm:items-center">

                    <Link :href="route('siswa.ujian.token')"
                        class="flex items-center justify-center gap-2 px-5 py-3 md:rounded-xl rounded-lg bg-gray-700 hover:bg-gray-800 text-white font-semibold transition shadow-sm hover:shadow">
                        <ArrowLeftIcon class="w-5 h-5" />
                        <span>Kembali</span>
                    </Link>

                    <Link :href="route('siswa.ujian.kerjakan', soal.id)"
                        class="flex items-center justify-center gap-2 px-6 py-3 md:rounded-xl rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-extrabold transition shadow-lg transform hover:-translate-y-0.5">
                        <PlayCircleIcon class="w-6 h-6" />
                        <span>Kerjakan Ujian</span>
                    </Link>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
