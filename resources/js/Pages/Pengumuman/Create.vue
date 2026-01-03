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
        <div class="bg-white p-6 rounded-lg shadow-md mx-auto">
            <!-- Header with Icon -->
            <div class="flex items-center gap-2 mb-5">
                <PencilSquareIcon class="w-6 h-6 text-blue-600" />
                <h1 class="text-2xl font-bold">Create Announcement <span class="sm:inline-block hidden">/ Message</span>
                </h1>
            </div>

            <!-- Announcement Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="font-semibold text-gray-700">Title</label>
                    <input v-model="form.judul" class="w-full border p-3 rounded-lg focus:ring focus:ring-blue-300"
                        placeholder="Enter the title" />
                    <div class="text-red-500 text-sm">{{ form.errors.judul }}</div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Content</label>
                    <textarea v-model="form.pengumuman"
                        class="w-full border p-3 rounded-lg focus:ring focus:ring-blue-300" rows="4"
                        placeholder="Enter your message content"></textarea>
                    <div class="text-red-500 text-sm">{{ form.errors.pengumuman }}</div>
                </div>

                <div>
                    <label class="font-semibold text-gray-700">Recipient</label>
                    <select v-model="form.penerima" class="w-full border p-3 rounded-lg">
                        <option value="semua">All Users</option>
                        <option value="admin">Admin</option>
                        <option value="guru">Teacher</option>
                        <option value="proktor">Proctor</option>
                        <option value="siswa">Student</option>
                    </select>
                </div>

                <div v-if="form.penerima === 'siswa'">
                    <label class="font-semibold text-gray-700">Select Class</label>
                    <select v-model="form.kelas_id" class="w-full border p-3 rounded-lg">
                        <option value="">All Classes</option>
                        <option v-for="kls in kelas" :key="kls.id" :value="kls.id">{{ kls.kelas }}</option>
                    </select>
                    <small class="text-gray-400">
                        <span class="text-red-600">*</span> Leave blank if the message is for all classes
                    </small>
                    <div class="text-red-500 text-sm">{{ form.errors.kelas_id }}</div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" @click="form.reset()"
                        class="flex items-center gap-2 px-6 py-3 bg-gray-600 text-white font-bold rounded-lg hover:bg-gray-700 transition">
                        <ArrowPathIcon class="w-5 h-5" />
                        Reset
                    </button>
                    <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-bold rounded-lg whitespace-nowrap hover:bg-blue-700 transition">
                        <PaperAirplaneIcon class="w-5 h-5" />
                        <span>{{ isSubmitting ? 'Sending...' : 'Send Message' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Announcements List -->
        <div class="bg-white p-6 mt-12 rounded-lg shadow-md mx-auto">
            <div class="flex justify-between mb-4">
                <h2 class="flex items-center gap-2 text-xl font-bold">
                    <i class="bi bi-megaphone text-blue-600 transition-colors duration-300"></i>
                    My Recent Messages
                </h2>

                <!-- Delete All -->
                <button @click="deleteAllAnnouncements"
                    class="sm:flex hidden items-center gap-2 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition">
                    <TrashIcon class="w-5 h-5" />
                    Delete All Messages
                </button>
            </div>

            <ul class="space-y-4 max-h-96 overflow-auto">
                <li v-for="item in paginatedAnnouncements" :key="item.id"
                    class="border p-4 rounded-xl bg-gray-50 flex justify-between items-start shadow-sm hover:shadow-md transition">
                    <div>
                        <p
                            class="bg-blue-50 text-blue-600 -ml-2 font-semibold py-1 px-3 rounded-full text-sm inline-block mb-2">
                            Sent to: {{ displayPenerima(item) }}
                        </p>
                        <h3 class="font-bold text-lg">{{ item.judul }}</h3>
                        <p class="text-gray-600 mt-1">{{ item.pengumuman }}</p>
                        <p class="text-gray-400 text-xs mt-1">{{ new Date(item.created_at).toLocaleString() }}</p>
                    </div>
                    <button @click="deletePengumuman(item.id)" class="text-red-500 hover:text-red-700 transition">
                        <TrashIcon class="w-5 h-5" />
                    </button>
                </li>

                <!-- Fallback if empty -->
                <li v-if="paginatedAnnouncements.length === 0"
                    class="border p-6 rounded-xl bg-gray-100 text-center flex flex-col items-center justify-center space-y-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 font-medium">You have not created any messages yet.</p>
                    <p class="text-gray-400 text-sm">Create your first message now to notify everyone!</p>
                </li>
            </ul>

            <!-- Pagination -->
            <div class="flex justify-center gap-2 mt-6">
                <button @click="prevPage" :disabled="currentPage === 1"
                    class="p-2 bg-gray-200 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    <ChevronLeftIcon class="w-5 h-5" />
                </button>
                <span class="px-3 py-2 bg-gray-100 rounded">{{ currentPage }} / {{ totalPages }}</span>
                <button @click="nextPage" :disabled="currentPage === totalPages"
                    class="p-2 bg-gray-200 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    <ChevronRightIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

    </MenuLayout>
</template>
