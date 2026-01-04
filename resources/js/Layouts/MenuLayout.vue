<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/solid'
import { Cog6ToothIcon } from '@heroicons/vue/24/outline';
import AdminSidebar from '@/Components/Admin/Sidebar.vue';
import ProktorSidebar from '@/Components/Proktor/Sidebar.vue';
import GuruSidebar from '@/Components/Guru/Sidebar.vue';
import SiswaSidebar from '@/Components/Siswa/Sidebar.vue';
import { BellIcon } from '@heroicons/vue/24/outline'
import { ChevronRightIcon, UserIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    disableSwal: { type: Boolean, default: false },
    logoUrl: { type: String, default: '/storage/logo_app/logo.png' }
});

const page = usePage();
const { success, error } = ToastAlert();

const showingNotifDropdown = ref(false)
// Ambil ID notif yang sudah dibaca dari localStorage
const readNotifications = ref(JSON.parse(localStorage.getItem('readNotifications') || '[]'))

// Filter pengumuman terbaru
const role = page.props.auth.role.toLowerCase();

const notifications = computed(() => {
    const anns = page.props.announcements || []

    return anns
        .filter(item => {
            if (item.penerima === 'semua') return true
            if (item.penerima === role) return true
            if (role === 'siswa') {
                if (item.penerima === 'siswa' && item.kelas_id)
                    return Number(item.kelas_id) === Number(page.props.kelasId)
            }
            return false
        })
        .sort((a, b) => new Date(b?.created_at) - new Date(a?.created_at))
})

// Toggle dropdown dan tandai semua notif sebagai dibaca
const toggleNotifDropdown = () => {
    showingNotifDropdown.value = !showingNotifDropdown.value

    // Kalau dropdown dibuka, semua notif yang muncul dianggap dibaca
    if (showingNotifDropdown.value) {
        notifications.value.forEach(n => {
            if (!readNotifications.value.includes(n.id)) {
                readNotifications.value.push(n.id)
            }
        })
        // simpan di localStorage biar bertahan reload
        localStorage.setItem('readNotifications', JSON.stringify(readNotifications.value))
    }
}

// Redirect ke halaman index pengumuman
const goToIndex = (id) => {
    router.visit(route('pengumuman.index', { id }))
}

// Computed untuk cek ada notif belum dibaca
const hasUnread = computed(() => {
    return notifications.value.some(n => !readNotifications.value.includes(n.id))
})

const notifDropdownRef = ref(null)
const bellButtonRef = ref(null)

const handleClickOutsideNotif = (event) => {
    if (
        notifDropdownRef.value &&
        bellButtonRef.value &&
        !notifDropdownRef.value.contains(event.target) &&
        !bellButtonRef.value.contains(event.target)
    ) {
        showingNotifDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutsideNotif)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutsideNotif)
})

const showingNavigationDropdown = ref(false);

const handleClickOutside = (event) => {
    const menu = document.querySelector('.responsive-menu');
    const button = document.querySelector('.responsive-toggle-button');
    if (menu && button) {
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            showingNavigationDropdown.value = false;
        }
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Sidebar per role
const SidebarComponent = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return AdminSidebar;
        case 'proktor': return ProktorSidebar;
        case 'guru': return GuruSidebar;
        case 'siswa': return SiswaSidebar;
        default: return null;
    }
});

// Dashboard per role
const dashboardHref = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return route('admin.dashboard');
        case 'proktor': return route('proktor.dashboard');
        case 'guru': return route('guru.dashboard');
        case 'siswa': return route('siswa.dashboard');
        default: return route('dashboard');
    }
});

