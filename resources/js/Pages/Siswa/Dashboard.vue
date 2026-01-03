<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { ref, computed } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import {
    UserIcon,
    UserGroupIcon,
    RectangleStackIcon,
    ClipboardDocumentListIcon,
    AcademicCapIcon,
    CheckBadgeIcon,
    XMarkIcon,
    NewspaperIcon,
    DocumentTextIcon,
    MegaphoneIcon,
    SpeakerWaveIcon
} from '@heroicons/vue/24/solid'
import { BookOpenIcon, } from '@heroicons/vue/24/outline'

const page = usePage()
const userName = page.props.auth.user.name || 'User'

const toast = ref({
    show: false,
    message: '',
    type: 'info'
});

const showToast = (message, type = 'info') => {
    toast.value.message = message;
    toast.value.type = type;
    toast.value.show = true;

    setTimeout(() => {
        toast.value.show = false;
    }, 2000);
};

const menuItems = [
    // { title: 'Peserta', icon: ClipboardDocumentListIcon, route: route('siswa.peserta.index') },
    { title: 'Ruang Materi', icon: NewspaperIcon, route: route('siswa.dashboard') },
    { title: 'Ruang Tugas', icon: DocumentTextIcon, route: route('siswa.dashboard') },
    { title: 'Ruang Ujian', icon: ClipboardDocumentListIcon, route: route('siswa.ujian.token') },
    { title: 'Ruang Siswa', icon: UserGroupIcon, route: route('siswa.dashboard') },
]

// Ambil list siswa
const siswa = computed(() => page.props.siswa || {});

const goTo = (url) => {
    router.visit(url);
}

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text)
        .then(() => {
            showToast('Berhasil mengcopy ke clipboard', 'success');
        })
        .catch((err) => {
            console.error('Gagal menyalin', err);
            showToast('Gagal menyalin ke clipboard!', 'error');
        });
}
</script>

