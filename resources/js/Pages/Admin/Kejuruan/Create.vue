<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

// Props dari controller
const props = defineProps({
    guru: Array
})

const form = useForm({
    kejuruan: '',
    guru_id: ''
})

const submit = () => {
    form.post(route('admin.kejuruan.store'))
}
</script>

<template>

    <Head title="Add Vocational Program" />

    <MenuLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold mb-6">Add Vocational Program Data</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Vocational Program Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Program Name</label>
                    <input v-model="form.kejuruan" type="text" class="w-full rounded-lg border-gray-300" />
                    <div v-if="form.errors.kejuruan" class="text-red-500 text-sm">
                        {{ form.errors.kejuruan }}
                    </div>
                </div>

                <!-- Head of Program -->
                <div>
                    <label class="block text-sm font-medium mb-1">Head of Program</label>
                    <select v-model="form.guru_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- Select Teacher --</option>
                        <option v-for="g in props.guru" :key="g.id" :value="g.id">
                            {{ g.nama_lengkap }}
                        </option>
                    </select>

                    <div v-if="form.errors.guru_id" class="text-red-500 text-sm">
                        {{ form.errors.guru_id }}
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-2 pt-4">
                    <Link :href="route('admin.kejuruan.index')" class="px-4 py-2 rounded-lg border">
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
