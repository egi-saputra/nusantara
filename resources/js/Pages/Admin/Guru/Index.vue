<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref } from 'vue'
import { PencilSquareIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    guru: Array,
    users: Array,    // Tambahan
})

const showModal = ref(false)

const form = ref({
    id: null,
    user_id: '',
    nama_lengkap: '',
})

const openEdit = (g) => {
    form.value.id = g.id
    form.value.user_id = g.user_id
    form.value.nama_lengkap = g.nama_lengkap
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.value = { id: null, user_id: '', nama_lengkap: '' }
}

// UPDATE Guru
const update = () => {
    router.put(
        route('admin.guru.update', form.value.id),
        {
            user_id: form.value.user_id,
            nama_lengkap: form.value.nama_lengkap,
        },
        {
            preserveScroll: true,
            onSuccess: () => closeModal()
        }
    )
}

// HAPUS Guru
const hapus = (id) => {
    if (confirm('Yakin ingin menghapus guru ini?')) {
        router.delete(route('admin.guru.destroy', id))
    }
}
</script>

<template>

    <Head title="Teacher List" />

    <MenuLayout>
        <div class="sm:bg-white/60 dark:sm:bg-gray-800/60 sm:backdrop-blur-md sm:rounded sm:shadow sm:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl dark:text-gray-200 font-semibold">All Teachers</h1>
                    <p class="text-sm text-gray-500">Manage teacher data</p>
                </div>

                <Link :href="route('admin.guru.create')"
                    class="px-4 py-2 rounded-lg bg-blue-800 text-white hover:bg-blue-900 transition">
                    + <span class="inline-block sm:hidden">Add</span>
                    <span class="sm:inline-block hidden">Add Teacher</span>
                </Link>
            </div>

            <!-- Table -->
            <div
                class="hidden md:block rounded-lg overflow-hidden shadow-lg bg-white/60 dark:bg-gray-800/60 backdrop-blur-md">
                <table class="w-full border-collapse">

                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center border-r">No</th>
                            <th class="px-4 py-2 whitespace-nowrap text-center">Full Name</th>
                            <th class="px-4 py-2 text-center border-l">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(g, index) in guru" :key="g.id"
                            class="border-t dark:border-none hover:bg-gray-200 dark:hover:bg-gray-700 dark:text-gray-300">
                            <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
                            <td class="px-10 whitespace-nowrap py-2">{{ g.nama_lengkap }}</td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="openEdit(g)"
                                        class="text-blue-600 hover:text-blue-800 dark:text-gray-100 dark:hover:text-gray-300"
                                        title="Edit">
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </button>
                                    <button @click="hapus(g.id)" class="text-red-600 hover:text-red-800" title="Delete">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="guru.length === 0">
                            <td colspan="3" class="text-center py-6 text-gray-500">
                                No teacher data available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </MenuLayout>

    <!-- EDIT MODAL -->
    <div v-if="showModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm transition flex items-center justify-center z-50">
        <div
            class="relative w-full max-w-md rounded-2xl bg-white/70 dark:bg-gray-800/70 backdrop-blur-md shadow-xl p-6 m-3 transition">

            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Teacher Data</h2>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- USER DROPDOWN -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Teacher User</label>
                <select v-model="form.user_id"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-700/60 px-3 py-2 text-gray-900 dark:text-white
                           placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">

                    <option value="">-- select teacher user --</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">
                        {{ u.name }}
                    </option>

                </select>
            </div>

            <!-- FULL NAME INPUT -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Teacher Name</label>
                <input v-model="form.nama_lengkap" type="text"
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white/70 dark:bg-gray-700/60 px-3 py-2
                          text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
            </div>

            <!-- BUTTONS -->
            <div class="flex justify-end gap-2">
                <button @click="closeModal"
                    class="px-4 py-2 rounded-xl border border-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition dark:text-gray-300">
                    Cancel
                </button>
                <button @click="update" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Save
                </button>
            </div>

        </div>
    </div>
</template>
