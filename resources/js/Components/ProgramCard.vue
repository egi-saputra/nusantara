<template>
    <div
        :class="`bg-gradient-to-r ${gradient} rounded-2xl shadow-lg p-8 text-white hover:scale-105 transform transition duration-300 flex items-start gap-4`">

        <div class="text-4xl">
            {{ icon }}
        </div>

        <div>
            <h3 class="text-2xl font-semibold mb-2">{{ title }}</h3>
            <p>{{ description }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    description: String,
    gradient: String,
    icon: String
})
</script>
