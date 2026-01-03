<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/solid';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';
import { ToastAlert } from '@/Composables/ToastAlert.js';

const { success, error, confirm } = ToastAlert();

// Data peserta & kelas dari server
const page = usePage();
const pesertaData = ref([...page.props.pesertaAll]);
const kelasAll = ref([...page.props.kelasList]);

// Filter
const filterNama = ref('');
const filterKelas = ref('');

// Editing
const editing = ref(false);
const editForm = ref({
    id: null,
    nama_lengkap: '',
    kelas_id: '',
    status: '',
    email: '',
    password: '',
});

// Select kelas untuk edit (tanpa tenant filter)
const kelasForEdit = computed(() => {
    return kelasAll.value; // tampilkan semua kelas
});

// Refresh data
const refreshData = () => {
    filterNama.value = '';
    filterKelas.value = '';
    Inertia.get(route('proktor.peserta.index'), {}, {
        preserveScroll: true,
        preserveState: false
    });
};

// --- Pagination Frontend ---
const currentPage = ref(1);
const perPage = 10;
const MAX_VISIBLE_PAGES = 7;

const filteredPeserta = computed(() => {
    return pesertaData.value.filter(p => {
        const byNama = p.nama.toLowerCase().includes(filterNama.value.toLowerCase());
        const byKelas = filterKelas.value ? p.kelas_id == filterKelas.value : true;
        return byNama && byKelas;
    });
});

const paginatedPeserta = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredPeserta.value.slice(start, start + perPage).map(p => ({
        ...p,
        displayStatus: p.status === 'Activated' ? 'Active' : 'Inactive',
    }));
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
    if (end > total) { end = total; start = total - MAX_VISIBLE_PAGES + 1; }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const prevPage = () => { currentPage.value = Math.max(currentPage.value - 1, 1); }
const nextPage = () => { currentPage.value = Math.min(currentPage.value + 1, totalPages.value); }

// Reset page jika filter berubah
watch([filterNama, filterKelas], () => { currentPage.value = 1; });

// --- Edit ---
const openEdit = (p) => {
    editing.value = true;
    editForm.value = {
        id: p.id,
        nama_lengkap: p.nama,
        kelas_id: p.kelas_id,
        status: p.status,
        email: p.email,
        password: '',
    };
};

const closeEdit = () => { editing.value = false; }

const updatePeserta = () => {
    axios.put(`/proktor/peserta/${editForm.value.id}`, editForm.value)
        .then(res => {
            success(res.data.success || 'Peserta berhasil diupdate');
            Inertia.reload({ only: ['peserta', 'pesertaAll'], preserveScroll: true });
            closeEdit();
        })
        .catch(err => { error(err.response?.data?.error || 'Gagal update peserta'); });
};

// --- Delete ---
const deletePeserta = (id) => {
    confirm({ text: 'Yakin ingin menghapus peserta ini?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/proktor/peserta/${id}`)
                    .then(res => {
                        success(res.data.success || 'Peserta berhasil dihapus');
                        Inertia.reload({ only: ['peserta', 'pesertaAll'], preserveScroll: true });
                    })
                    .catch(err => { error(err.response?.data?.error || 'Gagal hapus peserta'); });
            }
        });
};

const deleteAllPeserta = () => {
    confirm({ text: 'Yakin ingin menghapus semua peserta?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete('/proktor/peserta/destroy-all')
                    .then(res => {
                        success(res.data.success || 'Semua peserta berhasil dihapus');
                        Inertia.reload({ only: ['peserta', 'pesertaAll'], preserveScroll: true });
                    })
                    .catch(err => { error(err.response?.data?.message || 'Gagal hapus semua peserta'); });
            }
        });
};

