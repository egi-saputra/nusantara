<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'
import { PencilSquareIcon, TrashIcon, EnvelopeIcon, UserIcon, AcademicCapIcon, Cog6ToothIcon, ArrowPathIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'

/* Props */
const props = defineProps({
    siswa: Array,
})

/* State Filter & Pagination */
const search = ref('')
const sort = ref('asc')
const currentPage = ref(1)
const perPage = ref(12) // jumlah card/table per halaman

/* Filter & Sort Computed */
const filteredSiswa = computed(() => {
    let data = [...props.siswa]

    if (search.value) {
        data = data.filter(s =>
            s.nama_lengkap.toLowerCase().includes(search.value.toLowerCase())
        )
    }

    data.sort((a, b) =>
        sort.value === 'asc'
            ? a.nama_lengkap.localeCompare(b.nama_lengkap)
            : b.nama_lengkap.localeCompare(a.nama_lengkap)
    )

    return data
})

/* Pagination Computed */
const totalPages = computed(() => Math.ceil(filteredSiswa.value.length / perPage.value))
const paginatedSiswa = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    return filteredSiswa.value.slice(start, start + perPage.value)
})

/* Visible Pages for Pagination */
const MAX_VISIBLE_PAGES = 6
const visiblePages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    if (total <= MAX_VISIBLE_PAGES) return Array.from({ length: total }, (_, i) => i + 1)
    const half = Math.floor(MAX_VISIBLE_PAGES / 2)
    let start = current - half
    let end = current + half
    if (start < 1) { start = 1; end = MAX_VISIBLE_PAGES }
    if (end > total) { end = total; start = total - MAX_VISIBLE_PAGES + 1 }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

/* Reset Filter */
const resetFilter = () => {
    search.value = ''
    sort.value = 'asc'
    currentPage.value = 1
}

/* Hapus Siswa */
const hapus = (id) => {
    if (confirm('Yakin ingin menghapus siswa ini?')) {
        router.delete(route('admin.siswa.destroy', id))
    }
}
</script>

<template>

    <Head title="Student List" />

    <MenuLayout>
        <div class="sm:bg-white sm:rounded-lg sm:shadow sm:p-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl font-semibold">List of All Students</h1>
                    <p class="text-sm text-gray-500">Manage student data</p>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <input v-model="search" type="text" placeholder="Search student name..."
                    class="w-full px-4 py-2 border rounded text-sm focus:ring-indigo-500 focus:border-indigo-500" />

                <select v-model="sort" class="w-full px-4 py-2 border rounded text-sm">
                    <option value="asc">Sort A – Z</option>
                    <option value="desc">Sort Z – A</option>
                </select>

                <button @click="resetFilter"
                    class="flex items-center justify-center gap-2 px-4 py-2 rounded border bg-gray-700 text-white hover:bg-gray-800 transition text-sm">
                    <ArrowPathIcon class="w-4 h-4" /> Reset
                </button>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block">
                <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    <InformationCircleIcon class="w-6 h-6 text-blue-500" />
                    Student List Updates Automatically
                </h2>

                <table class="w-full border rounded-lg">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center border-r">No</th>
                            <th class="px-4 py-2 text-center border-r">Full Name</th>
                            <th class="px-4 py-2 text-center border-r">NIS / NISN</th>
                            <th class="px-4 py-2 text-center border-r">Class / Major</th>
                            <th class="px-4 py-2 text-center border-r">Account Status</th>
                            <th class="px-4 py-2 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(s, index) in paginatedSiswa" :key="s.id" class="border-t">
                            <td class="px-4 py-2 text-center border-r">
                                {{ (currentPage - 1) * perPage + index + 1 }}
                            </td>

                            <td class="px-4 py-2 border-r">{{ s.nama_lengkap ?? '-' }}</td>

                            <td class="px-4 py-2 border-r">
                                {{ s.nis ?? '-' }} / {{ s.nisn ?? '-' }}
                            </td>

                            <td class="px-4 py-2 border-r">
                                {{ s.kelas?.kelas ?? '-' }} / {{ s.kejuruan?.kejuruan ?? '-' }}
                            </td>

                            <td class="px-4 py-2 border-r">
                                <span class="font-semibold" :class="{
                                    'text-green-700': s.status === 'Activated',
                                    'text-red-700': s.status === 'Deactivated'
                                }">
                                    {{ s.status === 'Activated' ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-6 justify-center py-2 flex gap-2">
                                <Link :href="route('admin.siswa.edit', s.id)"
                                    class="text-indigo-600 hover:text-indigo-800">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </Link>

                                <button @click="hapus(s.id)" class="text-red-600 hover:text-red-800">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>

                        <tr v-if="filteredSiswa.length === 0">
                            <td colspan="6" class="text-center py-6 text-gray-500">
                                No student data available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden grid grid-cols-1 gap-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <InformationCircleIcon class="w-6 h-6 text-blue-500" />
                    Student List Updates Automatically
                </h2>

                <div v-for="(s, index) in paginatedSiswa" :key="s.id"
                    class="relative rounded-lg border p-5 shadow hover:shadow-lg transition">

                    <div
                        class="absolute inset-y-0 left-0 w-1 rounded-l-2xl bg-gradient-to-b from-indigo-500 to-purple-500">
                    </div>

                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex-1">
                            <div class="flex gap-3">
                                <div
                                    class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg">
                                    {{ s.nama_lengkap.charAt(0).toUpperCase() }}
                                </div>
                                <h3 class="font-semibold mb-3 text-gray-800">{{ s.nama_lengkap }}</h3>
                            </div>

                            <p class="text-sm mb-2 ml-10 text-gray-500">
                                NIS / NISN: {{ s.nis }} / {{ s.nisn }}
                            </p>

                            <p class="text-sm mb-4 ml-10 text-gray-500">
                                Class: {{ s.kelas?.kelas ?? '-' }} ({{ s.kejuruan?.kejuruan ?? '-' }})
                            </p>

                            <span
                                class="inline-flex ml-8 items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-medium border"
                                :class="{
                                    'bg-green-50 border-green-200 text-green-700': s.status === 'Activated',
                                    'bg-red-50 border-red-200 text-red-700': s.status === 'Deactivated'
                                }">
                                {{ s.status === 'Activated' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="absolute right-4 bottom-4 flex gap-2">
                        <Link :href="route('admin.siswa.edit', s.id)"
                            class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center">
                            <PencilSquareIcon class="w-5 h-5" />
                        </Link>

                        <button @click="hapus(s.id)"
                            class="w-9 h-9 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center">
                            <TrashIcon class="w-5 h-5" />
                        </button>
                    </div>

                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-center gap-2 mt-6">
                <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 rounded-md text-sm"
                    :class="currentPage === 1
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    ‹ Prev
                </button>

                <button v-for="p in visiblePages" :key="p" @click="currentPage = p" class="px-3 py-1 rounded-md text-sm"
                    :class="p === currentPage
                        ? 'bg-indigo-600 text-white'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    {{ p }}
                </button>

                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="px-3 py-1 rounded-md text-sm" :class="currentPage === totalPages
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Next ›
                </button>
            </div>

        </div>
    </MenuLayout>
</template>
