<template>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_#ffeef4,_#fde5ee_45%,_#f8d8e5_100%)] px-4 py-10 text-[#5f3d48]">
        <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="rounded-[2.5rem] border border-white/70 bg-white/75 p-8 shadow-[0_30px_80px_rgba(180,109,109,0.14)] backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#c67e97]">Backoffice Access</p>
                <h1 class="mt-4 text-5xl font-black leading-tight text-[#7f485b]">
                    The New DIVA Management Console
                </h1>
                <p class="mt-5 max-w-xl text-base leading-8 text-[#8f6674]">
                    Manage catalog content, customer records, orders, and payments from one commercial interface with staff-based access.
                </p>

                <div class="mt-10 grid gap-4 sm:grid-cols-2">
                    <article class="rounded-[1.75rem] border border-[#f0d7e3] bg-[#fff6fa] p-5">
                        <p class="text-sm font-semibold text-[#7f485b]">Catalog and Content</p>
                        <p class="mt-2 text-sm leading-6 text-[#8f6674]">Products, categories, visual assets, and core business data.</p>
                    </article>
                    <article class="rounded-[1.75rem] border border-[#f0d7e3] bg-[#fff6fa] p-5">
                        <p class="text-sm font-semibold text-[#7f485b]">Orders and Payments</p>
                        <p class="mt-2 text-sm leading-6 text-[#8f6674]">Control status flows, technical payment states, and operational history.</p>
                    </article>
                </div>
            </section>

            <section class="rounded-[2.5rem] border border-[#cf9fb0] bg-[linear-gradient(180deg,_#9a5f74_0%,_#7f485b_100%)] p-8 text-white shadow-[0_30px_80px_rgba(116,64,86,0.3)]">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#f7dbe6]">Staff Login</p>
                <h2 class="mt-4 text-3xl font-black tracking-wide">Sign in to Backoffice</h2>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="admin-email" class="mb-2 block text-sm font-semibold text-[#f8e7de]">Email</label>
                        <input
                            id="admin-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            class="w-full rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-white placeholder:text-[#f1cad9] focus:border-[#ffe4ef] focus:outline-none focus:ring-2 focus:ring-[#d9a5b8]"
                            placeholder="staff@diva.local"
                        >
                        <p v-if="form.errors.email" class="mt-2 text-sm text-rose-300">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="admin-password" class="mb-2 block text-sm font-semibold text-[#f8e7de]">Password</label>
                        <input
                            id="admin-password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-white placeholder:text-[#f1cad9] focus:border-[#ffe4ef] focus:outline-none focus:ring-2 focus:ring-[#d9a5b8]"
                            placeholder="Enter your password"
                        >
                    </div>

                    <label class="flex items-center gap-3 text-sm text-[#d7cbc5]">
                        <input v-model="form.remember" type="checkbox" class="rounded border-white/20 bg-white/10">
                        <span>Remember this session</span>
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-full bg-white px-6 py-4 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff2f7] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'Signing in...' : 'Enter Admin Panel' }}
                    </button>
                </form>
            </section>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('admin.authenticate'))
}
</script>
