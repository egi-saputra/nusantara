<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { PlayIcon, CheckBadgeIcon, UserIcon } from '@heroicons/vue/24/solid'
import { ClipboardDocumentCheckIcon, ArrowPathIcon, BookOpenIcon, AcademicCapIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
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

            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-6 h-6 text-blue-600" />
                Assessment Summary
            </h1>

            <!-- FILTER -->
            <div class="sm:p-4 space-y-4 mb-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

                    <select v-model="filter.soal_id"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Quiz Title --</option>
                        <option v-for="s in uniqueSoal" :key="s.id" :value="s.id">
                            {{ s.title }}
                        </option>
                    </select>

                    <select v-model="filter.mapel_id"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Subject --</option>
                        <option v-for="m in listMapel" :value="m.id">{{ m.mapel }}</option>
                    </select>

                    <select v-model="filter.kelas_id"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Class --</option>
                        <option v-for="k in listKelas" :value="k.id">{{ k.kelas }}</option>
                    </select>
                </div>

                <div class="flex sm:justify-end">

                    <button @click="generate"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded sm:rounded-lg w-full sm:w-auto flex items-center justify-center gap-2">

                        <template v-if="loading">
                            <ArrowPathIcon class="w-5 h-5 animate-spin text-white" />
                            Generating...
                        </template>
                        <template v-else>
                            <PlayIcon class="w-5 h-5 text-white" />
                            Generate
                        </template>
                    </button>
                </div>

            </div>

            <!-- LOADING -->
            <div v-if="loading" class="text-center py-10 text-gray-500">
                Loading data...
            </div>

            <!-- NO DATA -->
            <div v-else-if="rekap.length === 0 && loaded"
                class="text-center py-10 text-gray-500 text-lg font-semibold bg-white rounded-2xl shadow-sm">
                🚫 No assessment data available.
            </div>

            <!-- === MOBILE CARD VIEW === -->
            <div v-else-if="rekap.length > 0" class="space-y-4 mt-6 md:hidden">

                <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <ArrowPathIcon class="w-5 h-5 text-gray-800" />
                    Result Items
                </h1>

                <!-- Completed Only -->
                <template v-if="rekap.filter(r => r.status === 'Selesai').length > 0">

                    <div v-for="item in rekap.filter(r => r.status === 'Selesai')"
                        :key="`${item.user_id}-${item.soal_id}`"
                        class="bg-white rounded-lg shadow p-4 space-y-3 border">

                        <!-- NAME -->
                        <div class="flex items-center">
                            <UserIcon class="h-5 w-5 mr-2 text-gray-700" />
                            <span class="font-semibold text-gray-800">
                                {{ item.user?.siswa?.nama_lengkap ?? "-" }}
                            </span>
                        </div>

                        <!-- SUBJECT -->
                        <div
                            class="flex items-center gap-2 bg-green-50 text-green-700 py-1 px-2 rounded text-sm font-medium">
                            <BookOpenIcon class="h-4 w-4" />
                            Subject: {{ item.soal?.mapel?.mapel ?? "-" }}
                        </div>

                        <!-- CLASS -->
                        <div
                            class="flex items-center gap-2 bg-orange-50 text-amber-600 py-1 px-2 rounded text-sm font-medium">
                            <AcademicCapIcon class="h-4 w-4" />
                            Class: {{ item.user?.siswa?.kelas?.kelas ?? "-" }}
                        </div>

                        <!-- TOTAL -->
                        <div class="grid grid-cols-3 gap-2 text-sm text-gray-700 font-medium">

                            <!-- TOTAL QUESTIONS -->
                            <div>
                                Questions:
                                <span class="font-bold">{{ item.total_soal }}</span>
                            </div>

                            <!-- CORRECT -->
                            <div>
                                Correct:
                                <span class="font-bold text-green-600">{{ item.total_benar }}</span>
                            </div>

                            <!-- WRONG -->
                            <div>
                                Wrong:
                                <span class="font-bold text-red-600">
                                    {{ item.total_soal - item.total_benar }}
                                </span>
                            </div>

                        </div>

                        <!-- SCORE + STATUS -->
                        <div
                            class="flex justify-between items-center bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded shadow-sm">

                            <div class="flex items-center gap-2">
                                <CheckBadgeIcon class="h-5 w-5 text-blue-600" />

                                <span class="font-semibold">Score:</span>
                                <span class="font-bold font-mono text-blue-800 text-lg">
                                    {{ item.total_nilai }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1 text-green-600 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ item.status }}
                            </div>
                        </div>

                    </div>
                </template>

                <!-- FALLBACK -->
                <div v-else class="text-center py-10 text-gray-500 text-base font-semibold bg-white rounded-xl shadow">
                    🚫 No completed assessments yet.
                </div>

            </div>

            <!-- === DESKTOP TABLE VIEW === -->
            <div v-if="rekap.length > 0" class="overflow-x-auto rounded-xl px-6 shadow-sm hidden md:block">

                <div class="flex justify-between pr-2">
                    <h1 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                        <ArrowPathIcon class="w-5 h-5 text-gray-800" />
                        Result Items
                    </h1>

                    <h1 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                        <CheckCircleIcon class="w-7 h-7 text-green-600" />
                        {{ rekap[0]?.soal?.title ?? "-" }}
                    </h1>
                </div>

                <table class="min-w-full bg-white">
                    <thead class="bg-blue-700 border border-collapse text-white">
                        <tr class="border border-collapse">
                            <th class="p-3 text-center">Full Name</th>
                            <th class="p-3 text-center">Subject</th>
                            <th class="p-3 text-center">Class / Group</th>
                            <th class="p-3 text-center">Correct Answers</th>
                            <th class="p-3 text-center">Total Score</th>
                            <th class="p-3 text-center">Exam Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in rekap" :key="`${item.user_id}-${item.soal_id}`"
                            class="border-t hover:bg-gray-50">

                            <td class="p-3">{{ item.user?.siswa?.nama_lengkap ?? "-" }}</td>
                            <td class="p-3">{{ item.soal?.mapel?.mapel ?? "-" }}</td>
                            <td class="p-3 text-center">{{ item.user?.siswa?.kelas?.kelas ?? "-" }}</td>
                            <td class="p-3 text-center text-green-600 font-semibold">{{ item.total_benar }}</td>
                            <td class="p-3 text-center text-blue-600 font-bold">{{ item.total_nilai }}</td>
                            <td class="p-3 text-center">
                                <span
                                    :class="item.status === 'Selesai' ? 'text-green-600 font-bold font-mono' : 'text-yellow-600 font-bold font-mono'">
                                    {{ item.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex justify-end py-4">
                    <button @click="exportExcel"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-2">
                        Export to Excel
                    </button>
                </div>
            </div>

        </div>
    </MenuLayout>
</template>
