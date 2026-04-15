<template>
    <header class="bg-white py-4 relative">
        <div class="container mx-auto flex items-center justify-between text-[#B46D6D] relative">
            <!-- Левая навигация -->
            <div class="flex items-center gap-10 z-10">
                <Link :href="route('home')" class="flex flex-col items-center hover:opacity-70">
                    <HomeIcon class="h-6 w-6"/>
                    <span class="text-sm">Головна</span>
                </Link>
                <Link :href="route('catalog')" class="flex flex-col items-center hover:opacity-70">
                    <SparklesIcon class="h-6 w-6"/>
                    <span class="text-sm">Каталог</span>
                </Link>
                <Link href="/contacts" class="flex flex-col items-center hover:opacity-70">
                    <PhoneIcon class="h-6 w-6"/>
                    <span class="text-sm">Контакти</span>
                </Link>
            </div>

            <!-- Центрированный логотип -->
            <div class="absolute left-1/2 transform -translate-x-1/2 text-5xl font-extrabold tracking-wide z-0">
                DIVA
            </div>

            <!-- Правая навигация -->
            <div class="flex items-center gap-10 z-10">
                <template v-if="user">
                    <Link :href="route('profile.edit')" class="flex flex-col items-center hover:opacity-70">
                        <UserIcon class="h-6 w-6"/>
                        <span class="text-sm">{{ user.name }}</span>
                    </Link>
                    <Link :href="route('orders.index')" class="flex flex-col items-center hover:opacity-70">
                        <TruckIcon class="h-6 w-6"/>
                        <span class="text-sm">Мої замовлення</span>
                    </Link>
                </template>
                <template v-else>
                    <Link :href="route('login')" class="flex flex-col items-center hover:opacity-70">
                        <ArrowRightOnRectangleIcon class="h-6 w-6"/>
                        <span class="text-sm">Увійти</span>
                    </Link>
                    <Link :href="route('register')" class="flex flex-col items-center hover:opacity-70">
                        <UserPlusIcon class="h-6 w-6"/>
                        <span class="text-sm">Реєстрація</span>
                    </Link>
                </template>
                <Link :href="route('cart.index')" class="relative flex flex-col items-center hover:opacity-70">
                    <ShoppingCartIcon class="h-6 w-6"/>
                    <span class="text-sm">Кошик</span>
                    <span v-if="cartCount > 0"
                          class="absolute top-0 right-0 bg-red-600 text-white rounded-full text-xs px-1 transform translate-x-2 -translate-y-2">
            {{ cartCount }}
        </span>
                </Link>
            </div>
        </div>
    </header>
</template>

<script setup>
import {computed, watch} from 'vue'
import {Link, usePage} from '@inertiajs/vue3'
import {useCartUi} from '@/composables/useCartUi'

import {HomeIcon, PhoneIcon, UserIcon, ShoppingCartIcon, SparklesIcon, TruckIcon, ArrowRightOnRectangleIcon, UserPlusIcon} from '@heroicons/vue/24/outline'

const page = usePage()
const {count, sync} = useCartUi()
const user = computed(() => page.props.auth?.user)
const cartCount = computed(() => count.value ?? page.props.cartCount ?? 0)

watch(() => page.props.cartCount ?? 0, (value) => {
    sync(value)
}, {immediate: true})
</script>
