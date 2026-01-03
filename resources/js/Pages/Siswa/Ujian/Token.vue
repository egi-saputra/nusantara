<script setup>
import { ref, onMounted, nextTick } from 'vue'
import MenuLayout from '@/Layouts/MenuLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()


const form = useForm({ token: '' })
const isSubmitting = ref(false)
const tokenInput = ref(null)

const submit = () => {
    if (isSubmitting.value) return
    isSubmitting.value = true

    form.post(route('siswa.ujian.validateToken'), {
        preserveScroll: true,
        onFinish: () => { isSubmitting.value = false }
    })
}

onMounted(() => {
    // Reset token secara manual, tapi jangan reset ref input, supaya copy-paste bisa
    form.token = ''
    nextTick(() => {
        tokenInput.value?.focus()
        // Seleksi text supaya bisa langsung di-copy/paste
        tokenInput.value?.select()
    })
})
</script>

<template>
    <MenuLayout :disableSwal="true">

        <Head title="Input Token Ujian" />
        <!-- <Head title="Input Token Ujian">
            <meta name="disable-swal" content="true" />
        </Head> -->

        <!-- CENTER CONTAINER -->
        <div class="flex items-center justify-center">

            <!-- CARD -->
            <div class="w-full bg-white sm:pb-12 border border-gray-200 rounded-lg p-6 md:p-8">

                <!-- Title -->
                <h2 class="flex flex-col items-center gap-3 text-xl font-extrabold text-gray-700 text-center mb-6">
                    <!-- ICON WRAPPER -->
                    <span class="flex items-center justify-center w-14 h-14 rounded-full shadow-sm transition" :class="page.props.flash?.error
                        ? 'bg-red-100'
                        : 'bg-blue-100'">
                        <!-- ICON / SPINNER -->
                        <svg v-if="!form.processing" class="w-7 h-7 transition" :class="page.props.flash?.error
                            ? 'text-red-600'
                            : 'text-blue-600'" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12 2a5 5 0 00-5 5v3H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 8V7a3 3 0 116 0v3H9z" />
                        </svg>

                        <!-- SPINNER SAAT PROCESSING -->
                        <svg v-else class="w-6 h-6 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                    </span>

                    <!-- Error -->
                    <div v-if="page.props.flash?.error"
                        class="py-2 px-8 text-sm text-red-700 tracking-widest font-mono bg-red-50 rounded-lg text-center">
                        {{ page.props.flash.error }}
                    </div>

                    <!-- TITLE -->
                    <span class="font-raleway">
                        Enter Quiz / Exam Code
                    </span>
                </h2>

                <!-- Form -->
                <form @submit.prevent="submit">

                    <div class="md:p-8 p-4 border max-w-md mx-auto border-gray-300 border-dashed space-y-4 rounded-lg">
                        <input type="text" v-model="form.token" required placeholder="" ref="tokenInput"
                            class="w-full px-4 py-3 border text-blue-800 rounded-lg text-center md:text-xl tracking-widest md:font-bold font-mono" />

                        <!-- Validation Error -->
                        <p v-if="form.errors.token" class="text-sm text-red-600 text-center">
                            {{ form.errors.token }}
                        </p>

                        <button type="submit" :disabled="isSubmitting || form.processing" class="w-full py-3 rounded-lg font-bold text-white
                        bg-blue-600 hover:bg-blue-700
                        transition shadow-sm md:shadow-lg
                        disabled:opacity-50 disabled:cursor-not-allowed
                        flex items-center justify-center gap-2">

                            <!-- Spinner -->
                            <svg v-if="isSubmitting || form.processing" class="w-5 h-5 animate-spin text-white"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>

                            <!-- Text -->
                            <span>
                                {{ (isSubmitting || form.processing) ? 'Processing...' : 'Verification' }}
                            </span>
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </MenuLayout>
</template>
