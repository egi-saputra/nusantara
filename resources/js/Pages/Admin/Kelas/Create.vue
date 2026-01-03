<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    guru: Array,
})

const form = useForm({
    kelas: '',
    guru_id: '', // wali kelas
})

const submit = () => {
    form.post(route('admin.kelas.store'))
}
</script>

<template>

    <Head title="Add Class" />

    <MenuLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold mb-6">Add Class</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Class Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Class Name</label>
                    <input v-model="form.kelas" type="text" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.kelas" class="text-red-500 text-sm">
                        {{ form.errors.kelas }}
                    </div>
                </div>

                <!-- Homeroom Teacher -->
                <div>
                    <label class="block text-sm font-medium mb-1">Homeroom Teacher (Walas)</label>
                    <select v-model="form.guru_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- select homeroom teacher --</option>
                        <option v-for="g in guru" :key="g.id" :value="g.id">
                            {{ g.nama_lengkap }}
                        </option>
                    </select>

                    <div v-if="form.errors.guru_id" class="text-red-500 text-sm">
                        {{ form.errors.guru_id }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-4">
                    <Link :href="route('admin.kelas.index')" class="px-4 py-2 rounded-lg border">
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
