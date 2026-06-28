<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Saved Pieces</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">Favorites</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Save the pieces you like so you can return to them quickly before checkout.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[26rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Items</p>
                        <p class="mt-2 text-2xl font-black text-[#B46D6D]">{{ localFavorites.length }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4 text-sm text-[#8D6767]">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Purpose</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">Quick return to saved pieces</p>
                    </div>
                </div>
            </div>

            <div v-if="localFavorites.length === 0" class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center">
                <h2 class="text-2xl font-bold text-[#B46D6D]">Favorites List Is Empty</h2>
                <p class="mt-3 text-sm text-[#8D6767]">Add jewelry pieces to favorites from the catalog or product page.</p>
                <Link
                    :href="route('catalog')"
                    class="mt-6 inline-flex rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                >
                    Open Catalog
                </Link>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 2xl:grid-cols-3">
                <article
                    v-for="favorite in localFavorites"
                    :key="favorite.id"
                    class="flex h-full flex-col rounded-[2rem] border border-[#E3BEBE] bg-white p-6 shadow-[0_18px_50px_rgba(180,109,109,0.07)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_60px_rgba(180,109,109,0.1)]"
                >
                    <img
                        :src="favorite.product.image_path"
                        :alt="`Product photo: ${favorite.product.name}`"
                        class="mb-5 aspect-[4/3] w-full rounded-[1.5rem] object-cover"
                    />

                    <div class="flex flex-1 flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.28em] text-[#C49B9B]">{{ favorite.product.category.name }}</p>
                            <span class="rounded-full bg-[#FFF2F2] px-3 py-1 text-xs font-semibold text-[#A05F5F]">
                                {{ formatPrice(favorite.product.price) }} ₴
                            </span>
                        </div>

                        <Link
                            :href="route('products.show', { product: favorite.product.id })"
                            class="mt-3 min-h-[4rem] text-2xl font-bold leading-tight text-[#B46D6D] transition hover:text-[#9E5757]"
                        >
                            {{ favorite.product.name }}
                        </Link>
                        <p class="mt-3 min-h-[4.5rem] flex-1 text-sm leading-7 text-[#8D6767]">
                            {{ favorite.product.description || 'A jewelry piece from the Diva collection.' }}
                        </p>

                        <div class="mt-5 rounded-[1.35rem] border border-[#F3E2E2] bg-[#FFF8F8] px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.25em] text-[#C49B9B]">Tip</p>
                            <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                                Add the piece to your cart or open the product page to compare it with other items.
                            </p>
                        </div>

                        <div class="mt-6 grid gap-3">
                            <button
                                type="button"
                                :disabled="isAdding(favorite.product.id)"
                                class="rounded-full bg-[#B46D6D] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                                @click="addToCart(favorite.product.id)"
                            >
                                {{ isAdding(favorite.product.id) ? 'Adding...' : 'Add to Cart' }}
                            </button>
                            <button
                                type="button"
                                :disabled="isRemoving(favorite.product.id)"
                                class="rounded-full border border-[#E3BEBE] px-5 py-3 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1] disabled:cursor-not-allowed disabled:opacity-60"
                                @click="removeFavorite(favorite)"
                            >
                                {{ isRemoving(favorite.product.id) ? 'Removing...' : 'Remove from Favorites' }}
                            </button>
                        </div>
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
import { useCartUi } from '@/composables/useCartUi'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    favorites: Array,
})

const { adjust } = useCartUi()
const { error } = useToast()
const localFavorites = ref([...props.favorites])
const pendingAddIds = ref([])
const pendingRemoveIds = ref([])

watch(() => props.favorites, (favorites) => {
    localFavorites.value = [...favorites]
}, { deep: true })

function isAdding(productId) {
    return pendingAddIds.value.includes(productId)
}

function isRemoving(productId) {
    return pendingRemoveIds.value.includes(productId)
}

function addToCart(productId) {
    if (isAdding(productId)) {
        return
    }

    pendingAddIds.value = [...pendingAddIds.value, productId]
    adjust(1)

    router.post(route('cart.add'), {
        product_id: productId,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            adjust(-1)
            error('Could not add the item to the cart. Please try again.')
        },
        onFinish: () => {
            pendingAddIds.value = pendingAddIds.value.filter((id) => id !== productId)
        },
    })
}

function removeFavorite(favorite) {
    if (isRemoving(favorite.product.id)) {
        return
    }

    const snapshot = [...localFavorites.value]
    pendingRemoveIds.value = [...pendingRemoveIds.value, favorite.product.id]
    localFavorites.value = localFavorites.value.filter((entry) => entry.id !== favorite.id)

    router.delete(route('favorites.destroy', { product: favorite.product.id }), {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            localFavorites.value = snapshot
            error('Could not remove the item from favorites. Please try again.')
        },
        onFinish: () => {
            pendingRemoveIds.value = pendingRemoveIds.value.filter((id) => id !== favorite.product.id)
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
