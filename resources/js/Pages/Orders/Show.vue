<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 text-[#6D4C4C] sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <Link
                        :href="route('orders.index')"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-[#B46D6D] transition hover:text-[#9E5757]"
                    >
                        <span aria-hidden="true">←</span>
                        <span>Назад до замовлень</span>
                    </Link>

                    <p class="mt-6 text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Деталі замовлення</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Замовлення #{{ order.id }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Створено {{ formattedDate }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[34rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Разом</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ formatPrice(order.total) }} ₴</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Позиції</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ order.items.length }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Товарів</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ totalQuantity }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-8 flex flex-wrap gap-3">
                <span :class="statusClass(order.status)" class="rounded-full px-4 py-2 text-sm font-semibold">
                    {{ statusLabel(order.status) }}
                </span>
                <span :class="paymentStatusClass(order.payment_status)" class="rounded-full px-4 py-2 text-sm font-semibold">
                    {{ paymentStatusLabel(order.payment_status) }}
                </span>
                <span class="rounded-full border border-[#F0DEDE] bg-[#FFF8F8] px-4 py-2 text-sm font-semibold text-[#8D6767]">
                    {{ paymentMethodLabel(order.payment_method) }}
                </span>
                <a
                    :href="route('orders.receipt.show', { order: order.id })"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex rounded-full border border-[#E3BEBE] bg-white px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                >
                    Відкрити чек
                </a>
                <a
                    :href="route('orders.receipt.show', { order: order.id, print: 1 })"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex rounded-full border border-[#E3BEBE] bg-white px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                >
                    Друк чека
                </a>
                <button
                    type="button"
                    class="inline-flex rounded-full border border-[#E3BEBE] bg-white px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                    @click="openReceiptPdf"
                >
                    Завантажити PDF
                </button>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)]">
                    <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h2 class="text-2xl font-bold text-[#B46D6D]">Склад замовлення</h2>
                        <Link
                            :href="route('orders.repeat', { order: order.id })"
                            method="post"
                            as="button"
                            class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                        >
                            Повторити замовлення
                        </Link>
                    </div>

                    <ul class="space-y-4">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex flex-col gap-4 rounded-[1.5rem] border border-[#F3E2E2] bg-[#fffdfd] p-4 sm:flex-row sm:items-center"
                        >
                            <img
                                :src="item.product_image || 'https://placehold.co/180x180/F8E8E8/9A6B6B?text=Diva'"
                                :alt="`Фото товару ${item.product_name}`"
                                class="h-24 w-24 rounded-[1.25rem] object-cover"
                            />

                            <div class="min-w-0 flex-1">
                                <Link
                                    v-if="item.product"
                                    :href="route('products.show', { product: item.product.id })"
                                    class="text-lg font-semibold text-[#6D4C4C] transition hover:text-[#B46D6D]"
                                >
                                    {{ item.product_name }}
                                </Link>
                                <p v-else class="text-lg font-semibold text-[#6D4C4C]">{{ item.product_name }}</p>

                                <p v-if="item.product_category" class="mt-1 text-xs uppercase tracking-[0.25em] text-[#C49B9B]">
                                    {{ item.product_category }}
                                </p>
                                <p v-if="item.product_description" class="mt-2 text-sm leading-6 text-[#8D6767]">
                                    {{ item.product_description }}
                                </p>
                            </div>

                            <div class="text-right text-sm text-[#8D6767]">
                                <p>{{ item.quantity }} × {{ formatPrice(item.price) }} ₴</p>
                                <p class="mt-2 text-lg font-bold text-[#B46D6D]">{{ formatPrice(item.line_total) }} ₴</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)]">
                        <h2 class="text-2xl font-bold text-[#B46D6D]">Підсумок</h2>
                        <div class="mt-5 space-y-3 text-sm text-[#8D6767]">
                            <div class="flex items-center justify-between">
                                <span>Кількість позицій</span>
                                <strong class="text-[#6D4C4C]">{{ order.items.length }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Товарів у замовленні</span>
                                <strong class="text-[#6D4C4C]">{{ totalQuantity }}</strong>
                            </div>
                            <div class="flex items-center justify-between border-t border-[#F1E1E1] pt-3 text-base">
                                <span>Разом</span>
                                <strong class="text-xl text-[#B46D6D]">{{ formatPrice(order.total) }} ₴</strong>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)]">
                        <h2 class="text-2xl font-bold text-[#B46D6D]">Оплата та контакт</h2>
                        <div class="mt-5 space-y-3 text-sm leading-6 text-[#8D6767]">
                            <p><strong class="text-[#6D4C4C]">Отримувач:</strong> {{ order.full_name }}</p>
                            <p><strong class="text-[#6D4C4C]">Електронна пошта:</strong> {{ order.email }}</p>
                            <p><strong class="text-[#6D4C4C]">Спосіб оплати:</strong> {{ paymentMethodLabel(order.payment_method) }}</p>
                            <p v-if="order.payment_provider"><strong class="text-[#6D4C4C]">Провайдер:</strong> {{ order.payment_provider }}</p>
                            <p v-if="order.payment_reference"><strong class="text-[#6D4C4C]">Платіжний референс:</strong> {{ order.payment_reference }}</p>
                        </div>

                        <Link
                            v-if="order.payment_method === 'demo_card' && order.payment_reference"
                            :href="route('payments.show', { paymentReference: order.payment_reference })"
                            class="mt-5 inline-flex rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                        >
                            Відкрити статус оплати
                        </Link>
                    </div>
                </aside>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const props = defineProps({
    order: Object,
})

const formattedDate = computed(() => {
    if (!props.order?.created_at) {
        return 'невідомо'
    }

    return new Date(props.order.created_at).toLocaleString('uk-UA')
})

const totalQuantity = computed(() => props.order.items.reduce((sum, item) => sum + Number(item.quantity ?? 0), 0))

function statusLabel(status) {
    return {
        paid: 'Сплачено',
        pending: 'В очікуванні',
        failed: 'Помилка',
        cancelled: 'Скасовано',
    }[status] ?? status
}

function statusClass(status) {
    return {
        'bg-green-50 text-green-700': status === 'paid',
        'bg-orange-50 text-orange-700': status === 'pending',
        'bg-red-50 text-red-700': status === 'failed',
        'bg-gray-100 text-gray-600': status === 'cancelled',
    }
}

function paymentStatusLabel(status) {
    return {
        paid: 'Оплачено',
        pending: 'Оплата очікується',
        failed: 'Оплата неуспішна',
        cancelled: 'Оплату скасовано',
    }[status] ?? status
}

function paymentStatusClass(status) {
    return {
        'bg-emerald-50 text-emerald-700': status === 'paid',
        'bg-amber-50 text-amber-700': status === 'pending',
        'bg-rose-50 text-rose-700': status === 'failed',
        'bg-slate-100 text-slate-600': status === 'cancelled',
    }
}

function paymentMethodLabel(paymentMethod) {
    return {
        demo_card: 'Демо-картка',
        cash_on_delivery: 'Післяплата',
    }[paymentMethod] ?? paymentMethod
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}

function openReceiptPdf() {
    window.open(route('orders.receipt.download', { order: props.order.id }), '_blank', 'noopener,noreferrer')
}
</script>
