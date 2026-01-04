<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { CheckIcon, ArrowLeftIcon } from '@heroicons/vue/24/solid';

const page = usePage()

const props = defineProps({
    mapel: Array,
})

const form = useForm({
    title: '',
    mapel_id: '',
    kelas: '',
    waktu: '60',
    status: 'Tidak Aktif',
    tipe_soal: 'Berurutan',
});
</script>

<template>

    <MenuLayout>

        <div class="sm:py-8 sm:pb-0 pb-10 sm:px-4 min-h-screen">
            <div
                class="mx-auto border dark:bg-[#041C32] md:text-base text-sm border-gray-300 dark:border-gray-700 bg-white rounded-lg md:shadow-xl p-6 md:p-8">
                <div class="mb-6">
                    <h1
                        class="text-2xl sm:inline-block hidden font-extrabold text-gray-800 text-left dark:text-gray-100">
                        <span class="text-3xl pt-1 font-bold">+</span> Create / Add Quiz
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 sm:text-base text-sm">This page is intended to create or
                        add the quizzes.
                    </p>
                </div>

                <!-- <form @submit.prevent="form.post('/proktor/soal')" class="space-y-5"> -->
                <form @submit.prevent="() => { console.log(form); form.post('/guru/soal'); }">

                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-200 font-semibold">Title Quiz</label>
                        <input v-model="form.title" type="text" placeholder="e.g., ( ASAS GANJIL 2025 )"
                            class="w-full border dark:bg-gray-300 border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition capitalize-input" />
                        <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div class="flex w-full mb-4 flex-col sm:flex-row gap-4">
                        <!-- MAPEL (SELECT) -->
                        <div class="w-full">
                            <label class="block dark:text-gray-200 text-gray-700 font-semibold">
                                Subjects
                            </label>

                            <select v-model="form.mapel_id"
                                class="w-full border border-gray-300 dark:bg-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                                <option value="">-- Choose subjects --</option>
                                <option v-for="m in mapel" :key="m.id" :value="m.id">
                                    {{ m.mapel }}
                                </option>
                            </select>
                            <div v-if="form.errors.mapel_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.mapel_id }}
                            </div>
                        </div>

                        <!-- KELAS -->
                        <div class="w-full">
                            <label class="block dark:text-gray-200 text-gray-700 font-semibold">Class Unit (for
                                class)</label>
                            <input v-model="form.kelas" type="text" placeholder="e.g., ( XII BR - MP / XI BR / X MP )."
                                class="w-full border border-gray-300 dark:bg-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition uppercase-input" />
                            <p v-if="form.errors.kelas" class="text-red-600 text-sm mt-1">{{ form.errors.kelas }}</p>
                        </div>
                    </div>

                    <!-- BUTTON -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-6">
                        <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-800 transition"
                            :disabled="form.processing">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            <CheckIcon v-else class="w-5 h-5" />
                            <span>{{ form.processing ? 'Creating your quiz....' : 'Create Quiz' }}</span>
                        </button>

                        <Link href="/guru/soal/"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-700 text-white font-semibold rounded-lg shadow-lg hover:from-gray-600 hover:to-gray-800 transition">
                            <ArrowLeftIcon class="w-5 h-5" />
                            Back to Quiz
                        </Link>
                    </div>
                </form>
            </div>
        </div>

    </MenuLayout>
</template>

<style>
/* Input user otomatis uppercase */
.uppercase-input {
    text-transform: uppercase;
}

/* Placeholder tetap normal */
.uppercase-input::placeholder {
    text-transform: none;
}

/* Input user otomatis capitaliez */
.capitalize-input {
    text-transform: capitalize;
}

/* Placeholder tetap normal */
.capitalize-input::placeholder {
    text-transform: none;
}
</style>