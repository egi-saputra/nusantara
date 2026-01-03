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
        <div class="bg-white rounded-lg shadow p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-semibold">All Teachers</h1>
                    <p class="text-sm text-gray-500">Manage teacher data</p>
                </div>

                <Link :href="route('admin.guru.create')"
                    class="px-4 py-2 rounded bg-blue-800 text-white hover:bg-blue-900 transition">
                    + <span class="inline-block sm:hidden">Add</span>
                    <span class="sm:inline-block hidden">Add Teacher</span>
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full border rounded-lg">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center border-r">No</th>
                            <th class="px-4 py-2 whitespace-nowrap text-center">Full Name</th>
                            <th class="px-4 py-2 text-center border-l">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(g, index) in guru" :key="g.id" class="border-t">
                            <td class="px-4 py-2 text-center sm:w-20 border-r">{{ index + 1 }}</td>
                            <td class="px-4 whitespace-nowrap py-2">{{ g.nama_lengkap }}</td>
                            <td class="px-4 py-2 sm:w-40 border-l">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="openEdit(g)" class="text-indigo-600 hover:text-indigo-800"
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
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-md rounded-xl mx-3 shadow-lg p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Edit Teacher Data</h2>
                <button @click="closeModal">
                    <XMarkIcon class="w-5 h-5 text-gray-500 hover:text-gray-700" />
                </button>
            </div>

            <!-- USER DROPDOWN -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Teacher User</label>
                <select v-model="form.user_id"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">

                    <option value="">-- select teacher user --</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">
                        {{ u.name }}
                    </option>

                </select>
            </div>

            <!-- FULL NAME INPUT -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Teacher Name</label>
                <input v-model="form.nama_lengkap" type="text"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <!-- BUTTONS -->
            <div class="flex justify-end gap-2">
                <button @click="closeModal" class="px-4 py-2 rounded-lg border hover:bg-gray-100">
                    Cancel
                </button>
                <button @click="update" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Save
                </button>
            </div>

        </div>
    </div>
</template>
