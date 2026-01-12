<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import {
    AcademicCapIcon, BookOpenIcon, KeyIcon,
    CheckCircleIcon, ClipboardDocumentIcon, BuildingLibraryIcon, MagnifyingGlassIcon,
    TrashIcon, ArrowPathIcon
} from "@heroicons/vue/24/outline";
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { ToastAlert } from '@/Composables/ToastAlert.js';

const { success, error, confirm } = ToastAlert();

// Props
const props = defineProps({
    peserta: { type: Array, default: () => [] }
});

// STATE
const pesertaList = ref([...props.peserta]);
const filterKelas = ref('');
const filterMapel = ref('');
const searchNama = ref('');

// DROPDOWN OPTIONS (reactive)
const kelasOptions = computed(() => {
    const set = new Set();
    pesertaList.value.forEach(p => p.user?.siswa?.kelas?.kelas && set.add(p.user.siswa.kelas.kelas));
    return Array.from(set);
});

const mapelOptions = computed(() => {
    const set = new Set();
    pesertaList.value.forEach(p => p.soal?.mapel?.mapel && set.add(p.soal.mapel.mapel));
    return Array.from(set);
});

// FILTER & PAGINATION
const currentPage = ref(1);
const perPage = 10;
const MAX_VISIBLE_PAGES = 7;

const filteredPeserta = computed(() => {
    let data = [...pesertaList.value];
    if (filterKelas.value) data = data.filter(p => p.user?.siswa?.kelas?.kelas === filterKelas.value);
    if (filterMapel.value) data = data.filter(p => p.soal?.mapel?.mapel === filterMapel.value);
    if (searchNama.value) data = data.filter(p =>
        p.user?.siswa?.nama_lengkap?.toLowerCase().includes(searchNama.value.toLowerCase())
    );
    return data;
});

watch(filteredPeserta, () => currentPage.value = 1);

const paginatedPeserta = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredPeserta.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(filteredPeserta.value.length / perPage));

const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= MAX_VISIBLE_PAGES) return Array.from({ length: total }, (_, i) => i + 1);
    const half = Math.floor(MAX_VISIBLE_PAGES / 2);
    let start = current - half;
    let end = current + half - 1;
    if (start < 1) { start = 1; end = MAX_VISIBLE_PAGES; }
    if (end > total) { end = total - MAX_VISIBLE_PAGES + 1; }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const prevPage = () => currentPage.value = Math.max(currentPage.value - 1, 1);
const nextPage = () => currentPage.value = Math.min(currentPage.value + 1, totalPages.value);

// FILTER FUNCTION
const applyFilter = () => currentPage.value = 1;

const refreshFilter = () => {
    filterKelas.value = '';
    filterMapel.value = '';
    searchNama.value = '';
    applyFilter();
    // success("Filter dikembalikan!");
}

// COPY & REFRESH TOKEN
const copyToken = (token) => {
    navigator.clipboard.writeText(token);
    success("Token berhasil disalin!");
}

const refreshToken = async (id) => {
    await axios.get(`/proktor/ruang-ujian/peserta/${id}/refresh-token`);
}

// DELETE
const deletePeserta = async (id) => {
    const result = await confirm({ text: 'Hapus peserta ini?' });
    if (!result.isConfirmed) return;

    await axios.delete(`/proktor/ruang-ujian/peserta/${id}`);
    success("Peserta dihapus!");
}

const deleteAllPeserta = async () => {
    const result = await confirm({ text: 'Hapus semua peserta?' });
    if (!result.isConfirmed) return;

    try {
        await axios.delete(`/proktor/ruang-ujian/peserta/destroy-all`);
        success("Semua peserta dihapus!");
    } catch {
        error("Gagal hapus semua peserta");
    }
}

onMounted(() => {
    Echo.channel('ruang-ujian')
        .listen('.PesertaUpdated', (e) => {

            // 🔴 HANDLE DELETE
            if (e.peserta.deleted) {
                pesertaList.value = pesertaList.value.filter(
                    p => p.id !== e.peserta.id
                )
                return
            }

            // 🔵 HANDLE UPDATE / ADD
            const index = pesertaList.value.findIndex(
                p => p.id === e.peserta.id
            )

            if (index !== -1) {
                pesertaList.value[index] = e.peserta
            } else {
                pesertaList.value.unshift(e.peserta)
            }
        })
})

onBeforeUnmount(() => {
    Echo.leave('ruang-ujian');
});
</script>

