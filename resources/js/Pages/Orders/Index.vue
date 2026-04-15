<template>
    <page-layout>
        <section class="container mx-auto max-w-5xl px-6 py-12">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Order History</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Мої замовлення</h1>
                </div>

                <div class="rounded-2xl border border-[#E9CFCF] bg-[#FFF8F8] px-4 py-3 text-sm text-[#8D6767]">
                    <strong class="text-[#B46D6D]">{{ orders.length }}</strong> замовлень у поточній вибірці
                </div>
            </div>

            <form
                class="mb-8 rounded-[2rem] border border-[#E7C5C5] bg-white p-5 shadow-sm"
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
                    class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-sm transition hover:shadow-md"
                >
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-[#C49B9B]">Замовлення</p>
                            <p class="mt-2 text-xl font-bold text-[#B46D6D]">#{{ order.id }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm text-[#8D6767]">
                            <span>{{ new Date(order.created_at).toLocaleString() }}</span>
                            <span
                                :class="statusClass(order.status)"
                                class="rounded-full px-3 py-1 font-semibold"
                            >
                                {{ statusLabel(order.status) }}
                            </span>
                            <span
                                :class="paymentStatusClass(order.payment_status)"
                                class="rounded-full px-3 py-1 font-semibold"
                            >
                                {{ paymentStatusLabel(order.payment_status) }}
                            </span>
                        </div>
                    </div>

                    <ul class="space-y-4">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex flex-col gap-4 border-b border-[#F1E1E1] pb-4 sm:flex-row sm:items-center"
                        >
                            <img
                                :src="item.product.image_path"
                                :alt="`Фото товару ${item.product.name}`"
                                class="h-20 w-20 rounded-[1.25rem] object-cover"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-lg font-semibold text-[#6D4C4C]">{{ item.product.name }}</p>
                                <p class="mt-1 text-sm text-[#8D6767]">{{ item.product.description }}</p>
                            </div>
                            <div class="text-sm font-semibold text-[#6D4C4C]">
                                {{ item.quantity }} × {{ formatPrice(item.price) }} ₴
                            </div>
                        </li>
                    </ul>

                    <div class="mt-5 flex flex-col gap-2 text-sm text-[#8D6767] md:flex-row md:items-center md:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <span>Payment reference: {{ order.payment_reference }}</span>
                            <Link
                                v-if="order.payment_method === 'demo_card'"
                                :href="route('payments.show', { paymentReference: order.payment_reference })"
                                class="text-sm font-semibold text-[#B46D6D] transition hover:text-[#9E5757]"
                            >
                                Відкрити payment status
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
import { ref, watch } from 'vue'
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
        paid: 'Payment paid',
        pending: 'Payment pending',
        failed: 'Payment failed',
        cancelled: 'Payment cancelled',
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
