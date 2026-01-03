<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    siswa: Object,
    kelas: Array,
    kejuruan: Array,
})

const form = useForm({
    nama_lengkap: props.siswa.nama_lengkap,
    nis: props.siswa.nis,
    nisn: props.siswa.nisn,
    kelas_id: props.siswa.kelas_id,
    kejuruan_id: props.siswa.kejuruan_id,
    status: props.siswa.status,
})

const submit = () => {
    form.put(route('admin.siswa.update', props.siswa.id))
}
</script>

<template>

    <Head title="Edit Student Data" />

    <MenuLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex items-center gap-2 mb-6">
                <h1 class="text-xl font-semibold">Edit Student Data</h1>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name</label>
                    <input v-model="form.nama_lengkap" type="text" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.nama_lengkap" class="text-red-500 text-sm">
                        {{ form.errors.nama_lengkap }}
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-1">Account Status</label>
                    <select v-model="form.status" class="w-full rounded-lg border-gray-300">
                        <option value="Activated">Active</option>
                        <option value="Deactivated">Inactive</option>
                    </select>
                </div>

                <!-- NIS -->
                <div>
                    <label class="block text-sm font-medium mb-1">NIS</label>
                    <input v-model="form.nis" type="text" maxlength="10" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.nis" class="text-red-500 text-sm">
                        {{ form.errors.nis }}
                    </div>
                </div>

                <!-- NISN -->
                <div>
                    <label class="block text-sm font-medium mb-1">NISN</label>
                    <input v-model="form.nisn" type="text" maxlength="10" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.nisn" class="text-red-500 text-sm">
                        {{ form.errors.nisn }}
                    </div>
                </div>

                <!-- Class Unit -->
                <div>
                    <label class="block text-sm font-medium mb-1">Class Unit</label>
                    <select v-model="form.kelas_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- Select Class --</option>
                        <option v-for="k in kelas" :key="k.id" :value="k.id">
                            {{ k.kelas }}
                        </option>
                    </select>
                    <div v-if="form.errors.kelas_id" class="text-red-500 text-sm">
                        {{ form.errors.kelas_id }}
                    </div>
                </div>

                <!-- Major / Study Program -->
                <div>
                    <label class="block text-sm font-medium mb-1">Vocational Program</label>
                    <select v-model="form.kejuruan_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- Select Program --</option>
                        <option v-for="k in kejuruan" :key="k.id" :value="k.id">
                            {{ k.kejuruan }}
                        </option>
                    </select>
                    <div v-if="form.errors.kejuruan_id" class="text-red-500 text-sm">
                        {{ form.errors.kejuruan_id }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="md:col-span-2 flex justify-end gap-2 pt-4">
                    <Link :href="route('admin.siswa.index')" class="px-4 py-2 rounded-lg border">
                        Cancel
                    </Link>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition"
                        :disabled="form.processing">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </MenuLayout>
</template>
