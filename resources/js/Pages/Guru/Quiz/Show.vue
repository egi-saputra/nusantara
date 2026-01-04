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
        <!-- <template #header> -->
        <h2 class="md:text-2xl text-xl font-bold dark:text-white md:font-extrabold text-gray-800 mb-6">Quiz / Exam
            Question Details
        </h2>
        <!-- </template> -->

        <!-- Info Soal -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Quiz Token</p>
                <p class="font-bold text-indigo-600 dark:text-white">{{ soal.token }}</p>
            </div>
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Quiz Status</p>
                <p class="font-bold"
                    :class="soal.status === 'Aktif' ? 'text-green-600' : 'text-gray-700 dark:text-gray-200'">
                    {{ soal.status === 'Aktif' ? 'Active' : soal.status === 'Tidak Aktif' ? 'Inactive' : soal.status }}
                </p>
            </div>
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Quiz Format</p>
                <p class="font-semibold dark:text-gray-200">{{ soal.tipe_soal === 'Berurutan' ? 'Sequential' :
                    soal.tipe_soal
                        === 'Acak' ?
                        'Shuffle'
                        : soal.tipe_soal }}</p>
            </div>
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Exam Duration</p>
                <p class="font-semibold dark:text-gray-200">{{ soal.waktu }} minutes</p>
            </div>
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Subject</p>
                <p class="font-semibold dark:text-gray-200">{{ soal.mapel?.mapel ?? '-' }}</p>
            </div>
            <div class="p-3 bg-white dark:bg-[#0B1F3A] rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm dark:text-gray-400">Class Unit</p>
                <p class="font-semibold dark:text-gray-200">{{ soal.kelas }}</p>
            </div>
        </div>

        <!-- Tombol -->
        <div class="mb-6 flex flex-col md:flex-row justify-between gap-4">
            <Link href="/guru/soal"
                class="md:flex hidden items-center justify-center px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 dark:bg-gray-800 dark:hover:bg-gray-900 transition gap-2">
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Back to Quiz</span>
            </Link>

            <div class="flex gap-3">
                <Link :href="`/guru/bank-soal/create?soal_id=${soal.id}`"
                    class="flex justify-center items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-800 sm:w-auto w-1/2 transition text-white font-semibold rounded shadow">
                    + Add <span class="sm:inline-block hidden">Question</span>
                </Link>

                <button @click="confirmDeleteAll"
                    class="px-4 py-2 bg-red-600 sm:w-auto text-white rounded hover:bg-red-700 w-1/2 flex items-center justify-center gap-2">
                    <TrashIcon class="w-5 h-5" />
                    Delete All <span class="sm:inline-block hidden">Questions</span>
                </button>
            </div>
        </div>

        <!-- Bank Soal -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <div v-if="!soal.bank_soal || soal.bank_soal.length === 0"
                class="col-span-full text-center text-gray-400 p-6 bg-gray-50 dark:bg-[#0B1F3A] rounded-lg shadow">
                No questions have been added to this quiz yet.
            </div>

            <div v-for="(item, index) in soal.bank_soal" :key="item.id"
                class="bg-white dark:bg-[#0B1F3A] rounded-xl shadow-lg border border-gray-200 p-5 flex flex-col justify-between hover:shadow-2xl transition transform">

                <div class="flex justify-between items-start mb-3">
                    <span class="text-gray-400 font-semibold">No {{ index + 1 }}</span>
                    <span
                        class="text-sm font-bold px-4 py-1 rounded-full bg-indigo-100 dark:bg-orange-600 dark:shadow-md dark:text-white text-indigo-700">
                        {{ item.tipe_soal === 'PG' ? 'Multiple Choice' : item.tipe_soal === 'Essay' ? 'Typescript' :
                            item.tipe_soal }}
                    </span>
                </div>

                <p class="font-bold flex items-center dark:text-white gap-2">
                    <InformationCircleIcon class="text-blue-600 w-5 h-5" />
                    Question :
                </p>
                <p class="ml-8 text-sm font-raleway dark:text-gray-400 mb-4 line-clamp-3" :title="item.soal">{{
                    item.soal
                    }}
                </p>

                <!-- <p class="text-blue-600 font-bold">Question Type :</p>
                <p class="text-gray-800 font-semibold mb-3 line-clamp-3" :title="item.soal">{{ item.tipe_soal === 'PG' ?
                    'Multiple Choice' : item.tipe_soal === 'Essay' ? 'Typescript' :
                        item.tipe_soal }}</p> -->

                <!-- <p class="text-green-600 font-bold">Attachment Link:</p>
                <p class="text-gray-800 font-semibold mb-3 line-clamp-3">
                    <span v-if="item.link_lampiran">
                        <a :href="`/${item.link_lampiran}`" target="_blank" class="text-blue-500 underline">{{
                            item.link_lampiran }}</a>
                    </span>
                    <span v-else class="text-gray-400">No Attachment</span>
                </p> -->

                <p class="font-bold flex dark:text-white items-center gap-2">
                    <CheckBadgeIcon class="text-green-600 w-5 h-5" />
                    Correct Answer :
                </p>

                <p class="mb-3 ml-8 text-sm line-clamp-3"
                    :class="getCorrectAnswerText(item) ? 'text-gray-800 dark:text-gray-400' : 'text-gray-400'">
                    {{
                        getCorrectAnswerText(item)
                            ? getCorrectAnswerText(item)
                            : "No correct answer is currently defined."
                    }}
                </p>

                <!-- <p class="text-green-600 font-bold mb-3">Answer Option :</p>
                <div class="space-y-1 mb-4 text-gray-700">
                    <p v-if="item.opsi_a">A. {{ item.opsi_a }}</p>
                    <p v-if="item.opsi_b">B. {{ item.opsi_b }}</p>
                    <p v-if="item.opsi_c">C. {{ item.opsi_c }}</p>
                    <p v-if="item.opsi_d">D. {{ item.opsi_d }}</p>
                    <p v-if="item.opsi_e">E. {{ item.opsi_e }}</p>
                </div> -->

                <div class="flex justify-end gap-2">
                    <Link :href="`/guru/bank-soal/${item.id}/edit`"
                        class="px-3 py-1 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded hover:from-blue-600 hover:to-blue-800 transition flex items-center gap-1">
                        Edit
                    </Link>

                    <button @click="confirmDeleteItem(item.id)"
                        class="px-3 py-1 bg-gradient-to-r from-red-500 to-red-700 text-white rounded hover:from-red-600 hover:to-red-800 transition flex items-center gap-1">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </MenuLayout>
</template>
