<template>
    <page-layout>
        <div class="container mx-auto px-4 py-12 text-[#B46D6D]">
            <h1 class="text-4xl font-extrabold tracking-wide text-center mb-10">Каталог ювелірних виробів</h1>

            <!-- Фільтр -->
            <form @submit.prevent="applyFilters"
                  class="mb-10 flex flex-wrap gap-4 justify-center items-center">
                <input
                    v-model="localFilters.search"
                    type="text"
                    placeholder="Пошук за назвою"
                    class="border border-[#E3BEBE] rounded-full px-6 py-2 focus:outline-none focus:ring-2 focus:ring-[#B46D6D] w-full sm:w-auto"
                />

                <select
                    v-model="localFilters.category_id"
                    class="border border-[#E3BEBE] rounded-full pl-6 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-[#B46D6D] sm:w-auto"
                >
                    <option value="">Всі категорії</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>

                <button
                    type="submit"
                    class="bg-[#B46D6D] text-white px-6 py-2 rounded-full hover:bg-[#9e5757] transition"
                >
                    Застосувати
                </button>
            </form>

            <!-- Товари -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <div
                    v-for="product in products.data"
                    :key="product.id"
                    class="rounded-3xl border border-[#E3BEBE] shadow-md p-6 bg-white hover:shadow-lg transition"
                >
                    <img
                        :src="`${product.image_path}`"
                        alt="Зображення товару"
                        class="w-full h-auto object-cover rounded-2xl mb-4"
                    />
                    <h2 class="text-2xl font-bold mb-2">{{ product.name }}</h2>
                    <p class="text-sm mb-2 text-gray-500">{{ product.category.name }}</p>
                    <p class="text-xl font-semibold mb-4">{{ product.price }} ₴</p>
                    <button
                        type="button"
                        @click="addToCart(product.id)"
                        class="w-full bg-[#B46D6D] text-white py-2 rounded-full hover:bg-[#9e5757] transition"
                    >
                        До кошика
                    </button>
                </div>
            </div>
            <!-- Пагінація -->
            <div v-if="products.links.length > 3" class="mt-12 flex flex-wrap gap-3 justify-center">
                <button
                    v-for="(link, i) in products.links"
                    :key="i"
                    :disabled="!link.url"
                    @click.prevent="goToPage(link.url)"
                    class="px-4 py-2 rounded-full border transition"
                    :class="{
            'bg-[#B46D6D] text-white': link.active,
            'text-gray-400 cursor-not-allowed': !link.url,
            'hover:bg-[#F4E3E3]': link.url && !link.active
          }"
                    v-html="link.label"
                />
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import {router} from '@inertiajs/vue3'
import {ref} from 'vue'
import PageLayout from "@/Components/page-layout.vue"

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
})


function addToCart(productId) {
    router.post(route('cart.add'), {
        product_id: productId
    }, {
        preserveScroll: true
    })
}

function goToPage(url) {
    router.get(url, localFilters.value, {
        preserveScroll: true,
        preserveState: true
    })
}

const localFilters = ref({
    search: props.filters?.search ?? '',
    category_id: props.filters?.category_id ?? ''
})

function applyFilters() {
    router.get('/catalog', localFilters.value)
}
</script>