<template>
    <MenuLayout>
        <div class="mx-auto max-w-6xl w-full">

            <!-- HEADER -->
            <div class="flex items-center gap-2 mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Kelola Ruang Ujian</h1>
            </div>

            <!-- FILTER -->
            <div class="flex flex-col md:flex-row gap-4 mb-0 sm:mb-12">
                <div class="relative flex-1">
                    <BuildingLibraryIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <select v-model="filterKelas" @change="applyFilter" class="border p-2 pl-10 rounded-lg w-full">
                        <option value="">-- Pilih Kelas --</option>
                        <option v-for="kelas in kelasOptions" :key="kelas" :value="kelas">{{ kelas }}</option>
                    </select>
                </div>
                <div class="relative flex-1">
                    <BookOpenIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <select v-model="filterMapel" @change="applyFilter" class="border p-2 pl-10 rounded-lg w-full">
                        <option value="">-- Pilih Mapel --</option>
                        <option v-for="mapel in mapelOptions" :key="mapel" :value="mapel">{{ mapel }}</option>
                    </select>
                </div>
                <div class="relative flex-1">
                    <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input type="text" v-model="searchNama" placeholder="Cari nama peserta" @input="applyFilter"
                        class="border p-2 pl-10 rounded-lg w-full" />
                </div>
                <div class="flex items-center">
                    <button @click="refreshFilter"
                        class="sm:flex hidden items-center justify-center gap-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <ArrowPathIcon class="w-5 h-5" /> Refresh
                    </button>
                </div>
            </div>

            <div class="flex flex-row w-full justify-end gap-3 mb-10 sm:mb-4">
                <button @click="refreshFilter"
                    class="flex-1 sm:hidden md:flex-none flex items-center justify-center gap-1 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    <ArrowPathIcon class="w-5 h-5" /> Refresh
                </button>

                <button @click="deleteAllPeserta"
                    class="flex-1 sm:flex-none sm:flex hidden items-center justify-center gap-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                    <TrashIcon class="w-5 h-5" /> Delete All
                </button>
            </div>

            <!-- DESKTOP TABLE -->
            <div v-if="filteredPeserta.length" class="hidden md:block overflow-x-auto shadow-lg rounded-lg">
                <table class="w-full text-center border-collapse">
                    <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white uppercase text-sm">
                        <tr>
                            <th class="p-3 border">No</th>
                            <th class="p-3 border text-left">Nama Lengkap</th>
                            <th class="p-3 border">Unit Kelas</th>
                            <th class="p-3 border">Kode Token</th>
                            <th class="p-3 border">Status Ujian</th>
                            <th class="p-3 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(p, i) in paginatedPeserta" :key="p.id"
                            class="border-b hover:bg-gray-50 transition-colors duration-200"
                            :class="i % 2 === 0 ? 'bg-gray-50' : 'bg-white'">
                            <td class="p-3">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                            <td class="p-3 text-left font-medium">{{ p.user?.siswa?.nama_lengkap ?? '-' }}</td>
                            <td class="p-3">{{ p.user?.siswa?.kelas?.kelas ?? '-' }}</td>
                            <td class="p-3 flex items-center justify-center gap-2 rounded font-mono">
                                {{ p.token }}
                                <ClipboardDocumentIcon @click="copyToken(p.token)"
                                    class="w-5 h-5 cursor-pointer text-gray-500 hover:text-blue-600 transition" />
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 font-mono rounded-full" :class="{
                                    'bg-green-100 text-green-800': p.status === 'Selesai',
                                    'bg-yellow-100 text-yellow-800': p.status === 'Sedang Mengerjakan',
                                    'bg-gray-100 text-gray-800': !p.status || p.status === 'Belum Mulai'
                                }">
                                    {{ p.status ?? 'Belum Mulai' }}
                                </span>
                            </td>
                            <td class="p-3 flex gap-2 justify-center">
                                <button @click="refreshToken(p.id)"
                                    class="flex items-center gap-1 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                                    <ArrowPathIcon class="w-4 h-4" /> Refresh
                                </button>
                                <button @click="deletePeserta(p.id)"
                                    class="flex items-center gap-1 bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE CARD -->
            <div v-if="filteredPeserta.length" class="md:hidden space-y-4">
                <div v-for="(p, i) in paginatedPeserta" :key="p.id"
                    class="bg-white shadow-md rounded-xl p-4 border-l-4 border-blue-500">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-gray-800 text-lg">{{ p.user?.siswa?.nama_lengkap ?? '-' }}</div>
                        <div class="text-sm text-gray-500 font-mono">{{ (currentPage - 1) * perPage + i + 1 }}</div>
                    </div>
                    <div class="mt-2 flex flex-col gap-2 text-gray-600 text-sm">
                        <div class="flex items-center gap-2">
                            <AcademicCapIcon class="w-4 h-4 text-blue-500" /> Kelas: {{ p.user?.siswa?.kelas?.kelas ??
                                '-' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <KeyIcon class="w-4 h-4 text-yellow-500" /> Token:
                            <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ p.token }}</span>
                            <ClipboardDocumentIcon @click="copyToken(p.token)"
                                class="w-4 h-4 cursor-pointer text-gray-500 hover:text-blue-600 transition" />
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-4 h-4 text-green-500" /> Status:
                            <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="{
                                'bg-green-100 text-green-800': p.status === 'Selesai',
                                'bg-yellow-100 text-yellow-800': p.status === 'Sedang Mengerjakan',
                                'bg-gray-100 text-gray-800': !p.status || p.status === 'Belum Mulai'
                            }">
                                {{ p.status ?? 'Belum Mulai' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button @click="refreshToken(p.id)"
                            class="flex-1 flex items-center justify-center gap-1 bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">
                            <ArrowPathIcon class="w-4 h-4" /> Refresh
                        </button>
                        <button @click="deletePeserta(p.id)"
                            class="flex-1 flex items-center justify-center gap-1 bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition">
                            <TrashIcon class="w-4 h-4" /> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- NO DATA -->
            <div v-if="!filteredPeserta.length" class="text-center py-16 text-gray-500">🚫 Tidak ada data peserta.</div>

            <!-- PAGINATION -->
            <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4 flex-wrap">
                <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded border"
                    :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Prev</button>
                <button v-for="p in visiblePages" :key="p" @click="currentPage = p" class="px-3 py-1 rounded border"
                    :class="p === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">{{
                        p }}</button>
                <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border"
                    :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">Next</button>
            </div>

        </div>
    </MenuLayout>
</template>
