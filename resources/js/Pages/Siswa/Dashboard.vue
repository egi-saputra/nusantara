<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, computed, onMounted } from 'vue'
import { Head, usePage, router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

import {
    UserIcon,
    UserGroupIcon,
    ClipboardDocumentListIcon,
    AcademicCapIcon,
    CheckBadgeIcon,
    XMarkIcon,
    NewspaperIcon,
    DocumentTextIcon
} from '@heroicons/vue/24/solid'

/* ================= PAGE & USER ================= */
const page = usePage()
const userName = page.props.auth.user.name || 'User'

/* ================= TOAST ================= */
const toast = ref({
    show: false,
    message: '',
    type: 'info'
})

const showToast = (message, type = 'info') => {
    toast.value.message = message
    toast.value.type = type
    toast.value.show = true

    setTimeout(() => {
        toast.value.show = false
    }, 2000)
}

/* ================= MENU ================= */
const menuItems = [
    { title: 'Ruang Materi', icon: NewspaperIcon, route: route('siswa.dashboard') },
    { title: 'Ruang Tugas', icon: DocumentTextIcon, route: route('siswa.dashboard') },
    { title: 'Ruang Ujian', icon: ClipboardDocumentListIcon, route: route('siswa.ujian.token') },
    { title: 'Ruang Siswa', icon: UserGroupIcon, route: route('siswa.dashboard') },
]

/* ================= SISWA ================= */
const siswa = computed(() => page.props.siswa || {})

/* ================= NAV ================= */
const goTo = (url) => {
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

/* ================= COPY ================= */
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text)
        .then(() => showToast('Berhasil mengcopy ke clipboard', 'success'))
        .catch(() => showToast('Gagal menyalin ke clipboard!', 'error'))
}

/* ================= SLIDER (AMAN) ================= */
const sliderRef = ref(null)
const activeSlide = ref(0)

onMounted(() => {
    if (!sliderRef.value) return

    sliderRef.value.addEventListener('scroll', () => {
        activeSlide.value = Math.round(
            sliderRef.value.scrollLeft / sliderRef.value.clientWidth
        )
    })
})

/* ================= EXPORT ================= */
const exportExcel = () => {
    showToast('Export Excel dimulai...', 'success')

    router.visit(route('siswa.export.excel'), {
        preserveScroll: true
    })
}
</script>


