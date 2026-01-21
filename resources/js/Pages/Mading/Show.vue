<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDaysIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/solid';

const page = usePage();

const announcement = computed(() => page.props.announcement);

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <div class="max-w-6xl sm:py-8 mx-auto sm:px-6">

        <!-- Back -->
        <Link :href="route('mading.index')"
            class="inline-flex py-4 items-center gap-2 font-bold text-gray-500 hover:text-indigo-600 transition sm:px-0 px-4 sm:mb-6">
            <ArrowLeftIcon class="w-5 h-5" />
            Kembali ke Mading
        </Link>

        <!-- Card -->
        <article class="bg-white sm:rounded-2xl sm:shadow-lg sm:border sm:border-gray-200 overflow-hidden">

            <!-- Accent -->
            <div class="sm:h-2 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 animated-gradient">
            </div>

            <div class="p-6 sm:p-10">
                <!-- Title -->
                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
                    {{ announcement.judul }}
                </h1>

                <!-- Meta -->
                <div class="flex items-center gap-3 mt-2 sm:mt-4 text-sm text-gray-500">
                    <CalendarDaysIcon class="w-5 h-5" />
                    <span>{{ formatDate(announcement.created_at) }}</span>
                </div>

                <!-- Divider -->
                <div class="my-6 border-t"></div>

                <!-- Content -->
                <!-- <div class="prose prose-indigo max-w-none text-gray-700 leading-relaxed">
                    {{ announcement.pengumuman }}
                </div> -->
                <div v-html="announcement.pengumuman"
                    class="prose prose-indigo max-w-none text-gray-700 leading-relaxed">
                </div>
            </div>
        </article>
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
