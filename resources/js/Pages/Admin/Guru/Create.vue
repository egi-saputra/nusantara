<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
    users: Array,   // menerima data user role guru
})

const form = useForm({
    user_id: '',
    nama_lengkap: '',
})

const submit = () => {
    form.post(route('admin.guru.store'))
}
</script>

<template>

    <Head title="Add Teacher Data" />

    <MenuLayout>
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-xl font-semibold mb-6">Add Teacher</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Dropdown User -->
                <div>
                    <label class="block text-sm font-medium mb-1">Select User</label>
                    <select v-model="form.user_id" class="w-full rounded-lg border-gray-300">
                        <option value="">-- select teacher user --</option>
                        <option v-for="u in props.users" :key="u.id" :value="u.id">
                            {{ u.name }}
                        </option>
                    </select>

                    <div v-if="form.errors.user_id" class="text-red-500 text-sm">
                        {{ form.errors.user_id }}
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name + Title (Optional)</label>
                    <input v-model="form.nama_lengkap" type="text" class="w-full rounded-lg border-gray-300" />

                    <div v-if="form.errors.nama_lengkap" class="text-red-500 text-sm">
                        {{ form.errors.nama_lengkap }}
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Link :href="route('admin.guru.index')" class="px-4 py-2 rounded-lg border">
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
