<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { ToastAlert } from '@/Composables/ToastAlert.js';
import axios from 'axios';

// Heroicons
import { TrashIcon, ChevronLeftIcon, ChevronRightIcon, PencilSquareIcon, PaperAirplaneIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ kelas: Array });
const { success, error } = ToastAlert();

const form = useForm({
    judul: '',
    pengumuman: '',
    penerima: 'semua',
    kelas_id: null,
});

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const myAnnouncements = ref([]);

// Pagination
const currentPage = ref(1);
const perPage = 5;
const totalPages = computed(() => Math.ceil(myAnnouncements.value.length / perPage));
const paginatedAnnouncements = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return myAnnouncements.value.slice(start, start + perPage);
});

const isSubmitting = ref(false);

// Load pengumuman user
onMounted(async () => {
    try {
        const res = await axios.get('/pengumuman/mine');
        myAnnouncements.value = res.data;
    } catch (e) { console.log(e); }
});

async function submit() {
    isSubmitting.value = true;
    form.post('/pengumuman', {
        onSuccess: (page) => {
            myAnnouncements.value.unshift({
                ...form,
                id: page.props.flash?.id,
                created_at: new Date(),
            });
            form.reset();
        },
        onError: () => error("Gagal membuat pesan informasi."),
        onFinish: () => isSubmitting.value = false
    });
}

async function deletePengumuman(id) {
    if (!confirm("Yakin ingin menghapus pesan informasi ini?")) return;
    try {
        await axios.delete(`/pengumuman/${id}`);
        myAnnouncements.value = myAnnouncements.value.filter(a => a.id !== id);
        if (currentPage.value > totalPages.value) currentPage.value = totalPages.value;
    } catch (e) { error("Gagal menghapus pesan."); }
}

const displayPenerima = (item) => {
    if (item.penerima === 'semua') return 'Semua User';
    if (item.penerima === 'siswa' && item.kelas_id) {
        const kelas = props.kelas.find(k => k.id === item.kelas_id);
        return kelas ? `Siswa - Kelas ${kelas.kelas}` : 'Siswa';
    }
    return item.penerima.charAt(0).toUpperCase() + item.penerima.slice(1);
};

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };

async function deleteAllAnnouncements() {
    if (!confirm("Yakin anda ingin menghapus semua pesan?")) return;

    try {
        await axios.delete('/pengumuman/delete-all'); // endpoint baru di backend
        myAnnouncements.value = [];
        currentPage.value = 1;
    } catch (e) {
        error("Gagal menghapus semua pengumuman.");
    }
}
</script>

