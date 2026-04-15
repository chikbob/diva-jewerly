<template>
    <footer class="bg-gradient-to-tr from-[#FBECEC] to-[#FFF8F8] text-[#4A4A4A] py-8 shadow-inner">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left">
                <h3 class="text-xl font-bold text-[#B46D6D]">DIVA Boutique</h3>
                <p class="text-sm mt-1">© {{ new Date().getFullYear() }} Усі права захищені</p>
            </div>

            <div class="flex space-x-6">
                <a href="https://t.me/" target="_blank" class="hover:text-[#B46D6D]" aria-label="Telegram">
                    <i class="fab fa-telegram-plane text-2xl"></i>
                </a>
                <a href="https://facebook.com/" target="_blank" class="hover:text-[#B46D6D]" aria-label="Facebook">
                    <i class="fab fa-facebook-f text-2xl"></i>
                </a>
                <a href="https://www.tiktok.com" target="_blank" class="hover:text-[#B46D6D]" aria-label="TikTok">
                    <i class="fab fa-tiktok text-2xl"></i>
                </a>
                <a href="https://www.youtube.com" target="_blank" class="hover:text-[#B46D6D]" aria-label="YouTube">
                    <i class="fab fa-youtube text-2xl"></i>
                </a>
            </div>
        </div>
    </footer>
</template>

<script setup>

</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
</style>
