<template>
    <div class="landing-page font-sans text-gray-900">

        <!-- Navbar -->
        <header class="fixed w-full bg-white shadow-md z-50">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <h1 class="font-bold text-xl text-blue-900">SMK NUSANTARA</h1>
                <nav class="space-x-6 flex items-center">
                    <a href="#about" @click.prevent="scrollTo('#about')"
                        class="text-gray-700 hover:text-yellow-400 transition">About</a>
                    <a href="#program" @click.prevent="scrollTo('#program')"
                        class="text-gray-700 hover:text-yellow-400 transition">Program</a>
                    <a href="#articles" @click.prevent="scrollTo('#articles')"
                        class="text-gray-700 hover:text-yellow-400 transition">Articles</a>
                    <a href="#activities" @click.prevent="scrollTo('#activities')"
                        class="text-gray-700 hover:text-yellow-400 transition">Activities</a>
                    <a href="#partners" @click.prevent="scrollTo('#partners')"
                        class="text-gray-700 hover:text-yellow-400 transition">Partners</a>
                    <!-- Language Toggle -->
                    <!-- <select v-model="currentLang" class="ml-4 border rounded px-2 py-1">
                        <option value="id">ID</option>
                        <option value="en">EN</option>
                    </select> -->
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative h-screen flex items-center justify-center overflow-hidden fade-up">
            <video autoplay muted loop class="absolute w-full h-full object-cover">
                <source src="/videos/school-hero.mp4" type="video/mp4">
            </video>
            <div class="relative z-10 text-center text-white px-6">
                <h1 class="text-5xl lg:text-6xl font-bold mb-4 drop-shadow-lg">
                    {{ t.heroTitle }}
                </h1>
                <p class="text-lg lg:text-xl mb-6 drop-shadow-md">{{ t.heroSubtitle }}</p>
                <Link href="#program"
                    class="bg-yellow-400 hover:bg-yellow-300 text-gray-900 font-semibold py-3 px-6 rounded-lg shadow-lg transition-all duration-300">
                    {{ t.ctaButton }}
                </Link>
            </div>
            <div class="absolute inset-0 bg-black/40"></div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-24 bg-gray-50 fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-6">{{ t.aboutTitle }}</h2>
                <p class="max-w-3xl mx-auto text-gray-700 text-lg mb-12">{{ t.aboutDesc }}</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <Card title="Fasilitas Modern"
                        description="Laboratorium, workshop, ruang multimedia, perpustakaan lengkap." icon="🏫" />
                    <Card title="Guru Profesional" description="Tenaga pengajar bersertifikasi dan berpengalaman."
                        icon="👨‍🏫" />
                    <Card title="Program Unggulan"
                        description="Program keahlian berbasis industri untuk karir masa depan." icon="💼" />
                    <Card title="Kegiatan Kreatif"
                        description="Organisasi, lomba, dan mading untuk pengembangan karakter." icon="🎨" />
                </div>
            </div>
        </section>

        <!-- Program Section -->
        <section id="program" class="py-24 relative fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-12">{{ t.programTitle }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 fade-up">
                    <ProgramCard title="Manajemen Perkantoran Lembaga Bisnis"
                        description="Belajar administrasi kantor, manajemen dokumen, dan komunikasi profesional."
                        gradient="from-blue-900 to-blue-700" icon="🏢" />
                    <ProgramCard title="Bisnis Retail"
                        description="Belajar strategi pemasaran, digital marketing, dan branding produk."
                        gradient="from-red-800 to-red-600" icon="📈" />
                </div>

            </div>
        </section>

        <!-- Articles Section -->
        <section id="articles" class="py-24 bg-white fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-12">{{ t.articlesTitle }}</h2>
                <div class="flex overflow-x-auto gap-6 pb-4">
                    <ArticleCard title="Pameran Kreatif Siswa"
                        excerpt="Siswa memamerkan karya kreatif mereka di auditorium sekolah."
                        image="/images/artikel1.jpg" />
                    <ArticleCard title="Pelatihan Industri IT"
                        excerpt="Siswa belajar langsung dari profesional IT di industri."
                        image="/images/artikel2.jpg" />
                    <ArticleCard title="Workshop Akuntansi"
                        excerpt="Workshop praktik akuntansi modern bagi siswa kelas XI." image="/images/artikel3.jpg" />
                </div>
            </div>
        </section>

        <!-- Activities Section -->
        <section id="activities" class="py-24 bg-gray-50 fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-12">{{ t.activitiesTitle }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <ActivityCard title="Lomba Robotik"
                        description="Tim robotik sekolah memenangkan lomba tingkat provinsi."
                        image="/images/kegiatan1.jpg" />
                    <ActivityCard title="Pramuka & Outbond"
                        description="Siswa aktif mengikuti kegiatan pramuka dan outbond."
                        image="/images/kegiatan2.jpg" />
                    <ActivityCard title="Festival Seni" description="Festival seni tahunan menampilkan bakat siswa."
                        image="/images/kegiatan3.jpg" />
                </div>
            </div>
        </section>

        <!-- Partners Section -->
        <section id="partners" class="py-24 bg-blue-900 text-white fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-12">{{ t.partnersTitle }}</h2>
                <div class="flex items-center justify-center gap-12 overflow-x-auto" ref="partnersContainer">
                    <!-- <img v-for="n in 4" :key="n" :src=`/images/partner${n}.png` :alt=`Partner ${n}`
                        class="h-16 object-contain" /> -->
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-24 bg-gray-100 fade-up">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl font-bold mb-12">{{ t.testimonialsTitle }}</h2>
                <div class="relative flex items-center justify-center">
                    <button @click="prevTestimonial"
                        class="absolute left-0 p-3 bg-yellow-400 rounded-full shadow-lg hover:bg-yellow-300 transition">&lt;</button>
                    <div class="w-full max-w-3xl">
                        <TestimonialCard :testimonial="testimonials[current]" />
                    </div>
                    <button @click="nextTestimonial"
                        class="absolute right-0 p-3 bg-yellow-400 rounded-full shadow-lg hover:bg-yellow-300 transition">&gt;</button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-blue-900 text-white py-12 fade-up">
            <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-2">SMK NUSANTARA</h3>
                    <p>Jl. Pendidikan No. 123, Kota Kreatif, Indonesia</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2">Kontak</h3>
                    <p>Email: info@smknusantara.sch.id</p>
                    <p>Telp: (021) 1234567</p>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2">Sosial Media</h3>
                    <p>Instagram: @smknusantara</p>
                    <p>Facebook: SMK NUSANTARA</p>
                </div>
            </div>
            <p class="text-center mt-8">&copy; 2026 SMK NUSANTARA. All rights reserved.</p>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'

import Card from '../Components/Card.vue'
import ProgramCard from '../Components/ProgramCard.vue'
import ArticleCard from '../Components/ArticleCard.vue'
import ActivityCard from '../Components/ActivityCard.vue'
import TestimonialCard from '../Components/TestimonialCard.vue'
import translations from '../i18n/translations.js'

// Language
const currentLang = ref('id')
const t = computed(() => translations[currentLang.value])

// Testimonials
const testimonials = [
    { text: "SMK NUSANTARA memberikan pengalaman belajar luar biasa.", name: "Rina Siswa" },
    { text: "Program keahlian mempersiapkan siswa untuk dunia kerja.", name: "Budi Alumni" },
    { text: "Lingkungan sekolah mendukung kreativitas siswa.", name: "Ibu Lina, Orang Tua" },
]
const current = ref(0)
const nextTestimonial = () => current.value = (current.value + 1) % testimonials.length
const prevTestimonial = () => current.value = (current.value - 1 + testimonials.length) % testimonials.length

// Smooth scroll
const scrollTo = (selector) => {
    const el = document.querySelector(selector)
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

// Scroll-trigger animations + stagger
onMounted(() => {
    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('opacity-100', 'translate-y-0')
                    entry.target.classList.remove('opacity-0', 'translate-y-10')
                }
            })
        },
        { threshold: 0.2 }
    )
    document.querySelectorAll('.fade-up').forEach((el, i) => {
        el.classList.add('opacity-0', 'translate-y-10', 'transition-all', 'duration-700', 'ease-out')
        el.style.transitionDelay = `${i * 150}ms`
        observer.observe(el)
    })

    // Autoplay testimonial carousel
    setInterval(() => { nextTestimonial() }, 5000)
})
</script>

<style scoped>
.landing-page video {
    animation: fadeIn 2s ease-in-out;
}

@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.landing-page ::-webkit-scrollbar {
    height: 6px;
}

.landing-page ::-webkit-scrollbar-thumb {
    background: #facc15;
    border-radius: 3px;
}
</style>
