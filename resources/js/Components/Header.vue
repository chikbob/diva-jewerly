<template>
    <header class="border-b border-[#f3dddd] bg-white/90 px-4 py-4 backdrop-blur">
        <div class="mx-auto flex w-full max-w-[1480px] flex-col gap-4 text-[#B46D6D] lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center justify-between gap-4">
                <Link :href="route('home')" class="flex items-center gap-3 rounded-full border border-[#f1d8d8] bg-[#fff9f9] px-4 py-3 transition hover:bg-[#fff1f1]">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#fce8e8]">
                        <SparklesIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-[#cf9797]">Jewelry House</p>
                        <p class="text-2xl font-black tracking-[0.28em] text-[#B46D6D]">DIVA</p>
                    </div>
                </Link>

                <Link
                    :href="route('cart.index')"
                    class="relative inline-flex h-12 w-12 items-center justify-center rounded-full border border-[#f1d8d8] bg-[#fff8f8] transition hover:bg-[#fff1f1] lg:hidden"
                    aria-label="Кошик"
                >
                    <ShoppingCartIcon class="h-6 w-6" />
                    <span
                        v-if="cartCount > 0"
                        class="absolute -right-1 -top-1 min-w-[1.25rem] rounded-full bg-[#B46D6D] px-1 text-center text-xs font-semibold text-white"
                    >
                        {{ cartCount }}
                    </span>
                </Link>
            </div>

            <div class="flex flex-col gap-3 lg:flex-1 lg:flex-row lg:items-center lg:justify-end lg:gap-4">
                <nav class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:justify-end sm:gap-3">
                    <Link :href="route('home')" class="nav-pill">
                        <HomeIcon class="h-5 w-5" />
                        <span>Головна</span>
                    </Link>
                    <Link :href="route('catalog')" class="nav-pill">
                        <SparklesIcon class="h-5 w-5" />
                        <span>Каталог</span>
                    </Link>
                    <Link href="/contacts" class="nav-pill">
                        <PhoneIcon class="h-5 w-5" />
                        <span>Контакти</span>
                    </Link>
                </nav>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <template v-if="user">
                        <Link :href="route('profile.edit')" class="nav-pill">
                            <UserIcon class="h-5 w-5" />
                            <span class="truncate max-w-[11rem]">{{ user.name }}</span>
                        </Link>
                        <Link :href="route('favorites.index')" class="nav-pill relative">
                            <HeartIcon class="h-5 w-5" />
                            <span>Обране</span>
                            <span
                                v-if="favoritesCount > 0"
                                class="absolute -right-1 -top-1 min-w-[1.2rem] rounded-full bg-[#B46D6D] px-1 text-center text-[0.7rem] font-semibold text-white"
                            >
                                {{ favoritesCount }}
                            </span>
                        </Link>
                        <Link :href="route('orders.index')" class="nav-pill">
                            <TruckIcon class="h-5 w-5" />
                            <span>Замовлення</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="nav-pill">
                            <ArrowRightOnRectangleIcon class="h-5 w-5" />
                            <span>Увійти</span>
                        </Link>
                        <Link :href="route('register')" class="nav-pill">
                            <UserPlusIcon class="h-5 w-5" />
                            <span>Реєстрація</span>
                        </Link>
                    </template>

                    <Link :href="route('cart.index')" class="nav-pill relative hidden lg:inline-flex">
                        <ShoppingCartIcon class="h-5 w-5" />
                        <span>Кошик</span>
                        <span
                            v-if="cartCount > 0"
                            class="absolute -right-1 -top-1 min-w-[1.2rem] rounded-full bg-[#B46D6D] px-1 text-center text-[0.7rem] font-semibold text-white"
                        >
                            {{ cartCount }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useCartUi } from '@/composables/useCartUi'

import { ArrowRightOnRectangleIcon, HeartIcon, HomeIcon, PhoneIcon, ShoppingCartIcon, SparklesIcon, TruckIcon, UserIcon, UserPlusIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const { count, sync } = useCartUi()
const user = computed(() => page.props.auth?.user)
const cartCount = computed(() => count.value ?? page.props.cartCount ?? 0)
const favoritesCount = computed(() => page.props.favoritesCount ?? 0)

watch(() => page.props.cartCount ?? 0, (value) => {
    sync(value)
}, { immediate: true })
</script>

<style scoped>
.nav-pill {
    @apply inline-flex items-center justify-center gap-2 rounded-full border border-[#f1d8d8] bg-[#fff8f8] px-4 py-3 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#fff1f1];
}
</style>
