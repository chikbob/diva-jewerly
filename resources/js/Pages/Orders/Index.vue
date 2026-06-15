<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Історія замовлень</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Мої замовлення</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Контролюйте статуси, переглядайте склад попередніх замовлень і швидко повторюйте покупку.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[26rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Замовлення</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ orders.length }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Фільтр</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ activeSummary }}</p>
                    </div>
                </div>
            </div>

            <form
                class="mb-8 rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)]"
                @submit.prevent="applyFilters"
            >
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="flex flex-col gap-2 text-sm font-medium text-[#6D4C4C]">
                        <span>Статус</span>
                        <select
                            v-model="localFilters.status"
                            class="rounded-2xl border border-[#E3BEBE] px-4 py-3 focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                        >
                            <option v-for="option in statusOptions" :key="option.value || 'all'" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 text-sm font-medium text-[#6D4C4C]">
                        <span>Статус оплати</span>
                        <select
                            v-model="localFilters.payment_status"
                            class="rounded-2xl border border-[#E3BEBE] px-4 py-3 focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                        >
                            <option v-for="option in paymentStatusOptions" :key="`payment-${option.value || 'all'}`" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 text-sm font-medium text-[#6D4C4C]">
                        <span>Сортування</span>
                        <select
                            v-model="localFilters.sort"
                            class="rounded-2xl border border-[#E3BEBE] px-4 py-3 focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <button
                        type="submit"
                        :disabled="isFiltering"
                        class="rounded-full bg-[#B46D6D] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ isFiltering ? 'Оновлюємо...' : 'Застосувати' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-[#E3BEBE] px-5 py-3 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                        @click="resetFilters"
                    >
                        Скинути
                    </button>
                </div>
            </form>

            <div v-if="orders.length === 0" class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center text-[#8D6767]">
                У вас ще немає замовлень за поточними фільтрами.
            </div>

            <div v-else class="space-y-6" :aria-busy="isFiltering ? 'true' : 'false'">
                <article
                    v-for="order in orders"
                    :key="order.id"
                    class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_60px_rgba(180,109,109,0.1)]"
                >
                    <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[#C49B9B]">Замовлення</p>
                            <Link
                                :href="route('orders.show', { order: order.id })"
                                class="mt-2 inline-flex text-2xl font-bold text-[#B46D6D] transition hover:text-[#9E5757]"
                            >
                                #{{ order.id }}
                            </Link>
                            <p class="mt-2 text-sm text-[#8D6767]">{{ new Date(order.created_at).toLocaleString('uk-UA') }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 text-sm text-[#8D6767]">
                            <span :class="statusClass(order.status)" class="rounded-full px-4 py-2 font-semibold">
                                {{ statusLabel(order.status) }}
                            </span>
                            <span :class="paymentStatusClass(order.payment_status)" class="rounded-full px-4 py-2 font-semibold">
                                {{ paymentStatusLabel(order.payment_status) }}
                            </span>
                            <span class="rounded-full bg-[#FFF4F4] px-4 py-2 font-semibold text-[#A05F5F]">
                                {{ formatPrice(order.total) }} ₴
                            </span>
                        </div>
                    </div>

                    <ul class="space-y-4">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex flex-col gap-4 rounded-[1.5rem] border border-[#F3E2E2] bg-[#fffdfd] p-4 sm:flex-row sm:items-center"
                        >
                            <img
                                :src="item.product_image || 'https://placehold.co/160x160/F8E8E8/9A6B6B?text=Diva'"
                                :alt="`Фото товару ${item.product_name}`"
                                class="h-20 w-20 rounded-[1.25rem] object-cover"
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
                                <p v-if="item.product_description" class="mt-2 text-sm leading-6 text-[#8D6767]">{{ item.product_description }}</p>
                            </div>
                            <div class="text-sm font-semibold text-[#6D4C4C]">
                                {{ item.quantity }} × {{ formatPrice(item.price) }} ₴
                            </div>
                        </li>
                    </ul>

                    <div class="mt-5 flex flex-col gap-4 text-sm text-[#8D6767] xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full border border-[#F0DEDE] bg-[#FFF8F8] px-4 py-2">Платіжний референс: {{ order.payment_reference }}</span>
                            <a
                                :href="route('orders.receipt.show', { order: order.id })"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                            >
                                Відкрити чек
                            </a>
                            <a
                                :href="route('orders.receipt.show', { order: order.id, print: 1 })"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                            >
                                Друк чека
                            </a>
                            <a
                                :href="route('orders.receipt.download', { order: order.id })"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                            >
                                Завантажити PDF
                            </a>
                            <Link
                                :href="route('orders.repeat', { order: order.id })"
                                method="post"
                                as="button"
                                class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#FFF1F1]"
                            >
                                Повторити замовлення
                            </Link>
                            <Link
                                v-if="order.payment_method === 'demo_card'"
                                :href="route('payments.show', { paymentReference: order.payment_reference })"
                                class="rounded-full border border-[#E3BEBE] px-4 py-2 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#FFF1F1]"
                            >
                                Відкрити статус оплати
                            </Link>
                            <Link
                                :href="route('orders.show', { order: order.id })"
                                class="rounded-full bg-[#B46D6D] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                            >
                                Деталі замовлення
                            </Link>
                        </div>
                        <strong class="text-lg text-[#B46D6D]">Разом: {{ formatPrice(order.total) }} ₴</strong>
                    </div>
                </article>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const props = defineProps({
    orders: Array,
    filters: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
    sortOptions: Array,
})

const isFiltering = ref(false)
const localFilters = ref(buildFilters(props.filters))
const activeSummary = computed(() => {
    if (localFilters.value.status) {
        const option = props.statusOptions.find((item) => item.value === localFilters.value.status)

        return option?.label ?? 'Обраний статус'
    }

    if (localFilters.value.payment_status) {
        const option = props.paymentStatusOptions.find((item) => item.value === localFilters.value.payment_status)

        return option?.label ?? 'Фільтр оплати'
    }

    return 'Усі замовлення'
})

watch(() => props.filters, (filters) => {
    localFilters.value = buildFilters(filters)
}, { deep: true })

function buildFilters(filters = {}) {
    return {
        status: filters.status ?? '',
        payment_status: filters.payment_status ?? '',
        sort: filters.sort ?? 'newest',
    }
}

function applyFilters() {
    router.get(route('orders.index'), normalizedFilters(), {
        preserveScroll: true,
        preserveState: true,
        onStart: () => {
            isFiltering.value = true
        },
        onFinish: () => {
            isFiltering.value = false
        },
    })
}

function resetFilters() {
    localFilters.value = buildFilters({})
    applyFilters()
}

function normalizedFilters() {
    return Object.fromEntries(
        Object.entries(localFilters.value).filter(([, value]) => value !== '' && value !== null)
    )
}

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

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}
</script>
