<template>
    <page-layout>
        <div class="relative bg-gradient-to-r from-[#FCE8E8] to-[#F9DCDC] py-20 mb-12 rounded-3xl shadow-inner">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-5xl font-extrabold text-[#B46D6D] mb-4">DIVA Jewelry House</h1>
                <p class="text-xl text-gray-700 mb-6">Refined style, soft details, and expressive pieces for every day</p>
                <Link :href="route('catalog')"
                      class="bg-[#B46D6D] text-white px-6 py-3 rounded-full text-lg hover:bg-[#9e5757] transition">
                    Explore the Catalog
                </Link>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <h2 class="text-3xl font-bold text-[#B46D6D] mb-6">Jewelry Categories</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="bg-white rounded-3xl shadow-md hover:shadow-lg transition p-6 border border-[#F2DCDC]"
                >
                    <img :src="category.image_url"
                         alt="Category image"
                         class="w-full h-64 object-cover rounded-2xl mb-4">
                    <h3 class="text-2xl font-semibold text-[#B46D6D] mb-2">{{ category.name }}</h3>
                    <p class="text-gray-600 mb-4">{{ category.description }}</p>
                    <Link :href="route('catalog', { category_id: category.id })"
                          class="inline-flex items-center gap-2 text-sm text-[#B46D6D] border border-[#B46D6D] px-3 py-1.5 rounded-full hover:bg-[#B46D6D] hover:text-white transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                        </svg>
                        View
                    </Link>
                </div>
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import {Link} from "@inertiajs/vue3";
import PageLayout from "@/Components/page-layout.vue";

const props = defineProps({
    categories: Array
})
</script>
