<template>
    <page-layout>
        <div class="min-h-dvh flex flex-col justify-between">
            <div class="container mx-auto px-6 py-12 max-w-5xl flex-grow">
                <h1 class="text-4xl font-extrabold tracking-wide text-center mb-8 text-[#B46D6D]">Мої замовлення</h1>

                <div v-if="orders.length === 0" class="text-center text-gray-400">
                    У вас ще немає замовлень.
                </div>

                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="border rounded-2xl p-6 mb-8 bg-white shadow-md transition-transform duration-200 hover:shadow-lg hover:scale-[1.01]"
                >
                    <div class="mb-4 flex justify-between items-center">
                        <div>
                            <strong class="text-lg font-semibold">Номер замовлення: </strong>
                            <span class="text-[#B46D6D]">#{{ order.id }}</span>
                        </div>
                        <div>
                            <span
                                :class="[
                                    'text-sm font-semibold',
                                    order.status === 'paid'
                                        ? 'text-green-600'
                                        : order.status === 'cancelled'
                                            ? 'text-gray-500'
                                            : order.status === 'pending'
                                                ? 'text-orange-500'
                                                : 'text-yellow-500'
                                ]"
                            >
                                {{
                                    order.status === 'paid'
                                        ? 'Сплачено'
                                        : order.status === 'cancelled'
                                            ? 'Скасовано'
                                            : order.status === 'pending'
                                                ? 'В очікуванні'
                                                : order.status
                                }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 text-gray-600">
                        <strong class="text-lg">Дата: </strong>
                        <span>{{ new Date(order.created_at).toLocaleString() }}</span>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-xl font-semibold mb-4 text-[#B46D6D]">Товари:</h3>
                        <ul class="space-y-4">
                            <li
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center space-x-4 border-b pb-4"
                            >
                                <img
                                    :src="item.product.image_path"
                                    alt="Зображення товару"
                                    class="w-20 h-20 object-cover rounded-lg shadow-md"
                                />
                                <div class="flex flex-col flex-grow">
                                    <span class="font-semibold text-lg">{{ item.product.name }}</span>
                                    <div class="text-sm text-gray-500">{{ item.product.description }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold">{{ item.quantity }} × {{ item.price }} ₴</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Підсумкова сума -->
                    <div class="mt-6 text-gray-600 text-right">
                        <strong class="text-lg">Сума до сплати: </strong>
                        <span class="text-[#B46D6D] font-bold">{{ order.total }} ₴</span>
                    </div>
                </div>
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import PageLayout from '@/Components/page-layout.vue'

defineProps({
    orders: Array
})
</script>
