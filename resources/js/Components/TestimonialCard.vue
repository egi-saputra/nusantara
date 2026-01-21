<template>
    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-2xl mx-auto">
        <p class="italic mb-4">{{ testimonial.text }}</p>
        <h4 class="font-semibold">{{ testimonial.name }}</h4>
    </div>
</template>

<script setup>
defineProps({
    testimonial: Object
})
</script>
