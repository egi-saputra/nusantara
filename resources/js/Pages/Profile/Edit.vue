<script setup>
import MenuLayout from '@/Layouts/MenuLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage()

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<!-- <template>

    <Head title="Profile" />

    <MenuLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto space-y-6">
                <div
                    class="p-4 sm:p-8 bg-white dark:bg-gradient-to-br dark:from-[#1e1b4b] dark:via-[#312e81] dark:to-[#4c1d95] shadow rounded-lg">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <div
                    class="p-4 sm:p-8 bg-white dark:bg-gradient-to-br dark:from-[#1e1b4b] dark:via-[#312e81] dark:to-[#4c1d95] shadow rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="p-4 sm:p-8 bg-white dark:bg-gradient-to-br dark:from-[#1e1b4b] dark:via-[#312e81] dark:to-[#4c1d95] shadow rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </MenuLayout>
</template> -->

<!-- Glassmorphism Style -->
<template>

    <Head title="Profile" />

    <MenuLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Account Profile
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage your personal information & security
                </p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto space-y-8">

                <section class="relative p-6 sm:p-8 rounded-3xl
                           bg-white/70 dark:bg-white/5
                           backdrop-blur-xl
                           border border-white/20 dark:border-white/10
                           shadow-xl">

                    <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white">
                        👤 Profile Information
                    </h3>

                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </section>

                <section class="relative p-6 sm:p-8 rounded-3xl
                           bg-white/70 dark:bg-white/5
                           backdrop-blur-xl
                           border border-white/20 dark:border-white/10
                           shadow-xl">

                    <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white">
                        🔐 Security & Password
                    </h3>

                    <UpdatePasswordForm class="max-w-xl" />
                </section>

                <section class="relative p-6 sm:p-8 rounded-3xl
                           bg-red-50/70 dark:bg-red-500/5
                           backdrop-blur-xl
                           border border-red-200/50 dark:border-red-500/20
                           shadow-xl">

                    <h3 class="mb-6 text-lg font-semibold text-red-600 dark:text-red-400">
                        ⚠️ Danger Zone
                    </h3>

                    <DeleteUserForm class="max-w-xl" />
                </section>

            </div>
        </div>
    </MenuLayout>
</template>
