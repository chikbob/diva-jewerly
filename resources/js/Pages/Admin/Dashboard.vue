<template>
    <AdminLayout
        :navigation="navigation"
        title="Backoffice Overview"
        description="Quick access to the store's key metrics, latest orders, and primary operational data."
        eyebrow="Commercial Admin Panel"
    >
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#c67e97]">{{ stat.label }}</p>
                <p class="mt-4 text-4xl font-black text-[#7f485b]">{{ stat.value }}</p>
                <div class="mt-5 h-2 rounded-full bg-[#fbe9ef]">
                    <div
                        class="h-2 rounded-full"
                        :class="barClass(stat.accent)"
                        :style="{ width: `${Math.max(18, Math.min(100, Number(stat.value) || 0))}%` }"
                    />
                </div>
            </article>
        </section>

        <section class="mt-8 rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-black text-[#7f485b]">Latest Orders</h3>
                    <p class="mt-2 text-sm leading-6 text-[#8f6674]">
                        Operational snapshot of recent checkouts, statuses, and order totals.
                    </p>
                </div>

                <Link
                    :href="route('admin.resources.index', { resource: 'orders' })"
                    class="inline-flex rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                >
                    Open All Orders
                </Link>
                <Link
                    :href="route('admin.reports.orders')"
                    class="inline-flex rounded-full bg-[#b46d6d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757]"
                >
                    Build Report
                </Link>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#f6e3ea] text-xs uppercase tracking-[0.25em] text-[#bc8da0]">
                            <th class="px-4 py-3">Reference</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in latestOrders"
                            :key="order.id"
                            class="border-b border-[#faedf2] last:border-b-0"
                        >
                            <td class="px-4 py-4 font-semibold text-[#7f485b]">{{ order.payment_reference || `#${order.id}` }}</td>
                            <td class="px-4 py-4 text-[#7f626d]">{{ order.full_name }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#fff1f5] px-3 py-1 text-xs font-semibold text-[#a55d76]">
                                    {{ statusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-[#edf8f1] px-3 py-1 text-xs font-semibold text-[#327052]">
                                    {{ paymentStatusLabel(order.payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-[#7f485b]">{{ formatPrice(order.total) }} ₴</td>
                            <td class="px-4 py-4 text-[#8f6674]">{{ order.created_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    navigation: Array,
    stats: Array,
    latestOrders: Array,
})

function barClass(accent) {
    return {
        rose: 'bg-rose-400',
        gold: 'bg-pink-300',
        slate: 'bg-rose-300',
        emerald: 'bg-emerald-400',
        violet: 'bg-fuchsia-400',
        amber: 'bg-orange-300',
    }[accent] ?? 'bg-rose-400'
}

function statusLabel(status) {
    return {
        pending: 'Pending',
        paid: 'Paid',
        failed: 'Failed',
        cancelled: 'Cancelled',
    }[status] ?? status
}

function paymentStatusLabel(status) {
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
</script>