<template>

    <Head title="Dashboard" />

    <UserLayout>
        <!-- MOBILE TOAST (default) -->
        <div v-if="toast.show" class="fixed bottom-5 left-1/2 transform -translate-x-1/2 w-full max-w-3xl 
           bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg 
           flex items-center justify-between z-50 
           transition-all duration-300 ease-out opacity-0 scale-95 md:hidden"
            :class="toast.show ? 'opacity-100 scale-100' : ''">

            <span class="truncate">{{ toast.message }}</span>

            <button @click="toast.show = false" class="ml-4 flex-shrink-0">
                <XMarkIcon class="w-5 h-5 text-white" />
            </button>
        </div>


        <!-- DESKTOP TOAST (pojok kanan atas) -->
        <div v-if="toast.show" class="hidden md:flex fixed top-5 right-5 w-full max-w-sm 
           px-5 py-3 rounded-lg shadow-lg z-50
           transition-all duration-300 ease-out opacity-0 scale-95
           items-center gap-3 text-white" :class="[
            toast.show ? 'opacity-100 scale-100' : '',
            toast.type === 'success' ? 'bg-green-600' : 'bg-gray-800'
        ]">

            <!-- Icon success -->
            <template v-if="toast.type === 'success'">
                <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </template>

            <span class="truncate">{{ toast.message }}</span>

            <button @click="toast.show = false" class="ml-auto flex-shrink-0">
                <XMarkIcon class="w-5 h-5 text-white" />
            </button>

            <!-- PROGRESS BAR -->
            <!-- <div class="absolute bottom-0 left-0 h-0.5 bg-white" :style="{ width: progress + '%' }"></div> -->
        </div>

        <div>
            <div class="md:flex flex-col hidden">
                <h1 class="text-xl sm:text-2xl font-bold mb-3">Selamat datang, {{ userName }} <span
                        class="text-2xl md:text-3xl">👋</span></h1>
                <p class="text-gray-600 text-sm sm:text-base">Semoga harimu tetap produktif dan menyenangkan!</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6">

            <div
                class="bg-gradient-to-r mb-6 from-blue-500 to-indigo-600 text-white rounded-lg shadow hover:shadow-lg transition-all duration-300 p-4 sm:hidden flex flex-col sm:flex-row items-center text-center gap-4">
                <UserIcon class="w-12 h-12 text-white" />
                <div>
                    <h1 class="text-xl font-bold">Selamat datang, {{ userName }}! 👋</h1>
                    <p class="text-white/90 text-xs">Semoga harimu tetap produktif dan menyenangkan!</p>
                </div>
            </div>

            <!-- Mobile Menu Buttons -->
            <div class="grid md:hidden grid-cols-2 sm:grid-cols-3 gap-4">
                <button v-for="item in menuItems" :key="item.title" @click="goTo(item.route)" class="flex flex-col items-center justify-center gap-2 p-4 bg-white rounded-xl shadow hover:shadow-lg 
               transition transform hover:-translate-y-1 w-full">
                    <component :is="item.icon" class="w-10 h-10 text-blue-500" />
                    <span class="text-sm font-medium text-gray-700 text-center">{{ item.title }}</span>
                </button>
            </div>

            <!-- Card Siswa -->
            <div class="mx-auto space-y-4">
                <h2 class="sm:text-2xl text-xl font-bold sm:inline-block hidden text-gray-700 mb-2">Data Pribadi</h2>

                <div
                    class="bg-white md:shadow rounded-lg shadow border-gray-300 md:rounded-lg p-5 flex flex-col md:flex-row items-start md:items-center gap-4 hover:shadow-lg transition-all duration-300">

                    <!-- Info Siswa -->
                    <div class="flex-1 flex flex-col gap-3 w-full">

                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <UserIcon class="w-5 h-5 text-blue-500" /> {{ siswa.nama_lengkap }}
                        </h3>
                        <div class="border-b md:hidden -mt-2 mb-2 rounded-full border-gray-400 w-1/2"></div>

                        <div
                            class="grid grid-cols-2 text-center justify-center md:border md:border-dashed md:py-4 md:p-4 rounded sm:grid-cols-5 gap-2 text-sm text-gray-600">
                            <div class="flex items-center md:border-r-2 md:border-l-2 md:pl-2 gap-1">
                                <span class="w-4 h-4 text-gray-400">
                                    <CheckBadgeIcon class="w-4 h-4 text-orange-500" />
                                </span>
                                Kelas: {{ siswa.kelas?.kelas || 'Belum ada' }}
                            </div>
                            <div class="md:flex hidden md:border-r-2 items-center gap-1">
                                <span class="w-4 h-4 text-gray-400">
                                    <DocumentTextIcon class="w-4 h-4 text-red-600" />
                                </span>
                                Kejuruan: {{ siswa.kejuruan?.kejuruan || 'Belum ada' }}
                            </div>
                            <div class="flex items-center md:border-r-2 gap-2">
                                <span class="w-4 h-4 text-gray-400">
                                    <AcademicCapIcon class="w-4 h-4 text-blue-600" />
                                </span>
                                ID:
                                <span class="text-[#063970] font-mono font-bold">{{ siswa.id_siswa }}</span>

                                <!-- Copy Button -->
                                <button @click="copyToClipboard(siswa.id_siswa)"
                                    class="ml-2 p-1 rounded hover:bg-gray-200 transition" title="Copy ID">
                                    <ClipboardDocumentListIcon class="w-4 h-4 text-gray-500 hover:text-blue-600" />
                                </button>
                            </div>
                            <div class="flex items-center text-center md:border-r-2 md:px-2 gap-1">
                                <span class="w-4 h-4 text-gray-400">
                                    <ClipboardDocumentListIcon class="w-4 h-4 text-[#063970]" />
                                </span>
                                NIS: {{ siswa.nis }}
                            </div>
                            <div class="flex items-center md:border-r-2 gap-1">
                                <span class="w-4 h-4 text-gray-400">
                                    <ClipboardDocumentListIcon class="w-4 h-4 text-indigo-600" />
                                </span>
                                NISN: {{ siswa.nisn }}
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="flex justify-between flex-wrap gap-2 mt-2">
                            <span :class="[
                                'text-xs font-medium px-3 py-1 rounded-full flex items-center gap-1',
                                siswa.status === 'Activated' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                            ]">
                                <component :is="siswa.status === 'Activated' ? CheckBadgeIcon : XMarkIcon"
                                    class="w-3 h-3" />
                                Status
                                <span class="md:inline-block hidden">Akun</span>: {{ siswa.status === 'Activated' ?
                                    'Aktif' : 'Tidak Aktif' }}
                            </span>
                            <span
                                class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full sm:hidden flex items-center gap-1">
                                <AcademicCapIcon class="w-3 h-3" /> Kejuruan: {{ siswa.kejuruan?.kejuruan || 'Belum ada'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div>
                <h2 class="text-2xl font-bold sm:inline-block hidden text-gray-700 pt-4 mb-2">Informasi Lainnya</h2>
                <div
                    class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-gray-100">
                </div>
            </div> -->
        </div>
    </UserLayout>
</template>