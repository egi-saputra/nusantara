<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { PlayIcon, CheckBadgeIcon, UserIcon } from '@heroicons/vue/24/solid'
import { ClipboardDocumentCheckIcon, ArrowPathIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { ref, onMounted, computed } from "vue";
import { ToastAlert } from '@/Composables/ToastAlert.js';
import ExcelJS from 'exceljs';
import { saveAs } from "file-saver";

const { success, error, confirm } = ToastAlert();

const loading = ref(false);
const loaded = ref(false);
const rekap = ref([]);

const filter = ref({
    soal_id: "",
    mapel_id: "",
    kelas_id: ""
});

const listSoal = ref([]);
const listMapel = ref([]);
const listKelas = ref([]);

const uniqueSoal = computed(() => {
    const seenTitles = new Set();
    const unique = [];

    listSoal.value.forEach(s => {
        if (!seenTitles.has(s.title)) {
            seenTitles.add(s.title);
            unique.push(s);
        }
    });

    return unique;
});

onMounted(async () => {
    // gunakan route web session-based
    listSoal.value = await (await fetch("/guru/list-soal")).json();
    listMapel.value = await (await fetch("/guru/list-mapel")).json();
    listKelas.value = await (await fetch("/guru/list-kelas")).json();
});

// const generate = async () => {
//     // Cek filter kosong
//     if (!filter.value.soal_id || !filter.value.mapel_id || !filter.value.kelas_id) {
//         error("Silakan pilih semua filter terlebih dahulu!");
//         return; // hentikan eksekusi jika belum lengkap
//     }

//     loading.value = true;
//     loaded.value = false;

//     try {
//         const res = await fetch("/guru/rekap-filtered", {
//             method: "POST",
//             headers: {
//                 "Content-Type": "application/json",
//                 "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
//             },
//             body: JSON.stringify(filter.value)
//         });

//         rekap.value = await res.json();
//         loaded.value = true;

//         success("Data rekap berhasil di-generate!");
//     } catch (err) {
//         console.error(err);
//         error("Gagal memuat data. Silakan coba lagi!");
//     } finally {
//         loading.value = false;
//     }
// };

const generate = async () => {
    // Cek filter kosong
    if (!filter.value.soal_id || !filter.value.mapel_id || !filter.value.kelas_id) {
        error("Silakan pilih semua filter terlebih dahulu!");
        return; // hentikan eksekusi jika filter belum lengkap
    }

    loading.value = true;
    loaded.value = false;

    try {
        const res = await fetch("/guru/rekap-filtered", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(filter.value)
        });

        rekap.value = await res.json();
        loaded.value = true;

        success("Data rekap berhasil di-generate!");
    } catch (err) {
        console.error(err);
        error("Gagal memuat data. Silakan coba lagi!");
    } finally {
        loading.value = false;
    }
};

const exportExcel = async () => {
    if (rekap.value.length === 0) return;

    const mapel = filter.value.mapel_id
        ? listMapel.value.find(m => m.id === filter.value.mapel_id)?.mapel ?? "AllMapel"
        : "AllMapel";
    const kelas = filter.value.kelas_id
        ? listKelas.value.find(k => k.id === filter.value.kelas_id)?.kelas ?? "AllKelas"
        : "AllKelas";

    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet("Rekap Nilai");

    // Header & kolom
    sheet.columns = [
        { header: 'Nama Lengkap', key: 'nama', width: 30 },
        { header: 'Mata Pelajaran', key: 'mapel', width: 25 },
        { header: 'Kelas', key: 'kelas', width: 15 },
        { header: 'Nilai', key: 'nilai', width: 10 }
    ];

    // Data
    rekap.value.forEach(item => {
        sheet.addRow({
            nama: item.user?.siswa?.nama_lengkap ?? "-",
            mapel: item.soal?.mapel?.mapel ?? "-",
            kelas: item.user?.siswa?.kelas?.kelas ?? "-",
            nilai: item.total_nilai ?? "-"
        });
    });

    // Styling
    sheet.getRow(1).alignment = { horizontal: 'center', vertical: 'middle' };
    sheet.getRow(1).font = { bold: true };

    sheet.eachRow({ includeEmpty: false }, (row, rowNumber) => {
        if (rowNumber !== 1) {
            row.getCell(1).alignment = { horizontal: 'left', vertical: 'middle' };
            row.getCell(2).alignment = { horizontal: 'left', vertical: 'middle' };
            row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(4).alignment = { horizontal: 'center', vertical: 'middle' };
        }
    });

    // Export
    const buf = await workbook.xlsx.writeBuffer();
    saveAs(new Blob([buf], { type: "application/octet-stream" }), `Rekap_Nilai_${mapel}_${kelas}.xlsx`);
};
</script>

