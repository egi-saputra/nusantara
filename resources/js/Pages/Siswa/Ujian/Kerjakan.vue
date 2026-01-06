<script setup>
import { PaperAirplaneIcon } from '@heroicons/vue/24/outline'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

/* ================= PROPS ================= */
const props = defineProps({
    soal: Object,
    ujianSiswa: Object,
    quest: Object,
    riwayat: Object,
    nomorList: Array,
    no: Number,
    totalSoal: Number,
    sisaDetik: Number,
    answered: Array,
})

/* ================= TYPE & LINK ================= */
const directLampiranLink = computed(() => {
    const url = props.quest?.link_lampiran
    if (!url) return null

    // Jika lampiran tipe Gambar yang disimpan di storage
    if (props.quest.jenis_lampiran === 'Gambar') {
        // Storage path, pastikan sudah storage:link
        return `/storage/bank_soal/${url.split('/').pop()}`
    }

    // Jika Video, gunakan link langsung
    if (props.quest.jenis_lampiran === 'Video') {
        return url
    }

    return url
})

const lampiranType = computed(() => {
    if (!directLampiranLink.value) return null
    if (props.quest.jenis_lampiran === 'Gambar') return 'gambar'
    if (props.quest.jenis_lampiran === 'Video') return 'video'
    return 'link'
})

const lampiranName = computed(() => {
    if (!props.quest.link_lampiran) return ''
    return props.quest.link_lampiran.split('/').pop()
})

const isEssay = computed(() => {
    return props.quest?.tipe_soal === 'Essay'
})

/* ================= PREVIEW ================= */
const showPreview = ref(false)
const previewUrl = ref('')
const previewType = ref('')

const openPreview = () => {
    if (!directLampiranLink.value) return
    previewUrl.value = directLampiranLink.value
    previewType.value = lampiranType.value
    showPreview.value = true
}

const closePreview = () => {
    showPreview.value = false
    previewUrl.value = ''
    previewType.value = ''
}

/* ================= STATE ================= */
const perPage = 10
const currentPage = ref(1)
const token = ref(props.ujianSiswa.token)
const jawaban = ref(null)
const jawabanAwal = ref(props.riwayat.jawaban)
const timer = ref(props.sisaDetik)
let interval = null

/* ================= PAGINATION ================= */
const totalPages = computed(() => Math.ceil(props.nomorList.length / perPage))
const paginatedNomorList = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return props.nomorList.slice(start, start + perPage)
})
watch(() => props.no, val => {
    currentPage.value = Math.ceil(val / perPage)
})

/* ================= LEGEND ================= */
const showLegend = ref(false)
const toggleLegend = () => showLegend.value = !showLegend.value
const closeLegend = e => {
    if (!e.target.closest('.legend-wrapper')) showLegend.value = false
}

/* ================= UTIL ================= */
const isAnswered = (questId) => props.answered.includes(questId)

/* ================= SYNC SOAL ================= */
watch(() => props.quest.id, () => {
    jawaban.value = props.riwayat.jawaban ?? null
    jawabanAwal.value = props.riwayat.jawaban ?? null
}, { immediate: true })

/* ================= TIMER ================= */
const updateTimer = () => {
    if (timer.value <= 0) {
        clearInterval(interval)
        submitUjian()
        return
    }
    timer.value--
}

/* ================= AUTOSAVE ================= */
const autosave = () => {
    if (jawaban.value === null || jawaban.value === jawabanAwal.value) return Promise.resolve()
    return router.post(
        route('siswa.ujian.autosave'),
        { soal_id: props.soal.id, quest_id: props.quest.id, jawaban: jawaban.value, token: token.value },
        { preserveState: true, preserveScroll: true, onSuccess: () => jawabanAwal.value = jawaban.value }
    )
}

/* ================= NAVIGASI ================= */
const goTo = async (n) => {
    await autosave()
    router.get(route('siswa.ujian.kerjakan', props.soal.id), { no: n }, { preserveState: true })
}

/* ================= CHECK SEMUA JAWABAN ================= */
const allAnswered = computed(() => props.answered.length === props.totalSoal)

