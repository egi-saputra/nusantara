<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAlert } from '@/Composables/useAlert.js';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AdminSidebar from '@/Components/Admin/Sidebar.vue';
import ProktorSidebar from '@/Components/Proktor/Sidebar.vue';
import SiswaSidebar from '@/Components/Siswa/Sidebar.vue';

// Props
const props = defineProps({
    disableSwal: { type: Boolean, default: false }
});

const page = usePage();
const { success, error } = useAlert();

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

const showingNavigationDropdown = ref(false);

// Sidebar per role
const SidebarComponent = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return AdminSidebar;
        case 'proktor': return ProktorSidebar;
        case 'siswa': return SiswaSidebar;
        default: return null;
    }
});

// Dashboard per role
const dashboardHref = computed(() => {
    switch (page.props.auth.role) {
        case 'admin': return route('admin.dashboard');
        case 'proktor': return route('proktor.dashboard');
        case 'siswa': return route('siswa.dashboard');
        default: return route('dashboard');
    }
});
</script>


<template>
    <div class="flex min-h-screen bg-gray-100">
        <!-- Sidebar SPA -->
        <component :is="SidebarComponent" class="sticky top-0 h-screen hidden md:block" />

        <div class="flex-1 flex flex-col">
            <!-- Navbar SPA -->
            <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <!-- Logo tetap SPA -->
                            <div class="shrink-0 flex items-center">
                                <a :href="dashboardHref" class="md:hidden block">
                                    <ApplicationLogo class="h-9 w-auto" />
                                </a>
                                <span class="ml-2 font-bold text-[#063970]">Nama Sekolah</span>
                            </div>

                            <!-- Navigation Links -->
                            <!-- <div class="hidden space-x-8 sm:-my-px sm:flex ml-6">
                                <NavLink :href="dashboardHref"
                                    :active="page.url.startsWith('/' + page.props.auth.role + '/dashboard')">
                                    Dashboard
                                </NavLink>
                            </div> -->
                        </div>

                        <!-- User Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <Dropdown align="right" width="48">
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

                        <!-- Hamburger responsive -->
                        <div class="flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="dashboardHref"
                            :active="page.url.startsWith('/' + page.props.auth.role + '/dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <!-- <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div> -->
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content SPA -->
            <div class="flex-1 p-6 bg-slate-50">
                <slot />
            </div>
        </div>
    </div>
</template>
