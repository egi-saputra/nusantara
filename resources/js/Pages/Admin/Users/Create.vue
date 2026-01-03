<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()

const roles = page.props.roles

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: '', // 👈 tambahan
})

const submit = () => {
  form.post(route('admin.users.store'), {
    preserveScroll: true,
  })
}
</script>

<template>

  <Head title="Users Management" />

  <MenuLayout>
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">+ Create New User</h1>
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
            <option value="" disabled>-- Pilih Role --</option>
            <option v-for="role in roles" :key="role" :value="role">
              {{ role }}
            </option>
          </select>

          <div v-if="form.errors.role" class="text-red-600 text-sm">
            {{ form.errors.role }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input type="password" v-model="form.password"
            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" />
          <div v-if="form.errors.password" class="text-red-600 text-sm">
            {{ form.errors.password }}
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Link :href="route('admin.users.index')" class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100">
            Cancel
          </Link>

          <button type="submit" :disabled="form.processing"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
            Save
          </button>
        </div>
      </form>
    </div>
  </MenuLayout>
</template>
