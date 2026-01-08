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

    if (props.quest.jenis_lampiran === 'Gambar') {
        return `/storage/bank_soal/${url.split('/').pop()}`
    }

    return null
})

const isEssay = computed(() => {
    return props.quest?.tipe_soal === 'Essay'
})

/* ================= STATE ================= */
const perPage = 10
const currentPage = ref(1)
const token = ref(props.ujianSiswa.token)
const jawaban = ref(props.riwayat?.jawaban ?? null)
const jawabanAwal = ref(props.riwayat?.jawaban ?? null)
const timer = ref(props.sisaDetik)
let interval = null
const answeredLocal = ref([...props.answered])

watch(() => props.answered, (val) => {
    answeredLocal.value = [...val]
})

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
const isAnswered = (questId) => answeredLocal.value.includes(questId)
const ujianSelesai = ref(false)

/* ================= FULLSCREEN MODE ================= */
const isFullscreen = ref(false)

const requestFullscreen = () => {
    const el = document.documentElement

    if (el.requestFullscreen) el.requestFullscreen()
    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen()
    else if (el.msRequestFullscreen) el.msRequestFullscreen()
}

const exitFullscreen = () => {
    if (document.exitFullscreen) document.exitFullscreen()
}

const onFullscreenChange = () => {
    const fs =
        document.fullscreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement

    isFullscreen.value = !!fs

    // hanya blokir jika ujian belum selesai
    if (!fs && !ujianSelesai.value) {
        alert('Keluar dari mode layar penuh tidak diperbolehkan!')
        blockExit()
    }
}

/* ================= SYNC SOAL ================= */
watch(
    () => props.quest?.id,
    (id) => {
        if (!id) return
        jawaban.value = props.riwayat?.jawaban ?? null
        jawabanAwal.value = props.riwayat?.jawaban ?? null
    },
    { immediate: true }
)

/* ================= TIMER ================= */
const updateTimer = () => {
    if (timer.value <= 0) {
        clearInterval(interval)
        exitFullscreen()
        submitUjian()
        return
    }
    timer.value--
}

/* ================= Watcher Autosave ================= */
const isNavigating = ref(false)
let saveTimeout = null

watch(jawaban, () => {
    if (isNavigating.value) return

    clearTimeout(saveTimeout)
    saveTimeout = setTimeout(() => {
        autosave()
    }, 500)
})

/* ================= AUTOSAVE ================= */
const autosave = async () => {
    if (jawaban.value === null) return
    if (isEssay.value && (!jawaban.value || jawaban.value.trim() === ''))
        return Promise.resolve()
    if (jawaban.value === jawabanAwal.value) return

    await axios.post(route('siswa.ujian.autosave'), {
        soal_id: props.soal.id,
        quest_id: props.quest.id,
        jawaban: jawaban.value,
        token: token.value
    })

    jawabanAwal.value = jawaban.value

    if (!answeredLocal.value.includes(props.quest.id)) {
        answeredLocal.value.push(props.quest.id)
    }
}

/* ================= NAVIGASI ================= */
const goTo = (n) => {
    isNavigating.value = true

    // autosave terakhir (fire & forget)
    autosave()

    router.get(
        route('siswa.ujian.kerjakan', props.soal.id),
        { no: n },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isNavigating.value = false
            }
        }
    )
}

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

        await axios.post(route('siswa.ujian.submit', props.soal.id), {
            token: token.value
        })

        // Keluar fullscreen sebelum redirect
        ujianSelesai.value = true
        exitFullscreen()

        router.get(route('siswa.ujian.finish'))
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
const blockExit = () => {
    clearInterval(interval)
    refreshToken()
    window.location.href = route('siswa.ujian.token')
}

/* ================= BLOCK Keydown ================= */
const blockKeydown = e => {
    if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'r', 't', 'n', 'w'].includes(e.key.toLowerCase())) e.preventDefault()
    if (['F5', 'Escape'].includes(e.key)) e.preventDefault()
    if (e.key === 'Escape') {
        e.preventDefault()
        alert('ESC tidak diperbolehkan!')
    }
}

const blockContext = e => e.preventDefault()
const blockClipboard = e => e.preventDefault()

