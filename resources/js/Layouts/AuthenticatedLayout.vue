<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAlert } from '@/Composables/useAlert.js';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { PowerIcon } from '@heroicons/vue/24/solid'
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
                                <span class="font-bold mt-1 text-[#063970]">Nama Sekolah</span>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <div class="flex items-center">
                            <button @click="logout"
                                class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300">
                                <PowerIcon class="md:w-5 md:h-5 w-4 h-4" title="Logout" />
                            </button>
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
