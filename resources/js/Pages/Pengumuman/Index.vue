<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();

// Notifikasi bell
const showingNotifDropdown = ref(false);
const readNotifications = ref(JSON.parse(localStorage.getItem('readNotifications') || '[]'));
const role = page.props.auth.role.toLowerCase();

const notifications = computed(() => {
    const anns = page.props.announcements || [];

    return anns
        .filter(item => {
            if (item.penerima === 'semua') return true;
            if (item.penerima === role) return true;

            if (role === 'siswa') {
                if (item.penerima === 'siswa' && item.kelas_id) {
                    return Number(item.kelas_id) === Number(page.props.kelasId);
                }
            }

            return false;
        })
        .sort((a, b) => new Date(b?.created_at) - new Date(a?.created_at))
        .slice(0, 10);
});

const toggleNotifDropdown = () => {
    showingNotifDropdown.value = !showingNotifDropdown.value;

    if (showingNotifDropdown.value) {
        notifications.value.forEach(n => {
            if (!readNotifications.value.includes(n.id)) {
                readNotifications.value.push(n.id);
            }
        });
        localStorage.setItem('readNotifications', JSON.stringify(readNotifications.value));
    }
};

const hasUnread = computed(() => {
    return notifications.value.some(n => !readNotifications.value.includes(n.id));
});

const notifDropdownRef = ref(null);
const bellButtonRef = ref(null);

const handleClickOutsideNotif = (event) => {
    if (
        notifDropdownRef.value &&
        bellButtonRef.value &&
        !notifDropdownRef.value.contains(event.target) &&
        !bellButtonRef.value.contains(event.target)
    ) {
        showingNotifDropdown.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutsideNotif));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutsideNotif));

// Data pengumuman utama
const pengumuman = computed(() => page.props.announcements || []);

// Redirect ke halaman index pengumuman
const goToIndex = (id) => {
    router.visit(route('pengumuman.index', { id }));
};
</script>

<template>
    <MenuLayout>
        <div class="w-full py-4 sm:py-10 md:px-10 max-w-6xl mx-auto">

            <!-- Header -->
            <div class="mb-12 px-4 text-center">
                <h1 class="text-2xl sm:text-6xl font-extrabold tracking-tight font-poppins text-gray-900 leading-tight">
                    Mading <span class="text-indigo-600">Sekolah Nusantara</span>
                </h1>

                <p class="text-gray-500 mt-3 text-sm sm:text-lg font-light">
                    Pengumuman, informasi, dan berita terbaru sekolah
                </p>

                <!-- Decorative line -->
                <div class="mt-5 w-24 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full"></div>
            </div>

            <!-- Empty State -->
            <div v-if="pengumuman.length === 0" class="text-gray-400 italic text-center py-20 text-lg">
                Belum ada pengumuman.
            </div>

            <!-- Magazine Grid -->
            <div class="grid grid-cols-1">
                <div v-for="item in pengumuman" :key="item.id" class="group relative overflow-hidden rounded-2xl shadow-xl bg-white 
                           border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <!-- Accent bar -->
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-600"></div>

                    <!-- Background Fade -->
                    <div
                        class="absolute inset-0 opacity-0 group-hover:opacity-10 transition duration-300 bg-gradient-to-br from-indigo-200 to-purple-200">
                    </div>

                    <div class="p-6 relative z-10">
                        <h2 class="font-bold text-xl text-gray-800 mb-3 group-hover:text-indigo-700 transition">
                            {{ item.judul }}
                        </h2>

                        <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-4">
                            {{ item.pengumuman }}
                        </p>

                        <div class="text-xs text-gray-500 mt-4 pt-3 border-t border-gray-100">
                            Penerima: <span class="font-medium">{{ item.penerima }}</span>
                            <span v-if="item.kelas"> | Kelas: {{ item.kelas.kelas }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </MenuLayout>
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