/* ================= SUBMIT ================= */
const isSubmitting = ref(false)
const submitUjian = async () => {
    isSubmitting.value = true

    try {
        // Autosave soal terakhir jika ada jawaban
        if (jawaban.value !== null && jawaban.value !== jawabanAwal.value) {
            await router.post(
                route('siswa.ujian.autosave'),
                { soal_id: props.soal.id, quest_id: props.quest.id, jawaban: jawaban.value, token: token.value },
                { preserveState: true, preserveScroll: true, onSuccess: () => jawabanAwal.value = jawaban.value }
            )
        }

        await router.post(
            route('siswa.ujian.submit', props.soal.id),
            { token: token.value },
            {
                replace: true,
                onSuccess: () => {
                    router.get(route('siswa.ujian.finish'))
                }
            }
        )
    } finally {
        isSubmitting.value = false
    }
}

/* ================= REFRESH TOKEN (AXIOS SILENT) ================= */
const refreshToken = async () => {
    try {
        await axios.post(route('siswa.ujian.refreshToken', props.soal.id), {}, {
            headers: { 'X-CSRF-TOKEN': usePage().props.csrf_token }
        })
        // silent, tidak perlu response
    } catch (err) {
        console.error('Refresh token error:', err)
    }
}

/* ================= BLOCK EXIT ================= */
const blockExit = async () => {
    clearInterval(interval)
    await refreshToken()
    // router.get(route('siswa.ujian.token'), {}, { replace: true })
    window.location.href = route('siswa.ujian.token')
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
    interval = setInterval(updateTimer, 1000)

    // tab visibility → silent refresh token
    document.addEventListener('visibilitychange', async () => {
        if (document.hidden) await blockExit()
    })

    // before unload → refresh token
    window.addEventListener('beforeunload', refreshToken)

    // close legend
    window.addEventListener('click', closeLegend)

    // blokir back browser
    history.pushState(null, '', location.href)
    window.onpopstate = () => {
        history.pushState(null, '', location.href)
        alert('Tidak dapat kembali! Ujian sedang berlangsung.')
    }

    // nonaktifkan context menu / copy-paste / shortcut
    document.addEventListener('contextmenu', e => e.preventDefault())
    document.addEventListener('cut', e => e.preventDefault())
    document.addEventListener('copy', e => e.preventDefault())
    document.addEventListener('paste', e => e.preventDefault())
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'r', 't', 'n', 'w'].includes(e.key.toLowerCase())) e.preventDefault()
        if (e.key === 'F5') e.preventDefault()
        if (e.key === 'Escape') e.preventDefault()
    })
})

// onBeforeUnmount(() => {
//     clearInterval(interval)
//     window.removeEventListener('click', closeLegend)
// })

onBeforeUnmount(async () => {
    clearInterval(interval)
    window.removeEventListener('click', closeLegend)

    // Refresh token secara silent
    await refreshToken()
})
</script>


