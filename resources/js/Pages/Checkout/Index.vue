<template>
    <page-layout>
        <div class="min-h-screen flex flex-col justify-between">
            <div class="container mx-auto px-6 py-16 max-w-6xl flex-grow">
                <h1 class="text-4xl font-extrabold tracking-wide text-center mb-12 text-[#B46D6D]">Оформлення замовлення</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Товари -->
                    <div class="border p-8 rounded-xl bg-white shadow-lg max-w-3xl w-full">
                        <h2 class="text-2xl font-semibold mb-6 text-[#B46D6D]">Ваше замовлення</h2>
                        <ul class="space-y-6">
                            <li v-for="item in items" :key="item.id" class="flex items-center justify-between">
                                <div class="flex items-center space-x-6">
                                    <img :src="item.product.image_path" alt="Зображення товару"
                                         class="w-20 h-20 object-cover rounded-md shadow-md">
                                    <span class="text-lg font-medium">{{ item.product.name }} × {{
                                            item.quantity
                                        }}</span>
                                </div>
                                <span class="font-semibold text-[#B46D6D] text-lg">{{
                                        item.quantity * item.product.price
                                    }} грн</span>
                            </li>
                        </ul>
                        <p class="font-bold mt-6 text-xl text-[#B46D6D]">
                            Загальна сума: {{ total }} грн
                        </p>
                    </div>

                    <!-- Форма -->
                    <form @submit.prevent="submit" class="space-y-8">
                        <div class="mb-6">
                            <label class="block mb-2 text-lg font-semibold">ПІБ</label>
                            <input v-model="form.full_name" type="text" placeholder="Введіть ваше ПІБ"
                                   class="w-full border rounded-lg px-6 py-3 shadow-md focus:outline-none focus:ring-2 focus:ring-[#B46D6D]"/>
                            <p v-if="form.errors.full_name" class="mt-2 text-sm text-red-600">{{ form.errors.full_name }}</p>
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 text-lg font-semibold">Email</label>
                            <input v-model="form.email" type="email" placeholder="Введіть ваш email"
                                   class="w-full border rounded-lg px-6 py-3 shadow-md focus:outline-none focus:ring-2 focus:ring-[#B46D6D]"/>
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 text-lg font-semibold">Спосіб оплати</label>
                            <select
                                v-model="form.payment_method"
                                class="w-full border rounded-lg px-6 py-3 shadow-md focus:outline-none focus:ring-2 focus:ring-[#B46D6D]"
                            >
                                <option value="demo_card">Демо-оплата карткою</option>
                                <option value="cash_on_delivery">Післяплата</option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">
                                Номер картки не запитується і не зберігається. Замовлення створюється лише з референсом платежу.
                            </p>
                            <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">{{ form.errors.payment_method }}</p>
                        </div>

                        <p v-if="form.errors.cart" class="text-sm text-red-600">{{ form.errors.cart }}</p>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-green-500 text-white px-6 py-4 rounded-lg hover:bg-green-600 transition-colors duration-300">
                            {{ form.processing ? 'Обробка...' : 'Підтвердити замовлення' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import {useForm} from '@inertiajs/vue3'
import {computed} from 'vue'
import PageLayout from "@/Components/page-layout.vue";

const props = defineProps({items: Array})

const form = useForm({
    full_name: '',
    email: '',
    payment_method: 'demo_card'
})

const total = computed(() =>
    props.items.reduce((sum, item) => sum + item.quantity * item.product.price, 0)
)

function submit() {
    form.post('/checkout')
}
</script>

<style scoped>
.container {
    /* Убираем фон полупрозрачный */
    background: none;
}

input::placeholder {
    color: #B46D6D;
}
</style>
