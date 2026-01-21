<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckIcon, ArrowLeftIcon, DocumentArrowUpIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { useAlert } from '@/Composables/useAlert.js';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';
import Swal from 'sweetalert2';
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

const props = defineProps({ soal_id: [Number, String] });
const { success, error } = useAlert();
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

// State untuk opsi jawaban
const opsiState = ref(['a']);

function addOpsi() {
    if (opsiState.value.length < 5) {
        const nextOpsi = String.fromCharCode(97 + opsiState.value.length);
        opsiState.value.push(nextOpsi);
    }
}

function removeOpsi() {
    if (opsiState.value.length > 1) {
        const lastKey = opsiState.value.pop(); // hapus key terakhir
        form['opsi_' + lastKey] = '';         // kosongkan value di form
    }
}

// Upload file gambar
function handleFile(event) {
    form.lampiran_file = event.target.files[0] || null;
}

// Submit soal manual
function submitManual() {
    if (form.jenis_lampiran === 'Gambar' && !form.lampiran_file) {
        return Swal.fire('Error', 'Silakan upload file gambar terlebih dahulu!', 'error');
    }

    const data = new FormData();
    Object.keys(form).forEach(key => {
        if (key === 'lampiran_file') {
            if (form.jenis_lampiran === 'Gambar' && form.lampiran_file) {
                data.append('lampiran_file', form.lampiran_file);
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
    <div class="p-6 sm:py-12 sm:bg-gray-100 mx-auto">

        <form @submit.prevent="submitManual"
            class="sm:bg-white sm:border sm:border-gray-300 backdrop-blur mx-auto sm:max-w-3xl sm:shadow sm:rounded-2xl sm:p-6 space-y-5">

            <h1 class="text-2xl font-extrabold mb-6 text-gray-800">
                <span class="text-3xl">+</span> Tambahkan Soal Quiz
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
                        <svg v-if="form.processing" class="w-5 h-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
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
                <label class="font-semibold mb-1 dark:text-gray-300 block !text-gray-700"><span
                        class="text-red-600">*</span> Soal /
                    Pertanyaan</label>
                <div
                    class="relative rounded-xl overflow-hidden border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0F172A] shadow-sm">

                    <QuillEditor v-model:content="form.soal" placeholder="Type the question here..." content-type="html"
                        theme="snow" class="announcement-editor" :toolbar="[
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            [{ align: [] }],
                            ['clean']
                        ]" />

                    <!-- BRAND -->
                    <div class="flex w-full border-t border-gray-300 dark:border-gray-800 justify-end">
                        <span
                            class="flex justify-end px-3 text-xs py-2 editor-brand w-full text-gray-500 dark:text-gray-400">
                            Powered by<strong class="text-gray-700 pl-1 tracking-widest dark:text-gray-200 font-bold">
                                Nusaverse</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Jenis Lampiran -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700">Jenis Lampiran</label>
                <select v-model="form.jenis_lampiran" class="w-full border border-gray-300 p-3 rounded-lg"
                    :disabled="isManualDisabled">
                    <option value="Tanpa Lampiran">Tanpa Lampiran</option>
                    <option value="Gambar">Gambar</option>
                </select>
            </div>

            <!-- Lampiran File -->
            <div v-if="form.jenis_lampiran === 'Gambar'" class="mt-2">
                <label class="font-semibold mb-1 block text-gray-700">Upload Gambar</label>
                <input type="file" @change="handleFile" class="border p-2 rounded-lg w-full" />
                <p v-if="form.lampiran_file" class="text-green-600 mt-1">{{ form.lampiran_file.name }}</p>
            </div>

            <!-- OPTIONS -->
            <div v-if="form.tipe_soal === 'PG'" class="space-y-4 pt-4">
                <div class="flex sm:flex-row flex-col gap-3 justify-start sm:justify-between">
                    <h3 class="font-semibold !text-gray-700 dark:text-gray-200">
                        Answer Options
                    </h3>
                    <div class="flex gap-2">
                        <!-- Tombol Remove / Cancel -->
                        <button v-if="opsiState.length > 1" type="button" @click="removeOpsi"
                            class="text-red-600 btn-primary !py-1 !px-3 font-semibold flex items-center gap-1">
                            Remove
                        </button>

                        <!-- Tombol Add -->
                        <button v-if="opsiState.length < 5" type="button" @click="addOpsi"
                            class="text-indigo-600 font-semibold btn-primary !py-1 !px-3 flex items-center gap-1">
                            <PlusIcon class="w-4 h-4" /> Add
                        </button>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="key in opsiState" :key="key">
                        <label class="text-sm font-medium !text-gray-700 dark:text-gray-300">
                            Option {{ key.toUpperCase() }}
                        </label>
                        <input v-model="form['opsi_' + key]" class="form-input dark:text-gray-400 !text-gray-700"
                            placeholder="Enter Optional Answer" />
                    </div>
                </div>
            </div>

            <!-- Jawaban Benar -->
            <div>
                <label class="font-semibold mb-1 block text-gray-700">Jawaban Benar</label>
                <input v-if="form.tipe_soal === 'Essay'" v-model="form.jawaban_benar" type="text"
                    placeholder="Jawaban Essay"
                    class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                    :disabled="isManualDisabled" />
                <select v-else v-model="form.jawaban_benar"
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
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
                    class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
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
