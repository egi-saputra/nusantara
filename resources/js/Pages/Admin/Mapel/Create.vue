<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    guru: Array, // menerima list guru dari controller
})

const form = useForm({
    mapel: '',
    guru_id: '', // tambahkan ini
})

const submit = () => {
    form.post(route('admin.mapel.store'))
}
</script>

<template>

    <Head title="Add Subject" />

    <MenuLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold mb-6">Add Subject Data</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Subject Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Subject Name</label>
                    <input v-model="form.mapel" type="text" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.mapel" class="text-red-500 text-sm">
                        {{ form.errors.mapel }}
                    </div>
                </div>

                <!-- Subject Teacher -->
                <div>
                    <label class="block text-sm font-medium mb-1">Subject Teacher</label>
                    <select v-model="form.guru_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- select subject teacher --</option>
                        <option v-for="g in props.guru" :key="g.id" :value="g.id">
                            {{ g.nama_lengkap }}
                        </option>
                    </select>

                    <div v-if="form.errors.guru_id" class="text-red-500 text-sm">
                        {{ form.errors.guru_id }}
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Link :href="route('admin.mapel.index')" class="px-4 py-2 rounded-lg border">
                        Cancel
                    </Link>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white"
                        :disabled="form.processing">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </MenuLayout>
</template>
