<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckIcon, ArrowLeftIcon, DocumentArrowUpIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { useAlert } from '@/Composables/useAlert.js';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({ soal_id: [Number, String] });
const { success, error, confirm } = useAlert();
const page = usePage();

onMounted(() => {
    if (page.props.flash?.success) success(page.props.flash.success);
});

watch(
    () => page.props.flash?.success,
    (newVal) => { if (newVal) success(newVal); }
);

const form = useForm({
    soal_id: Number(props.soal_id),
    soal: '',
    tipe_soal: 'PG',
    jenis_lampiran: 'Tanpa Lampiran',
    link_lampiran: '',
    lampiran_file: null,
    opsi_a: '',
    opsi_b: '',
    opsi_c: '',
    opsi_d: '',
    opsi_e: '',
    jawaban_benar: '',
    nilai: 0,
    excel: null,
});

// State untuk menampilkan opsi jawaban
const opsiState = ref(['a']); // Awal hanya opsi_a

function addOpsi() {
    if (opsiState.value.length < 5) {
        const nextOpsi = String.fromCharCode(97 + opsiState.value.length); // 'b', 'c', ...
        opsiState.value.push(nextOpsi);
    }
}

// Lampiran file
function handleFile(event) {
    form.lampiran_file = event.target.files[0] || null;
}

// Submit soal manual
function submitManual() {
    // Validasi frontend
    if (form.jenis_lampiran === 'Gambar' && !form.lampiran_file) {
        return Swal.fire('Error', 'Silakan upload file gambar terlebih dahulu!', 'error');
    }
    if (form.jenis_lampiran === 'Video' && !form.link_lampiran) {
        return Swal.fire('Error', 'Silakan masukkan link video!', 'error');
    }

    const data = new FormData();

    Object.keys(form).forEach(key => {
        if (key === 'lampiran_file') {
            if (form.jenis_lampiran === 'Gambar' && form.lampiran_file) {
                data.append('lampiran_file', form.lampiran_file);
            }
        } else if (key === 'link_lampiran') {
            if (form.jenis_lampiran === 'Video' && form.link_lampiran) {
                data.append('link_lampiran', form.link_lampiran);
            }
        } else {
            data.append(key, form[key]);
        }
    });

    axios.post('/proktor/bank-soal', data)
        .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.data.success || 'Soal manual berhasil ditambahkan.',
                confirmButtonText: 'OKE',
                confirmButtonColor: '#3b82f6'
            }).then((result) => {
                if (result.isConfirmed) Inertia.visit(res.data.redirect || `/proktor/soal/${props.soal_id}`);
            });
        })
        .catch(err => {
            Swal.fire('Error', err.response?.data?.message || 'Terjadi kesalahan saat submit soal manual.', 'error');
        });
}

// Import Excel
function importExcel(event) { form.excel = event.target.files[0] || null; }
function submitExcel() {
    if (!form.excel) return;
    form.processing = true;
    const data = new FormData();
    data.append('excel', form.excel);
    data.append('soal_id', props.soal_id);
    axios.post('/proktor/bank-soal/import', data)
        .then(res => {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.data.success, confirmButtonText: 'OKE', confirmButtonColor: '#3b82f6' })
                .then((result) => { if (result.isConfirmed && res.data.redirect) Inertia.visit(res.data.redirect); });
        })
        .catch(err => Swal.fire('Error', 'Terjadi kesalahan saat import', 'error'))
        .finally(() => { form.processing = false; });
}

const isManualDisabled = computed(() => !!form.excel);
function downloadTemplate() { Inertia.visit('/proktor/bank-soal/template'); }
</script>

