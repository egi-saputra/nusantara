<template>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:scale-105 transform transition duration-300">
        <img :src="image" :alt="title" class="w-full h-48 object-cover" />
        <div class="p-4 text-left">
            <h3 class="font-semibold mb-2">{{ title }}</h3>
            <p class="text-gray-600">{{ description }}</p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    description: String,
    image: String
})
</script>
