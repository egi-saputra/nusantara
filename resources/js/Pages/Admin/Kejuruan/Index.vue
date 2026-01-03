<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { ref } from 'vue'
import { PencilSquareIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    kejuruan: Array,
    guru: Array,
})

const showModal = ref(false)
const form = ref({
    id: null,
    kejuruan: '',
    guru_id: ''
})

const openEdit = (k) => {
    form.value.id = k.id
    form.value.kejuruan = k.kejuruan
    form.value.guru_id = k.guru_id
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    form.value = { id: null, kejuruan: '', guru_id: '' }
}

const update = () => {
    router.put(
        route('admin.kejuruan.update', form.value.id),
        {
            kejuruan: form.value.kejuruan,
            guru_id: form.value.guru_id
        },
        {
            preserveScroll: true,
            onSuccess: () => closeModal()
        }
    )
}

const hapus = (id) => {
    if (confirm('Yakin ingin menghapus kejuruan ini?')) {
        router.delete(route('admin.kejuruan.destroy', id))
    }
}
</script>

<template>

    <Head title="Vocational Programs" />

    <MenuLayout>
        <div class="sm:bg-white sm:rounded-lg sm:shadow sm:p-6">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-semibold">
                        List of <span class="sm:inline-block hidden">Vocational Programs</span>
                        <span class="inline-block sm:hidden">Programs</span>
                    </h1>
                    <p class="text-sm text-gray-500">Manage vocational program data at school</p>
                </div>
                <Link :href="route('admin.kejuruan.create')"
                    class="px-4 py-2 rounded bg-blue-800 text-white hover:bg-blue-900 transition">
                    + <span class="inline-block sm:hidden">Add</span>
                    <span class="sm:inline-block hidden">Add Program</span>
                </Link>
            </div>

            <!-- TABLE FOR DESKTOP -->
            <div class="hidden md:block">
                <table class="w-full border rounded-lg">
                    <thead class="bg-blue-800 text-white">
                        <tr>
                            <th class="px-4 py-2 text-center border-r w-20">No</th>
                            <th class="px-4 py-2 text-center border-r">Vocational Program Name</th>
                            <th class="px-4 py-2 text-center border-r">Head of Program</th>
                            <th class="px-4 py-2 text-center border-r w-40">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(k, index) in props.kejuruan" :key="k.id" class="border-t">
                            <td class="px-4 py-2 text-center border-r">{{ index + 1 }}</td>
                            <td class="px-4 py-2 text-left border-r">{{ k.kejuruan }}</td>
                            <td class="px-4 py-2 text-left border-r">{{ k.guru?.nama_lengkap ?? '—' }}</td>
                            <td class="px-4 py-2 justify-center flex gap-2">
                                <button @click="openEdit(k)" class="text-indigo-600 hover:text-indigo-800">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <button @click="hapus(k.id)" class="text-red-600 hover:text-red-800">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="props.kejuruan.length === 0">
                            <td colspan="4" class="text-center py-6 text-gray-500">
                                No vocational program data available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CARD FOR MOBILE -->
            <div class="md:hidden space-y-4 bg-white">
                <div v-for="(k, index) in props.kejuruan" :key="k.id"
                    class="border rounded-lg p-6 shadow hover:shadow-lg transition">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-semibold text-indigo-600">{{ k.kejuruan }}</h2>
                        <span class="text-gray-500">{{ k.guru?.nama_lengkap ?? '—' }}</span>
                    </div>
                    <div class="flex gap-2 justify-end mt-3">
                        <button @click="openEdit(k)"
                            class="flex items-center gap-1 px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                            <PencilSquareIcon class="w-4 h-4" /> Edit
                        </button>
                        <button @click="hapus(k.id)"
                            class="flex items-center gap-1 px-3 py-1 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition">
                            <TrashIcon class="w-4 h-4" /> Delete
                        </button>
                    </div>
                </div>

                <div v-if="props.kejuruan.length === 0" class="text-center py-6 text-gray-500">
                    No vocational program data available
                </div>
            </div>

        </div>
    </MenuLayout>

    <!-- EDIT MODAL -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-md rounded-lg sm:m-0 m-3 shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Edit Program</h2>
                <button @click="closeModal">
                    <XMarkIcon class="w-5 h-5 text-gray-500 hover:text-gray-700" />
                </button>
            </div>

            <!-- Vocational Program Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Program Name</label>
                <input v-model="form.kejuruan" type="text"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <!-- Head of Program -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Head of Program</label>
                <select v-model="form.guru_id" class="w-full border rounded-lg px-3 py-2">
                    <option value="">-- Select Teacher --</option>
                    <option v-for="g in props.guru" :value="g.id" :key="g.id">
                        {{ g.nama_lengkap }}
                    </option>
                </select>
            </div>

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
