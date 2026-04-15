<template>
    <page-layout>
        <section class="container mx-auto max-w-5xl px-6 py-12">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Shopping Bag</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Кошик</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#8D6767]">
                        Оновлюйте кількість товарів без перезавантаження та швидко переходьте до оформлення замовлення.
                    </p>
                </div>

                <div class="rounded-2xl border border-[#E9CFCF] bg-[#FFF8F8] px-4 py-3 text-sm text-[#8D6767]">
                    <strong class="text-[#B46D6D]">{{ itemCount }}</strong> шт. на суму
                    <strong class="text-[#B46D6D]">{{ formatPrice(total) }} ₴</strong>
                </div>
            </div>

            <div v-if="localItems.length" class="space-y-5" :aria-busy="isBusy ? 'true' : 'false'">
                <article
                    v-for="item in localItems"
                    :key="item.product_id"
                    class="rounded-[2rem] border border-[#E7C5C5] bg-white p-5 shadow-sm transition"
                    :class="{
                        'opacity-70': isPending(item.product_id),
                    }"
                >
                    <div class="flex flex-col gap-5 md:flex-row md:items-center">
                        <img
                            :src="item.product.image_path"
                            :alt="`Фото товару ${item.product.name}`"
                            class="h-28 w-full rounded-[1.5rem] object-cover md:w-32"
                        >

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                <div class="min-w-0">
                                    <h2 class="truncate text-xl font-bold text-[#B46D6D]">{{ item.product.name }}</h2>
                                    <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                                        {{ item.product.description || 'Ювелірний виріб із колекції Diva.' }}
                                    </p>
                                </div>
                                <div class="rounded-full bg-[#FFF2F2] px-4 py-2 text-sm font-semibold text-[#A05F5F]">
                                    {{ formatPrice(item.product.price * item.quantity) }} ₴
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Ціна за одиницю</p>
                                    <p class="mt-1 text-base font-semibold text-[#6D4C4C]">{{ formatPrice(item.product.price) }} ₴</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-[#8D6767]">Кількість</span>
                                    <div class="inline-flex items-center rounded-full border border-[#E3BEBE] bg-[#FFF8F8] px-2 py-1">
                                        <button
                                            type="button"
                                            class="h-9 w-9 rounded-full text-xl text-[#B46D6D] transition hover:bg-[#FCEEEE] disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="isPending(item.product_id)"
                                            aria-label="Зменшити кількість"
                                            @click="changeQuantity(item, item.quantity - 1)"
                                        >
                                            −
                                        </button>
                                        <span class="min-w-[3rem] text-center text-base font-semibold text-[#6D4C4C]" aria-live="polite">
                                            {{ item.quantity }}
                                        </span>
                                        <button
                                            type="button"
                                            class="h-9 w-9 rounded-full text-xl text-[#B46D6D] transition hover:bg-[#FCEEEE] disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="isPending(item.product_id)"
                                            aria-label="Збільшити кількість"
                                            @click="changeQuantity(item, item.quantity + 1)"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="text-sm font-semibold text-red-600 transition hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="isPending(item.product_id)"
                                    @click="removeFromCart(item)"
                                >
                                    Видалити
                                </button>
                            </div>
                        </div>
                    </div>
                </article>

                <div class="mt-8 flex flex-col gap-4 rounded-[2rem] border border-[#E7C5C5] bg-[#FFF9F9] p-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.25em] text-[#C49B9B]">Разом до сплати</p>
                        <p class="mt-2 text-3xl font-extrabold text-[#B46D6D]" aria-live="polite">{{ formatPrice(total) }} ₴</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="route('catalog')"
                            class="rounded-full border border-[#E3BEBE] px-6 py-3 text-center text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                        >
                            Продовжити покупки
                        </Link>
                        <Link
                            :href="route('checkout.index')"
                            class="rounded-full bg-[#B46D6D] px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                        >
                            Перейти до оплати
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-[#B46D6D]">Кошик порожній</h2>
                <p class="mt-3 text-sm text-[#8D6767]">Додайте кілька прикрас у кошик, щоб перейти до оформлення замовлення.</p>
                <Link
                    :href="route('catalog')"
                    class="mt-6 inline-flex rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                >
                    Перейти до каталогу
                </Link>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'
import { useCartUi } from '@/composables/useCartUi'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    items: Array,
})

const { adjust } = useCartUi()
const { error } = useToast()
const pendingProducts = ref([])
const localItems = ref(cloneItems(props.items))

watch(() => props.items, (items) => {
    localItems.value = cloneItems(items)
}, { deep: true })

const itemCount = computed(() => localItems.value.reduce((sum, item) => sum + item.quantity, 0))
const total = computed(() => localItems.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0))
const isBusy = computed(() => pendingProducts.value.length > 0)

function cloneItems(items = []) {
    return items.map((item) => ({
        ...item,
        product: { ...item.product },
    }))
}

function isPending(productId) {
    return pendingProducts.value.includes(productId)
}

function markPending(productId) {
    pendingProducts.value = [...pendingProducts.value, productId]
}

function clearPending(productId) {
    pendingProducts.value = pendingProducts.value.filter((id) => id !== productId)
}

function removeFromCart(item) {
    if (isPending(item.product_id)) {
        return
    }

    const snapshot = cloneItems(localItems.value)
    localItems.value = localItems.value.filter((entry) => entry.product_id !== item.product_id)
    adjust(-item.quantity)
    markPending(item.product_id)

    router.post(route('cart.remove'), {
        product_id: item.product_id,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            localItems.value = snapshot
            adjust(item.quantity)
            error('Не вдалося видалити товар із кошика.')
        },
        onFinish: () => {
            clearPending(item.product_id)
        },
    })
}

function changeQuantity(item, quantity) {
    if (isPending(item.product_id)) {
        return
    }

    if (quantity < 1) {
        removeFromCart(item)

        return
    }

    const snapshot = cloneItems(localItems.value)
    const target = localItems.value.find((entry) => entry.product_id === item.product_id)

    if (!target) {
        return
    }

    const delta = quantity - target.quantity
    target.quantity = quantity
    adjust(delta)
    markPending(item.product_id)

    router.patch(route('cart.update'), {
        product_id: item.product_id,
        quantity,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            localItems.value = snapshot
            adjust(-delta)
            error('Не вдалося оновити кількість товару.')
        },
        onFinish: () => {
            clearPending(item.product_id)
        },
    })
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}
</script>
