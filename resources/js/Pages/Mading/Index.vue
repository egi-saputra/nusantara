<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClockIcon, MegaphoneIcon } from '@heroicons/vue/24/solid'

const page = usePage();

// Data pengumuman utama
const pengumuman = computed(() => page.props.announcements || []);

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <div class="w-full py-4 sm:py-10 md:px-10 max-w-6xl mx-auto">

        <!-- Header -->
        <div class="mb-12 px-4 w-full text-center">

            <Link :href="route('login')" prefetch preserve-scroll preserve-state
                class="flex flex-col w-full sm:flex-row items-center justify-center gap-4 sm:gap-6">
                <h1
                    class="text-3xl sm:text-6xl font-extrabold tracking-tight font-poppins text-gray-900 leading-tight text-center sm:text-left">
                    Mading <span class="text-indigo-600">Sekolah Nusantara</span>
                </h1>
            </Link>

            <p class="text-gray-500 mt-3 text-sm sm:text-lg font-light">
                Pengumuman, informasi, dan berita terbaru sekolah
            </p>

            <!-- Decorative line -->
            <div
                class="mt-5 w-24 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full animated-gradient">
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="pengumuman.length === 0" class="text-gray-400 italic text-center py-20 text-lg">
            Belum ada pengumuman.
        </div>

        <!-- Magazine Grid -->
        <div class="grid grid-cols-1 px-4">
            <div v-for="item in pengumuman" :key="item.id" class="group relative overflow-hidden rounded-2xl shadow-xl bg-white 
                           border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                <!-- Accent bar -->
                <div
                    class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 to-purple-600 animated-gradient">
                </div>

                <!-- Background Fade -->
                <div
                    class="absolute inset-0 opacity-0 group-hover:opacity-10 transition duration-300 bg-gradient-to-br from-indigo-200 to-purple-200">
                </div>

                <div class="p-6 relative z-10">
                    <h2 class="font-bold font-xl sm:text-2xl text-gray-800 mb-3 group-hover:text-indigo-700 transition">
                        <i class="bi bi-megaphone mr-2 animate-shake sm:text-2xl text-blue-600"></i> {{ item.judul
                        }}
                    </h2>

                    <div class="sm:border-dashed sm:border sm:rounded sm:p-4 sm:flex sm:w-full">
                        <p class="text-gray-600 text-sm leading-relaxed mb-4 line-clamp-4">
                            {{ item.pengumuman }}
                        </p>
                    </div>

                    <div class="text-xs text-gray-600 mt-4 pt-3 border-t border-gray-100 space-y-2">

                        <!-- Timestamp Created -->
                        <div class="flex items-center gap-2">
                            <CalendarDaysIcon class="w-4 h-4 text-gray-600" />
                            <span>Dibuat: {{ formatDate(item.created_at) }} • {{ formatTime(item.created_at) }}</span>
                        </div>

                        <!-- Timestamp Updated -->
                        <!-- <div class="flex items-center gap-2" v-if="item.updated_at">
                            <ClockIcon class="w-4 h-4 text-purple-600" />
                            <span>Diupdate: {{ formatDate(item.updated_at) }} • {{ formatTime(item.updated_at) }}</span>
                        </div> -->

                        <!-- Receiver Info -->
                        <!-- <div class="text-gray-500">
                            Penerima: <span class="font-medium">{{ item.penerima }}</span>
                            <span v-if="item.kelas"> | Kelas: {{ item.kelas.kelas }}</span>
                        </div> -->

                    </div>
                </div>
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

.animated-gradient {
    height: 0.5rem;
    /* 2 = 0.5rem */
    background: linear-gradient(270deg, #6366f1, #8b5cf6, #6366f1);
    /* from-indigo-500 to-purple-600 back to indigo */
    background-size: 600% 100%;
    animation: gradientMove 5s linear infinite;
}

@keyframes gradientMove {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>