<template>
    <div class="w-full min-h-screen md:py-10 p-6 dark:bg-[#020617] md:px-0">

        <div class="flex md:flex-row mx-auto md:max-w-[69rem] md:mt-12 flex-col gap-4">

            <!-- ================= PANEL SOAL ================= -->
            <div
                class="flex-1 bg-white/70 dark:bg-[#0F172A]/90 backdrop-blur-xl md:shadow-xl border border-white/20 dark:border-white/10 rounded-lg md:rounded-2xl md:p-6 pt-4 pb-6 px-4 md:px-6 md:pt-6 md:pb-6">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold dark:text-gray-200 text-gray-700">
                        Soal {{ no }} dari {{ totalSoal }}
                    </h3>

                    <!-- ================= TIMER FIXED ================= -->
                    <div
                        class="z-20 md:-mt-20 -mt-2 -mr-2 md:mr-0 dark:bg-[#0F172A] bg-white md:rounded-xl dark:md:border-white/10 px-4 py-2 flex items-center gap-2 md:border">
                        <svg class="w-5 h-5 text-red-500 dark:md:text-gray-300 md:text-[#063970]" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span
                            class="font-bold md:inline-block hidden -ml-1 mt-0.5 text-[#063970] dark:text-indigo-300 text-sm">
                            Timer :
                        </span>
                        <span class="font-bold text-red-600 dark:text-red-500 mt-0.5 text-sm">
                            {{ Math.floor(timer / 60) }}:{{ String(timer % 60).padStart(2, '0') }}
                        </span>
                    </div>

                </div>

                <!-- SOAL -->
                <!-- LAMPIRAN -->
                <div v-if="directLampiranLink" class="mb-4">
                    <!-- <p class="text-sm text-gray-500 mb-1">Nama file: {{ lampiranName }}</p> -->

                    <!-- Jika gambar, tampilkan langsung dengan max-width -->
                    <img v-if="lampiranType === 'gambar'" :src="directLampiranLink"
                        class="w-full max-w-sm max-h-24 -ml-32 p-0 rounded-lg object-contain" />

                    <!-- Jika video, tampilkan tombol untuk modal -->
                    <button v-else-if="lampiranType === 'video'" @click="openPreview"
                        class="text-blue-600 font-semibold underline cursor-pointer">
                        Lihat Video
                    </button>
                </div>

                <!-- Modal Preview untuk Video -->
                <div v-if="showPreview && previewType === 'video'"
                    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-xl max-w-xl w-full p-4 relative">
                        <button @click="closePreview"
                            class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>

                        <video controls class="w-full rounded-lg dark:text-gray-400">
                            <source :src="previewUrl" type="video/mp4" />
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>

                <div v-html="quest.soal" class="mb-6 text-gray-800 dark:text-gray-100 leading-relaxed">
                </div>

                <!-- JAWABAN -->
                <!-- PILIHAN GANDA -->
                <div v-if="!isEssay" class="space-y-3">
                    <template v-for="opsi in ['A', 'B', 'C', 'D', 'E']" :key="opsi">
                        <label v-if="quest['opsi_' + opsi.toLowerCase()]"
                            class="flex gap-3 items-start cursor-pointer p-3 rounded-xl border bg-white/60 dark:bg-slate-800/50 backdrop-blur hover:bg-blue-50 dark:hover:bg-indigo-500/10 transition"
                            :class="jawaban === opsi ? 'border-blue-600 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" :value="opsi" v-model="jawaban" class="mt-1 accent-blue-600" />
                            <span v-html="quest['opsi_' + opsi.toLowerCase()]" class="dark:text-gray-300"></span>
                        </label>
                    </template>
                </div>

                <!-- ESSAY -->
                <div v-else class="mt-4">
                    <label class="block font-semibold dark:text-gray-400 text-gray-700 mb-2">
                        Your Answer
                    </label>

                    <textarea v-model="jawaban" rows="6" placeholder="Type your answer here..."
                        class="w-full rounded-xl bg-white/70 dark:bg-[#0F172A]/90 backdrop-blur border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-300 p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-indigo-400 transition"></textarea>

                    <p class="text-xs dark:text-gray-400 text-gray-500 mt-2">
                        Your answer will be saved automatically.
                    </p>
                </div>

                <!-- NAVIGASI -->
                <div class="flex flex-col sm:flex-row gap-3 justify-between mt-8">

                    <button v-if="no > 1" @click="goTo(no - 1)"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Sebelumnya
                    </button>

                    <button v-if="no < totalSoal" @click="goTo(no + 1)"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button v-if="no === totalSoal" @click="submitUjian"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition">
                        <!-- Spinner sebelum teks saat submit -->
                        <svg v-if="isSubmitting" class="animate-spin w-5 h-5" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8v8z" />
                        </svg>

                        <!-- Teks -->
                        <span>{{ isSubmitting ? 'Mengirim Jawaban...' : 'Selesaikan Ujian' }}</span>

                        <!-- Icon plane setelah teks saat normal -->
                        <PaperAirplaneIcon v-if="!isSubmitting" class="w-5 h-5" />
                    </button>

                </div>
            </div>

            <!-- ================= DAFTAR NOMOR (DESKTOP) ================= -->
            <div
                class="hidden md:block w-72 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl shadow-xl rounded-2xl p-4 border border-white/20 dark:border-white/10 relative">

                <!-- HEADER + ICON INFO -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold dark:text-gray-300 text-gray-700">
                        Daftar Soal
                    </h3>

                    <!-- ICON INFO -->
                    <div class="relative legend-wrapper">
                        <button @click.stop="toggleLegend"
                            class="w-6 h-6 rounded-xl border border-gray-300 font-extrabold flex items-center justify-center dark:text-gray-300 text-gray-500  transition">
                            !
                        </button>

                        <!-- POPUP LEGEND -->
                        <div v-if="showLegend"
                            class="absolute right-8 -mt-6 w-48 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:text-gray-200 border border-white/20 dark:border-white/10 shadow-xl rounded-xl p-3 text-xs z-50">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded bg-green-600"></span>
                                    <span>Soal aktif</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded bg-blue-600"></span>
                                    <span>Sudah dijawab</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded bg-gray-300"></span>
                                    <span>Belum dijawab</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LIST NOMOR -->
                <div class="grid grid-cols-5 gap-2">
                    <button v-for="(id, i) in nomorList" :key="id" @click="goTo(i + 1)"
                        class="aspect-square rounded-lg font-bold text-sm border transition" :class="{
                            'bg-green-600 text-white border-green-700': i + 1 === no,
                            'bg-blue-600 text-white border-blue-700': i + 1 !== no && isAnswered(id),
                            'bg-gray-100 text-gray-700 border-gray-300': i + 1 !== no && !isAnswered(id),
                        }">
                        {{ i + 1 }}
                    </button>
                </div>
            </div>

        </div>

        <!-- ================= DAFTAR NOMOR (MOBILE) ================= -->
        <div
            class="md:hidden mt-6 bg-white/70 dark:bg-[#0F172A]/90 backdrop-blur-xl rounded-lg md:border-t border-white/20 dark:border-white/10 md:shadow-xl p-4">
            <!-- HEADER + ICON INFO -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold dark:text-gray-300 text-gray-700">
                    Daftar Soal
                </h3>

                <!-- ICON INFO -->
                <div class="relative legend-wrapper">
                    <button @click.stop="toggleLegend"
                        class="w-6 h-6 rounded-full border border-gray-500 dark:border-gray-300 flex items-center justify-center
                       text-gray-500 hover:bg-gray-50 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-800 dark:hover:border-gray-800 transition">
                        !
                    </button>

                    <!-- POPUP LEGEND -->
                    <div v-if="showLegend"
                        class="absolute right-0 mt-2 w-48 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl dark:text-gray-200 border border-white/20 dark:border-white/10 shadow-xl rounded-xl p-3 text-xs z-50">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-green-600"></span>
                                <span>Soal aktif</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-blue-600"></span>
                                <span>Sudah dijawab</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-gray-300"></span>
                                <span>Belum dijawab</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-2">
                <button v-for="(id, i) in paginatedNomorList" :key="id"
                    @click="goTo((currentPage - 1) * perPage + i + 1)"
                    class="aspect-square rounded-lg font-bold text-sm border transition" :class="{
                        'bg-green-600 text-white border-green-700':
                            (currentPage - 1) * perPage + i + 1 === no,

                        'bg-blue-600 text-white border-blue-700':
                            (currentPage - 1) * perPage + i + 1 !== no && isAnswered(id),

                        'bg-gray-100 text-gray-700 border-gray-300':
                            (currentPage - 1) * perPage + i + 1 !== no && !isAnswered(id),
                    }">
                    {{ (currentPage - 1) * perPage + i + 1 }}
                </button>
            </div>

            <div class="flex justify-between items-center mt-4 text-sm">

                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="px-3 py-2 rounded border dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed">
                    ← Prev
                </button>

                <span class="text-gray-500 dark:text-gray-400">
                    {{ currentPage }} / {{ totalPages }}
                </span>

                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="px-3 py-2 rounded border dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed">
                    Next →
                </button>

            </div>

        </div>

    </div>
</template>
