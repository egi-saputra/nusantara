<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { ToastAlert } from '@/Composables/ToastAlert.js';
// import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AdminSidebar from '@/Components/Admin/Sidebar.vue';
import ProktorSidebar from '@/Components/Proktor/Sidebar.vue';
import GuruSidebar from '@/Components/Guru/Sidebar.vue';
import SiswaSidebar from '@/Components/Siswa/Sidebar.vue';
import { BellIcon, EllipsisVerticalIcon } from '@heroicons/vue/24/outline'
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline'
import { ChevronRightIcon } from '@heroicons/vue/24/solid'

// Props
const props = defineProps({
    disableSwal: { type: Boolean, default: false },
    logoUrl: { type: String, default: '/storage/logo_app/logo.png' }
});

const page = usePage();
const { success, error } = ToastAlert();

// Flash messages
onMounted(() => {
    if (!props.disableSwal) {
        if (page.props.flash?.success) success(page.props.flash.success);
        if (page.props.flash?.error) error(page.props.flash.error);
    }
});

watch(
    () => page.props.flash,
    (flash) => {
        if (props.disableSwal) return;  // skip SweetAlert
        if (flash?.success) success(flash.success);
        if (flash?.error) error(flash.error);
    }
);

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
            { name: 'Profile', href: route('profile.edit') },
            // { name: 'Quiz Management', href: route('guru.soal.index') },
        ],
        siswa: [
            { name: 'Dashboard', href: route('siswa.dashboard') },
            { name: 'Ruang Ujian', href: route('siswa.ujian.token') },
        ],
    };
    return links[role] || [];
});

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

const toggleButton = ref(null);
const dropdownMenu = ref(null);

