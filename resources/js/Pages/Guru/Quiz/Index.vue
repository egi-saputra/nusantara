<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Link, usePage, Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { EyeIcon, TrashIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

// Props dari backend
const props = defineProps({
    soal: Object
});

const page = usePage();
const { success, error, confirm } = ToastAlert();

// Ambil success flash
const flash = computed(() => page.props.flash?.success);

// Dropdown state
const openDropdown = ref(null);

function toggleDropdown(id, event) {
    event.stopPropagation();
    openDropdown.value = openDropdown.value === id ? null : id;
}

function handleClickOutside() {
    openDropdown.value = null;
}

// SweetAlert flash
watch(flash, (val) => {
    if (val) {
        success(val);
    }
});

onMounted(() => {
    window.addEventListener('click', handleClickOutside);

    if (flash.value) {
        success(flash.value);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutside);
});

// Konfirmasi hapus satu quiz
function confirmDeleteItem(id) {
    confirm({ text: 'Yakin hapus quiz ini?' })
        .then(result => {
            if (result.isConfirmed) {
                axios.delete(`/guru/soal/${id}`)
                    .then(res => {
                        success(res.data.success || 'Quiz berhasil dihapus');
                        Inertia.reload();
                    })
                    .catch(err => error(err.response?.data?.message || 'Gagal hapus quiz'));
            }
        });
}
</script>

<template>

    <Head title="Quiz List" />

    <MenuLayout>
        <!-- DESKTOP -->
        <div class="sm:p-4 sm:block hidden p-0">
            <div class="flex justify-between items-center mb-4" v-if="soal.data && soal.data.length">
                <div class="flex w-full justify-between space-x-2">
                    <h1 class="text-2xl sm:inline-block hidden font-extrabold text-gray-800">Quiz / Exam Question List
                    </h1>

                    <Link href="/guru/soal/create"
                        class="flex px-5 py-2 mb-4 sm:mb-0 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">
                        + Create New Quiz
                    </Link>
                </div>
            </div>

            <div v-if="!soal.data || soal.data.length === 0"
                class="text-center py-20 bg-white rounded-xl shadow border border-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-3-3v6m-6 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 mb-4">No quizzes or questions available.</p>
                <Link href="/guru/soal/create"
                    class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
                    Create Quiz Now!
                </Link>
            </div>

            <div v-else class="sm:overflow-x-auto p-6 pt-8 rounded bg-white md:rounded shadow pb-24">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-[#063970] text-white text-center">
                        <tr>
                            <th class="p-3 border-b font-medium">No</th>
                            <th class="p-3 border-b font-medium">Subject</th>
                            <th class="p-3 border-b font-medium">Class Unit</th>
                            <th class="p-3 border-b font-medium">Quiz Token</th>
                            <th class="p-3 border-b font-medium">Quiz Status</th>
                            <th class="p-3 border-b font-medium">Question Format</th>
                            <th class="p-3 border-b font-medium">Exam Duration</th>
                            <th class="p-3 border-b font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(item, index) in soal.data" :key="item.id" class="hover:bg-gray-50 transition">
                            <td class="p-3 text-center border-b">{{ index + 1 }}</td>
                            <td class="p-3 border-b">{{ item.mapel?.mapel ?? '-' }}</td>
                            <td class="p-3 text-center border-b">{{ item.kelas }}</td>
                            <td class="p-3 border-b text-center font-semibold text-indigo-600">{{ item.token }}</td>
                            <td class="p-3 text-center border-b"
                                :class="item.status === 'Aktif' ? 'text-green-600 font-semibold' : 'text-gray-400 font-medium'">
                                {{ item.status === 'Aktif' ? 'Active' : item.status === 'Tidak Aktif' ? 'Inactive' :
                                    item.status }}
                            </td>
                            <td class="p-3 text-center border-b">{{ item.tipe_soal === 'Berurutan' ? 'Sequential' :
                                item.tipe_soal === 'Acak' ?
                                    'Shuffle'
                                    : item.tipe_soal }}</td>
                            <td class="p-3 text-center border-b">{{ item.waktu }} minutes</td>
                            <td class="p-3 border-b relative">
                                <button @click="toggleDropdown(item.id, $event)"
                                    class="p-2 rounded-full hover:bg-gray-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 6v.01M12 12v.01M12 18v.01" />
                                    </svg>
                                </button>

                                <div v-if="openDropdown === item.id"
                                    class="absolute right-12 md:right-16 -mt-8 w-36 bg-white border rounded-lg shadow-lg z-10">

                                    <Link :href="`/guru/soal/${item.id}`"
                                        class="flex items-center px-4 py-2 w-full hover:bg-gray-100 font-medium">
                                        <EyeIcon class="w-4 h-4 mr-2" /> Preview
                                    </Link>

                                    <button @click="confirmDeleteItem(item.id)"
                                        class="flex items-center px-4 py-2 hover:bg-red-100 w-full text-red-600 font-medium rounded-b-lg">
                                        <TrashIcon class="w-4 h-4 mr-2" /> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center text-gray-700" v-if="soal.data.length">
                <Link v-if="soal.prev_page_url" :href="soal.prev_page_url"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
                    Previous
                </Link>
                <span class="font-medium">Showing page {{ soal.current_page }} of {{ soal.last_page }}</span>
                <Link v-if="soal.next_page_url" :href="soal.next_page_url"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
                    Next
                </Link>
            </div>
        </div>

        <!-- MOBILE VERSION -->
        <div class="sm:hidden block p-0">
            <div class="flex mb-4" v-if="soal.data && soal.data.length">
                <div class="flex w-full flex-col">
                    <Link href="/guru/quiz/create"
                        class="flex py-2 w-full justify-center mb-3 bg-blue-600 text-white font-medium rounded shadow hover:bg-blue-700 transition">
                        + Create New Quiz
                    </Link>
                </div>
            </div>

            <div v-if="!soal.data || soal.data.length === 0"
                class="text-center py-20 bg-white rounded-xl shadow border border-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-3-3v6m-6 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 mb-4">No quizzes or questions available.</p>
                <Link href="/guru/quiz/create"
                    class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
                    Create Quiz Now!
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 gap-6">
                <div v-for="(item, index) in soal.data" :key="item.id"
                    class="bg-white rounded shadow hover:shadow-lg border border-gray-200 transition p-6 flex flex-col justify-between">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-bold text-gray-800 truncate flex items-center gap-2">
                            <ClipboardDocumentCheckIcon class="w-5 h-5 text-blue-500" />
                            {{ item.mapel?.mapel ?? '-' }}
                        </h2>
                        <!-- <span :class="item.status === 'Aktif'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-200 text-gray-500'"
                            class="text-sm font-semibold px-6 py-1 rounded sm:rounded-full uppercase">
                            {{ item.status === 'Aktif' ? 'Active' : item.status === 'Tidak Aktif' ? 'Inactive' :
                                item.status }}
                        </span> -->
                    </div>

                    <!-- Info badges -->
                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div class="flex py-3 items-center justify-center px-4 bg-blue-50 rounded shadow-inner">
                            <span class="material-icons mr-2 text-blue-500 text-sm">Class Unit :</span>
                            <span class="font-semibold text-sm">{{ item.kelas }}</span>
                        </div>
                        <div
                            class="flex py-3 items-center justify-center px-4 sm:bg-indigo-50 bg-amber-50 rounded shadow-inner">
                            <span class="material-icons mr-2 text-amber-500 sm:text-indigo-500 text-sm">Key ID :</span>
                            <span class="font-semibold text-sm">{{ item.token }}</span>
                        </div>
                        <div class="flex py-3 items-center justify-center px-4 bg-red-50 rounded shadow-inner">
                            <span class="material-icons mr-2 text-red-500 text-sm">Duration :</span>
                            <span class="font-medium text-sm">{{ item.waktu }} minutes</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <Link :href="`/guru/soal/${item.id}`"
                            class="flex items-center justify-center gap-2 px-6 py-2 bg-gray-700 text-white rounded hover:bg-gray-800 transition font-semibold">
                            <EyeIcon class="w-5 h-5" /> Detail
                        </Link>
                        <button @click="confirmDeleteItem(item.id)"
                            class="flex items-center justify-center gap-2 px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition font-semibold">
                            <TrashIcon class="w-5 h-5" /> Delete
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center text-gray-700" v-if="soal.data.length">
                <Link v-if="soal.prev_page_url" :href="soal.prev_page_url"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
                    Previous
                </Link>
                <span class="font-medium">Showing page {{ soal.current_page }} of {{ soal.last_page }}</span>
                <Link v-if="soal.next_page_url" :href="soal.next_page_url"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
                    Next
                </Link>
            </div>
        </div>
    </MenuLayout>
</template>