<template>
    <MenuLayout>
        <div class="max-w-5xl mx-auto space-y-14 sm:px-4 sm:py-6">

            <!-- CREATE FORM -->
            <div class="rounded-xl border border-white/20 dark:border-white/10
                       bg-white/70 dark:bg-white/5
                       backdrop-blur-xl shadow-xl p-6 sm:p-8">

                <!-- HEADER -->
                <div class="flex items-center gap-3 mb-8">
                    <div class="sm:p-3 p-2 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow">
                        <PencilSquareIcon class="h-6" />
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-xl sm:text-3xl font-bold text-gray-800 dark:text-white">
                            Create Announcement
                            <span class="hidden sm:inline text-gray-400 font-medium"> / Message</span>
                        </h1>
                        <span class="text-sm sm:block hidden dark:text-gray-400">
                            Sending messages and sharing information is now easier than ever.
                        </span>
                        <span class="sm:hidden block text-xs dark:text-gray-400">
                            Simplifying the way you share information.
                        </span>
                    </div>
                </div>

                <!-- FORM -->
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- TITLE -->
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">
                            Title
                        </label>
                        <input v-model="form.judul" placeholder="Enter the title here..." class="w-full mt-1 rounded-xl px-4 py-3
                                   bg-white/80 dark:bg-[#0F172A]
                                   border border-gray-300 dark:border-white/10
                                   text-gray-800 dark:text-gray-100
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                        <div class="text-red-500 text-sm mt-1">{{ form.errors.judul }}</div>
                    </div>

                    <!-- CONTENT -->
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">
                            Content
                        </label>
                        <textarea v-model="form.pengumuman" rows="4" placeholder="Type your message here..." class="w-full mt-1 rounded-xl px-4 py-3
                                   bg-white/80 dark:bg-[#0F172A]
                                   border border-gray-300 dark:border-white/10
                                   text-gray-800 dark:text-gray-100
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </textarea>
                        <div class="text-red-500 text-sm mt-1">{{ form.errors.pengumuman }}</div>
                    </div>

                    <!-- RECIPIENT -->
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">
                            Recipient
                        </label>
                        <select v-model="form.penerima" class="w-full mt-1 rounded-xl px-4 py-3
                                   bg-white/80 dark:bg-[#0F172A]
                                   border border-gray-300 dark:border-white/10
                                   text-gray-800 dark:text-gray-100">
                            <option value="semua">All Users</option>
                            <option value="admin">Admin</option>
                            <option value="guru">Teacher</option>
                            <option value="proktor">Proctor</option>
                            <option value="siswa">Student</option>
                        </select>
                    </div>

                    <!-- CLASS -->
                    <div v-if="form.penerima === 'siswa'">
                        <label class="font-semibold text-gray-700 dark:text-gray-300">
                            Select Class
                        </label>
                        <select v-model="form.kelas_id" class="w-full mt-1 rounded-xl px-4 py-3
                                   bg-white/80 dark:bg-[#0F172A]
                                   border border-gray-300 dark:border-white/10
                                   text-gray-800 dark:text-gray-100">
                            <option value="">All Classes</option>
                            <option v-for="kls in kelas" :key="kls.id" :value="kls.id">
                                {{ kls.kelas }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">
                            * Leave blank for all classes
                        </p>
                        <div class="text-red-500 text-sm">{{ form.errors.kelas_id }}</div>
                    </div>

                    <!-- ACTION -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="form.reset()" class="px-6 py-3 rounded-xl bg-gray-600 hover:bg-gray-700 dark:bg-transparent dark:hover:bg-gray-800 dark:border
                                   text-white font-semibold flex items-center gap-2">
                            <ArrowPathIcon class="w-5 h-5" />
                            Reset
                        </button>

                        <button type="submit" class="px-6 py-3 rounded-xl
                                   bg-gradient-to-r from-indigo-600 to-purple-600
                                   hover:opacity-90 text-white font-semibold
                                   flex items-center gap-2">
                            <PaperAirplaneIcon class="w-5 h-5 sm:inline-block hidden" />
                            {{ isSubmitting ? 'Sending...' : 'Send Message' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- ANNOUNCEMENT LIST -->
            <div class="rounded-xl border border-white/20 dark:border-white/10
                       bg-white/70 dark:bg-white/5
                       backdrop-blur-xl shadow-xl p-6 sm:p-8">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        📢 My Recent Messages
                    </h2>

                    <button @click="deleteAllAnnouncements" class="hidden sm:flex items-center gap-2
                               px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700
                               text-white text-sm font-semibold">
                        <TrashIcon class="w-4 h-4" />
                        Delete All
                    </button>
                </div>

                <ul class="space-y-4 max-h-[420px] overflow-y-auto pr-1">

                    <li v-for="item in paginatedAnnouncements" :key="item.id" class="rounded-2xl p-5
                               bg-white/80 dark:bg-[#0F172A]
                               border border-gray-200 dark:border-white/10
                               shadow hover:shadow-lg transition">

                        <div class="flex justify-between gap-4">
                            <div>
                                <span class="inline-block mb-2 px-3 py-1 text-xs font-semibold
                                           rounded-full bg-indigo-100 text-indigo-700">
                                    Sent to: {{ displayPenerima(item) }}
                                </span>

                                <h3 class="font-bold text-lg text-gray-800 dark:text-white">
                                    {{ item.judul }}
                                </h3>

                                <p class="text-gray-600 dark:text-gray-300 mt-1">
                                    {{ item.pengumuman }}
                                </p>

                                <p class="text-xs text-gray-400 mt-2">
                                    {{ new Date(item.created_at).toLocaleString() }}
                                </p>
                            </div>

                            <button @click="deletePengumuman(item.id)" class="text-red-500 hover:text-red-700">
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </li>

                    <!-- EMPTY -->
                    <li v-if="paginatedAnnouncements.length === 0" class="rounded-xl bg-gray-100 dark:bg-white/5
                               py-10 text-center text-gray-500">
                        No announcements yet.
                    </li>
                </ul>

                <!-- PAGINATION -->
                <div class="flex justify-center gap-3 mt-6">
                    <button @click="prevPage" :disabled="currentPage === 1" class="p-2 rounded-lg bg-gray-200 dark:bg-white/10
                               hover:bg-gray-300 disabled:opacity-50">
                        <ChevronLeftIcon class="w-5 h-5" />
                    </button>

                    <span class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-white/10">
                        {{ currentPage }} / {{ totalPages }}
                    </span>

                    <button @click="nextPage" :disabled="currentPage === totalPages" class="p-2 rounded-lg bg-gray-200 dark:bg-white/10
                               hover:bg-gray-300 disabled:opacity-50">
                        <ChevronRightIcon class="w-5 h-5" />
                    </button>
                </div>

            </div>
        </div>
    </MenuLayout>
</template>
