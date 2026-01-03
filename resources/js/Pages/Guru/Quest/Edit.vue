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
        <div>
            <div class="mx-auto sm:bg-white sm:border sm:border-gray-300 sm:rounded-2xl sm:shadow-xl md:p-8">
                <h1 class="sm:text-2xl text-lg font-bold text-gray-800 mb-6 text-left flex items-center gap-2">
                    <PencilSquareIcon class="w-6 h-6 text-gray-800" />
                    Edit Question Details
                </h1>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Question Type, Score, Attachment Type -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-700 font-semibold mb-2 flex items-center gap-2">
                                <AdjustmentsHorizontalIcon class="w-5 h-5 text-indigo-600" />
                                Question Type
                            </label>
                            <select v-model="form.tipe_soal"
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                                <option value="PG">Multiple Choice</option>
                                <option value="Essay">Essay</option>
                            </select>
                        </div>

                        <!-- Score Poin -->
                        <!-- <div>
                        <label class="block text-gray-700 font-semibold mb-2">Score Weight</label>
                        <input type="number" v-model="form.nilai" min="0"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                            placeholder="Enter score (optional)" />
                    </div> -->

                        <!-- Attachment File -->
                        <div>
                            <label class="text-gray-700 font-semibold mb-2 flex items-center gap-2">
                                <PaperClipIcon class="w-5 h-5 text-indigo-600" />
                                Attachment Type
                            </label>
                            <select v-model="form.jenis_lampiran"
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                                <option value="Tanpa Lampiran">No Attachment</option>
                                <option value="Gambar">Image</option>
                                <option value="Video">Video</option>
                            </select>
                        </div>
                    </div>

                    <!-- File / Link Attachment -->
                    <div v-if="form.jenis_lampiran === 'Gambar'">
                        <label class="text-gray-700 font-semibold mb-1 flex items-center gap-2">
                            <PhotoIcon class="w-5 h-5 text-blue-600" />
                            Upload Image
                        </label>
                        <input type="file" @change="handleFile" class="border p-2 rounded-lg w-full" />
                        <p v-if="form.lampiran_file" class="text-green-600 mt-1">{{ form.lampiran_file.name }}</p>
                        <p v-else-if="form.link_lampiran" class="text-gray-500 mt-1">Current: {{ form.link_lampiran }}
                        </p>
                    </div>
                    <div v-else-if="form.jenis_lampiran === 'Video'">
                        <label class="text-gray-700 font-semibold mb-1 flex items-center gap-2">
                            <VideoCameraIcon class="w-5 h-5 text-purple-600" />
                            Video Link
                        </label>
                        <input type="text" v-model="form.link_lampiran" placeholder="Enter video URL"
                            class="w-full border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition" />
                    </div>

                    <!-- Question -->
                    <div>
                        <label class="text-gray-700 font-extrabold tracking-wide mb-2 flex items-center gap-2">
                            <InformationCircleIcon class="w-5 h-5 text-amber-600" />
                            Question
                        </label>
                        <textarea v-model="form.soal"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                            rows="4" placeholder="Enter the question"></textarea>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div v-if="form.tipe_soal === 'PG'" class="grid grid-cols-1 gap-4">
                        <div v-for="key in opsiState" :key="key">
                            <label class="font-semibold">Option {{ key.toUpperCase() }}</label>
                            <input v-model="form['opsi_' + key]"
                                class="w-full border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
                        </div>
                        <button v-if="opsiState.length < 5" type="button" @click="addOpsi"
                            class="flex items-center gap-1 text-blue-600 font-semibold">
                            <PlusIcon class="w-4 h-4" /> Add
                        </button>
                    </div>

                    <!-- Correct Answer -->
                    <div>
                        <label class="text-gray-700 font-semibold mb-2 flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-green-600" />
                            Correct Answer
                        </label>
                        <input v-if="form.tipe_soal === 'Essay'" v-model="form.jawaban_benar" type="text"
                            placeholder="Essay Answer"
                            class="w-full border pl-3 border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
                        <select v-else v-model="form.jawaban_benar"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            <option v-for="key in opsiState" :key="key" :value="'opsi_' + key">
                                {{ key.toUpperCase() }}. {{ form['opsi_' + key] }}
                            </option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-6">
                        <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 transition"
                            :disabled="form.processing">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            <CheckIcon v-else class="w-5 h-5" />
                            <span>{{ form.processing ? 'Updating...' : 'Update' }}</span>
                        </button>

                        <Link :href="`/guru/soal/${props.bankSoal.soal_id}`"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-700 text-white font-semibold rounded-lg shadow-lg hover:from-gray-600 hover:to-gray-800 transition">
                            <ArrowLeftIcon class="w-5 h-5" /> Cancel
                        </Link>
                    </div>

                </form>
            </div>
        </div>
    </MenuLayout>
</template>