// Menu dinamis per role
const navLinks = computed(() => {
    const role = page.props.auth.role;
    const links = {
        admin: [
            { name: 'Dashboard', href: route('admin.dashboard') },
            { name: 'Users Management', href: route('admin.users.index') },
            // { name: 'Quiz Management', href: route('admin.quiz.index') },
        ],
        proktor: [
            { name: 'Dashboard', href: route('proktor.dashboard') },
            { name: 'Users Management', href: route('proktor.peserta.index') },
            { name: 'Quiz Management', href: route('proktor.soal.index') },
        ],
        guru: [
            { name: 'Dashboard', href: route('guru.dashboard') },
            // { name: 'Quiz Management', href: route('guru.soal.index') },
        ],
        siswa: [
            { name: 'Dashboard', href: route('siswa.dashboard') },
            { name: 'Ruang Ujian', href: route('siswa.ujian.token') },
        ],
    };
    return links[role] || [];
});

// Fungsi back
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // fallback: redirect ke dashboard
        Inertia.visit(route(`${page.props.auth.role}.dashboard`));
    }
}

// Fungsi back langsung ke dashboard
// const goBack = () => {
//     Inertia.visit(route(`${page.props.auth.role}.dashboard`));
// }

// DARK MODE
const isDark = ref(false)

const applyTheme = (dark) => {
    const html = document.documentElement
    if (dark) {
        html.classList.add('dark')
    } else {
        html.classList.remove('dark')
    }
    localStorage.setItem('theme', dark ? 'dark' : 'light')
}

const toggleDarkMode = () => {
    const html = document.documentElement
    const layer = document.getElementById('theme-transition-layer')

    // reset class
    layer.classList.remove('reveal-in', 'reveal-out')

    // IN
    layer.classList.add('reveal-in')

    // ganti theme DI TENGAH
    setTimeout(() => {
        isDark.value = !isDark.value
        html.classList.toggle('dark', isDark.value)
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    }, 600)

    // OUT (arah SAMA)
    setTimeout(() => {
        layer.classList.remove('reveal-in')
        layer.classList.add('reveal-out')
    }, 600)

    // bersih
    setTimeout(() => {
        layer.classList.remove('reveal-out')
        layer.style.clipPath = 'circle(0% at 0% 0%)'
    }, 1200)
}

onMounted(() => {
    const theme = localStorage.getItem('theme')
    isDark.value = theme === 'dark'
    applyTheme(isDark.value)
})
</script>