const handleClickOutside = (event) => {
    if (
        dropdownMenu.value &&
        toggleButton.value &&
        !dropdownMenu.value.contains(event.target) &&
        !toggleButton.value.contains(event.target)
    ) {
        showingNavigationDropdown.value = false;
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
    <div id="theme-transition-layer"></div>
    <div class="h-screen bg-gray-100 dark:bg-[#063970] flex flex-col overflow-hidden">
        <!-- Navbar full width -->
        <nav
            class="bg-white dark:bg-[#041C32] sm:dark:bg-[#0F172A] border-b border-gray-300 dark:sm:border-gray-700 dark:border-[#1e1b4b] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between h-16 items-center">
                    <!-- Nama Sekolah -->
                    <div class="flex items-center">
                        <!-- Logo Sekolah SPA-friendly -->
                        <img :src="props.logoUrl" class="h-16 -ml-10 sm:block hidden object-contain" alt="Logo Sekolah"
                            loading="lazy" />
                        <span
                            class="sm:text-lg text-base sm:-ml-6 ml-4 font-raleway font-extrabold dark:text-white text-[#063970]">
                            SMK NUSANTARA
                        </span>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDarkMode"
                        class="relative w-14 h-8 sm:flex hidden items-center rounded-full bg-gray-200 dark:bg-gray-800 shadow-inner transition-colors duration-300 ease-in-out"
                        title="Toggle Dark Mode">
                        <!-- ICON BACKGROUND -->
                        <SunIcon class="absolute left-2 w-4 h-4 text-yellow-400 z-0" />
                        <MoonIcon class="absolute right-2 w-4 h-4 text-[#1e1b4b] z-0" />

                        <!-- TOGGLE CIRCLE -->
                        <span
                            class="absolute w-6 h-6 bg-white dark:bg-gray-600 rounded-full shadow-inner transform transition-all duration-300 ease-[cubic-bezier(.4,0,.2,1)] z-10"
                            :class="isDark ? 'translate-x-7' : 'translate-x-1'" />
                    </button>

                    <div class="flex">
                        <!-- Dark Mode Toggle -->
                        <button @click="toggleDarkMode"
                            class="relative w-14 h-8 mt-1 mr-3 flex sm:hidden items-center rounded-full bg-gray-200 dark:bg-gray-800 shadow-inner transition-colors duration-300 ease-in-out"
                            title="Toggle Dark Mode">
                            <!-- ICON BACKGROUND -->
                            <SunIcon class="absolute left-2 w-4 h-4 text-yellow-400 z-0" />
                            <MoonIcon class="absolute right-2 w-4 h-4 text-[#1e1b4b] z-0" />

                            <!-- TOGGLE CIRCLE -->
                            <span
                                class="absolute w-6 h-6 bg-white dark:bg-gray-600 rounded-full shadow-inner transform transition-all duration-300 ease-[cubic-bezier(.4,0,.2,1)] z-10"
                                :class="isDark ? 'translate-x-7' : 'translate-x-1'" />
                        </button>

                        <!-- Bell Notification -->
                        <div class="relative">

                            <!-- Icon Bell -->
                            <button ref="bellButtonRef" @click="toggleNotifDropdown"
                                class="relative p-2 rounded-full transition">
                                <BellIcon
                                    class="w-6 h-6 sm:-mr-4 hover:text-gray-900 text-gray-500 dark:text-white dark:hover:text-gray-300" />
                                <span v-if="hasUnread"
                                    class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                            </button>

                            <!-- Dropdown -->
                            <transition name="fade">
                                <div ref="notifDropdownRef" v-if="showingNotifDropdown"
                                    class="absolute -right-6 mt-2 w-80 bg-white dark:bg-gradient-to-br dark:from-[#041C32] dark:via-[#0F172A] dark:to-[#1e1b4b] dark:border-[#0F172A] border border-gray-200 rounded-lg dark:shadow-none shadow-lg z-30">

                                    <!-- Header -->
                                    <h3
                                        class="px-4 py-2 flex items-center gap-2 font-semibold border-b dark:border-gray-700 dark:text-white border-gray-100 text-gray-700">
                                        <i class="bi bi-megaphone dark:text-blue-400 text-blue-600"></i>
                                        Notifikasi Terbaru
                                    </h3>

                                    <!-- List Notifikasi -->
                                    <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-auto">
                                        <li v-for="notif in notifications" :key="notif.id"
                                            @click.prevent="goToIndex(notif.id)"
                                            class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-transparent transition flex justify-between items-center">
                                            <div class="flex flex-col overflow-hidden">
                                                <p class="font-medium dark:text-white max-w-60 text-gray-700 truncate">
                                                    {{ notif.judul
                                                    }}</p>
                                                <p class="text-gray-500 dark:text-gray-400 text-sm max-w-60 truncate">{{
                                                    notif.pengumuman
                                                }}</p>
                                            </div>
                                            <ChevronRightIcon class="w-5 h-5 dark:text-gray-300 text-gray-400 mt-1" />
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
                            <Dropdown align="right">
                                <template #trigger>
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 dark:bg-[#0F172A] dark:text-white bg-white dark:hover:text-gray-300 hover:text-gray-700">
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

                        <!-- EllipsisVerticalIcon Icon responsive -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-[#063970] dark:hover:text-gray-300 dark:text-white transition-transform duration-300 ease-in-out responsive-toggle-button">
                                <EllipsisVerticalIcon :class="showingNavigationDropdown"
                                    class="h-6 w-6 transform transition-transform duration-300 ease-in-out text-gray-700 dark:text-white" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div class="sm:hidden overflow-hidden transition-all duration-300 ease-in-out responsive-menu"
                :style="{ maxHeight: showingNavigationDropdown ? '500px' : '0px' }">
                <div class="border-t py-2">
                    <ResponsiveNavLink v-for="link in navLinks" :key="link.name" :href="link.href" prefetch
                        preserve-scroll preserve-state :active="page.url.startsWith(link.href)">
                        {{ link.name }}
                    </ResponsiveNavLink>
                </div>

                <div class="py-3 pb-1 border-t border-gray-200">
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main area: Sidebar + Content -->
        <div class="flex flex-1 min-h-0">

            <!-- SIDEBAR (auto width + scrollable) -->
            <component :is="SidebarComponent" class="hidden md:block bg-white dark:bg-[#0F172A] border-r dark:border-gray-600 border-gray-300 pt-4
               overflow-y-auto overflow-x-hidden" />

            <!-- MAIN CONTENT (otomatis menyesuaikan) -->
            <div class="flex-1 px-4 sm:px-8 py-6 bg-gray-100 dark:bg-[#020617] overflow-auto">
                <slot />
            </div>

        </div>

        <div class="fixed bottom-0 left-0 right-0 z-40
         bg-white/90 dark:bg-[#020617]
         backdrop-blur
         border-t border-gray-200 dark:border-gray-700
         md:hidden safe-bottom">
            <div class="flex items-center justify-center h-14 px-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 text-center">
                    © {{ new Date().getFullYear() }}
                    <span class="font-semibold text-gray-700 dark:text-gray-200">
                        LMS NUSANTARA
                    </span>
                    <br class="hidden xs:block" />
                    All rights reserved
                </p>
            </div>
        </div>
    </div>
</template>