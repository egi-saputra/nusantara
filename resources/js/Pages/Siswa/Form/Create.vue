<script setup>
import { useForm, Head, router, usePage } from '@inertiajs/vue3'
import { ArrowLeftOnRectangleIcon, CheckBadgeIcon, CheckCircleIcon } from '@heroicons/vue/24/solid'
import { ToastAlert } from '@/Composables/ToastAlert.js';
import { route } from 'ziggy-js'
import { ref, onMounted } from 'vue'

const page = usePage();
const { success, error } = ToastAlert();

const showToast = ref(false)
const toastMessage = ref('')

onMounted(() => {
    if (page.props.flash?.success) {
        toastMessage.value = page.props.flash.success
        showToast.value = true

        setTimeout(() => {
            showToast.value = false
        }, 4000)
    }
})

const props = defineProps({
    kelas: Array,
    kejuruan: Array,
})

const form = useForm({
    nama_lengkap: '',
    nis: '',
    nisn: '',
    kelas_id: '',
    kejuruan_id: '',
})

const submit = () => {
    form.post(route('siswa.form.store'))
}

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>

    <Head title="Form Siswa" />

    <!-- Toast -->
    <div v-if="showToast" class="
        fixed z-50 bg-green-600 text-white px-5 py-3 shadow-lg flex items-center gap-3
        animate-slide-in

        /* Mobile */
        bottom-4 left-4 right-4 rounded-lg

        /* Desktop */
        md:top-5 md:right-5 md:bottom-auto md:left-auto md:rounded-lg md:w-auto
    ">
        <CheckBadgeIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm font-medium">
            {{ toastMessage }}
        </span>
    </div>

    <div class="min-h-screen flex items-center justify-center bg-gray-100 py-6 px-4 sm:px-6 lg:px-8">
        <div
            class="max-w-3xl w-full bg-white border border-gray-300 md:rounded-2xl rounded shadow md:shadow-xl p-8 sm:p-10">

            <!-- Header -->
            <div class="mb-6 text-center">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Form Data Siswa</h1>
                <p class="mt-2 text-gray-500 text-sm sm:text-base">
                    Mohon lengkapi data diri Anda sebelum melanjutkan.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-red-600">*</span> Nama
                        Lengkap (Sesuai ijazah terakhir)</label>
                    <input v-model="form.nama_lengkap" type="text"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none capitalize" />
                    <div v-if="form.errors.nama_lengkap" class="text-red-500 text-sm mt-1">
                        {{ form.errors.nama_lengkap }}
                    </div>
                </div>

                <!-- NIS -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIS (Opsional)</label>
                    <input v-model="form.nis" type="text" maxlength="10"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" />
                    <div v-if="form.errors.nis" class="text-red-500 text-sm mt-1">
                        {{ form.errors.nis }}
                    </div>
                </div>

                <!-- NISN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-red-600">*</span> NISN
                        (10 digit)</label>
                    <input v-model="form.nisn" type="text" maxlength="10"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none" />
                    <div v-if="form.errors.nisn" class="text-red-500 text-sm mt-1">
                        {{ form.errors.nisn }}
                    </div>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-red-600">*</span>
                        Unit Kelas</label>
                    <select v-model="form.kelas_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        <option v-for="k in kelas" :key="k.id" :value="k.id">
                            {{ k.kelas }}
                        </option>
                    </select>
                    <div v-if="form.errors.kelas_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.kelas_id }}
                    </div>
                </div>

                <!-- Kejuruan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-red-600">*</span>
                        Program Kejuruan</label>
                    <select v-model="form.kejuruan_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none">
                        <option value="">-- Pilih Kejuruan --</option>
                        <option v-for="k in kejuruan" :key="k.id" :value="k.id">
                            {{ k.kejuruan }}
                        </option>
                    </select>
                    <div v-if="form.errors.kejuruan_id" class="text-red-500 text-sm mt-1">
                        {{ form.errors.kejuruan_id }}
                    </div>
                </div>

                <!-- Buttons Desktop -->
                <div class="md:flex hidden flex-col sm:flex-row justify-end gap-3 pt-4">
                    <!-- Logout Button -->
                    <button type="button" @click="logout"
                        class="w-full sm:w-auto px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 justify-center">
                        <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                        Logout
                    </button>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 rounded-lg text-white font-semibold bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg flex items-center justify-center gap-2"
                        :disabled="form.processing">

                        <!-- Spinner -->
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        <!-- Icon -->
                        <CheckBadgeIcon v-else class="w-5 h-5" />

                        <span>
                            {{ form.processing ? 'Processing...' : 'Simpan' }}
                        </span>
                    </button>

                </div>

                <!-- Buttons Mobile -->
                <div class="flex md:hidden flex-col sm:flex-row justify-end gap-3 pt-4">

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 rounded-lg text-white font-semibold bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg flex items-center justify-center gap-2"
                        :disabled="form.processing">

                        <!-- Spinner -->
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        <!-- Icon -->
                        <CheckCircleIcon v-else class="w-5 h-5" />

                        <span>
                            {{ form.processing ? 'Processing...' : 'Simpan' }}
                        </span>
                    </button>

                    <!-- Logout Button -->
                    <button type="button" @click="logout"
                        class="w-full sm:w-auto px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition flex items-center gap-2 justify-center">
                        <ArrowLeftOnRectangleIcon class="w-5 h-5" />
                        Logout
                    </button>

                </div>

            </form>
        </div>
    </div>
</template>

<style>
@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Default: Mobile */
.animate-slide-in {
    animation: slideUp 0.3s ease-out;
}

/* Desktop */
@media (min-width: 768px) {
    .animate-slide-in {
        animation: slideRight 0.3s ease-out;
    }
}
</style>