<template>
    <MenuLayout>
        <div class="sm:space-y-6 space-y-4">

            <h1 class="flex items-center gap-3 text-2xl font-bold text-gray-800 dark:text-white">
                <ClipboardDocumentCheckIcon class="w-7 h-7 text-blue-600" />
                Assessment Summary
            </h1>

            <!-- FILTER BAR -->
            <div
                class="rounded-2xl p-4 bg-white/70 dark:bg-white/5 backdrop-blur-xl border border-gray-200/60 dark:border-white/10 shadow-lg space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <select v-model="filter.soal_id" class="form-input dark:text-gray-300">
                        <option value="">Select Quiz</option>
                        <option v-for="s in uniqueSoal" :key="s.id" :value="s.id">
                            {{ s.title }}
                        </option>
                    </select>

                    <select v-model="filter.mapel_id" class="form-input dark:text-gray-300">
                        <option value="">Select Subject</option>
                        <option v-for="m in listMapel" :key="m.id" :value="m.id">
                            {{ m.mapel }}
                        </option>
                    </select>

                    <select v-model="filter.kelas_id" class="form-input dark:text-gray-300">
                        <option value="">Select Class</option>
                        <option v-for="k in listKelas" :key="k.id" :value="k.id">
                            {{ k.kelas }}
                        </option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button @click="generate" class="btn-primary w-full sm:w-auto">
                        <ArrowPathIcon v-if="loading" class="w-5 h-5 animate-spin" />
                        <PlayIcon v-else class="w-5 h-5" />
                        {{ loading ? 'Generating...' : 'Generate' }}
                    </button>
                </div>
            </div>

            <!-- LOADING -->
            <div v-if="loading" class="text-center py-10 dark:text-gray-300 text-gray-500">
                Loading data...
            </div>

            <!-- NO DATA -->
            <div v-else-if="rekap.length === 0 && loaded"
                class="text-center py-10 text-gray-500 dark:text-gray-300 text-lg font-semibold bg-white rounded-2xl shadow-sm">
                🚫 No assessment data available.
            </div>

            <!-- === MOBILE CARD VIEW === -->
            <div v-if="rekap.length > 0" class="md:hidden space-y-4 mt-6">
                <div v-for="item in rekap.filter(r => r.status === 'Selesai')" :key="`${item.user_id}-${item.soal_id}`"
                    class="rounded-2xl p-4 bg-white/80 dark:bg-white/5 backdrop-blur-xl border border-gray-200/50 dark:border-white/10 shadow-lg space-y-3">

                    <div
                        class="flex flex-row w-full justify-between items-center gap-2 font-semibold text-gray-800 dark:text-white">
                        <div class="flex gap-2">
                            <UserIcon class="w-5 h-5 text-blue-600" />
                            {{ item.user?.siswa?.nama_lengkap ?? '-' }}
                        </div>

                        <span class="badge badge-orange">
                            {{ item.user?.siswa?.kelas?.kelas ?? '-' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2 dark:text-gray-300 text-sm">
                        <span class="badge badge-green">
                            {{ item.soal?.mapel?.mapel ?? '-' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 dark:text-gray-300 text-center text-sm font-medium">
                        <div>
                            <p>Question</p>
                            <p class="font-bold">{{ item.total_soal }}</p>
                        </div>
                        <div class="text-green-600">
                            <p>Correct</p>
                            <p class="font-bold">{{ item.total_benar }}</p>
                        </div>
                        <div class="text-red-600">
                            <p>Wrong</p>
                            <p class="font-bold">{{ item.total_soal - item.total_benar }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center
                    bg-blue-50 dark:bg-blue-900/30
                    border border-blue-200/60 dark:border-blue-500/20
                    rounded-xl px-4 py-2">
                        <span class="font-semibold text-blue-700 dark:text-blue-300">
                            Score
                        </span>
                        <span class="text-xl font-bold text-blue-800 dark:text-blue-200">
                            {{ item.total_nilai }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- === DESKTOP TABLE VIEW === -->
            <div v-if="rekap.length > 0" class="hidden md:block mt-6">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                        Result Items
                    </h2>

                    <span class="badge badge-blue">
                        {{ rekap[0]?.soal?.title ?? '-' }}
                    </span>
                </div>

                <div
                    class="rounded-2xl overflow-hidden bg-white/80 dark:bg-white/5 backdrop-blur-xl border border-gray-200/60 dark:border-white/10 shadow-lg">

                    <table class="min-w-full">
                        <thead class="bg-blue-600 text-white text-sm">
                            <tr>
                                <th class="p-4 text-left">Student</th>
                                <th class="p-4 text-center">Subject</th>
                                <th class="p-4 text-center">Class</th>
                                <th class="p-4 text-center">Correct</th>
                                <th class="p-4 text-center">Score</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in rekap" :key="`${item.user_id}-${item.soal_id}`"
                                class="border-t dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">

                                <td class="p-4 font-medium">
                                    {{ item.user?.siswa?.nama_lengkap ?? '-' }}
                                </td>
                                <td class="p-4 text-center">
                                    {{ item.soal?.mapel?.mapel ?? '-' }}
                                </td>
                                <td class="p-4 text-center">
                                    {{ item.user?.siswa?.kelas?.kelas ?? '-' }}
                                </td>
                                <td class="p-4 text-center text-green-600 font-bold">
                                    {{ item.total_benar }}
                                </td>
                                <td class="p-4 text-center text-blue-600 font-bold">
                                    {{ item.total_nilai }}
                                </td>
                                <td class="p-4 text-center">
                                    <span :class="item.status === 'Selesai'
                                        ? 'badge badge-green'
                                        : 'badge badge-yellow'">
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="fixed bottom-6 right-6 z-50">
                    <button @click="exportExcel"
                        class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold shadow-xl hover:shadow-2xl hover:from-green-600 hover:to-emerald-700 transition">

                        <ArrowDownTrayIcon class="w-5 h-5" />
                        Export Excel
                    </button>
                </div>
            </div>

        </div>
    </MenuLayout>
</template>
