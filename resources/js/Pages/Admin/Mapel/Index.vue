<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref } from 'vue'
import { PencilSquareIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    mapel: Array,
    guru: Array, // ← ditambahkan
})

const showModal = ref(false)

const form = ref({
    id: null,
    mapel: '',
    guru_id: '',
})

// ------ OPEN EDIT ------
const openEdit = (m) => {
    form.value.id = m.id
    form.value.mapel = m.mapel
    form.value.guru_id = m.guru_id ?? ''   // penting!
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.value = { id: null, mapel: '', guru_id: '' }
}

// ------ UPDATE ------
const update = () => {
    router.put(
        route('admin.mapel.update', form.value.id),
        {
            mapel: form.value.mapel,
            guru_id: form.value.guru_id,
        },
        {
            preserveScroll: true,
            onSuccess: () => closeModal()
        }
    )
}

// ------ DELETE ------
const hapus = (id) => {
    if (confirm('Yakin ingin menghapus mapel ini?')) {
        router.delete(route('admin.mapel.destroy', id))
    }
}
</script>

<template>

    <Head title="Subjects Data" />

    <MenuLayout>
        <div class="sm:bg-white sm:rounded-lg sm:shadow sm:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-semibold">
                        List of All Subjects
                    </h1>
                    <p class="text-sm text-gray-500">Manage school subjects data</p>
                </div>
                <Link :href="route('admin.mapel.create')"
                    class="px-4 py-2 rounded bg-blue-800 text-white hover:bg-blue-900 transition">
                    + <span class="inline-block sm:hidden">Add</span>
                    <span class="sm:inline-block hidden">Add Subject</span>
                </Link>
            </div>

            <!-- TABLE FOR DESKTOP -->
            <div class="hidden md:block">
                <table class="w-full border rounded-lg">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center border-r w-20">No</th>
                            <th class="px-4 py-2 text-center border-r">Subject Name</th>
                            <th class="px-4 py-2 text-center border-r">Teacher</th>
                            <th class="px-4 py-2 text-center w-40">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(m, index) in mapel" :key="m.id" class="border-t">
                            <td class="px-4 py-2 text-center border-r">{{ index + 1 }}</td>
                            <td class="px-4 py-2 text-left border-r">{{ m.mapel }}</td>
                            <td class="px-4 py-2 text-left border-r">{{ m.guru?.nama_lengkap ?? '-' }}</td>
                            <td class="px-4 py-2 justify-center flex gap-2">
                                <button @click="openEdit(m)" class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <button @click="hapus(m.id)" class="text-red-600 hover:text-red-800" title="Delete">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>

                        <tr v-if="mapel.length === 0">
                            <td colspan="4" class="text-center py-6 text-gray-500">
                                No subjects available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CARD FOR MOBILE -->
            <div class="md:hidden space-y-4 bg-white">
                <div v-for="(m, index) in mapel" :key="m.id"
                    class="border rounded-lg p-6 shadow hover:shadow-lg transition">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-semibold text-indigo-600">{{ m.mapel }}</h2>
                        <span class="text-sm text-gray-500">
                            {{ m.guru?.nama_lengkap ?? '-' }}
                        </span>
                    </div>

                    <div class="flex gap-2 mt-3 justify-end">
                        <button @click="openEdit(m)"
                            class="flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                            <PencilSquareIcon class="w-4 h-4" /> Edit
                        </button>
                        <button @click="hapus(m.id)"
                            class="flex items-center gap-1 px-3 py-1 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                            <TrashIcon class="w-4 h-4" /> Delete
                        </button>
                    </div>
                </div>

                <div v-if="mapel.length === 0" class="text-center py-6 text-gray-500">
                    No subjects available
                </div>
            </div>

            <!-- EDIT MODAL -->
            <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white w-full max-w-md rounded-lg shadow-lg sm:m-0 m-3 p-6">

                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Edit Subject</h2>
                        <button @click="closeModal">
                            <XMarkIcon class="w-5 h-5 text-gray-500 hover:text-gray-700" />
                        </button>
                    </div>

                    <!-- Subject Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Subject Name</label>
                        <input v-model="form.mapel" type="text"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>

                    <!-- Teacher -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Subject Teacher</label>
                        <select v-model="form.guru_id" class="w-full border rounded-lg px-3 py-2">
                            <option value="">-- select teacher --</option>
                            <option v-for="g in guru" :key="g.id" :value="g.id">
                                {{ g.nama_lengkap }}
                            </option>
                        </select>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-end gap-2">
                        <button @click="closeModal" class="px-4 py-2 rounded-lg border hover:bg-gray-100">
                            Cancel
                        </button>
                        <button @click="update"
                            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            Save
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </MenuLayout>
</template>
