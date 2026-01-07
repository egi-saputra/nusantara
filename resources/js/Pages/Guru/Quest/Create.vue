<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckIcon, ArrowLeftIcon, DocumentArrowUpIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';

const props = defineProps({ soal_id: [Number, String] });
const { success, error } = ToastAlert();
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

// State untuk menampilkan opsi jawaban
const opsiState = ref(['a']); // Awal hanya opsi_a

function addOpsi() {
    if (opsiState.value.length < 5) {
        const nextOpsi = String.fromCharCode(97 + opsiState.value.length); // 'b', 'c', ...
        opsiState.value.push(nextOpsi);
    }
}

function removeOpsi() {
    if (opsiState.value.length > 1) {
        const lastKey = opsiState.value.pop(); // hapus key terakhir
        form['opsi_' + lastKey] = '';         // kosongkan value di form
    }
}

// Lampiran file
function handleFile(event) {
    form.lampiran_file = event.target.files[0] || null;
}

// Submit soal manual
function submitManual() {
    form.processing = true;

    if (form.jenis_lampiran === 'Gambar' && !form.lampiran_file) {
        form.processing = false;
        return error('Silakan upload file gambar terlebih dahulu!');
    }

    const data = new FormData();

    Object.keys(form).forEach(key => {
        if (key === 'lampiran_file' && form.jenis_lampiran === 'Gambar' && form.lampiran_file) {
            data.append('lampiran_file', form.lampiran_file);
        } else {
            data.append(key, form[key]);
        }
    });

    axios.post('/guru/bank-soal', data)
        .then(res => {
            success(res.data.success || 'Soal berhasil ditambahkan.');
            Inertia.visit(res.data.redirect || `/guru/soal/${props.soal_id}`);
        })
        .catch(err => {
            error(err.response?.data?.message || 'Terjadi kesalahan saat submit.');
        })
        .finally(() => {
            form.processing = false;
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
    axios.post('/guru/bank-soal/import', data)
        .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: res.data.success,
                confirmButtonText: 'OKE',
                confirmButtonColor: '#3b82f6'
            }).then((result) => { if (result.isConfirmed && res.data.redirect) Inertia.visit(res.data.redirect); });
        })
        .catch(() => Swal.fire('Error', 'Terjadi kesalahan saat import', 'error'))
        .finally(() => { form.processing = false; });
}

const isManualDisabled = computed(() => !!form.excel);
function downloadTemplate() { Inertia.visit('/guru/bank-soal/template'); }
</script>


