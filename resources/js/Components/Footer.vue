<template>
    <footer class="border-t border-[#f3dddd] bg-[linear-gradient(180deg,_#fffafa_0%,_#fdf1f4_100%)] text-[#6d5d5d] shadow-inner">
        <div class="mx-auto grid w-full max-w-[1480px] gap-8 px-4 py-10 md:grid-cols-[1.2fr_0.8fr_0.8fr] md:px-6 xl:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#cf9797]">Jewelry House</p>
                <h3 class="mt-3 text-3xl font-black tracking-[0.25em] text-[#B46D6D]">DIVA</h3>
                <p class="mt-4 max-w-md text-sm leading-7 text-[#7a6666]">
                    A jewelry storefront with a soft romantic presentation, fast catalog browsing, and a smooth checkout flow.
                </p>
            </div>

            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#cf9797]">Navigation</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="/" class="rounded-full border border-[#f1d8d8] px-4 py-2 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#fff1f1]">Home</a>
                    <a href="/catalog" class="rounded-full border border-[#f1d8d8] px-4 py-2 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#fff1f1]">Catalog</a>
                    <a href="/contacts" class="rounded-full border border-[#f1d8d8] px-4 py-2 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#fff1f1]">Contacts</a>
                </div>
            </div>

            <div class="md:text-right">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#cf9797]">Social</p>
                <div class="mt-4 flex justify-start gap-4 md:justify-end">
                    <a href="https://t.me/" target="_blank" class="social-link" aria-label="Telegram">
                    <i class="fab fa-telegram-plane text-2xl"></i>
                </a>
                    <a href="https://facebook.com/" target="_blank" class="social-link" aria-label="Facebook">
                    <i class="fab fa-facebook-f text-2xl"></i>
                </a>
                    <a href="https://www.tiktok.com" target="_blank" class="social-link" aria-label="TikTok">
                    <i class="fab fa-tiktok text-2xl"></i>
                </a>
                    <a href="https://www.youtube.com" target="_blank" class="social-link" aria-label="YouTube">
                    <i class="fab fa-youtube text-2xl"></i>
                </a>
                </div>

                <p class="mt-5 text-sm text-[#7a6666]">© {{ new Date().getFullYear() }} All rights reserved</p>
            </div>
        </div>
    </footer>
</template>

<script setup>

</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');

.social-link {
    @apply inline-flex h-11 w-11 items-center justify-center rounded-full border border-[#f1d8d8] bg-white text-[#B46D6D] transition hover:bg-[#fff1f1];
}
</style>
