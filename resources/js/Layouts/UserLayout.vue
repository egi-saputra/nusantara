<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import { UserIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/solid'
// import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AdminSidebar from '@/Components/Admin/Sidebar.vue';
import ProktorSidebar from '@/Components/Proktor/Sidebar.vue';
import GuruSidebar from '@/Components/Guru/Sidebar.vue';
import SiswaSidebar from '@/Components/Siswa/Sidebar.vue';
import { BellIcon } from '@heroicons/vue/24/outline'
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
</script>


<template>
    <div class="h-screen bg-gray-100 flex flex-col overflow-hidden">
        <!-- Navbar full width -->
        <nav class="bg-white border-b border-gray-300 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between h-16 items-center">
                    <!-- Nama Sekolah -->
                    <div class="flex items-center text-[#063970]">
                        <!-- Logo Sekolah SPA-friendly -->
                        <img :src="props.logoUrl" class="h-16 -ml-10 sm:block hidden object-contain" alt="Logo Sekolah"
                            loading="lazy" />
                        <span
                            class="sm:text-lg text-base sm:-ml-6 ml-4 font-raleway font-extrabold dark:text-white text-[#063970]">
                            SMK NUSANTARA
                        </span>
                    </div>

                    <div class="flex">
                        <div class="relative">
                            <!-- Icon Bell -->
                            <button ref="bellButtonRef" @click="toggleNotifDropdown"
                                class="relative p-2 rounded-full transition">
                                <BellIcon class="w-6 h-6 sm:-mr-4 hover:text-gray-900 text-gray-500" />
                                <span v-if="hasUnread"
                                    class="absolute top-2 right-2 w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                            </button>

                            <!-- Dropdown -->
                            <transition name="fade">
                                <div ref="notifDropdownRef" v-if="showingNotifDropdown"
                                    class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-lg z-30">

                                    <!-- Header -->
                                    <h3
                                        class="px-4 py-2 flex items-center gap-2 font-semibold border-b border-gray-100 text-gray-700">
                                        <i class="bi bi-megaphone text-blue-600"></i>
                                        Notifikasi Terbaru
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
                            <Dropdown align="right">
                                <template #trigger>
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700">
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

                        <!-- Icon responsive -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown" ref="toggleButton"
                                class="inline-flex items-center justify-center p-2 rounded-md text-[#063970] dark:text-white transition-transform duration-300 ease-in-out">
                                <svg :class="showingNavigationDropdown ? 'rotate-0' : '-rotate-90'"
                                    class="h-5 w-5 transform transition-transform duration-300 ease-in-out"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
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
                    class="absolute top-12 right-0 mr-3 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                    <div class="py-2 flex flex-col gap-1">
                        <ResponsiveNavLink :href="route('profile.edit')"
                            class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded-md">
                            <UserIcon class="w-5 h-5 text-blue-500" />
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                            class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 rounded-md">
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
            <component :is="SidebarComponent" class="hidden md:block bg-white border-r border-gray-300 pt-4
               overflow-y-auto overflow-x-hidden" />

            <!-- MAIN CONTENT (otomatis menyesuaikan) -->
            <div class="flex-1 px-4 sm:px-8 py-6 bg-gray-100 overflow-auto">
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