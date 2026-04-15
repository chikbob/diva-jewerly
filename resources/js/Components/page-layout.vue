<template>
    <div class="min-h-screen flex flex-col">
        <Header/>
        <ToastStack />
        <main class="flex-1 flex flex-col">
            <slot/>
        </main>
        <Footer/>
    </div>
</template>

<script setup>
import {computed, watch} from "vue";
import {usePage} from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue"
import ToastStack from "@/Components/ToastStack.vue";
import {useToast} from "@/composables/useToast";

const page = usePage()
const flashMessage = computed(() => page.props.flash?.message ?? '')
const flashError = computed(() => page.props.flash?.error ?? '')
const {success, error} = useToast()

watch(flashMessage, (message, previousMessage) => {
    if (message && message !== previousMessage) {
        success(message)
    }
}, { immediate: true })

watch(flashError, (message, previousMessage) => {
    if (message && message !== previousMessage) {
        error(message)
    }
}, { immediate: true })
</script>

<style lang="scss" scoped>

</style>
