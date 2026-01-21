<template>
    <MenuLayout>
        <div class="sm:space-y-6 space-y-4">

            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-6 h-6 text-blue-600" />
                Rekap Penilaian
            </h1>

            <!-- FILTER -->
            <div class="sm:p-4 space-y-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <select v-model="filter.soal_title"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Judul Soal --</option>
                        <option v-for="s in uniqueSoal" :key="s.id" :value="s.title">
                            {{ s.title }}
                        </option>
                    </select>

                    <select v-model="filter.mapel_id"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Mapel --</option>
                        <option v-for="m in listMapel" :value="m.id">{{ m.mapel }}</option>
                    </select>

                    <select v-model="filter.kelas_id"
                        class="border p-2 rounded sm:rounded-lg w-full bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Kelas --</option>
                        <option v-for="k in listKelas" :value="k.id">{{ k.kelas }}</option>
                    </select>
                </div>

                <div class="flex sm:justify-end">
                    <button @click="generate"
                        class="bg-blue-600 hover:bg-blue-700 transition text-white px-4 py-2 rounded sm:rounded-lg w-full sm:w-auto flex items-center justify-center gap-2"
                        :disabled="loading">
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

            <!-- HASIL REKAP -->
            <div v-if="loading" class="text-center py-10 text-gray-500">
                Memuat data...
            </div>

            <div v-else-if="filteredRekap.length === 0 && loaded"
                class="text-center py-10 text-gray-500 text-lg font-semibold">
                🚫 Tidak ada data nilai <span class="sm:inline-block hidden">ditemukan</span>
            </div>

            <!-- MOBILE CARD VIEW -->
            <div v-else-if="filteredRekap.length > 0" class="space-y-4 mt-6 md:hidden">
                <div v-for="item in paginatedRekap" :key="`${item.user_id}-${item.soal_id}`"
                    class="bg-white rounded-lg shadow p-4 space-y-3 border">
                    <div class="flex items-center">
                        <UserIcon class="h-5 w-5 mr-2 text-gray-700" />
                        <span class="font-semibold text-gray-800">
                            {{ item.user?.siswa?.nama_lengkap ?? "-" }}
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-2 bg-green-50 text-green-700 py-1 px-2 rounded text-sm font-medium">
                        <BookOpenIcon class="h-4 w-4" /> Mapel: {{ item.soal?.mapel?.mapel ?? "-" }}
                    </div>

                    <div
                        class="flex items-center gap-2 bg-orange-50 text-amber-600 py-1 px-2 rounded text-sm font-medium">
                        <AcademicCapIcon class="h-4 w-4" /> Kelas: {{ item.user?.siswa?.kelas?.kelas ?? "-" }}
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm text-gray-700 font-medium">
                        <div>Soal: <span class="font-bold">{{ item.total_soal }}</span></div>
                        <div>Benar: <span class="font-bold text-green-600">{{ item.total_benar }}</span></div>
                        <div>Salah: <span class="font-bold text-red-600">{{ item.total_soal - item.total_benar }}</span>
                        </div>
                    </div>

                    <div
                        class="flex justify-between items-center bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded shadow-sm">
                        <div class="flex items-center gap-2">
                            <CheckBadgeIcon class="h-5 w-5 text-blue-600" />
                            <span class="font-semibold">Nilai:</span>
                            <span class="font-bold font-mono text-blue-800 text-lg">{{ item.total_nilai }}</span>
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

                <!-- Pagination Mobile -->
                <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
                    <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded border"
                        :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        Prev
                    </button>
                    <button v-for="p in visiblePages" :key="p" @click="currentPage = p" class="px-3 py-1 rounded border"
                        :class="p === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        {{ p }}
                    </button>
                    <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border"
                        :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        Next
                    </button>
                </div>
            </div>

            <!-- DESKTOP TABLE -->
            <div v-if="filteredRekap.length > 0" class="overflow-x-auto rounded-xl shadow-sm hidden md:block">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="p-3 text-left">Nama Lengkap</th>
                            <th class="p-3 text-left">Unit Kelas</th>
                            <th class="p-3 text-left">Mata Pelajaran</th>
                            <th class="p-3 text-center">Total Soal</th>
                            <th class="p-3 text-center">Total Nilai</th>
                            <th class="p-3 text-center">Status Ujian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in paginatedRekap" :key="`${item.user_id}-${item.soal_id}`"
                            class="border-t hover:bg-gray-50">
                            <td class="p-3">{{ item.user?.siswa?.nama_lengkap ?? "-" }}</td>
                            <td class="p-3">{{ item.user?.siswa?.kelas?.kelas ?? "-" }}</td>
                            <td class="p-3">{{ item.soal?.mapel?.mapel ?? "-" }}</td>
                            <td class="p-3 text-center">{{ item.total_soal }}</td>
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

                <!-- Pagination Desktop -->
                <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
                    <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded border"
                        :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        Prev
                    </button>
                    <button v-for="p in visiblePages" :key="p" @click="currentPage = p" class="px-3 py-1 rounded border"
                        :class="p === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        {{ p }}
                    </button>
                    <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border"
                        :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </MenuLayout>
