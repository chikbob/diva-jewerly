<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1520px] px-4 py-12 text-[#B46D6D] sm:px-6 xl:px-8">
            <div class="mx-auto max-w-[1480px]">
                <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">DIVA Collection</p>
                        <h1 class="mt-2 text-4xl font-extrabold tracking-wide">Jewelry Catalog</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#8D6767]">
                            Browse pieces by category, budget, and sort order. The catalog updates without a full page reload.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[#E9CFCF] bg-[#FFF8F8] px-4 py-3 text-sm text-[#8D6767]">
                        <strong class="text-[#B46D6D]">{{ products.total }}</strong> products found
                    </div>
                </div>

                <form
                    class="mb-10 rounded-[2rem] border border-[#E7C5C5] bg-white/95 p-6 shadow-sm"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Search</span>
                            <input
                                v-model="localFilters.search"
                                type="search"
                                placeholder="Jewelry name"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            />
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Category</span>
                            <select
                                v-model="localFilters.category_id"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            >
                                <option value="">All categories</option>
                                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                    {{ category.name }}
                                </option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Min price</span>
                            <input
                                v-model="localFilters.min_price"
                                type="number"
                                min="0"
                                step="0.01"
                                :placeholder="String(Math.floor(priceRange.min || 0))"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            />
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Max price</span>
                            <input
                                v-model="localFilters.max_price"
                                type="number"
                                min="0"
                                step="0.01"
                                :placeholder="String(Math.ceil(priceRange.max || 0))"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            />
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Sorting</span>
                            <select
                                v-model="localFilters.sort"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            >
                                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <label class="mt-5 inline-flex items-center gap-3 rounded-full border border-[#E3BEBE] bg-[#FFF8F8] px-4 py-3 text-sm font-medium text-[#6D4C4C]">
                        <input
                            v-model="localFilters.only_new"
                            type="checkbox"
                            class="rounded border-[#D8A8A8] text-[#B46D6D] focus:ring-[#E7B7B7]"
                        />
                        <span>Show only items added in the last 30 days</span>
                    </label>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            :disabled="isFiltering"
                            class="rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ isFiltering ? 'Refreshing catalog...' : 'Apply Filters' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[#E3BEBE] px-6 py-3 text-sm font-semibold transition hover:bg-[#FFF1F1]"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                        <span class="text-xs text-[#9B7B7B]">
                            Price range: ${{ formatPrice(priceRange.min) }} - ${{ formatPrice(priceRange.max) }}
                        </span>
                    </div>

                    <div v-if="activeFilterLabels.length" class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="label in activeFilterLabels"
                            :key="label"
                            class="rounded-full bg-[#FFF1F1] px-3 py-1 text-xs font-semibold text-[#A05F5F]"
                        >
                            {{ label }}
                        </span>
                    </div>
                </form>

                <div
                    class="relative"
                    :aria-busy="isFiltering ? 'true' : 'false'"
                >
                    <div
                        v-if="isFiltering"
                        class="pointer-events-none absolute inset-0 z-10 rounded-[2rem] bg-white/70 backdrop-blur-[1px]"
                    />

                    <div v-if="products.data.length === 0" class="rounded-[2rem] border border-dashed border-[#E7C5C5] bg-[#FFF9F9] px-6 py-16 text-center">
                        <h2 class="text-2xl font-bold">Nothing found</h2>
                        <p class="mt-3 text-sm text-[#8D6767]">Try broadening the filters or changing the search query.</p>
                        <button
                            type="button"
                            class="mt-6 rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                            @click="resetFilters"
                        >
                            Reset Filters
                        </button>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 2xl:grid-cols-3">
                        <article
                            v-for="product in products.data"
                            :key="product.id"
                            class="flex h-full min-h-[100%] flex-col rounded-[2rem] border border-[#E3BEBE] bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                        >
                            <img
                                :src="product.image_path"
                                :alt="`Product photo: ${product.name}`"
                                class="mb-5 aspect-[4/3] w-full rounded-[1.5rem] object-cover"
                            />

                            <div class="flex flex-1 flex-col">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-[#C49B9B]">{{ product.category.name }}</p>
                                        <h2 class="mt-2 min-h-[4rem] text-2xl font-bold leading-tight">{{ product.name }}</h2>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="rounded-full bg-[#FFF2F2] px-3 py-1 text-xs font-semibold text-[#A05F5F]">
                                            ${{ formatPrice(product.price) }}
                                        </span>
                                        <span
                                            v-if="isNewProduct(product)"
                                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                                        >
                                            New
                                        </span>
                                    </div>
                                </div>

                                <p class="mb-6 min-h-[4.5rem] flex-1 text-sm leading-6 text-[#8D6767]">
                                    {{ product.description || 'A refined piece for everyday and occasion styling.' }}
                                </p>

                                <div class="mt-auto grid gap-3 xl:grid-cols-3">
                                    <button
                                        type="button"
                                        :disabled="isFavoritePending(product.id)"
                                        class="rounded-full border border-[#E3BEBE] px-5 py-3 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60"
                                        :class="isFavorite(product.id) ? 'bg-[#FFF1F1] text-[#B46D6D]' : 'text-[#8D6767] hover:bg-[#FFF1F1]'"
                                        @click="toggleFavorite(product.id)"
                                    >
                                        {{ isFavorite(product.id) ? 'In Favorites' : 'Add to Favorites' }}
                                    </button>
                                    <Link
                                        :href="route('products.show', { product: product.id })"
                                        class="inline-flex items-center justify-center rounded-full border border-[#E3BEBE] px-5 py-3 text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                                    >
                                        View Details
                                    </Link>
                                    <button
                                        type="button"
                                        :disabled="isAdding(product.id)"
                                        class="rounded-full bg-[#B46D6D] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                                        @click="addToCart(product.id)"
                                    >
                                        {{ isAdding(product.id) ? 'Adding...' : 'Add to Cart' }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <nav v-if="products.links.length > 3" class="mt-12 flex flex-wrap justify-center gap-3" aria-label="Catalog pagination">
                    <button
                        v-for="(link, index) in products.links"
                        :key="index"
                        :disabled="!link.url || isFiltering"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition"
                        :class="{
                            'bg-[#B46D6D] text-white border-[#B46D6D]': link.active,
                            'text-gray-400 cursor-not-allowed border-gray-200': !link.url,
                            'hover:bg-[#FCEEEE] border-[#E3BEBE] text-[#8D6767]': link.url && !link.active,
                        }"
                        v-html="link.label"
                        @click.prevent="goToPage(link.url)"
                    />
                </nav>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'
import { useCartUi } from '@/composables/useCartUi'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    favoriteIds: Array,
    priceRange: Object,
    sortOptions: Array,
})

const page = usePage()
const { adjust } = useCartUi()
const { error } = useToast()
const isFiltering = ref(false)
const pendingCartIds = ref([])
const pendingFavoriteIds = ref([])
const localFilters = ref(buildFilters(props.filters))
const localFavoriteIds = ref([...(props.favoriteIds ?? [])])
const activeFilterLabels = computed(() => {
    const labels = []
    const selectedCategory = props.categories.find((category) => String(category.id) === localFilters.value.category_id)

    if (localFilters.value.search) {
        labels.push(`Search: ${localFilters.value.search}`)
    }

    if (selectedCategory) {
        labels.push(`Category: ${selectedCategory.name}`)
    }

    if (localFilters.value.min_price) {
        labels.push(`From $${formatPrice(localFilters.value.min_price)}`)
    }

    if (localFilters.value.max_price) {
        labels.push(`To $${formatPrice(localFilters.value.max_price)}`)
    }

    if (localFilters.value.only_new) {
        labels.push('Only new items')
    }

    return labels
})
const isAuthenticated = computed(() => Boolean(page.props.auth?.user))

watch(() => props.filters, (filters) => {
    localFilters.value = buildFilters(filters)
}, { deep: true })

watch(() => props.favoriteIds, (favoriteIds) => {
    localFavoriteIds.value = [...(favoriteIds ?? [])]
}, { deep: true })

function buildFilters(filters = {}) {
    return {
        search: filters.search ?? '',
        category_id: filters.category_id ? String(filters.category_id) : '',
        min_price: filters.min_price ?? '',
        max_price: filters.max_price ?? '',
        only_new: Boolean(filters.only_new),
        sort: filters.sort ?? 'name_asc',
    }
}

function normalizedFilters() {
    return Object.fromEntries(
        Object.entries(localFilters.value).filter(([, value]) => value !== '' && value !== null && value !== false)
    )
}

function applyFilters() {
    router.get(route('catalog'), normalizedFilters(), {
        preserveState: true,
        preserveScroll: true,
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

function goToPage(url) {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
            isFiltering.value = true
        },
        onFinish: () => {
            isFiltering.value = false
        },
    })
}

function isNewProduct(product) {
    if (!product?.created_at) {
        return false
    }

    const createdAt = new Date(product.created_at)
    const threshold = new Date()
    threshold.setDate(threshold.getDate() - 30)

    return createdAt >= threshold
}

function isFavorite(productId) {
    return localFavoriteIds.value.includes(productId)
}

function isFavoritePending(productId) {
    return pendingFavoriteIds.value.includes(productId)
}

function toggleFavorite(productId) {
    if (isFavoritePending(productId)) {
        return
    }

    if (!isAuthenticated.value) {
        router.visit(route('login'))

        return
    }

    const favorited = isFavorite(productId)
    const snapshot = [...localFavoriteIds.value]
    pendingFavoriteIds.value = [...pendingFavoriteIds.value, productId]
    localFavoriteIds.value = favorited
        ? localFavoriteIds.value.filter((id) => id !== productId)
        : [...localFavoriteIds.value, productId]

    const options = {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            localFavoriteIds.value = snapshot
            error('Could not update favorites. Please try again.')
        },
        onFinish: () => {
            pendingFavoriteIds.value = pendingFavoriteIds.value.filter((id) => id !== productId)
        },
    }

    if (favorited) {
        router.delete(route('favorites.destroy', { product: productId }), options)

        return
    }

    router.post(route('favorites.store', { product: productId }), {}, options)
}

function addToCart(productId) {
    if (isAdding(productId)) {
        return
    }

    pendingCartIds.value = [...pendingCartIds.value, productId]
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
            pendingCartIds.value = pendingCartIds.value.filter((id) => id !== productId)
        },
    })
}

function isAdding(productId) {
    return pendingCartIds.value.includes(productId)
}

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}
</script>