<template>
    <div class="p-6 py-12 bg-gray-100  mx-auto">

        <!-- Form Manual + Import -->
        <form @submit.prevent="submitManual"
            class="bg-white border border-gray-300 backdrop-blur mx-auto max-w-3xl shadow rounded-2xl p-6 space-y-5">
            <h1 class="text-2xl font-extrabold mb-6 text-gray-800"><span class="text-3xl">+</span> Tambahkan Soal Quiz
            </h1>

            <!-- Import Excel + Download Template -->
            <div class="border border-dashed border-gray-300 p-4 rounded-lg bg-gray-50 text-center space-y-2">
                <label class="flex flex-col items-center justify-center cursor-pointer">
                    <DocumentArrowUpIcon class="w-10 h-10 text-blue-500 mb-2" />
                    <span class="text-gray-600 font-semibold mb-1">Upload File Soal</span>
                    <span class="text-gray-400 text-sm">(.xlsx / .xls)</span>
                    <input type="file" accept=".xlsx,.xls" @change="importExcel" class="hidden" />
                </label>
                <p v-if="form.excel" class="mt-2 text-green-600 font-medium">{{ form.excel.name }}</p>

                <div class="flex justify-center gap-2 mt-2">
                    <button type="button" @click="submitExcel" :class="[
                        'px-4 py-2 text-white rounded-lg transition font-medium flex items-center justify-center gap-2',
                        form.processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'
                    ]" :disabled="!form.excel || form.processing">

                        <!-- Spinner saat proses -->
                        <svg v-if="form.processing" class="w-5 h-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        <!-- Teks tombol -->
                        <span>{{ form.processing ? 'Importing...' : 'Import Excel' }}</span>
                    </button>

                    <button type="button" @click="downloadTemplate"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        Unduh Template
                    </button>
                </div>
            </div>

            <!-- Tipe Soal -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700"><span class="text-red-600">*</span> Tipe
                    Soal</label>
                <select v-model="form.tipe_soal"
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    :disabled="isManualDisabled">
                    <option value="PG">Pilihan Ganda</option>
                    <option value="Essay">Essay</option>
                </select>
            </div>

            <!-- Pertanyaan -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700"><span class="text-red-600">*</span> Soal /
                    Pertanyaan</label>
                <textarea v-model="form.soal" rows="4"
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    placeholder="Masukkan pertanyaan!" :disabled="isManualDisabled">
                </textarea>
            </div>

            <!-- Jenis Lampiran -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700">Jenis Lampiran</label>
                <select v-model="form.jenis_lampiran" class="w-full border p-3 rounded-lg" :disabled="isManualDisabled">
                    <option value="Tanpa Lampiran">Tanpa Lampiran</option>
                    <option value="Gambar">Gambar</option>
                    <option value="Video">Video</option>
                </select>
            </div>

            <!-- Lampiran File atau Link -->
            <div v-if="form.jenis_lampiran === 'Gambar'" class="mt-2">
                <label class="font-semibold mb-1 block text-gray-700">Upload Gambar</label>
                <input type="file" @change="handleFile" class="border p-2 rounded-lg w-full" />
                <p v-if="form.lampiran_file" class="text-green-600 mt-1">{{ form.lampiran_file.name }}</p>
            </div>
            <div v-else-if="form.jenis_lampiran === 'Video'" class="mt-2">
                <label class="font-semibold mb-1 block text-gray-700">Link Video</label>
                <input type="text" v-model="form.link_lampiran" class="border p-2 rounded-lg w-full"
                    placeholder="Masukkan URL video" />
            </div>

            <!-- Opsi Pilihan Ganda -->
            <div v-if="form.tipe_soal === 'PG'" class="grid grid-cols-1 gap-4">
                <div v-for="key in opsiState" :key="key">
                    <label class="font-semibold">Jawaban Opsi {{ key.toUpperCase() }}</label>
                    <input v-model="form['opsi_' + key]" class="w-full border p-2 rounded-lg" />
                </div>
                <button v-if="opsiState.length < 5" type="button" @click="addOpsi"
                    class="flex items-center gap-1 text-blue-600 font-semibold">
                    <PlusIcon class="w-4 h-4" /> Tambah
                </button>
            </div>

            <!-- Jawaban Benar -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700">Jawaban Benar</label>
                <input v-if="form.tipe_soal === 'Essay'" v-model="form.jawaban_benar" type="text"
                    placeholder="Jawaban Essay"
                    class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    :disabled="isManualDisabled" />
                <select v-else v-model="form.jawaban_benar"
                    class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    :disabled="isManualDisabled">
                    <option value="opsi_a">A. {{ form.opsi_a }}</option>
                    <option value="opsi_b">B. {{ form.opsi_b }}</option>
                    <option value="opsi_c">C. {{ form.opsi_c }}</option>
                    <option value="opsi_d">D. {{ form.opsi_d }}</option>
                    <option value="opsi_e">E. {{ form.opsi_e }}</option>
                </select>
            </div>

            <!-- Nilai -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700">Bobot Nilai</label>
                <input v-model="form.nilai" type="number" min="0"
                    class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    :disabled="isManualDisabled" />
            </div>

            <!-- Submit Manual -->
            <div class="flex flex-col md:flex-row gap-4 mt-4">
                <button type="submit"
                    class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 transition"
                    :disabled="form.processing">
                    <CheckIcon class="w-5 h-5" />
                    <span>Create Quest</span>
                </button>

                <Link :href="`/proktor/soal/${props.soal_id}`"
                    class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg shadow hover:bg-gray-700 transition">
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Quiz List
                </Link>
            </div>

        </form>
    </div>
</template>