</template>

<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { PlayIcon, CheckBadgeIcon, UserIcon } from '@heroicons/vue/24/solid'
import { ClipboardDocumentCheckIcon, ArrowPathIcon, BookOpenIcon, AcademicCapIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { ref, computed, onMounted, watch } from "vue";
import { ToastAlert } from '@/Composables/ToastAlert.js';

const { success, error, confirm } = ToastAlert();

const loading = ref(false);
const rekap = ref([]);

const filter = ref({
    soal_title: "",
    mapel_id: "",
    kelas_id: ""
});

const listSoal = ref([]);
const listMapel = ref([]);
const listKelas = ref([]);

// hanya soal unik berdasarkan title untuk dropdown
const uniqueSoal = computed(() => {
    const map = new Map()
    listSoal.value.forEach(s => {
        if (!map.has(s.title)) map.set(s.title, s)
    })
    return Array.from(map.values())
})

onMounted(async () => {
    listSoal.value = await (await fetch("/api/list-soal")).json();
    listMapel.value = await (await fetch("/api/list-mapel")).json();
    listKelas.value = await (await fetch("/api/list-kelas")).json();
});

const loaded = ref(false);
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const generate = async () => {
    // Cek apakah semua filter kosong
    if (!filter.value.soal_title && !filter.value.mapel_id && !filter.value.kelas_id) {
        error("Silakan isi minimal satu filter terlebih dahulu!"); // <-- toast alert
        return; // hentikan fungsi generate
    }

    loading.value = true;
    try {
        const res = await fetch("/api/rekap-filtered", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            credentials: "include",
            body: JSON.stringify(filter.value)
        });
        rekap.value = await res.json();
        loaded.value = true;
        success("Rekap berhasil di-generate!"); // optional, toast sukses
    } catch (err) {
        console.error("Gagal generate rekap:", err);
        error("Terjadi kesalahan saat generate rekap."); // toast error
    } finally {
        loading.value = false;
    }
};

// --- Pagination Frontend ---
const currentPage = ref(1);
const perPage = 5;
const MAX_VISIBLE_PAGES = 7;

const filteredRekap = computed(() => {
    return rekap.value.filter(r => {
        const bySoal = filter.value.soal_title ? r.soal?.title === filter.value.soal_title : true;
        const byMapel = filter.value.mapel_id ? r.soal?.mapel_id == filter.value.mapel_id : true;
        const byKelas = filter.value.kelas_id ? r.user?.siswa?.kelas_id == filter.value.kelas_id : true;
        return r.status === 'Selesai' && bySoal && byMapel && byKelas;
    });
});

const paginatedRekap = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredRekap.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(filteredRekap.value.length / perPage));

const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= MAX_VISIBLE_PAGES) return Array.from({ length: total }, (_, i) => i + 1);
    const half = Math.floor(MAX_VISIBLE_PAGES / 2);
    let start = current - half;
    let end = current + half - 1;
    if (start < 1) { start = 1; end = MAX_VISIBLE_PAGES; }
    if (end > total) { end = total; start = total - MAX_VISIBLE_PAGES + 1; }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const prevPage = () => { currentPage.value = Math.max(currentPage.value - 1, 1); }
const nextPage = () => { currentPage.value = Math.min(currentPage.value + 1, totalPages.value); }

watch(filter, () => { currentPage.value = 1; }, { deep: true });

</script>