<template>
    <div class="h-screen bg-gray-100 dark:bg-[#0B1F3A] flex flex-col overflow-hidden">
        <!-- Navbar SPA -->
        <nav
            class="bg-white dark:bg-[#041C32] sm:dark:bg-[#0F172A] border-b border-gray-300 dark:sm:border-gray-600 dark:border-[#1e1b4b] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto sm:px-0 px-2">
                <div class="flex justify-between h-16">

                    <div class="flex items-center">
                        <div class="shrink-0 flex items-center">
                            <!-- Nama Sekolah -->
                            <div class="sm:flex hidden items-center text-[#063970]">
                                <!-- Logo Sekolah SPA-friendly -->
                                <img :src="props.logoUrl" class="h-16 -ml-10 sm:block hidden object-contain"
                                    alt="Logo Sekolah" loading="lazy" />
                                <span
                                    class="sm:text-lg text-base sm:-ml-6 ml-4 font-raleway font-extrabold dark:text-white text-[#063970]">
                                    SMK NUSANTARA
                                </span>
                            </div>

                            <button @click="goBack"
                                class="p-1 mr-2 sm:hidden block rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                                <ArrowLeftIcon class="h-5 w-5 text-[#063970] dark:text-white" />
                            </button>

                            <span class="text-lg font-bold sm:hidden block dark:text-white text-[#063970]">
                                {{ page.props.title || '' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex">
                        <div class="sm:block hidden relative">
                            <!-- Icon Bell -->
                            <button ref="bellButtonRef" @click="toggleNotifDropdown"
                                class="relative p-2 rounded-full transition">
                                <BellIcon
                                    class="w-6 h-6 mt-3 sm:-mr-4 hover:text-gray-900 text-gray-500 dark:text-white dark:hover:text-gray-300" />
                                <span v-if="hasUnread"
                                    class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                            </button>

                            <!-- Dropdown -->
                            <transition name="fade">
                                <div ref="notifDropdownRef" v-if="showingNotifDropdown"
                                    class="absolute -right-10 md:-right-6 sm:mt-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-30">

                                    <!-- Header -->
                                    <h3
                                        class="px-4 py-2 flex items-center gap-2 font-semibold border-b border-gray-100 text-gray-700">
                                        <i class="bi bi-megaphone text-blue-600"></i>
                                        Informasi Terbaru
                                    </h3>

                                    <!-- List Notifikasi -->
                                    <ul class="divide-y divide-gray-100 max-h-80 overflow-auto">
                                        <li v-for="notif in notifications" :key="notif.id"
                                            @click.prevent="goToIndex(notif.id)"
                                            class="px-4 py-3 cursor-pointer hover:bg-gray-50 transition flex justify-between items-center">
                                            <div class="flex flex-col overflow-hidden">
                                                <p class="font-medium max-w-60 text-gray-700 truncate">{{ notif.judul
                                                    }}</p>
                                                <p class="text-gray-500 text-sm max-w-60 truncate">{{ notif.pengumuman
                                                    }}</p>
                                            </div>
                                            <ChevronRightIcon class="w-5 h-5 text-gray-400 mt-1" />
                                        </li>

                                        <!-- Fallback jika kosong -->
                                        <li v-if="notifications.length === 0"
                                            class="px-4 py-3 text-gray-400 italic text-center">
                                            Tidak ada pemberitahuan.
                                        </li>
                                    </ul>
                                </div>
                            </transition>
                        </div>

                        <!-- User Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent font-medium rounded-md text-gray-500 bg-white dark:bg-[#0F172A] dark:text-white dark:hover:text-gray-300 hover:text-gray-700 text-sm">
                                        {{ $page.props.auth.user.name }}
                                        <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log Out
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Gear Icon responsive -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-[#063970] dark:text-white transition-transform duration-150 ease-in-out transform responsive-toggle-button">
                                <Cog6ToothIcon :class="showingNavigationDropdown ? 'rotate-90' : 'rotate-0'"
                                    class="h-6 w-6 transform transition-transform duration-300 ease-in-out text-gray-700 dark:text-white" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive Dropend Navigation Menu -->
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="translate-x-10"
                enter-to-class="translate-x-0" leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-x-0" leave-to-class="opacity-0 translate-x-10">
                <div v-if="showingNavigationDropdown" ref="dropdownMenu"
                    class="absolute sm:top-12 top-14 right-0 mr-3 w-48 bg-white dark:bg-gradient-to-br dark:from-[#041C32] dark:via-[#0F172A] dark:to-[#1e1b4b] border dark:border-none border-gray-200 rounded-md shadow-lg dark:shadow-none z-50">
                    <div class="py-2 flex flex-col gap-1">
                        <ResponsiveNavLink :href="route('dashboard')" prefetch preserve-state preserve-scroll
                            class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-[#0F172A] rounded-md dark:text-white">
                            <UserIcon class="w-5 h-5 text-blue-500" />
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                            class="flex items-center gap-2 dark:text-white dark:hover:text-gray-800 px-4 py-2 hover:bg-gray-100 rounded-md">
                            <ArrowRightOnRectangleIcon class="w-5 h-5 text-red-500" />
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Main area: Sidebar + Content -->
        <div class="flex flex-1 min-h-0">

            <!-- SIDEBAR (auto width + scrollable) -->
            <component :is="SidebarComponent" class="hidden md:block bg-white dark:bg-[#0F172A] dark:border-gray-600 border-r border-gray-300 pt-4
               overflow-y-auto overflow-x-hidden" />

            <!-- MAIN CONTENT (otomatis menyesuaikan) -->
            <div class="flex-1 px-4 sm:px-8 py-6 bg-gray-100 dark:bg-[#020617] overflow-auto">
                <slot />
            </div>

        </div>
    </div>
</template>
<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
