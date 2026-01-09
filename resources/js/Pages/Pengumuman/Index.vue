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
        <div class="w-full py-3 sm:py-12 sm:px-6 max-w-7xl mx-auto">

            <!-- HEADER -->
            <div class="mb-14 text-center">
                <h1 class="text-3xl sm:text-6xl font-extrabold tracking-tight
                           text-gray-900 dark:text-white">
                    Mading
                    <span class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500
                               bg-clip-text text-transparent">
                        Sekolah Nusantara
                    </span>
                </h1>

                <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm sm:text-lg">
                    Pengumuman, informasi, dan berita terbaru sekolah
                </p>

                <div class="mt-6 w-28 h-1 mx-auto rounded-full
                           bg-gradient-to-r from-indigo-500 to-purple-600">
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-if="pengumuman.length === 0"
                class="text-center py-24 text-gray-400 dark:text-gray-500 italic text-lg">
                Belum ada pengumuman 📭
            </div>

            <!-- GRID -->
            <div v-else class="grid grid-cols-1 gap-8">

                <div v-for="item in pengumuman" :key="item.id" class="group relative overflow-hidden rounded-xl sm:rounded-3xl
                           bg-white/70 dark:bg-white/5
                           backdrop-blur-xl
                           border border-white/20 dark:border-white/10
                           shadow-lg hover:shadow-2xl
                           transition-all duration-300">

                    <!-- Gradient Accent -->
                    <div class="absolute top-0 left-0 w-full h-1.5
                               bg-gradient-to-r from-indigo-500 to-purple-600">
                    </div>

                    <!-- Hover Glow -->
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-20
                               transition duration-300
                               bg-gradient-to-br from-indigo-400 to-purple-500">
                    </div>

                    <div class="relative z-10 p-6 flex flex-col h-full">

                        <!-- TITLE -->
                        <h2 class="text-xl font-bold mb-3
                                   text-gray-800 dark:text-white
                                   group-hover:text-indigo-600 transition">
                            {{ item.judul }}
                        </h2>

                        <!-- CONTENT -->
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <div v-html="item.pengumuman"
                                class="prose dark:prose-invert max-w-full announcement-content"></div>
                        </div>

                        <!-- META -->
                        <div class="mt-6 pt-4 border-t border-gray-200/50 dark:border-white/10
                                   text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium">
                                🎯 Informasi untuk: {{ item.penerima }}
                            </span>
                            <span v-if="item.kelas">
                                • Kelas: {{ item.kelas.kelas }}
                            </span>
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
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