// --- Import Excel ---
const submitExcel = async (fileInput) => {
    if (!fileInput.value?.files[0]) {
        return Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Pilih file Excel terlebih dahulu!',
            toast: true,
            position: 'top-end',
            timer: 2000,
            showConfirmButton: false,
        });
    }
    const formData = new FormData();
    formData.append('excel', fileInput.value.files[0]);
    try {
        await axios.post(route('proktor.peserta.import'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        window.location.href = route('proktor.peserta.index');
    } catch (err) {
        console.error(err);
    }
};

// --- Toast flash ---
onMounted(() => {
    const flashSuccess = page.props.flash?.success;
    if (flashSuccess) success(flashSuccess);
});
</script>

<template>
    <MenuLayout>
        <div>
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center sm:mb-10 mb-4 gap-3">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 w-full sm:w-auto">
                    Daftar Peserta Didik
                </h1>
                <Link href="/proktor/peserta/register"
                    class="px-5 py-2 bg-blue-600 text-white font-medium rounded shadow hover:bg-blue-700 w-full sm:w-auto text-center">
                    + Tambah Peserta
                </Link>
            </div>

            <div class="sm:bg-white sm:rounded sm:p-6 sm:shadow">
                <!-- Filter -->
                <div class="flex flex-col md:flex-row md:justify-between gap-3 mb-6 items-start md:items-center">
                    <!-- Filter Inputs -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:mb-0 mb-6 w-full md:w-auto">
                        <input type="text" v-model="filterNama" placeholder="Cari nama..."
                            class="border px-3 py-2 rounded w-full sm:w-auto flex-1" />
                        <select v-model="filterKelas" class="border px-4 py-2 pr-10 rounded bg-white w-full sm:w-auto">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in kelasAll" :key="k.id" :value="k.id">{{ k.kelas }}</option>
                        </select>
                        <button @click="refreshData"
                            class="flex gap-2 bg-gray-700 text-white px-4 py-2 rounded w-full sm:w-auto">
                            <ArrowPathIcon class="w-5 h-5" /> Refresh
                        </button>
                    </div>

                    <!-- Delete All Button -->
                    <button @click="deleteAllPeserta"
                        class="bg-red-700 text-white px-4 py-2 rounded hidden sm:flex items-center justify-center gap-1 w-full sm:w-auto">
                        <TrashIcon class="w-5 h-5" /> Hapus Semua
                    </button>
                </div>

                <!-- Table Desktop -->
                <table class="w-full border text-center hidden md:table">
                    <thead class="bg-[#063970] text-white">
                        <tr>
                            <th class="p-2 border">No</th>
                            <th class="p-2 border">Full Name</th>
                            <th class="p-2 border">Email Address</th>
                            <th class="p-2 border">Class Name</th>
                            <!-- <th class="p-2 border">ID PD</th> -->
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <tr v-for="(p, i) in paginatedPeserta" :key="p.id">
                            <td class="p-2 border">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                            <td class="p-2 text-left border">{{ p.nama }}</td>
                            <td class="p-2 text-left border">{{ p.email }}</td>
                            <td class="p-2 border">{{ p.kelas }}</td>
                            <!-- <td class="p-2 border font-extrabold font-mono">{{ p.id }}</td> -->
                            <td class="p-2 border">
                                <span
                                    :class="p.status === 'Activated' ? 'text-green-600 font-semibold' : 'text-gray-500'">
                                    {{ p.displayStatus }}
                                </span>
                            </td>
                            <td class="p-2 border">
                                <div class="flex justify-center gap-3">
                                    <button @click="openEdit(p)" class="p-2 bg-blue-600 text-white rounded">
                                        <PencilSquareIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="deletePeserta(p.id)" class="p-2 bg-red-600 text-white rounded">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedPeserta.length === 0">
                            <td colspan="7" class="p-3 text-gray-500">Tidak ada data peserta.</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Card Mobile -->
                <div class="md:hidden flex flex-col gap-4">
                    <div v-for="(p, i) in paginatedPeserta" :key="p.id"
                        class="bg-white shadow-md rounded-lg p-4 border-l-4 border-blue-500">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-gray-800">{{ p.nama }}</div>
                            <div class="text-sm text-gray-500 font-mono">{{ (currentPage - 1) * perPage + i + 1 }}</div>
                        </div>
                        <div class="mt-2 flex flex-col gap-2 text-gray-600 text-sm">
                            <div>Email: <span class="font-mono">{{ p.email }}</span></div>
                            <!-- <div>ID PD: <span class="font-mono font-bold">{{ p.id }}</span></div> -->
                            <div class="flex justify-between w-full gap 4">
                                <div
                                    class="bg-ambers-50 w-1/2 p-2 px-4 items-center text-center rounded-lg text-amber-600">
                                    Kelas:
                                    <span class="font-semibold text-amber-600">{{ p.kelas }}</span>
                                </div>
                                <div class="w-1/2 p-2 px-4 items-center text-center rounded-lg"
                                    :class="p.status === 'Activated' ? 'text-green-600 font-semibold bg-green-50' : 'text-gray-500 bg-gray-50'">
                                    Status:
                                    <span>
                                        {{ p.displayStatus }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button @click="openEdit(p)"
                                class="flex-1 bg-blue-600 text-white px-2 py-1 rounded flex justify-center items-center gap-1">
                                <PencilSquareIcon class="w-4 h-4" /> Edit
                            </button>
                            <button @click="deletePeserta(p.id)"
                                class="flex-1 bg-red-600 text-white px-2 py-1 rounded flex justify-center items-center gap-1">
                                <TrashIcon class="w-4 h-4" /> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit -->
                <div v-if="editing" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                    <div class="bg-white p-6 w-96 rounded shadow-lg">
                        <h2 class="text-xl font-bold mb-4">Edit Peserta</h2>
                        <div class="mb-3">
                            <label class="block font-semibold">Nama Lengkap</label>
                            <input v-model="editForm.nama_lengkap" class="border px-3 py-2 w-full rounded" />
                        </div>
                        <div class="mb-3">
                            <label class="block font-semibold">Email</label>
                            <input v-model="editForm.email" type="email" class="border px-3 py-2 w-full rounded" />
                        </div>
                        <div class="mb-3">
                            <label class="block font-semibold">Kelas</label>
                            <select v-model="editForm.kelas_id" class="border px-3 py-2 w-full rounded">
                                <option v-for="k in kelasForEdit" :key="k.id" :value="k.id">{{ k.kelas }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block font-semibold">Password</label>
                            <input v-model="editForm.password" type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah"
                                class="border px-3 py-2 w-full rounded" />
                        </div>
                        <div class="mb-3">
                            <label class="block font-semibold">Status</label>
                            <select v-model="editForm.status" class="border px-3 py-2 w-full rounded">
                                <option value="Activated">Activated</option>
                                <option value="Deactivated">Deactivated</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="closeEdit" class="px-3 py-2 bg-gray-300 rounded">Batal</button>
                            <button @click="updatePeserta"
                                class="px-3 py-2 bg-blue-600 text-white rounded">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex justify-center gap-2 mt-4">
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
