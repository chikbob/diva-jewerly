<template>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_#fff7fa,_#fdeff4_42%,_#f8e4ea_100%)] text-[#5f3d48]">
        <div class="grid min-h-screen lg:grid-cols-[280px_1fr]">
            <aside class="border-b border-white/20 bg-[linear-gradient(180deg,_#9a5f74_0%,_#7f485b_100%)] px-5 py-6 text-white lg:border-b-0 lg:border-r lg:border-r-white/20">
                <div class="rounded-[2rem] border border-white/20 bg-white/10 px-5 py-5 shadow-[0_20px_50px_rgba(92,42,59,0.24)] backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.35em] text-[#f9dce8]">Backoffice</p>
                    <h1 class="mt-3 text-3xl font-black tracking-[0.18em] text-white">DIVA</h1>
                    <p class="mt-3 text-sm leading-6 text-[#fdeff4]">
                        Commercial workspace for catalog, customer, order, and payment operations.
                    </p>
                </div>

                <nav class="mt-8 space-y-6">
                    <div>
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="route().current('admin.dashboard') ? 'bg-[#ffe8f1] text-[#7f485b] shadow-sm' : 'text-[#fff5f8] hover:bg-white/10'"
                        >
                            <span>Dashboard</span>
                            <span class="text-xs uppercase tracking-[0.25em]">/admin</span>
                        </Link>
                    </div>

                    <div v-for="group in navigation" :key="group.label">
                        <p class="px-4 text-xs font-semibold uppercase tracking-[0.35em] text-[#f1cad9]">
                            {{ group.label }}
                        </p>
                        <div class="mt-3 space-y-2">
                            <Link
                                v-for="item in group.items"
                                :key="item.key"
                                :href="route('admin.resources.index', { resource: item.key })"
                                class="block rounded-2xl px-4 py-3 transition"
                                :class="route().current('admin.resources.*') && route().params.resource === item.key ? 'bg-[#ffe8f1] text-[#7f485b] shadow-sm' : 'text-[#fff5f8] hover:bg-white/10'"
                            >
                                <p class="text-sm font-semibold">{{ item.label }}</p>
                                <p class="mt-1 text-xs leading-5 text-inherit/75">{{ item.description }}</p>
                            </Link>
                        </div>
                    </div>
                </nav>

                <div class="mt-8 rounded-[1.75rem] border border-white/20 bg-white/10 p-4 text-sm text-[#fff2f6]">
                    <p class="font-semibold text-white">DIVA Backoffice</p>
                    <p class="mt-2 leading-6">Operational navigation across customers, products, orders, and payments.</p>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <header class="border-b border-[#f0d7e3] bg-white/80 px-6 py-5 backdrop-blur">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#c67e97]">{{ eyebrow }}</p>
                            <h2 class="mt-2 text-3xl font-black tracking-wide text-[#7f485b]">{{ title }}</h2>
                            <p v-if="description" class="mt-2 max-w-3xl text-sm leading-6 text-[#8f6674]">{{ description }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-full border border-[#f1d8e3] bg-[#fff5f8] px-4 py-2 text-right">
                                <p class="text-xs uppercase tracking-[0.25em] text-[#bf7a92]">Session</p>
                                <p class="mt-1 text-sm font-semibold text-[#7f485b]">{{ backofficeUser?.name || 'Staff' }}</p>
                            </div>
                            <Link
                                :href="route('admin.logout')"
                                method="post"
                                as="button"
                                class="rounded-full bg-[#b46d6d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757]"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-6 py-6">
                    <div
                        v-if="flashMessage"
                        class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                    >
                        {{ flashMessage }}
                    </div>

                    <div
                        v-if="flashError"
                        class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"
                    >
                        {{ flashError }}
                    </div>

                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
    navigation: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    eyebrow: {
        type: String,
        default: 'Admin Panel',
    },
})

const page = usePage()
const backofficeUser = computed(() => page.props.backoffice?.user)
const flashMessage = computed(() => page.props.flash?.message)
const flashError = computed(() => page.props.flash?.error)
</script>
