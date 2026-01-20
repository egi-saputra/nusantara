<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { ToastAlert } from '@/Composables/ToastAlert.js'
import { onClickOutside } from '@vueuse/core'

import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
// import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

import AdminSidebar from '@/Components/Admin/Sidebar.vue'
import ProktorSidebar from '@/Components/Proktor/Sidebar.vue'
import GuruSidebar from '@/Components/Guru/Sidebar.vue'
import SiswaSidebar from '@/Components/Siswa/Sidebar.vue'

import { BellIcon, EllipsisVerticalIcon } from '@heroicons/vue/24/outline'
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline'
import { ChevronRightIcon } from '@heroicons/vue/24/solid'

/* ================= PROPS ================= */
const props = defineProps({
    disableSwal: { type: Boolean, default: false },
    logoUrl: { type: String, default: '/public/images/logo.png' }
})

const page = usePage()
const { success, error } = ToastAlert()

/* ================= FLASH MESSAGE ================= */
onMounted(() => {
    if (props.disableSwal) return
    if (page.props.flash?.success) success(page.props.flash.success)
    if (page.props.flash?.error) error(page.props.flash.error)
})

watch(
    () => page.props.flash,
    flash => {
        if (props.disableSwal) return
        if (flash?.success) success(flash.success)
        if (flash?.error) error(flash.error)
    }
)

/* ================= NOTIFICATION ================= */
const showingNotifDropdown = ref(false)
const notifDropdownRef = ref(null)
const bellButtonRef = ref(null)

const readNotifications = ref(
    JSON.parse(localStorage.getItem('readNotifications') || '[]')
)

const role = page.props.auth.role.toLowerCase()

const notifications = computed(() => {
    const anns = page.props.announcements || []

    return anns
        .filter(item => {
            // if (item.penerima === 'semua') return true
            if (item.penerima === role) return true
            if (role === 'siswa' && item.kelas_id) {
                return Number(item.kelas_id) === Number(page.props.kelasId)
            }
            return false
        })
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const toggleNotifDropdown = () => {
    showingNotifDropdown.value = !showingNotifDropdown.value

    if (showingNotifDropdown.value) {
        notifications.value.forEach(n => {
            if (!readNotifications.value.includes(n.id)) {
                readNotifications.value.push(n.id)
            }
        })
        localStorage.setItem(
            'readNotifications',
            JSON.stringify(readNotifications.value)
        )
    }
}

const hasUnread = computed(() =>
    notifications.value.some(n => !readNotifications.value.includes(n.id))
)

const goToIndex = id => {
    router.visit(route('pengumuman.index', { id }))
}

/* 👉 CLICK OUTSIDE NOTIF */
onClickOutside(
    notifDropdownRef,
    () => (showingNotifDropdown.value = false),
    { ignore: [bellButtonRef] }
)

/* ================= NAV DROPDOWN ================= */
const showingNavigationDropdown = ref(false)
const dropdownMenu = ref(null)
const toggleButton = ref(null)

/* 👉 CLICK OUTSIDE NAV */
onClickOutside(
    dropdownMenu,
    () => (showingNavigationDropdown.value = false),
    { ignore: [toggleButton] }
)

/* ================= SIDEBAR ================= */
const SidebarComponent = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return AdminSidebar
        case 'proktor': return ProktorSidebar
        case 'guru': return GuruSidebar
        case 'siswa': return SiswaSidebar
        default: return null
    }
})

/* ================= DASHBOARD ================= */
const dashboardHref = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return route('admin.dashboard')
        case 'proktor': return route('proktor.dashboard')
        case 'guru': return route('guru.dashboard')
        case 'siswa': return route('siswa.dashboard')
        default: return route('dashboard')
    }
})

/* ================= MENU ================= */
const navLinks = computed(() => {
    const role = page.props.auth.role
    const links = {
        admin: [
            { name: 'Profile', href: route('profile.edit') }
        ],
        proktor: [
            { name: 'Profile', href: route('profile.edit') }
        ],
        guru: [
            { name: 'Profile', href: route('profile.edit') }
        ],
        siswa: [
            { name: 'Profile', href: route('profile.edit') }
        ]
    }

    return links[role] || []
})

/* ================= DARK MODE ================= */
const isDark = ref(false)

const applyTheme = dark => {
    const html = document.documentElement
    html.classList.toggle('dark', dark)
    localStorage.setItem('theme', dark ? 'dark' : 'light')
}

