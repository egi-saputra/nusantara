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
                    <h1 class="text-2xl sm:inline-block hidden font-extrabold text-gray-800 dark:text-white">Quiz / Exam
                        Question List
                    </h1>

                    <Link href="/guru/soal/create"
                        class="flex px-5 py-2 mb-4 sm:mb-0 bg-blue-600 dark:bg-[#1e1b4b] text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">
                        + Create New Quiz
                    </Link>
                </div>
            </div>

            <div v-if="!soal.data || soal.data.length === 0"
                class="text-center py-20 bg-white dark:border-gray-600 dark:bg-[#0F172A] rounded-xl shadow border border-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-3-3v6m-6 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-300 mb-4">No quizzes or questions available.</p>
                <Link href="/guru/soal/create"
                    class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
                    Create Quiz Now!
                </Link>
            </div>

            <div v-else class="sm:overflow-x-auto p-6 pt-8 pb-24 rounded-2xl
           backdrop-blur-xl
           bg-white/70 dark:bg-slate-900/70
           border border-white/40 dark:border-white/10
           shadow-xl shadow-black/10 dark:shadow-black/40">
                <table class="w-full text-left border-collapse whitespace-nowrap">

                    <!-- TABLE HEAD -->
                    <thead class="bg-[#063970]/90 dark:bg-[#1e1b4b]
                   backdrop-blur-md
                   text-white text-center">
                        <tr>
                            <th class="p-3 border-b border-white/20 font-medium">No</th>
                            <th class="p-3 border-b border-white/20 font-medium">Subject</th>
                            <th class="p-3 border-b border-white/20 font-medium">Class Unit</th>
                            <th class="p-3 border-b border-white/20 font-medium">Quiz Token</th>
                            <th class="p-3 border-b border-white/20 font-medium">Quiz Status</th>
                            <th class="p-3 border-b border-white/20 font-medium">Question Format</th>
                            <th class="p-3 border-b border-white/20 font-medium">Exam Duration</th>
                            <th class="p-3 border-b border-white/20 font-medium"></th>
                        </tr>
                    </thead>

                    <!-- TABLE BODY -->
                    <tbody class="text-gray-700 dark:text-gray-200">
                        <tr v-for="(item, index) in soal.data" :key="item.id" class="transition
                       hover:bg-white/50 dark:hover:bg-white/5">
                            <td class="p-3 text-center border-b border-white/20">
                                {{ index + 1 }}
                            </td>

                            <td class="p-3 border-b border-white/20">
                                {{ item.mapel?.mapel ?? '-' }}
                            </td>

                            <td class="p-3 text-center border-b border-white/20">
                                {{ item.kelas }}
                            </td>

                            <td class="p-3 text-center border-b border-white/20 font-semibold text-indigo-600">
                                {{ item.token }}
                            </td>

                            <td class="p-3 text-center border-b border-white/20 font-medium" :class="item.status === 'Aktif'
                                ? 'text-green-600'
                                : 'text-gray-700 dark:text-gray-300'">
                                {{ item.status === 'Aktif'
                                    ? 'Active'
                                    : item.status === 'Tidak Aktif'
                                        ? 'Inactive'
                                        : item.status }}
                            </td>

                            <td class="p-3 text-center border-b border-white/20">
                                {{ item.tipe_soal === 'Berurutan'
                                    ? 'Sequential'
                                    : item.tipe_soal === 'Acak'
                                        ? 'Shuffle'
                                        : item.tipe_soal }}
                            </td>

                            <td class="p-3 text-center border-b border-white/20">
                                {{ item.waktu }} minutes
                            </td>

                            <!-- ACTION -->
                            <td class="p-3 border-b border-white/20 relative">
                                <button @click="toggleDropdown(item.id, $event)" class="p-2 rounded-full
                               hover:bg-white/60 dark:hover:bg-white/10
                               transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M12 6v.01M12 12v.01M12 18v.01" />
                                    </svg>
                                </button>

                                <!-- DROPDOWN ACTION -->
                                <div v-if="openDropdown === item.id" class="absolute right-12 md:right-16 -mt-8 z-10 w-36 rounded-xl
                               backdrop-blur-lg
                               bg-white/80 dark:bg-slate-900/80
                               border border-white/40 dark:border-white/10
                               shadow-xl">
                                    <Link :href="`/guru/soal/${item.id}`" class="flex items-center px-4 py-2
                                   hover:bg-white/60 dark:hover:bg-white/10
                                   rounded-t-xl font-medium">
                                        <EyeIcon class="w-4 h-4 mr-2" /> Preview
                                    </Link>

                                    <button @click="confirmDeleteItem(item.id)" class="flex items-center px-4 py-2 w-full
                                   text-red-600 font-medium
                                   hover:bg-red-100/70 dark:hover:bg-red-900/20
                                   rounded-b-xl">
                                        <TrashIcon class="w-4 h-4 mr-2" /> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center dark:text-gray-500 text-gray-700"
                v-if="soal.data.length">
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
        <div class="sm:hidden block min-h-screen px-2 pb-28
           bg-transparent
           dark:from-[#020617] dark:to-[#020617]">

            <!-- EMPTY STATE -->
            <div v-if="!soal.data || soal.data.length === 0" class="mt-4 text-center p-10
               rounded-2xl
               bg-white/70 dark:bg-[#041C32]
               backdrop-blur-xl
               border border-white/20 dark:border-white/10
               shadow-xl">

                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-4 h-14 w-14 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-3-3v6m-6 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>

                <p class="text-gray-600 dark:bg-[#041C32] dark:text-gray-300 mb-5 text-sm">
                    No quizzes or questions available.
                </p>
            </div>

            <!-- LIST -->
            <div v-else class="grid grid-cols-1 gap-6">

                <div v-for="item in soal.data" :key="item.id" class="relative p-5 rounded-2xl
                   bg-white/70 dark:bg-white/5
                   backdrop-blur-xl
                   border border-white/20 dark:border-white/10
                   shadow-[0_10px_30px_rgba(0,0,0,0.15)]
                   transition hover:scale-[1.01]">

                    <!-- HEADER -->
                    <div class="flex items-center gap-2 mb-5">
                        <ClipboardDocumentCheckIcon class="w-5 h-5 text-blue-500 dark:text-cyan-400" />
                        <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100 truncate">
                            {{ item.mapel?.mapel ?? '-' }}
                        </h2>
                    </div>

                    <!-- INFO -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between px-4 py-3
                           rounded-xl bg-white/60 dark:bg-white/10
                           backdrop-blur border border-white/20">
                            <span class="text-xs dark:text-gray-400 text-gray-500">Class</span>
                            <span class="font-semibold dark:text-gray-300 text-sm">
                                {{ item.kelas }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between px-4 py-3
                           rounded-xl bg-white/60 dark:bg-white/10
                           backdrop-blur border border-white/20">
                            <span class="text-xs dark:text-gray-400 text-gray-500">Token</span>
                            <span class="font-mono dark:text-gray-300 text-sm">
                                {{ item.token }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between px-4 py-3
                           rounded-xl bg-white/60 dark:bg-white/10
                           backdrop-blur border border-white/20">
                            <span class="text-xs dark:text-gray-400 text-gray-500">Duration</span>
                            <span class="font-medium dark:text-gray-300 text-sm">
                                {{ item.waktu }} min
                            </span>
                        </div>
                    </div>

                    <!-- ACTIONS -->
                    <div class="grid grid-cols-2 gap-3">
                        <Link :href="`/guru/soal/${item.id}`" class="flex items-center justify-center gap-2
                           py-3 rounded-xl
                           bg-gradient-to-r from-blue-600 to-indigo-600
                           text-white font-semibold shadow-lg
                           active:scale-95 transition">
                            <EyeIcon class="w-5 h-5" />
                            Detail
                        </Link>

                        <button @click="confirmDeleteItem(item.id)" class="flex items-center justify-center gap-2
                           py-3 rounded-xl
                           bg-red-500/90 text-white font-semibold
                           shadow-lg active:scale-95 transition">
                            <TrashIcon class="w-5 h-5" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- PAGINATION -->
            <div v-if="soal.data.length"
                class="mt-8 flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                <Link v-if="soal.prev_page_url" :href="soal.prev_page_url" class="px-4 py-2 rounded-xl bg-white/60 dark:bg-white/10
                   backdrop-blur border border-white/20 shadow">
                    Previous
                </Link>

                <span class="font-medium">
                    Page {{ soal.current_page }} / {{ soal.last_page }}
                </span>

                <Link v-if="soal.next_page_url" :href="soal.next_page_url" class="px-4 py-2 rounded-xl bg-white/60 dark:bg-white/10
                   backdrop-blur border border-white/20 shadow">
                    Next
                </Link>
            </div>

            <!-- FLOATING CREATE BUTTON -->
            <Link href="/guru/soal/create" class="fixed bottom-6 right-5 z-50
               flex items-center gap-2
               px-6 py-3 rounded-full
               bg-gradient-to-r from-blue-600 to-indigo-600
               text-white font-semibold shadow-2xl
               active:scale-95 transition">
                + New Quiz
            </Link>

        </div>
    </MenuLayout>
</template>
