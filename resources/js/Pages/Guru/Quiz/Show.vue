<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, TrashIcon } from '@heroicons/vue/24/solid';
import { InformationCircleIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

// Props dari backend
const props = defineProps({
    soal: Object,
});

const { success, error, confirm } = ToastAlert();

const opsiLabel = {
    opsi_a: "A",
    opsi_b: "B",
    opsi_c: "C",
    opsi_d: "D",
    opsi_e: "E",
};

const getCorrectAnswerText = (item) => {
    const key = item.jawaban_benar;

    // Jika kosong → tidak ada jawaban
    if (!key || key.trim() === "") {
        return null;
    }

    // Jika PG → key = "opsi_a" / "opsi_b" / dst
    if (item.tipe_soal === "PG") {
        if (!item[key] || item[key].trim() === "") {
            return null;
        }

        return `${opsiLabel[key]}. ${item[key]}`;
    }

    // Jika ESSAY → jawaban langsung
    if (item.tipe_soal === "Essay") {
        return key; // langsung tampilkan jawaban essay
    }

    return null;
};

function confirmDeleteItem(id) {
    confirm({ text: 'You want delete this question?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/guru/bank-soal/${id}`)
                    .then(res => {
                        success(res.data.success || 'Question has been successfully deleted! ✅');
                        Inertia.reload();
                    })
                    .catch(err => error(err.response?.data?.message || 'Fail to delete this question! ❌'));
            }
        });
}

function confirmDeleteAll() {
    confirm({ text: 'Are you sure you want to delete all questions?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/guru/bank-soal/soal/${props.soal.id}/delete-all`)
                    .then(res => {
                        success(res.data.success || 'All questions have been successfully deleted! ✅');
                        Inertia.reload();
                    })
                    .catch(err => error(err.response?.data?.message || 'Failed to delete all questions! ❌'));
            }
        });
}
</script>

<template>

    <Head title="Detail Quiz" />

    <MenuLayout>

        <!-- TITLE -->
        <h2 class="mb-8 text-xl md:text-2xl font-extrabold
               text-gray-800 dark:text-white
               tracking-tight">
            Quiz / Exam Question Details
        </h2>

        <!-- INFO GRID -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">

            <div v-for="([label, value], i) in [
                ['Quiz Token', soal.token],
                ['Quiz Status', soal.status === 'Aktif' ? 'Active' : 'Inactive'],
                ['Quiz Format',
                    soal.tipe_soal === 'Berurutan'
                        ? 'Sequential'
                        : soal.tipe_soal === 'Acak'
                            ? 'Shuffle'
                            : soal.tipe_soal
                ],
                ['Exam Duration', soal.waktu + ' minutes'],
                ['Subject', soal.mapel?.mapel ?? '-'],
                ['Class Unit', soal.kelas]
            ]" :key="i" class="p-4 rounded-2xl
           bg-white/70 dark:bg-white/5
           backdrop-blur-xl
           border border-white/20 dark:border-white/10
           shadow-lg">

                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ label }}
                </p>

                <p class="mt-1 font-semibold" :class="label === 'Quiz Status'
                    ? soal.status === 'Aktif'
                        ? 'text-green-500'
                        : 'text-gray-400'
                    : 'text-gray-800 dark:text-white'">
                    {{ value }}
                </p>
            </div>
        </div>

        <!-- ACTION BAR -->
        <div class="mb-8 flex flex-col md:flex-row justify-between gap-4">

            <Link href="/guru/soal" class="hidden md:inline-flex items-center gap-2
                   px-5 py-2 rounded-xl
                   bg-white/60 dark:bg-white/10
                   backdrop-blur border border-white/20
                   text-gray-700 dark:text-gray-200
                   shadow hover:shadow-lg transition">
                <ArrowLeftIcon class="w-5 h-5" />
                Back to Quiz
            </Link>

            <div class="flex gap-3">
                <Link :href="`/guru/bank-soal/create?soal_id=${soal.id}`" class="flex-1 md:flex-none flex items-center justify-center gap-2
                       px-6 py-2 rounded-xl
                       bg-gradient-to-r from-blue-600 to-indigo-600
                       text-white font-semibold shadow-lg
                       active:scale-95 transition">
                    + Add <span class="hidden sm:inline">Question</span>
                </Link>

                <button @click="confirmDeleteAll" class="flex-1 md:flex-none flex items-center justify-center gap-2
                       px-6 py-2 rounded-xl
                       bg-red-500/90 text-white font-semibold
                       shadow-lg active:scale-95 transition">
                    <TrashIcon class="w-5 h-5" />
                    Delete <span class="hidden sm:inline">All</span>
                </button>
            </div>
        </div>

        <!-- QUESTION LIST -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- EMPTY -->
            <div v-if="!soal.bank_soal || soal.bank_soal.length === 0" class="col-span-full p-10 text-center
                   rounded-2xl
                   bg-white/60 dark:bg-white/5
                   backdrop-blur-xl
                   border border-white/20
                   text-gray-400 shadow">
                No questions have been added to this quiz yet.
            </div>

            <!-- QUESTION CARD -->
            <div v-for="(item, index) in soal.bank_soal" :key="item.id" class="p-6 rounded-2xl
                   bg-white/70 dark:bg-white/5
                   backdrop-blur-xl
                   border border-white/20 dark:border-white/10
                   shadow-lg hover:shadow-2xl
                   transition">

                <!-- HEADER -->
                <div class="flex justify-between items-start mb-4">
                    <span class="text-sm text-gray-400 font-semibold">
                        No {{ index + 1 }}
                    </span>

                    <span class="px-4 py-1 rounded-full text-xs font-bold
                           bg-gradient-to-r
                           from-indigo-500 to-purple-600
                           text-white shadow">
                        {{ item.tipe_soal === 'PG' ? 'Multiple Choice' : 'Essay' }}
                    </span>
                </div>

                <!-- QUESTION -->
                <p class="font-semibold flex items-center gap-2 text-gray-800 dark:text-white">
                    <InformationCircleIcon class="w-5 h-5 text-blue-500" />
                    Question
                </p>
                <div class="text-sm ml-7 mt-1 text-gray-600 dark:text-gray-400 line-clamp-3">
                    <div v-html="item.soal" class="prose prose-sm max-w-none dark:prose-invert announcement-content">
                    </div>
                </div>

                <!-- ANSWER -->
                <p class="mt-4 font-semibold flex items-center gap-2 text-gray-800 dark:text-white">
                    <CheckBadgeIcon class="w-5 h-5 text-green-500" />
                    Correct Answer
                </p>

                <p class="ml-7 mt-1 text-sm line-clamp-3" :class="getCorrectAnswerText(item)
                    ? 'text-gray-700 dark:text-gray-300'
                    : 'text-gray-400'">
                    {{ getCorrectAnswerText(item) || 'No correct answer defined.' }}
                </p>

                <!-- ACTION -->
                <div class="mt-5 flex justify-end gap-3">
                    <Link :href="`/guru/bank-soal/${item.id}/edit`" class="px-4 py-2 rounded-xl
                           bg-gradient-to-r from-blue-600 to-indigo-600
                           text-white font-semibold shadow
                           active:scale-95 transition">
                        Edit
                    </Link>

                    <button @click="confirmDeleteItem(item.id)" class="px-4 py-2 rounded-xl
                           bg-gradient-to-r from-red-500 to-red-700
                           text-white font-semibold shadow
                           active:scale-95 transition">
                        Delete
                    </button>
                </div>

            </div>
        </div>

    </MenuLayout>
</template>