const toggleDarkMode = () => {
    const html = document.documentElement
    const layer = document.getElementById('theme-transition-layer')

    layer.classList.remove('reveal-in', 'reveal-out')
    layer.classList.add('reveal-in')

    setTimeout(() => {
        isDark.value = !isDark.value
        html.classList.toggle('dark', isDark.value)
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    }, 600)

    setTimeout(() => {
        layer.classList.remove('reveal-in')
        layer.classList.add('reveal-out')
    }, 600)

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
            <div class="max-w-7xl mx-auto sm:px-0 px-2">
                <div class="flex justify-between h-16 items-center">
                    <!-- Navbar Sebelah Kiri -->
                    <div class="flex items-center">
                        <!-- Logo Sekolah SPA-friendly -->
                        <img :src="props.logoUrl" class="h-16 -ml-10 sm:block hidden object-contain" alt="Logo Sekolah"
                            loading="lazy" />
                        <span
                            class="sm:text-lg text-base sm:-ml-6 ml-4 font-raleway font-extrabold dark:text-white text-[#063970]">
                            SMK NUSANTARA
                        </span>
                    </div>

                    <!-- Dark Mode Toggle Desktop -->
                    <button @click="toggleDarkMode" title="Toggle Dark Mode"
                        class="relative mt-1 mr-3 sm:flex hidden w-16 h-8 rounded-full backdrop-blur-xl bg-white/60 dark:bg-slate-900/60 border border-white/40 dark:border-white/10 shadow-inner shadow-black/10 dark:shadow-black/40 transition-all duration-300">
                        <!-- ICONS -->
                        <SunIcon
                            class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-yellow-400 transition-opacity duration-300"
                            :class="isDark ? 'opacity-90' : 'opacity-100'" />
                        <MoonIcon
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400 transition-opacity duration-300"
                            :class="isDark ? 'opacity-100' : 'opacity-60'" />

                        <!-- TOGGLE KNOB -->
                        <span
                            class="absolute top-1/2 -translate-y-1/2 w-6 h-6 rounded-full backdrop-blur-md bg-white/80 dark:bg-slate-800 shadow-md shadow-black/20 transition-all duration-300 ease-[cubic-bezier(.4,0,.2,1)]"
                            :class="isDark ? 'translate-x-8' : 'translate-x-1'" />
                    </button>

                    <!-- Navbar Sebelah Kanan -->
                    <div class="flex">
                        <!-- Dark Mode Toggle (Glassmorphism) Mobile -->
                        <button @click="toggleDarkMode" title="Toggle Dark Mode"
                            class="relative mt-1 mr-3 sm:hidden w-14 h-8 rounded-full backdrop-blur-xl bg-white/60 dark:bg-slate-900/60 border border-white/40 dark:border-white/10 shadow-inner shadow-black/10 dark:shadow-black/40 transition-all duration-300">
                            <!-- ICONS -->
                            <SunIcon
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-4 h-4 text-yellow-400 transition-opacity duration-300"
                                :class="isDark ? 'opacity-30' : 'opacity-100'" />
                            <MoonIcon
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-indigo-400 transition-opacity duration-300"
                                :class="isDark ? 'opacity-100' : 'opacity-30'" />

                            <!-- TOGGLE KNOB -->
                            <span
                                class="absolute top-1/2 -translate-y-1/2 w-6 h-6 rounded-full backdrop-blur-md bg-white/80 dark:bg-slate-800/80 shadow-md shadow-black/20 transition-all duration-300 ease-[cubic-bezier(.4,0,.2,1)]"
                                :class="isDark ? '-translate-x-6' : 'translate-x-0'" />
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

                            <!-- Dropdown Notifikasi (Glassmorphism) -->
                            <transition enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-2 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 translate-y-2 scale-95">
                                <div v-if="showingNotifDropdown" ref="notifDropdownRef"
                                    class="absolute -right-6 mt-2 z-30 w-80 rounded-xl backdrop-blur-xl bg-white/70 dark:bg-slate-900/70 border border-white/40 dark:border-white/10 shadow-xl shadow-black/10 dark:shadow-black/40">

                                    <!-- Header -->
                                    <h3
                                        class="px-4 py-2 flex items-center gap-2 font-semibold border-b border-gray-200 dark:border-white/10 text-gray-700 dark:text-white">
                                        <i class="bi bi-megaphone text-blue-600 dark:text-blue-400"></i>
                                        Notifikasi Terbaru
                                    </h3>

                                    <!-- List Notifikasi -->
                                    <ul class="divide-y divide-white/30 dark:divide-white/10 max-h-80 overflow-auto">
                                        <li v-for="notif in notifications" :key="notif.id"
                                            @click.prevent="goToIndex(notif.id)"
                                            class="px-4 py-3 cursor-pointer transition hover:bg-white/50 dark:hover:bg-white/5 flex justify-between items-center">
                                            <div class="flex flex-col overflow-hidden">
                                                <p class="font-medium text-gray-700 dark:text-white max-w-60 truncate">
                                                    {{ notif.judul }}
                                                </p>
                                                <div v-html="notif.pengumuman"
                                                    class="prose dark:prose-invert max-w-60 max-h-8 truncate">
                                                </div>
                                                <!-- <p class="text-sm text-gray-500 dark:text-gray-400 max-w-60 truncate">
                                                    {{ notif.pengumuman }}
                                                </p> -->
                                            </div>
                                            <ChevronRightIcon class="w-5 h-5 text-gray-400 dark:text-gray-300" />
                                        </li>

                                        <!-- Fallback jika kosong -->
                                        <li v-if="notifications.length === 0"
                                            class="px-4 py-3 text-center italic text-gray-400 dark:text-gray-500">
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
                            <button ref="toggleButton" @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="sm:hidden p-2 rounded-md dark:hover:text-gray-300 dark:text-white text-gray-500 hover:text-gray-800">
                                <EllipsisVerticalIcon class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsive Popup Dropend Navigation Menu -->
            <transition enter-active-class="transition-all ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all ease-in duration-200" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-3">
                <div v-if="showingNavigationDropdown" ref="dropdownMenu" class="absolute right-3 top-14 z-50 w-56 rounded-2xl
               backdrop-blur-xl
               bg-white/70 dark:bg-slate-900/70
               shadow-xl shadow-black/10 dark:shadow-black/40
               border border-white/40 dark:border-white/10
               origin-top">
                    <div class="py-2">
                        <ResponsiveNavLink v-for="link in navLinks" :key="link.name" :href="link.href" prefetch
                            preserve-scroll preserve-state :active="page.url.startsWith(link.href)" class="block px-4 py-2 text-gray-700 dark:text-gray-200
                       hover:bg-white/60 dark:hover:bg-white/10
                       rounded-lg transition" @click="showingNavigationDropdown = false">
                            {{ link.name }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-white/30 dark:border-white/10 px-2 py-2">
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2
                       text-red-600 dark:text-red-400
                       hover:bg-red-500/10
                       rounded-lg transition" @click="showingNavigationDropdown = false">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </transition>
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