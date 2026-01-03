<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon, XMarkIcon, CheckCircleIcon, ArrowPathIcon, } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const props = defineProps({
    user: Object,
})

const page = usePage()
const roles = page.props.roles
const showPassword = ref(false)

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
})

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password')
            showPassword.value = false
        },
    })
}
</script>

<template>

    <Head title="Edit User" />

    <MenuLayout>
        <div class="">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-xl font-semibold">User Profile Settings</h1>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Username</label>
                        <input type="text" v-model="form.name"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="form.errors.name" class="text-red-600 text-sm">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email Address</label>
                        <input type="email" v-model="form.email"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="form.errors.email" class="text-red-600 text-sm">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Role User</label>

                        <select v-model="form.role"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="" disabled>-- Select Role --</option>

                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </option>
                        </select>

                        <div v-if="form.errors.role" class="text-red-600 text-sm">
                            {{ form.errors.role }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            New Password (Optional)
                        </label>

                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                                class="w-full pr-10 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                                <EyeSlashIcon v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <!-- Cancel -->
                        <Link :href="route('admin.users.index')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100">
                            <XMarkIcon class="w-4 h-4" />
                            <span>Cancel</span>
                        </Link>

                        <!-- Update -->
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <!-- Loading -->
                            <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin" />

                            <!-- Normal -->
                            <CheckCircleIcon v-else class="w-4 h-4" />

                            <span>
                                {{ form.processing ? 'Updating process…' : 'Update' }}
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </MenuLayout>
</template>
