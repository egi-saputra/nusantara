<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowPathIcon, ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    soal: Object,
    nilai_per_soal: Number,
    mapel: Array, // tambahkan props mapel dari backend
});

// Form pengaturan quiz
const form = useForm({
    title: props.soal.title || '',
    mapel_id: props.soal.mapel_id || '',  // gunakan mapel_id
    kelas: props.soal.kelas || '',
    waktu: props.soal.waktu || 0,
    status: props.soal.status || 'Tidak Aktif',
    tipe_soal: props.soal.tipe_soal || 'Berurutan',
    token: props.soal.token || '',
    nilai_per_soal: props.nilai_per_soal,
});

// Generate token 6 digit
// const generateToken = () => {
//     form.token = Math.floor(100000 + Math.random() * 900000).toString();
// };
const generateToken = () => {
    const t = Math.floor(100000 + Math.random() * 900000);
    form.token = t.toString().padStart(6, '0'); // selalu 6 digit
};

// Submit form & update semua nilai butir soal
const submit = async () => {
    form.processing = true;

    try {
        // Update data quiz utama, pastikan token dikirim
        await form.put(`/proktor/soal/${props.soal.id}`);

        // Hanya update nilai per butir soal jika ada soal
        if (props.soal.bank_soal && props.soal.bank_soal.length > 0) {
            await axios.put(`/proktor/soal/${props.soal.id}/update-nilai`, {
                nilai: form.nilai_per_soal
            });
        }

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Pengaturan quiz, mapel, kelas, dan nilai berhasil diperbarui.',
            confirmButtonColor: '#3b82f6',
        }).then(() => {
            window.location.href = `/proktor/soal`;
        });

    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan saat update.', 'error');
    } finally {
        form.processing = false;
    }
};
</script>

<template>
    <div class="md:py-8 py-2 px-4 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto md:bg-white md:rounded-2xl md:shadow-xl md:p-8 p-2">
            <h1 class="md:text-2xl text-xl font-bold text-gray-800 mb-4 md:mb-8 text-left">
                Pengaturan / Konfigurasi Quiz
            </h1>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Judul Quiz</label>
                    <input v-model="form.title" type="text"
                        class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                        placeholder="Masukkan judul quiz" />
                </div>

                <!-- Mapel & Kelas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Mata Pelajaran</label>
                        <select v-model="form.mapel_id"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <option v-for="m in mapel" :key="m.id" :value="m.id">
                                {{ m.mapel }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Kelas</label>
                        <input v-model="form.kelas" type="text"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                            placeholder="Masukkan kelas" />
                    </div>
                </div>

                <!-- Waktu & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Waktu (Menit)</label>
                        <input v-model="form.waktu" type="number"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                            placeholder="Masukkan waktu pengerjaan" />
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Status Quiz</label>
                        <select v-model="form.status"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <!-- Format & Nilai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Format Soal</label>
                        <select v-model="form.tipe_soal"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            <option value="Berurutan">Berurutan</option>
                            <option value="Acak">Acak</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nilai (/Butir Soal)</label>
                        <input v-model="form.nilai_per_soal" type="number" min="0"
                            :disabled="!props.soal.bank_soal || props.soal.bank_soal.length === 0"
                            class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-green-400 transition"
                            placeholder="Masukkan bobot nilai per soal" />
                        <p v-if="!props.soal.bank_soal || props.soal.bank_soal.length === 0"
                            class="text-red-500 text-sm mt-1">
                            Tidak ada soal, tidak dapat mengisi nilai poin.
                        </p>
                    </div>
                </div>

                <!-- Token -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Token Quiz</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input v-model="form.token" type="text" readonly
                            class="flex-1 border border-gray-300 rounded-lg p-3 bg-gray-100 text-gray-700 font-semibold focus:outline-none" />
                        <button type="button" @click="generateToken"
                            class="flex items-center justify-center gap-2 px-5 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition">
                            <ArrowPathIcon class="w-5 h-5" />
                            Generate Token Baru
                        </button>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex flex-col sm:flex-row gap-4 mt-6">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 transition"
                        :disabled="form.processing">
                        <CheckIcon class="w-5 h-5" />
                        <span>{{ form.processing ? 'Updating...' : 'Update' }}</span>
                    </button>

                    <Link href="/proktor/soal"
                        class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-700 text-white font-semibold rounded-lg shadow-lg hover:bg-gray-800 transition">
                        <ArrowLeftIcon class="w-5 h-5" />
                        Kembali
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