<template>

    <Head title="Dashboard" />

    <UserLayout>
        <!-- ================= MOBILE TOAST ================= -->
        <div v-if="toast.show" class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[92%] max-w-md
                   bg-black/70 backdrop-blur-xl text-white
                   px-5 py-3 rounded-2xl shadow-2xl
                   flex items-center justify-between z-50
                   md:hidden">
            <span class="truncate text-sm font-medium">{{ toast.message }}</span>
            <button @click="toast.show = false">
                <XMarkIcon class="w-5 h-5" />
            </button>
        </div>

        <div class="sm:max-w-7xl mx-auto overflow-x-hidden sm:py-6 space-y-6 min-h-screen">

            <!-- ================= MOBILE JUMBOTRON SLIDER ================= -->
            <div ref="sliderRef" class="flex md:flex-col gap-6
                       overflow-x-auto no-scrollbar
                       snap-x snap-mandatory md:snap-none
                       scroll-smooth
                       -mx-6 px-6 md:mx-0 md:px-0">

                <!-- ===== SLIDE 1 : WELCOME ===== -->
                <div class="min-w-full snap-center
                            relative overflow-hidden rounded-xl sm:rounded-3xl p-6 sm:p-8
                            bg-gradient-to-br items-center from-indigo-600 via-blue-600 to-purple-600 dark:bg-gradient-to-br dark:sm:from-[#1e1b4b] dark:sm:via-[#312e81] dark:sm:to-[#4c1d95] dark:from-[#063970] dark:via-[#0a4e8c] dark:to-[#1e1b4b] flex gap-4 sm:flex-row flex-col
                            text-white dark:shadow-xl">

                    <UserIcon class="w-12 h-12 text-center text-white" />
                    <div class="relative z-10">
                        <h1 class="text-2xl sm:text-3xl font-bold">
                            Selamat datang, {{ userName }} 👋
                        </h1>
                        <p class="text-white/90 text-sm sm:text-base mt-1">
                            Semoga harimu tetap produktif dan menyenangkan!
                        </p>
                    </div>

                    <div class="absolute sm:hidden -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
                </div>

                <!-- ===== SLIDE 2 : CARD SISWA ===== -->
                <h1 class="sm:inline-flex hidden font-bold -mb-2 text-2xl font-raleway mt-4 pl-3 text-gray-800 gap-3">
                    <UserIcon
                        class="w-10 h-10 p-1 dark:bg-gradient-to-br dark:from-[#1e1b4b] dark:via-[#312e81] dark:to-[#4c1d95] dark:border-none dark:text-white border-2 border-gray-600 rounded-xl text-center" />
                    <span class="mt-1 dark:text-gray-300">Informasi Pribadi</span>
                </h1>
                <div class="min-w-full snap-center
                rounded-xl sm:rounded-3xl bg-white/70 dark:backdrop-blur-xl
                border border-gray-200 dark:border-gray-500 dark:shadow-xl dark:bg-[#0F172A] p-6">

                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                        <UserIcon class="w-6 h-6 sm:hidden text-white" />
                        <span class="dark:text-gray-200">{{ siswa.nama_lengkap }}</span>
                    </h3>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 text-sm text-gray-600">
                        <div class="flex items-center sm:border-l-2 dark:border-gray-400 sm:pl-4 gap-2">
                            <AcademicCapIcon class="w-4 h-4  text-blue-500" />
                            <span class="dark:text-gray-400">{{ siswa.kelas?.kelas || 'Belum ada' }}</span>
                        </div>

                        <div class="hidden sm:flex sm:border-l-2 dark:border-gray-400 sm:pl-4 items-center gap-2">
                            <DocumentTextIcon class="w-4 h-4 text-red-500" />
                            <span class="dark:text-gray-400">{{ siswa.kejuruan?.kejuruan || 'Belum ada' }}</span>
                        </div>

                        <div
                            class="flex items-center sm:border-l-2 dark:border-gray-400 sm:pl-4 gap-2 font-mono font-bold text-indigo-700">
                            <span class="dark:text-gray-400">ID: {{ siswa.id_siswa }}</span>
                            <button @click="copyToClipboard(siswa.id_siswa)" class="p-1">
                                <ClipboardDocumentListIcon class="w-4 h-4 dark:text-gray-300" />
                            </button>
                        </div>

                        <div class="sm:border-l-2 sm:pl-4 dark:border-gray-400">
                            <span class="dark:text-gray-400">NIS: {{ siswa.nis }}</span>
                        </div>
                        <div class="sm:border-x-2 sm:pl-4 dark:border-gray-400">
                            <span class="dark:text-gray-400">NISN: {{ siswa.nisn }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <span :class="[
                            'px-4 py-1 rounded-full text-xs font-semibold flex items-center gap-1',
                            siswa.status === 'Activated'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        ]">
                            <CheckBadgeIcon v-if="siswa.status === 'Activated'" class="w-3 h-3" />
                            Status: {{ siswa.status === 'Activated' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ===== INDICATOR DOT (MOBILE ONLY) ===== -->
            <div class="flex justify-center gap-2 mt-3 md:hidden">
                <span v-for="i in 2" :key="i" class="w-2.5 h-2.5 rounded-full transition-all duration-300" :class="activeSlide === i - 1
                    ? 'bg-indigo-600 scale-125 dark:bg-[#0a4e8c]'
                    : 'bg-gray-300'" />
            </div>

            <!-- ================= MOBILE MENU ================= -->
            <div class="grid md:hidden grid-cols-2 sm:grid-cols-3 gap-4">
                <Link v-for="item in menuItems" :key="item.title" :href="item.route" preserve-scroll
                    class="rounded-2xl p-4
                           bg-white/70 backdrop-blur-lg
                           border border-white/20
                           shadow hover:shadow-xl
                           flex flex-col items-center gap-2
                           transition hover:-translate-y-1 dark:bg-gradient-to-br dark:from-[#063970] dark:via-[#0a4e8c] dark:to-[#1e1b4b]">

                    <component :is="item.icon" class="w-9 h-9 dark:text-gray-300 text-indigo-600" />
                    <span class="text-sm font-semibold dark:text-gray-300 text-gray-700 text-center">
                        {{ item.title }}
                    </span>
                </Link>
            </div>

        </div>
    </UserLayout>
</template>

<style>
/* hide scrollbar but keep scroll */
.no-scrollbar {
    -ms-overflow-style: none;
    /* IE & Edge */
    scrollbar-width: none;
    /* Firefox */
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
    /* Chrome, Safari */
}
</style>