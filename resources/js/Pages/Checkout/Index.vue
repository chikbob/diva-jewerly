<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-16 sm:px-6 xl:px-8">
            <div class="mb-10 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Безпечне оформлення</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Оформлення замовлення</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Перевірте склад кошика, залиште контактні дані та оберіть demo-спосіб оплати без введення карткових реквізитів.
                    </p>
                </div>

                <div v-if="items.length" class="grid gap-3 sm:grid-cols-2 xl:min-w-[28rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Позиції</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ items.length }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Сума</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ formatPrice(total) }} ₴</p>
                    </div>
                </div>
            </div>

            <div v-if="items.length === 0" class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-[#B46D6D]">У кошику ще немає товарів</h2>
                <p class="mt-3 text-sm text-[#8D6767]">Поверніться до каталогу, щоб додати прикраси та перейти до оплати.</p>
                <Link
                    :href="route('catalog')"
                    class="mt-6 inline-flex rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                >
                    До каталогу
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 gap-10 xl:grid-cols-[1.05fr_0.95fr]">
                <section class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-[0_18px_50px_rgba(180,109,109,0.07)]">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-2xl font-semibold text-[#B46D6D]">Ваше замовлення</h2>
                        <span class="rounded-full bg-[#FFF2F2] px-4 py-2 text-sm font-semibold text-[#A05F5F]">
                            {{ items.length }} позицій
                        </span>
                    </div>

                    <ul class="space-y-5">
                        <li
                            v-for="item in items"
                            :key="item.id"
                            class="flex flex-col gap-4 rounded-[1.5rem] border border-[#F1E1E1] bg-[#fffdfd] p-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    :src="item.product.image_path"
                                    :alt="`Фото товару ${item.product.name}`"
                                    class="h-20 w-20 rounded-[1.25rem] object-cover"
                                />
                                <div>
                                    <p class="text-lg font-semibold text-[#6D4C4C]">{{ item.product.name }}</p>
                                    <p class="mt-1 text-sm text-[#8D6767]">Кількість: {{ item.quantity }}</p>
                                </div>
                            </div>

                            <p class="text-lg font-semibold text-[#B46D6D]">{{ formatPrice(item.quantity * item.product.price) }} ₴</p>
                        </li>
                    </ul>

                    <div class="mt-6 rounded-[1.5rem] border border-[#F1E1E1] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-sm uppercase tracking-[0.25em] text-[#C49B9B]">Загальна сума</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#B46D6D]" aria-live="polite">{{ formatPrice(total) }} ₴</p>
                    </div>
                </section>

                <form class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-[0_18px_50px_rgba(180,109,109,0.07)]" @submit.prevent="submit">
                    <h2 class="text-2xl font-semibold text-[#B46D6D]">Контактні дані</h2>
                    <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                        Демо-оплата не запитує номер картки. Після створення замовлення ви перейдете на окрему сторінку статусу оплати, а система збереже лише технічний стан транзакції без чутливих карткових даних.
                    </p>

                    <div class="mt-8 space-y-6">
                        <div>
                            <label for="checkout-full-name" class="mb-2 block text-sm font-semibold text-[#6D4C4C]">ПІБ</label>
                            <input
                                id="checkout-full-name"
                                v-model="form.full_name"
                                type="text"
                                autocomplete="name"
                                placeholder="Введіть ваше ПІБ"
                                class="w-full rounded-2xl border px-5 py-3 shadow-sm focus:outline-none focus:ring-2"
                                :class="fieldClass(form.errors.full_name)"
                                :aria-invalid="form.errors.full_name ? 'true' : 'false'"
                            />
                            <p v-if="form.errors.full_name" class="mt-2 text-sm text-red-600">{{ form.errors.full_name }}</p>
                        </div>

                        <div>
                            <label for="checkout-email" class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Електронна пошта</label>
                            <input
                                id="checkout-email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="Введіть ваш email"
                                class="w-full rounded-2xl border px-5 py-3 shadow-sm focus:outline-none focus:ring-2"
                                :class="fieldClass(form.errors.email)"
                                :aria-invalid="form.errors.email ? 'true' : 'false'"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <fieldset>
                            <legend class="mb-3 text-sm font-semibold text-[#6D4C4C]">Спосіб оплати</legend>
                            <div class="grid gap-3">
                                <label
                                    v-for="option in paymentMethods"
                                    :key="option.value"
                                    class="flex cursor-pointer items-start gap-3 rounded-2xl border px-4 py-4 transition"
                                    :class="form.payment_method === option.value ? 'border-[#B46D6D] bg-[#FFF4F4]' : 'border-[#E7C5C5] hover:bg-[#FFF9F9]'"
                                >
                                    <input
                                        v-model="form.payment_method"
                                        type="radio"
                                        name="payment_method"
                                        :value="option.value"
                                        class="mt-1"
                                    />
                                    <span>
                                        <span class="block text-sm font-semibold text-[#6D4C4C]">{{ option.label }}</span>
                                        <span class="mt-1 block text-sm text-[#8D6767]">{{ option.description }}</span>
                                    </span>
                                </label>
                            </div>
                            <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">{{ form.errors.payment_method }}</p>
                        </fieldset>

                        <div
                            v-if="form.errors.cart"
                            class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {{ form.errors.cart }}
                        </div>

                        <div class="rounded-[1.5rem] border border-[#F1E1E1] bg-[#FFF8F8] px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Перед підтвердженням</p>
                            <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                                Переконайтеся, що ПІБ, email і спосіб оплати вказані коректно. Після цього замовлення буде створено одразу.
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-full bg-[#B46D6D] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ form.processing ? 'Створюємо замовлення...' : 'Підтвердити замовлення' }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    items: Array,
    defaults: Object,
})

const { error } = useToast()
const paymentMethods = [
    {
        value: 'demo_card',
        label: 'Демо-оплата карткою',
        description: 'Сценарій для демонстрації checkout без зберігання чутливих карткових даних.',
    },
    {
        value: 'cash_on_delivery',
        label: 'Післяплата',
        description: 'Оплата при отриманні замовлення.',
    },
]

const form = useForm({
    full_name: props.defaults?.full_name ?? '',
    email: props.defaults?.email ?? '',
    payment_method: 'demo_card',
})

const total = computed(() =>
    props.items.reduce((sum, item) => sum + item.quantity * item.product.price, 0)
)

function submit() {
    form.post(route('checkout.store'), {
        onError: () => {
            error('Будь ласка, перевірте форму замовлення та спробуйте ще раз.')
        },
    })
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}

function fieldClass(hasError) {
    return hasError
        ? 'border-red-300 focus:border-red-400 focus:ring-red-200'
        : 'border-[#E3BEBE] focus:border-[#B46D6D] focus:ring-[#E7B7B7]'
}
</script>
