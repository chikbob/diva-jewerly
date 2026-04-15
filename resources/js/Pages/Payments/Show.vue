<template>
    <page-layout>
        <section class="container mx-auto max-w-4xl px-6 py-12">
            <div class="mb-8">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Payment Status</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Оплата замовлення #{{ order.id }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-[#8D6767]">
                    Тут відображається поточний payment lifecycle замовлення: provider, transaction reference та останній відомий статус.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <span :class="statusClass(order.status)" class="rounded-full px-4 py-2 text-sm font-semibold">
                            {{ statusLabel(order.status) }}
                        </span>
                        <span :class="paymentStatusClass(order.payment_status)" class="rounded-full px-4 py-2 text-sm font-semibold">
                            {{ paymentStatusLabel(order.payment_status) }}
                        </span>
                    </div>

                    <dl class="mt-8 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Payment reference</dt>
                            <dd class="mt-2 text-base font-semibold text-[#6D4C4C]">{{ order.payment_reference }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Provider</dt>
                            <dd class="mt-2 text-base font-semibold text-[#6D4C4C]">{{ transaction.provider }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Provider reference</dt>
                            <dd class="mt-2 text-base font-semibold text-[#6D4C4C]">{{ transaction.provider_reference || 'pending assignment' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Сума</dt>
                            <dd class="mt-2 text-base font-semibold text-[#6D4C4C]">{{ formatPrice(order.total) }} {{ transaction.currency }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 rounded-[1.5rem] bg-[#FFF8F8] px-5 py-5 text-sm text-[#8D6767]">
                        <p v-if="order.payment_status === 'pending'">
                            Платіж ще не підтверджений. Для demo-card сценарію ви можете симулювати webhook-підтвердження праворуч.
                        </p>
                        <p v-else-if="order.payment_status === 'paid'">
                            Платіж підтверджено. Замовлення перейшло в стан `paid`.
                        </p>
                        <p v-else>
                            Платіж завершився неуспішно. Система зберегла transaction state для подальшого аналізу та reconciliation.
                        </p>
                    </div>
                </section>

                <aside class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-semibold text-[#B46D6D]">Demo actions</h2>
                    <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                        Ці дії доступні тільки для `demo_card` платежів у стані pending і оновлюють замовлення через той самий webhook processing path.
                    </p>

                    <div class="mt-6 space-y-3">
                        <button
                            type="button"
                            :disabled="!canSimulate || loading === 'paid'"
                            class="w-full rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="simulate('paid')"
                        >
                            {{ loading === 'paid' ? 'Підтверджуємо...' : 'Симулювати успішну оплату' }}
                        </button>

                        <button
                            type="button"
                            :disabled="!canSimulate || loading === 'failed'"
                            class="w-full rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="simulate('failed')"
                        >
                            {{ loading === 'failed' ? 'Оновлюємо...' : 'Симулювати failed payment' }}
                        </button>
                    </div>

                    <Link
                        :href="route('orders.index')"
                        class="mt-6 inline-flex text-sm font-semibold text-[#B46D6D] transition hover:text-[#9E5757]"
                    >
                        Повернутися до замовлень
                    </Link>
                </aside>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const props = defineProps({
    order: Object,
    transaction: Object,
    canSimulate: Boolean,
})

const loading = ref(null)

function simulate(status) {
    loading.value = status

    router.post(route('payments.simulate', {
        paymentReference: props.order.payment_reference,
        status,
    }), {}, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = null
        },
    })
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
