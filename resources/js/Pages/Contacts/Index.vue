<template>
    <PageLayout>
        <div class="container mx-auto px-4 py-12 text-[#4A4A4A]">
            <h1 class="text-4xl font-extrabold tracking-wide text-center text-[#B46D6D] mb-12">Контакти</h1>

            <!-- Контактна інформація -->
            <div class="grid md:grid-cols-3 gap-8 mb-16 text-center">
                <div class="space-y-2">
                    <i class="fa fa-phone-alt text-3xl text-[#B46D6D] mb-2"></i>
                    <h3 class="font-semibold text-xl">Телефон</h3>
                    <p class="text-lg">+38 (099) 999-99-99</p>
                </div>
                <div class="space-y-2">
                    <i class="fa fa-envelope text-3xl text-[#B46D6D] mb-2"></i>
                    <h3 class="font-semibold text-xl">Email</h3>
                    <p class="text-lg">support@diva-jewels.com</p>
                </div>
                <div class="space-y-2">
                    <i class="fa fa-map-marker-alt text-3xl text-[#B46D6D] mb-2"></i>
                    <h3 class="font-semibold text-xl">Адреса</h3>
                    <p class="text-lg">м. Київ, вул. Хрещатик, 44</p>
                </div>
            </div>

            <!-- Карта -->
            <div class="mb-16">
                <h2 class="text-3xl font-semibold mb-6 text-center text-[#B46D6D]">Наш магазин на карті</h2>
                <div class="w-full h-[400px] rounded-lg overflow-hidden shadow-lg">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1510.8010672219639!2d30.519689886089814!3d50.443812570980555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40d4ce56094d696b%3A0x2eec283b33169e54!2z0YPQuy4g0JrRgNC10YnQsNGC0LjQuiwgNDQsINCa0LjQtdCyLCDQo9C60YDQsNC40L3QsCwgMDIwMDA!5e0!3m2!1sru!2sde!4v1753179858403!5m2!1sru!2sde"
                        width="1800" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <!-- Соціальні мережі -->
            <div class="text-center">
                <h2 class="text-3xl font-semibold mb-6 text-[#B46D6D]">Ми у соціальних мережах</h2>
                <p class="text-lg mb-6">Слідкуйте за новинами та акціями у наших соцмережах:</p>
                <div class="flex justify-center space-x-6 text-[#B46D6D]">
                    <a href="https://t.me/" target="_blank" rel="noopener noreferrer"
                       class="text-3xl hover:text-[#9e5757]">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                    <a href="https://facebook.com/" target="_blank" rel="noopener noreferrer"
                       class="text-3xl hover:text-[#9e5757]">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer"
                       class="text-3xl hover:text-[#9e5757]">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://www.youtube.com" target="_blank" rel="noopener noreferrer"
                       class="text-3xl hover:text-[#9e5757]">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </PageLayout>
</template>

<script setup>
import PageLayout from "@/Components/page-layout.vue";
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');

.fa {
    transition: color 0.3s;
}
</style>
