<template>
    <div class="min-h-screen flex flex-col">
        <Header/>
        <div v-if="flashMessage" class="mx-auto mt-4 w-full max-w-5xl px-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ flashMessage }}
            </div>
        </div>
        <div v-if="flashError" class="mx-auto mt-4 w-full max-w-5xl px-4">
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ flashError }}
            </div>
        </div>
        <main class="flex-1 flex flex-col">
            <slot/>
        </main>
        <Footer/>
    </div>
</template>

<script setup>
import {computed} from "vue";
import {usePage} from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue"

const page = usePage()
const flashMessage = computed(() => page.props.flash?.message ?? '')
const flashError = computed(() => page.props.flash?.error ?? '')
</script>

<style lang="scss" scoped>

</style>
