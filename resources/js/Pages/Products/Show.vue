<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 text-[#6D4C4C] sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Вітрина DIVA</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D] xl:text-5xl">{{ product.name }}</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Детальна сторінка прикраси з ключовою інформацією, статусом доступності та швидкими діями для покупки.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[33rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Категорія</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ product.category.name }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Ціна</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ formatPrice(product.price) }} ₴</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Статус</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ availability.label }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <Link
                    :href="route('catalog')"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-[#B46D6D] transition hover:text-[#9E5757]"
                >
                    <span aria-hidden="true">←</span>
                    <span>Повернутися до каталогу</span>
                </Link>
            </div>

            <div class="grid gap-8 rounded-[2.25rem] border border-[#E7C5C5] bg-white p-6 shadow-[0_24px_70px_rgba(180,109,109,0.08)] xl:grid-cols-[1.08fr_0.92fr] xl:p-8">
                <div class="overflow-hidden rounded-[1.9rem] border border-[#F3E2E2] bg-[#FFF8F8]">
                    <img
                        :src="product.image_path"
                        :alt="`Фото товару ${product.name}`"
                        class="aspect-[4/3] h-full w-full object-cover xl:aspect-[5/4]"
                    />
                </div>

                <div class="flex flex-col justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#FFF1F1] px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-[#A05F5F]">
                            {{ product.category.name }}
                        </span>
                        <span class="rounded-full px-4 py-2 text-xs font-semibold" :class="availabilityClass">
                            {{ availability.label }}
                        </span>
                        <span
                            v-if="isNewProduct"
                            class="rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700"
                        >
                            Новинка
                        </span>
                    </div>

                    <p class="mt-6 text-4xl font-black text-[#A05F5F] xl:text-[2.75rem]">
                        {{ formatPrice(product.price) }} ₴
                    </p>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-[#7E6161]">
                        {{ product.description || 'Ювелірний виріб із колекції Diva з акцентом на щоденну елегантність.' }}
                    </p>

                    <div class="mt-8 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-[1.6rem] border border-[#F3E2E2] bg-[#FFF8F8] p-5">
                            <p class="text-sm uppercase tracking-[0.25em] text-[#C49B9B]">Статус товару</p>
                            <p class="mt-2 text-lg font-semibold text-[#6D4C4C]">{{ availability.label }}</p>
                            <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                                Підійде для швидкого замовлення або додавання в обране без зайвих переходів.
                            </p>
                        </div>
                        <div class="rounded-[1.6rem] border border-[#F3E2E2] bg-[#FFF8F8] p-5">
                            <p class="text-sm uppercase tracking-[0.25em] text-[#C49B9B]">Рекомендація</p>
                            <p class="mt-2 text-lg font-semibold text-[#6D4C4C]">Поєднуйте в сет</p>
                            <p class="mt-2 text-sm leading-6 text-[#8D6767]">
                                Перейдіть до інших товарів цієї категорії, щоб зібрати завершений ювелірний образ.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-3 xl:grid-cols-3">
                        <button
                            type="button"
                            :disabled="isFavoritePending"
                            class="rounded-full border border-[#E3BEBE] px-6 py-4 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60"
                            :class="isFavorite ? 'bg-[#FFF1F1] text-[#B46D6D]' : 'text-[#8D6767] hover:bg-[#FFF1F1]'"
                            @click="toggleFavorite"
                        >
                            {{ isFavorite ? 'В обраному' : 'В обране' }}
                        </button>
                        <button
                            type="button"
                            :disabled="isAdding"
                            class="rounded-full bg-[#B46D6D] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                            @click="addToCart"
                        >
                            {{ isAdding ? 'Додаємо...' : actionLabel }}
                        </button>
                        <Link
                            :href="route('catalog', { category_id: product.category.id })"
                            class="rounded-full border border-[#E3BEBE] px-6 py-4 text-center text-sm font-semibold text-[#8D6767] transition hover:bg-[#FFF1F1]"
                        >
                            Ще в цій категорії
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'
import { useCartUi } from '@/composables/useCartUi'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    product: Object,
    availability: Object,
    isFavorited: Boolean,
})

const page = usePage()
const { adjust } = useCartUi()
const { error } = useToast()
const isAdding = ref(false)
const isFavoritePending = ref(false)
const isFavorite = ref(props.isFavorited)
const isAuthenticated = computed(() => Boolean(page.props.auth?.user))
const actionLabel = computed(() => (isAuthenticated.value ? 'Додати в кошик' : 'Увійти, щоб додати'))
const availabilityClass = computed(() => ({
    'bg-emerald-50 text-emerald-700': props.availability?.code === 'available',
    'bg-amber-50 text-amber-700': props.availability?.code !== 'available',
}))
const isNewProduct = computed(() => {
    if (!props.product?.created_at) {
        return false
    }

    const createdAt = new Date(props.product.created_at)
    const threshold = new Date()
    threshold.setDate(threshold.getDate() - 30)

    return createdAt >= threshold
})

function toggleFavorite() {
    if (isFavoritePending.value) {
        return
    }

    if (!isAuthenticated.value) {
        router.visit(route('login'))

        return
    }

    const nextValue = !isFavorite.value
    isFavoritePending.value = true
    isFavorite.value = nextValue

    const options = {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            isFavorite.value = !nextValue
            error('Не вдалося оновити обране. Спробуйте ще раз.')
        },
        onFinish: () => {
            isFavoritePending.value = false
        },
    }

    if (nextValue) {
        router.post(route('favorites.store', { product: props.product.id }), {}, options)

        return
    }

    router.delete(route('favorites.destroy', { product: props.product.id }), options)
}

function addToCart() {
    if (isAdding.value) {
        return
    }

    if (!isAuthenticated.value) {
        router.visit(route('login'))

        return
    }

    isAdding.value = true
    adjust(1)

    router.post(route('cart.add'), {
        product_id: props.product.id,
    }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            adjust(-1)
            error('Не вдалося додати товар до кошика. Спробуйте ще раз.')
        },
        onFinish: () => {
            isAdding.value = false
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