<template>
    <MenuLayout>
        <div class="max-w-5xl mx-auto sm:px-4 sm:py-6">

            <form @submit.prevent="submitManual" class="relative overflow-hidden
                       bg-white/80 dark:bg-white/5
                       backdrop-blur-xl
                       border border-gray-200/60 dark:border-white/10
                       sm:rounded-3xl rounded-xl sm:shadow-2xl p-6 md:p-8 space-y-8">

                <!-- HEADER -->
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl bg-blue-600/10 text-blue-600">
                        <PlusIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                            Add Quiz Question
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Create manually or import from Excel template
                        </p>
                    </div>
                </div>

                <!-- IMPORT EXCEL -->
                <section class="relative rounded-2xl border border-dashed border-blue-400/40
                           bg-blue-50/40 dark:bg-[#0B1F3A]
                           p-6 text-center space-y-4">

                    <DocumentArrowUpIcon class="w-12 h-12 mx-auto text-blue-500" />

                    <div>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">
                            Import Questions from Excel
                        </p>
                        <p class="text-sm text-gray-500">
                            Supported format: .xlsx / .xls
                        </p>
                    </div>

                    <label class="inline-flex items-center gap-2 px-5 py-2.5
                               bg-blue-600 text-white rounded-xl cursor-pointer
                               hover:bg-blue-700 transition font-semibold">
                        <input type="file" accept=".xlsx,.xls" class="hidden" @change="importExcel" />
                        <DocumentArrowUpIcon class="w-5 h-5" />
                        Choose File
                    </label>

                    <p v-if="form.excel" class="text-green-600 font-medium">
                        {{ form.excel.name }}
                    </p>

                    <div class="flex justify-center gap-3 pt-2">
                        <button type="button" @click="submitExcel" class="btn-success"
                            :disabled="!form.excel || form.processing">

                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                            </svg>

                            {{ form.processing ? 'Importing...' : 'Import Excel' }}
                        </button>

                        <button type="button" @click="downloadTemplate" class="btn-secondary">
                            Download <span class="sm:inline-block hidden">Template</span>
                        </button>
                    </div>
                </section>

                <!-- DIVIDER -->
                <div class="flex items-center gap-4">
                    <div class="flex-1 h-px bg-gray-300 dark:bg-white/10"></div>
                    <span class="text-sm font-semibold text-gray-400">OR</span>
                    <div class="flex-1 h-px bg-gray-300 dark:bg-white/10"></div>
                </div>

                <!-- MANUAL FORM -->
                <section class="space-y-6">

                    <!-- TYPE -->
                    <div>
                        <label class="form-label">Question Type</label>
                        <select v-model="form.tipe_soal" class="form-input dark:text-gray-400"
                            :disabled="isManualDisabled">
                            <option value="PG">Multiple Choice</option>
                            <option value="Essay">Essay</option>
                        </select>
                    </div>

                    <!-- QUESTION -->
                    <div>
                        <label class="form-label">Question</label>
                        <textarea v-model="form.soal" rows="4" placeholder="Write your question here..."
                            class="form-input dark:text-gray-400" :disabled="isManualDisabled"></textarea>
                    </div>

                    <!-- ATTACHMENT -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Attachment Type</label>
                            <select v-model="form.jenis_lampiran" class="form-input dark:text-gray-400"
                                :disabled="isManualDisabled">
                                <option value="Tanpa Lampiran">No Attachment</option>
                                <option value="Gambar">Image</option>
                            </select>
                        </div>

                        <div v-if="form.jenis_lampiran === 'Gambar'">
                            <label class="form-label">Upload Image</label>
                            <input type="file" @change="handleFile" class="form-input dark:text-gray-500" />
                        </div>
                    </div>

                    <!-- OPTIONS -->
                    <div v-if="form.tipe_soal === 'PG'" class="space-y-4 pt-4">
                        <div class="flex sm:flex-row flex-col gap-3 justify-start sm:justify-between">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">
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
                                <label class="text-sm font-medium dark:text-gray-300">
                                    Option {{ key.toUpperCase() }}
                                </label>
                                <input v-model="form['opsi_' + key]" class="form-input dark:text-gray-400"
                                    placeholder="Enter Optional Answer" />
                            </div>
                        </div>
                    </div>

                    <!-- CORRECT ANSWER -->
                    <div>
                        <label class="form-label">Correct Answer</label>

                        <select v-if="form.tipe_soal === 'PG'" v-model="form.jawaban_benar" class="form-input">
                            <option v-for="key in opsiState" :key="key" :value="'opsi_' + key">
                                {{ key.toUpperCase() }} — {{ form['opsi_' + key] || '(empty)' }}
                            </option>
                        </select>

                        <textarea v-else v-model="form.jawaban_benar" placeholder="Correct answer..."
                            class="form-input dark:text-gray-400"></textarea>
                    </div>

                </section>

                <!-- ACTION -->
                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        <CheckIcon class="w-5 h-5" />
                        {{ form.processing ? 'Creating...' : 'Create Question' }}
                    </button>

                    <Link :href="`/guru/soal/${props.soal_id}`" class="btn-secondary">
                        <ArrowLeftIcon class="w-5 h-5" />
                        Back to List
                    </Link>
                </div>

            </form>
        </div>
    </MenuLayout>
</template>
