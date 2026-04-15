<template>
    <page-layout>
        <section class="container mx-auto px-4 py-12 text-[#B46D6D]">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Diva Collection</p>
                        <h1 class="mt-2 text-4xl font-extrabold tracking-wide">Каталог ювелірних виробів</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-[#8D6767]">
                            Підбирайте прикраси за категорією, бюджетом і способом сортування. Каталог оновлюється без повного перезавантаження сторінки.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[#E9CFCF] bg-[#FFF8F8] px-4 py-3 text-sm text-[#8D6767]">
                        <strong class="text-[#B46D6D]">{{ products.total }}</strong> товарів знайдено
                    </div>
                </div>

                <form
                    class="mb-10 rounded-[2rem] border border-[#E7C5C5] bg-white/95 p-6 shadow-sm"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Пошук</span>
                            <input
                                v-model="localFilters.search"
                                type="search"
                                placeholder="Назва прикраси"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            />
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Категорія</span>
                            <select
                                v-model="localFilters.category_id"
                                class="rounded-2xl border border-[#E3BEBE] px-4 py-3 text-[#6D4C4C] focus:border-[#B46D6D] focus:outline-none focus:ring-2 focus:ring-[#E7B7B7]"
                            >
                                <option value="">Всі категорії</option>
                                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                    {{ category.name }}
                                </option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-2 text-sm font-medium">
                            <span>Мін. ціна</span>
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
                            <span>Макс. ціна</span>
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
                            <span>Сортування</span>
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

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button
                            type="submit"
                            :disabled="isFiltering"
                            class="rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ isFiltering ? 'Оновлюємо каталог...' : 'Застосувати фільтри' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[#E3BEBE] px-6 py-3 text-sm font-semibold transition hover:bg-[#FFF1F1]"
                            @click="resetFilters"
                        >
                            Скинути
                        </button>
                        <span class="text-xs text-[#9B7B7B]">
                            Діапазон цін: {{ formatPrice(priceRange.min) }} ₴ - {{ formatPrice(priceRange.max) }} ₴
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
                        <h2 class="text-2xl font-bold">Нічого не знайдено</h2>
                        <p class="mt-3 text-sm text-[#8D6767]">Спробуйте послабити фільтри або змінити пошуковий запит.</p>
                        <button
                            type="button"
                            class="mt-6 rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757]"
                            @click="resetFilters"
                        >
                            Скинути фільтри
                        </button>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="product in products.data"
                            :key="product.id"
                            class="flex h-full flex-col rounded-[2rem] border border-[#E3BEBE] bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                        >
                            <img
                                :src="product.image_path"
                                :alt="`Фото товару ${product.name}`"
                                class="mb-5 aspect-[4/3] w-full rounded-[1.5rem] object-cover"
                            />

                            <div class="flex flex-1 flex-col">
                                <div class="mb-3 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-[#C49B9B]">{{ product.category.name }}</p>
                                        <h2 class="mt-2 text-2xl font-bold leading-tight">{{ product.name }}</h2>
                                    </div>
                                    <span class="rounded-full bg-[#FFF2F2] px-3 py-1 text-xs font-semibold text-[#A05F5F]">
                                        {{ formatPrice(product.price) }} ₴
                                    </span>
                                </div>

                                <p class="mb-6 flex-1 text-sm leading-6 text-[#8D6767]">
                                    {{ product.description || 'Лаконічна прикраса для повсякденних і святкових образів.' }}
                                </p>

                                <button
                                    type="button"
                                    :disabled="isAdding(product.id)"
                                    class="w-full rounded-full bg-[#B46D6D] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                                    @click="addToCart(product.id)"
                                >
                                    {{ isAdding(product.id) ? 'Додаємо...' : 'До кошика' }}
                                </button>
                            </div>
                        </article>
                    </div>
                </div>

                <nav v-if="products.links.length > 3" class="mt-12 flex flex-wrap justify-center gap-3" aria-label="Пагінація каталогу">
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
import { router } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'
import { useCartUi } from '@/composables/useCartUi'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
    priceRange: Object,
    sortOptions: Array,
})

const { adjust } = useCartUi()
const { error } = useToast()
const isFiltering = ref(false)
const pendingCartIds = ref([])
const localFilters = ref(buildFilters(props.filters))

watch(() => props.filters, (filters) => {
    localFilters.value = buildFilters(filters)
}, { deep: true })

function buildFilters(filters = {}) {
    return {
        search: filters.search ?? '',
        category_id: filters.category_id ? String(filters.category_id) : '',
        min_price: filters.min_price ?? '',
        max_price: filters.max_price ?? '',
        sort: filters.sort ?? 'name_asc',
    }
}

function normalizedFilters() {
    return Object.fromEntries(
        Object.entries(localFilters.value).filter(([, value]) => value !== '' && value !== null)
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
            error('Не вдалося додати товар до кошика. Спробуйте ще раз.')
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
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}
</script>
