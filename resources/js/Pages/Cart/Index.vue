<template>
    <page-layout>
        <div class="min-h-screen flex flex-col justify-between">
            <div class="container mx-auto px-6 py-12 max-w-4xl flex-grow">
                <h1 class="text-4xl font-extrabold tracking-wide text-center mb-8 text-[#B46D6D]">Кошик</h1>

                <!-- Вміст кошика -->
                <div v-if="items.length">
                    <div v-for="item in props.items" :key="item.id" class="flex items-center space-x-6 border-b py-4">
                        <!-- Зображення товару -->
                        <img :src="item.product.image_path" alt="Зображення товару"
                             class="w-24 h-24 object-cover rounded-lg shadow-md">

                        <!-- Інформація про товар -->
                        <div class="flex flex-col flex-grow">
                            <span class="font-semibold text-lg">{{ item.product.name }}</span>
                            <p class="text-sm text-gray-500">Кількість: {{ item.quantity }}</p>
                        </div>

                        <!-- Ціна та видалення -->
                        <div class="text-right">
                            <p class="font-semibold text-xl text-[#B46D6D]">{{ item.product.price * item.quantity }}
                                ₴</p>
                            <button
                                type="button"
                                @click="removeFromCart(item.product_id)"
                                class="mt-2 text-red-600 hover:underline text-sm"
                            >
                                Видалити
                            </button>
                        </div>
                    </div>

                    <!-- Загальна сума та перехід до оплати -->
                    <div class="mt-6 flex justify-between items-center">
                        <p class="text-2xl font-bold text-[#B46D6D]">Разом: {{ total }} ₴</p>
                        <Link href="/checkout"
                              class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors duration-300">
                            Перейти до оплати
                        </Link>
                    </div>
                </div>

                <!-- Якщо кошик порожній -->
                <div v-else class="text-center text-gray-500">
                    <p>Кошик порожній</p>
                </div>
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import {Link, router} from '@inertiajs/vue3'
import PageLayout from "@/Components/page-layout.vue";
import {computed} from "vue";

const props = defineProps({items: Array})

const total = computed(() => {
    return props.items.reduce((sum, item) => sum + item.product.price * item.quantity, 0)
})

function removeFromCart(productId) {
    router.post(route('cart.remove'), {
        product_id: productId,
    }, {
        preserveScroll: true,
    })
}
</script>
