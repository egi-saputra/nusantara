<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { CheckIcon, ArrowLeftIcon, PlusIcon } from '@heroicons/vue/24/solid';
import { InformationCircleIcon, PencilSquareIcon, AdjustmentsHorizontalIcon, PaperClipIcon, CheckCircleIcon, VideoCameraIcon, PhotoIcon } from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';

const page = usePage();
const { success, error, confirm } = ToastAlert();

const props = defineProps({
    bankSoal: Object,
});

const form = ref({
    soal: props.bankSoal.soal,
    tipe_soal: props.bankSoal.tipe_soal,
    jawaban_benar: props.bankSoal.jawaban_benar || '',
    nilai: props.bankSoal.nilai,
    jenis_lampiran: props.bankSoal.jenis_lampiran,
    link_lampiran: props.bankSoal.link_lampiran,
    lampiran_file: '',
    opsi_a: props.bankSoal.opsi_a,
    opsi_b: props.bankSoal.opsi_b,
    opsi_c: props.bankSoal.opsi_c,
    opsi_d: props.bankSoal.opsi_d,
    opsi_e: props.bankSoal.opsi_e,
    processing: false,
});

// State opsi jawaban dinamis
const opsiState = ref([]);
['a', 'b', 'c', 'd', 'e'].forEach(k => {
    if (form.value['opsi_' + k]) opsiState.value.push(k);
});
if (!opsiState.value.length) opsiState.value.push('a');

function addOpsi() {
    if (opsiState.value.length < 5) {
        const nextOpsi = String.fromCharCode(97 + opsiState.value.length); // 'b', 'c', ...
        opsiState.value.push(nextOpsi);
    }
}

function handleFile(event) {
    form.value.lampiran_file = event.target.files[0] || null;
}

// Reactive validation messages
const lampiranError = ref('');
watch(() => form.value.jenis_lampiran, (val) => {
    lampiranError.value = '';
    if (val === 'Gambar' && !form.value.lampiran_file) {
        lampiranError.value = 'Please upload an image file for the attachment.';
    } else if (val === 'Video' && !form.value.link_lampiran) {
        lampiranError.value = 'Please provide a video link for the attachment.';
    }
});

function submit() {
    // Validasi lampiran
    if (form.value.jenis_lampiran === 'Gambar' && !form.value.lampiran_file) {
        Swal.fire('Error', 'Please upload an image file for the attachment.', 'error');
        return;
    }

    if (form.value.jenis_lampiran === 'Video' && !form.value.link_lampiran) {
        Swal.fire('Error', 'Please provide a video link for the attachment.', 'error');
        return;
    }

    form.value.processing = true;

    const data = new FormData();
    Object.keys(form.value).forEach(key => {
        if (key === 'lampiran_file' && form.value.lampiran_file) {
            data.append('lampiran_file', form.value.lampiran_file);
        } else {
            data.append(key, form.value[key]);
        }
    });

    axios.post(`/guru/bank-soal/${props.bankSoal.id}?_method=PUT`, data)
        .then(res => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: res.data.success || 'Question item has been successfully updated!',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3b82f6',
            }).then(result => {
                if (result.isConfirmed) {
                    Inertia.visit(`/guru/soal/${props.bankSoal.soal_id}`);
                }
            });
        })
        .catch(err => {
            const errors = err.response?.data?.errors;
            if (errors) {
                Object.values(errors).forEach(e => Swal.fire('Error', e[0], 'error'));
            } else {
                Swal.fire('Error', 'An error occurred while updating the question.', 'error');
            }
        })
        .finally(() => { form.value.processing = false; });
}
</script>

<template>
    <MenuLayout>
        <div class="max-w-5xl mx-auto sm:px-4 sm:py-6">

            <!-- Main Card -->
            <div class="relative overflow-hidden
                       bg-white/80 dark:bg-white/5
                       backdrop-blur-xl
                       border border-gray-200/60 dark:border-white/10
                       sm:rounded-3xl rounded-xl sm:shadow-2xl p-6 md:p-8">

                <!-- Header -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="p-3 rounded-xl bg-indigo-600/10 text-indigo-600">
                        <PencilSquareIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                            Edit Question
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Update question type, content, and correct answer
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">

                    <!-- SECTION : Basic Settings -->
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Question Type</label>
                            <select v-model="form.tipe_soal" class="form-input dark:text-gray-400">
                                <option value="PG">Multiple Choice</option>
                                <option value="Essay">Essay</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Attachment Type</label>
                            <select v-model="form.jenis_lampiran" class="form-input  dark:text-gray-400">
                                <option value="Tanpa Lampiran">No Attachment</option>
                                <option value="Gambar">Image</option>
                                <option value="Video">Video</option>
                            </select>
                        </div>
                    </section>

                    <!-- SECTION : Attachment -->
                    <section v-if="form.jenis_lampiran !== 'Tanpa Lampiran'"
                        class="rounded-2xl border border-dashed border-gray-300 dark:border-white/20 p-5">

                        <div v-if="form.jenis_lampiran === 'Gambar'">
                            <label class="form-label">Upload Image</label>
                            <input type="file" @change="handleFile" class="form-input dark:text-gray-400" />
                            <p v-if="form.lampiran_file" class="text-green-500 text-sm mt-2">
                                {{ form.lampiran_file.name }}
                            </p>
                        </div>

                        <div v-else>
                            <label class="form-label">Video URL</label>
                            <input type="text" v-model="form.link_lampiran" placeholder="https://youtube.com/..."
                                class="form-input dark:text-gray-400" />
                        </div>
                    </section>

                    <!-- SECTION : Question -->
                    <section>
                        <label class="form-label">Question</label>
                        <textarea v-model="form.soal" rows="4" placeholder="Write the question here..."
                            class="form-input dark:text-gray-400"></textarea>
                    </section>

                    <!-- SECTION : Options -->
                    <section v-if="form.tipe_soal === 'PG'" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-200">
                                Answer Options
                            </h3>
                            <button v-if="opsiState.length < 5" type="button" @click="addOpsi"
                                class="flex items-center gap-1 text-indigo-600 font-semibold">
                                <PlusIcon class="w-4 h-4" /> Add Option
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="key in opsiState" :key="key">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    Option {{ key.toUpperCase() }}
                                </label>
                                <input v-model="form['opsi_' + key]" class="form-input dark:text-gray-400" />
                            </div>
                        </div>
                    </section>

                    <!-- SECTION : Correct Answer -->
                    <section>
                        <label class="form-label">Correct Answer</label>

                        <input v-if="form.tipe_soal === 'Essay'" v-model="form.jawaban_benar" placeholder="Essay answer"
                            class="form-input dark:text-gray-400" />

                        <select v-else v-model="form.jawaban_benar" class="form-input dark:text-gray-400">
                            <option v-for="key in opsiState" :key="key" :value="'opsi_' + key">
                                {{ key.toUpperCase() }}. {{ form['opsi_' + key] }}
                            </option>
                        </select>
                    </section>

                    <!-- ACTION -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <button type="submit" class="flex-1 btn-primary" :disabled="form.processing">
                            <CheckIcon v-if="!form.processing" class="w-5 h-5" />
                            <svg v-else class="w-5 h-5 animate-spin" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                            </svg>
                            {{ form.processing ? 'Updating...' : 'Update Question' }}
                        </button>

                        <Link :href="`/guru/soal/${props.bankSoal.soal_id}`" class="flex-1 btn-secondary">
                            <ArrowLeftIcon class="w-5 h-5" />
                            Cancel
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </MenuLayout>
</template>
