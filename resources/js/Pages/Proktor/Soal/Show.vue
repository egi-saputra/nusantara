<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/solid';
import { useAlert } from '@/Composables/useAlert.js';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

// Props dari backend
const props = defineProps({
    soal: Object,
});

const { success, error, confirm } = useAlert();

const getJawaban = (item) => {
    if (item.jawaban_benar === null || item.jawaban_benar === '') {
        return 'No correct answer defined.';
    }
    return item.jawaban_benar;
};

function confirmDeleteItem(id) {
    confirm({ text: 'Yakin hapus soal ini?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/proktor/bank-soal/${id}`)
                    .then(res => {
                        success(res.data.success || 'Soal berhasil dihapus');
                        Inertia.reload();
                    })
                    .catch(err => error(err.response?.data?.message || 'Gagal hapus soal'));
            }
        });
}

function confirmDeleteAll() {
    confirm({ text: 'Yakin hapus semua soal?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/proktor/bank-soal/soal/${props.soal.id}/delete-all`)
                    .then(res => {
                        success(res.data.success || 'Semua soal berhasil dihapus');
                        Inertia.reload();
                    })
                    .catch(err => error(err.response?.data?.message || 'Gagal hapus semua soal'));
            }
        });
}
</script>

<template>

    <Head title="Detail Quiz" />

    <MenuLayout>
        <template #header>
            <h2 class="md:text-2xl text-xl font-bold md:font-extrabold text-gray-800 mb-6">Detail Quiz / Soal Ujian</h2>
        </template>

        <!-- Info Soal -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Token Quiz</p>
                <p class="font-bold text-indigo-600">{{ soal.token }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Status Quiz</p>
                <p class="font-bold" :class="soal.status === 'Aktif' ? 'text-green-600' : 'text-gray-700'">{{
                    soal.status }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Format Soal</p>
                <p class="font-semibold">{{ soal.tipe_soal }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Waktu Ujian</p>
                <p class="font-semibold">{{ soal.waktu }} menit</p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Mata Pelajaran</p>
                <p class="font-semibold">{{ soal.mapel?.mapel ?? '-' }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm">Unit Kelas</p>
                <p class="font-semibold">{{ soal.kelas }}</p>
            </div>
        </div>

        <!-- Tombol -->
        <div class="mb-6 flex flex-col md:flex-row justify-between gap-4">
            <Link href="/proktor/soal"
                class="md:flex hidden items-center justify-center px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition gap-2">
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Back to Quiz</span>
            </Link>

            <div class="flex gap-3">
                <Link :href="`/proktor/bank-soal/create?soal_id=${soal.id}`"
                    class="flex justify-center items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-800 transition text-white font-semibold rounded-lg shadow">
                    + Tambah Soal
                </Link>

                <button @click="confirmDeleteAll" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Hapus Semua Soal
                </button>
            </div>
        </div>

        <!-- List Soal -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="!soal.bank_soal || soal.bank_soal.length === 0"
                class="col-span-full text-center text-gray-400 p-6 bg-gray-50 rounded-lg shadow">
                Belum ada soal untuk quiz ini.
            </div>

            <div v-for="(item, index) in soal.bank_soal" :key="item.id"
                class="bg-white rounded-xl shadow-lg border border-gray-200 p-5 flex flex-col justify-between hover:shadow-2xl transition transform">

                <div class="flex justify-between items-start mb-3">
                    <span class="text-gray-400 font-semibold">No {{ index + 1 }}</span>
                    <span class="text-sm font-medium px-2 py-1 rounded-full bg-indigo-100 text-indigo-700">{{
                        item.tipe_soal }}</span>
                </div>

                <p class="text-green-600 font-bold">Pertanyaan:</p>
                <p class="text-gray-800 font-semibold mb-3 line-clamp-3" :title="item.soal">{{ item.soal }}</p>

                <p class="text-green-600 font-bold">Link Lampiran:</p>
                <p class="text-gray-800 font-semibold mb-3 line-clamp-3">
                    <span v-if="item.link_lampiran">
                        <a :href="`/${item.link_lampiran}`" target="_blank" class="text-blue-500 underline">{{
                            item.link_lampiran }}</a>
                    </span>
                    <span v-else class="text-gray-400">Tanpa Lampiran</span>
                </p>

                <!-- Jawaban Benar -->
                <p class="text-green-600 font-bold mb-3">
                    Jawaban: {{ getJawaban(item) }}
                </p>

                <!-- Opsi Jawaban (hanya untuk PG) -->
                <div class="space-y-1 mb-4 text-gray-700" v-if="item.tipe_soal === 'PG'">
                    <p v-if="item.opsi_a">A. {{ item.opsi_a }}</p>
                    <p v-if="item.opsi_b">B. {{ item.opsi_b }}</p>
                    <p v-if="item.opsi_c">C. {{ item.opsi_c }}</p>
                    <p v-if="item.opsi_d">D. {{ item.opsi_d }}</p>
                    <p v-if="item.opsi_e">E. {{ item.opsi_e }}</p>
                </div>

                <div class="flex justify-end gap-2">
                    <Link :href="`/proktor/bank-soal/${item.id}/edit`"
                        class="px-3 py-1 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded hover:from-blue-600 hover:to-blue-800 transition flex items-center gap-1">
                        Edit
                    </Link>

                    <button @click="confirmDeleteItem(item.id)"
                        class="px-3 py-1 bg-gradient-to-r from-red-500 to-red-700 text-white rounded hover:from-red-600 hover:to-red-800 transition flex items-center gap-1">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </MenuLayout>
</template>