/* ================= BLOCK SCREENSHOT ================= */
const blockScreenshot = (e) => {
    // Windows: PrintScreen
    if (e.key === 'PrintScreen') {
        e.preventDefault()
        alert('Screenshot tidak diperbolehkan!')
        blockExit()
    }

    // MacOS: Cmd + Shift + 3 / 4 / 5
    if ((e.metaKey || e.ctrlKey) && e.shiftKey && ['3', '4', '5'].includes(e.key)) {
        e.preventDefault()
        alert('Screenshot tidak diperbolehkan!')
        blockExit()
    }
}

/* ================= LIFECYCLE ================= */
const onVisibilityChange = () => {
    if (document.hidden)
        blockExit()
}

onMounted(() => {
    interval = setInterval(updateTimer, 1000)

    // tab visibility → silent refresh token
    document.addEventListener('visibilitychange', onVisibilityChange)
    document.addEventListener('keydown', blockKeydown)
    document.addEventListener('keydown', blockScreenshot)
    document.addEventListener('contextmenu', blockContext)
    document.addEventListener('cut', blockClipboard)
    document.addEventListener('copy', blockClipboard)
    document.addEventListener('paste', blockClipboard)
    document.addEventListener('fullscreenchange', onFullscreenChange)
    document.addEventListener('webkitfullscreenchange', onFullscreenChange)
    document.addEventListener('msfullscreenchange', onFullscreenChange)

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
})

onBeforeUnmount(() => {
    clearInterval(interval)

    document.removeEventListener('visibilitychange', onVisibilityChange)
    window.removeEventListener('beforeunload', refreshToken)
    window.removeEventListener('click', closeLegend)
    document.removeEventListener('keydown', blockKeydown)
    document.removeEventListener('keydown', blockScreenshot)
    document.removeEventListener('contextmenu', blockContext)
    document.removeEventListener('cut', blockClipboard)
    document.removeEventListener('copy', blockClipboard)
    document.removeEventListener('paste', blockClipboard)
    document.removeEventListener('fullscreenchange', onFullscreenChange)
    document.removeEventListener('webkitfullscreenchange', onFullscreenChange)
    document.removeEventListener('msfullscreenchange', onFullscreenChange)

    refreshToken() // fire & forget
})

const showFullscreenGate = ref(true)
</script>


<template>
    <div class="min-h-screen w-full md:py-10 p-4 dark:bg-[#020617] md:px-6">

        <!-- ================= FULLSCREEN GATE ================= -->
        <div v-if="showFullscreenGate"
            class="fixed inset-0 z-[9999] bg-slate-900 flex items-center justify-center text-white">
            <div class="text-center space-y-6 w-full px-6">
                <h2 class="text-2xl font-bold">Masuk Mode Ujian</h2>
                <p class="text-sm text-gray-300">
                    Ujian harus dikerjakan dalam <b>mode layar penuh (Full screen)</b>.
                    Keluar dari mode full screen akan mengakhiri ujian dan anda akan dikeluarkan secara paksa.
                </p>

                <button @click="() => { requestFullscreen(); showFullscreenGate = false }"
                    class="px-6 py-3 rounded bg-blue-600 hover:bg-blue-700 font-semibold">
                    Mulai Ujian
                </button>
            </div>
        </div>

        <div v-if="isSubmitting" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow">
                Mengirim jawaban...
            </div>
        </div>

        <div class="flex sm:flex-row w-full sm:mx-auto sm:max-w-6xl mt-4 sm:mt-12 flex-col gap-4">

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

                <!-- LAMPIRAN SOAL -->
                <div v-if="directLampiranLink" class="mb-4 mt-3 w-full">

                    <!-- GAMBAR -->
                    <img :src="directLampiranLink"
                        class="w-full max-w-sm max-h-24 sm:max-h-32 object-contain object-left" />
                </div>

                <div v-html="quest.soal" :key="quest.id" class="mb-6 text-gray-800 dark:text-gray-100 leading-relaxed">
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
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800 transition">
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
                    class="px-3 py-2 rounded border dark:hover:bg-gray-800 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed">
                    ← Prev
                </button>

                <span class="text-gray-500 dark:text-gray-400">
                    {{ currentPage }} / {{ totalPages }}
                </span>

                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="px-3 py-2 rounded border dark:hover:bg-gray-800 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed">
                    Next →
                </button>

            </div>

        </div>

    </div>
</template>
