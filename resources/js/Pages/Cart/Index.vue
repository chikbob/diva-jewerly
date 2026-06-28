<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Shopping Cart</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Cart</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#8D6767]">
                        Update quantities without a page reload and move quickly to checkout.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[26rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Items</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ itemCount }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Total</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">${{ formatPrice(total) }}</p>
                    </div>
                </div>
            </div>

            <div v-if="localItems.length" class="grid gap-8 xl:grid-cols-[1.15fr_0.85fr]" :aria-busy="isBusy ? 'true' : 'false'">
                <div class="space-y-5">
                    <article
                        v-for="item in localItems"
                        :key="item.product_id"
                        class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)] transition"
                        :class="{ 'opacity-70': isPending(item.product_id) }"
                    >
                        <div class="flex flex-col gap-5 md:flex-row md:items-center">
                            <img
                                :src="item.product.image_path"
                                :alt="`Product photo: ${item.product.name}`"
                                class="h-32 w-full rounded-[1.6rem] object-cover md:w-36"
                            >

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">{{ item.product.category?.name }}</p>
                                        <Link
                                            :href="route('products.show', { product: item.product.id })"
                                            class="mt-2 block text-xl font-bold leading-tight text-[#B46D6D] transition hover:text-[#9E5757]"
                                        >
                                            {{ item.product.name }}
                                        </Link>
                                        <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                                            {{ item.product.description || 'A jewelry piece from the Diva collection.' }}
                                        </p>
                                    </div>
                                    <div class="rounded-full bg-[#FFF2F2] px-4 py-2 text-sm font-semibold text-[#A05F5F]">
                                        ${{ formatPrice(item.product.price * item.quantity) }}
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-4 lg:grid-cols-[0.8fr_1fr_auto] lg:items-center">
                                    <div class="rounded-[1.35rem] border border-[#F3E2E2] bg-[#FFF8F8] px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Unit price</p>
                                        <p class="mt-1 text-base font-semibold text-[#6D4C4C]">${{ formatPrice(item.product.price) }}</p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-[#8D6767]">Quantity</span>
                                        <div class="inline-flex items-center rounded-full border border-[#E3BEBE] bg-[#FFF8F8] px-2 py-1">
                                            <button
                                                type="button"
                                                class="h-9 w-9 rounded-full text-xl text-[#B46D6D] transition hover:bg-[#FCEEEE] disabled:cursor-not-allowed disabled:opacity-40"
                                                :disabled="isPending(item.product_id) || item.quantity <= 1"
                                                aria-label="Decrease quantity"
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
                                                aria-label="Increase quantity"
                                                @click="changeQuantity(item, item.quantity + 1)"
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="rounded-full border border-rose-200 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="isPending(item.product_id)"
                                        @click="removeFromCart(item)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-[2rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Cart Summary</p>
                        <p class="mt-3 text-3xl font-black text-[#B46D6D]" aria-live="polite">${{ formatPrice(total) }}</p>
                        <div class="mt-5 space-y-3 text-sm text-[#8D6767]">
                            <div class="flex items-center justify-between">
                                <span>Total items</span>
                                <strong class="text-[#6D4C4C]">{{ itemCount }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Unique products</span>
                                <strong class="text-[#6D4C4C]">{{ localItems.length }}</strong>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3">
                            <Link
                                :href="route('checkout.index')"
                                class="rounded-full bg-[#B46D6D] px-6 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                            >
                                Proceed to Checkout
                            </Link>
                            <Link
                                :href="route('catalog')"
                                class="rounded-full border border-[#E3BEBE] px-6 py-3 text-center text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                            >
                                Continue Shopping
                            </Link>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-[#E7C5C5] bg-[#FFF9F9] p-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Tip</p>
                        <p class="mt-3 text-sm leading-7 text-[#8D6767]">
                            Update quantities directly in the cart. Totals refresh immediately without a full page reload.
                        </p>
                    </div>
                </aside>
            </div>

            <div v-else class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-[#B46D6D]">Your Cart Is Empty</h2>
                <p class="mt-3 text-sm text-[#8D6767]">Add a few pieces to the cart to continue to checkout.</p>
                <Link
                    :href="route('catalog')"
                    class="mt-6 inline-flex rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                >
                    Open Catalog
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
            error('Could not remove the item from the cart.')
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
            error('Could not update the item quantity.')
        },
        onFinish: () => {
            clearPending(item.product_id)
        },
    })
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}
</script>
