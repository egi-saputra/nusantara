<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    CalendarDaysIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/solid';
import { ChevronRightIcon } from '@heroicons/vue/24/outline';

const page = usePage();

// Data pengumuman
const pengumuman = computed(() => page.props.announcements || []);

// Helpers
const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const truncate = (text, length = 120) => {
    if (!text) return '';
    return text.length > length ? text.slice(0, length) + '…' : text;
};

// Pagination
const perPage = 10;
const currentPage = ref(1);

const totalPages = computed(() =>
    Math.ceil(pengumuman.value.length / perPage)
);

const paginatedPengumuman = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return pengumuman.value.slice(start, start + perPage);
});

const goToPage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>

<template>
    <div class="w-full py-6 sm:py-10 md:px-10 max-w-4xl mx-auto">

        <!-- Header -->
        <div class="mb-12 px-4 text-center">
            <h1 class="text-3xl sm:text-6xl font-extrabold tracking-tight text-gray-900">
                Mading <span class="text-indigo-600">Sekolah Nusantara</span>
            </h1>

            <p class="text-gray-500 mt-3 text-sm sm:text-lg font-light">
                Pengumuman, informasi, dan berita terbaru sekolah
            </p>

            <div
                class="mt-5 w-24 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full animated-gradient">
            </div>
        </div>

        <!-- Empty -->
        <div v-if="pengumuman.length === 0" class="text-gray-400 italic text-center py-20 text-lg">
            Belum ada pengumuman.
        </div>

        <!-- List Preview -->
        <div class="grid grid-cols-1 gap-4 px-4">
            <Link :href="route('login')"
                class="inline-flex items-center gap-2 font-bold text-gray-500 hover:text-indigo-600 transition">
                <ArrowLeftIcon class="w-5 h-5" />
                Back to Sign In
            </Link>

            <Link v-for="item in paginatedPengumuman" :key="item.id" :href="route('mading.show', item.id)" class="group relative overflow-hidden rounded-xl bg-white border border-gray-200
                       transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
                <!-- Accent -->
                <div
                    class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 to-purple-600 animated-gradient">
                </div>

                <div class="p-5 flex gap-3 items-start">
                    <!-- Content -->
                    <div class="flex-1">
                        <h2 class="font-semibold text-gray-800 text-base sm:text-lg
                                   line-clamp-1 group-hover:text-indigo-700 transition">
                            {{ truncate(item.judul, 60) }}
                        </h2>

                        <p class="text-sm text-gray-600 mt-1 sm:mb-4 line-clamp-2">
                            {{ truncate(item.pengumuman, 140) }}
                        </p>

                        <div class="mt-2 sm:flex hidden items-center gap-2 text-xs text-gray-500">
                            <CalendarDaysIcon class="w-4 h-4" />
                            <span>{{ formatDate(item.created_at) }}</span>
                        </div>
                    </div>

                    <!-- Chevron -->
                    <ChevronRightIcon class="sm:w-10 sm:h-10 w-8 h-8 text-gray-400 mt-2 sm:mt-6
                               group-hover:text-indigo-600
                               group-hover:translate-x-1 -mr-2
                               transition-all duration-300" />
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-10 px-4">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-2 rounded-lg border text-sm font-medium
                       disabled:opacity-40 disabled:cursor-not-allowed
                       hover:bg-indigo-50 hover:text-indigo-600">
                Prev
            </button>

            <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                class="w-10 h-10 rounded-lg text-sm font-semibold transition" :class="page === currentPage
                    ? 'bg-indigo-600 text-white shadow'
                    : 'border hover:bg-indigo-50 hover:text-indigo-600'">
                {{ page }}
            </button>

            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-2 rounded-lg border text-sm font-medium
                       disabled:opacity-40 disabled:cursor-not-allowed
                       hover:bg-indigo-50 hover:text-indigo-600">
                Next
            </button>
        </div>
    </div>
</template>

<style>
.animated-gradient {
    background: linear-gradient(270deg, #6366f1, #8b5cf6, #6366f1);
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
