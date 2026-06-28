<template>
    <AdminLayout
        :navigation="navigation"
        title="Order Report"
        description="Filtered sales reporting with average order value, daily trends, and best-selling products."
        eyebrow="Sales Analytics"
    >
        <div ref="reportRoot">
        <section class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <form class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="applyFilters">
                    <label class="text-sm text-[#8f6674]">
                        <span class="mb-2 block font-semibold text-[#7f485b]">Date from</span>
                        <input
                            v-model="localFilters.date_from"
                            type="date"
                            class="w-full rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                    </label>

                    <label class="text-sm text-[#8f6674]">
                        <span class="mb-2 block font-semibold text-[#7f485b]">Date to</span>
                        <input
                            v-model="localFilters.date_to"
                            type="date"
                            class="w-full rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                    </label>

                    <label class="text-sm text-[#8f6674]">
                        <span class="mb-2 block font-semibold text-[#7f485b]">Order status</span>
                        <select
                            v-model="localFilters.status"
                            class="w-full rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="">All</option>
                            <option v-for="option in filterOptions.statuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="text-sm text-[#8f6674]">
                        <span class="mb-2 block font-semibold text-[#7f485b]">Payment status</span>
                        <select
                            v-model="localFilters.payment_status"
                            class="w-full rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="">All</option>
                            <option v-for="option in filterOptions.paymentStatuses" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </form>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="button"
                        class="rounded-full bg-[#b46d6d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757]"
                        @click="applyFilters"
                    >
                        Build Report
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#8f6674] transition hover:bg-[#fff1f5]"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                    <a
                        :href="route('admin.reports.orders.export', normalizedFilters())"
                        class="inline-flex rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                    >
                        Export CSV
                    </a>
                    <button
                        type="button"
                        :disabled="isDownloadingPdf"
                        class="rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5] disabled:cursor-not-allowed disabled:opacity-60"
                        @click="downloadPdf"
                    >
                        {{ isDownloadingPdf ? 'Preparing PDF...' : 'Export PDF' }}
                    </button>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#c67e97]">Orders</p>
                <p class="mt-4 text-4xl font-black text-[#7f485b]">{{ summary.orders_count }}</p>
            </article>
            <article class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#c67e97]">Revenue</p>
                <p class="mt-4 text-4xl font-black text-[#7f485b]">${{ formatPrice(summary.revenue_total) }}</p>
            </article>
            <article class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#c67e97]">Paid Orders</p>
                <p class="mt-4 text-4xl font-black text-[#7f485b]">{{ summary.paid_count }}</p>
            </article>
            <article class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#c67e97]">Average order</p>
                <p class="mt-4 text-4xl font-black text-[#7f485b]">${{ formatPrice(summary.average_total) }}</p>
            </article>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-black text-[#7f485b]">Orders in Report</h3>
                        <p class="mt-2 text-sm leading-6 text-[#8f6674]">Operational dataset for the current filters.</p>
                    </div>
                    <Link
                        :href="route('admin.resources.index', { resource: 'orders' })"
                        class="inline-flex rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                    >
                        Back to Registry
                    </Link>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-[#f6e3ea] text-xs uppercase tracking-[0.25em] text-[#bc8da0]">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3">Qty</th>
                                <th class="px-4 py-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-[#9b7482]">
                                    No orders matched the current filters.
                                </td>
                            </tr>
                            <tr v-for="order in orders.data" :key="order.id" class="border-b border-[#faedf2] last:border-b-0">
                                <td class="px-4 py-4 font-semibold text-[#7f485b]">#{{ order.id }}</td>
                                <td class="px-4 py-4 text-[#7f626d]">{{ order.created_at }}</td>
                                <td class="px-4 py-4 text-[#7f626d]">
                                    <p class="font-semibold text-[#7f485b]">{{ order.full_name }}</p>
                                    <p class="mt-1 text-xs text-[#b28494]">{{ order.email }}</p>
                                </td>
                                <td class="px-4 py-4 text-[#7f626d]">{{ statusLabel(order.status) }}</td>
                                <td class="px-4 py-4 text-[#7f626d]">{{ statusLabel(order.payment_status) }}</td>
                                <td class="px-4 py-4 text-[#7f626d]">{{ order.quantity_total }}</td>
                                <td class="px-4 py-4 font-semibold text-[#7f485b]">${{ formatPrice(order.total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav v-if="orders.links.length > 3" class="mt-6 flex flex-wrap gap-2">
                    <button
                        v-for="(link, index) in orders.links"
                        :key="index"
                        :disabled="!link.url"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition"
                        :class="{
                            'border-[#b46d6d] bg-[#b46d6d] text-white': link.active,
                            'border-[#efcfdb] text-[#c8a6b3]': !link.url,
                            'border-[#efcfdb] text-[#9e5757] hover:bg-[#fff1f5]': link.url && !link.active,
                        }"
                        v-html="link.label"
                        @click.prevent="goToPage(link.url)"
                    />
                </nav>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                    <h3 class="text-2xl font-black text-[#7f485b]">Top Products</h3>
                    <div class="mt-5 space-y-4">
                        <div
                            v-for="product in topProducts"
                            :key="`${product.product_id}-${product.product_name}`"
                            class="rounded-[1.5rem] border border-[#f4e3ea] bg-[#fff9fb] px-4 py-4"
                        >
                            <p class="font-semibold text-[#7f485b]">{{ product.product_name }}</p>
                            <div class="mt-2 flex items-center justify-between text-sm text-[#8f6674]">
                                <span>{{ product.quantity_sold }} units</span>
                                <strong class="text-[#7f485b]">${{ formatPrice(product.revenue_total) }}</strong>
                            </div>
                        </div>
                        <p v-if="topProducts.length === 0" class="text-sm text-[#9b7482]">No product data available.</p>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
                    <h3 class="text-2xl font-black text-[#7f485b]">Daily Breakdown</h3>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="row in dailyBreakdown"
                            :key="row.date"
                            class="flex items-center justify-between rounded-[1.25rem] border border-[#f4e3ea] bg-[#fff9fb] px-4 py-3 text-sm"
                        >
                            <div>
                                <p class="font-semibold text-[#7f485b]">{{ row.date }}</p>
                                <p class="mt-1 text-[#8f6674]">{{ row.orders_count }} orders</p>
                            </div>
                            <strong class="text-[#7f485b]">${{ formatPrice(row.revenue_total) }}</strong>
                        </div>
                        <p v-if="dailyBreakdown.length === 0" class="text-sm text-[#9b7482]">No daily data available.</p>
                    </div>
                </section>
            </div>
        </section>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { downloadOrdersReportPdf } from '@/composables/usePdf'

const props = defineProps({
    navigation: Array,
    filters: Object,
    filterOptions: Object,
    summary: Object,
    orders: Object,
    topProducts: Array,
    dailyBreakdown: Array,
})

const localFilters = ref(buildFilters(props.filters))
const isDownloadingPdf = ref(false)
const reportRoot = ref(null)

watch(() => props.filters, (filters) => {
    localFilters.value = buildFilters(filters)
}, { deep: true })

function buildFilters(filters = {}) {
    return {
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
        status: filters.status ?? '',
        payment_status: filters.payment_status ?? '',
    }
}

function normalizedFilters() {
    return Object.fromEntries(
        Object.entries(localFilters.value).filter(([, value]) => value !== '' && value !== null)
    )
}

function applyFilters() {
    router.get(route('admin.reports.orders'), normalizedFilters(), {
        preserveScroll: true,
        preserveState: true,
    })
}

function resetFilters() {
    localFilters.value = buildFilters({})
    applyFilters()
}

function goToPage(url) {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

function statusLabel(status) {
    return {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        cancelled: 'Cancelled',
    }[status] ?? status
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}

async function downloadPdf() {
    isDownloadingPdf.value = true

    try {
        await downloadOrdersReportPdf(reportRoot.value)
    } finally {
        isDownloadingPdf.value = false
    }
}
</script>
