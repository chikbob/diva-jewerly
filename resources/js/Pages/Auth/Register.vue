<template>
    <page-layout>
        <div class="min-h-[100dvh] flex items-center justify-center px-4">
            <div class="w-full max-w-md p-8 rounded-3xl shadow-lg border border-[#F2DCDC]">
                <h2 class="text-3xl font-bold text-center text-[#B46D6D] mb-6">Create an Account</h2>
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">Name</label>
                        <input v-model="form.name" type="text"
                               class="w-full px-4 py-2 border border-[#F2DCDC] rounded-full focus:outline-none focus:ring-2 focus:ring-[#B46D6D] transition"
                               required/>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">Email</label>
                        <input v-model="form.email" type="email"
                               class="w-full px-4 py-2 border border-[#F2DCDC] rounded-full focus:outline-none focus:ring-2 focus:ring-[#B46D6D] transition"
                               required/>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">Password</label>
                        <input v-model="form.password" type="password"
                               class="w-full px-4 py-2 border border-[#F2DCDC] rounded-full focus:outline-none focus:ring-2 focus:ring-[#B46D6D] transition"
                               required/>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm text-gray-600 mb-2">Confirm Password</label>
                        <input v-model="form.password_confirmation" type="password"
                               class="w-full px-4 py-2 border border-[#F2DCDC] rounded-full focus:outline-none focus:ring-2 focus:ring-[#B46D6D] transition"
                               required/>
                    </div>
                    <button
                        class="w-full bg-[#B46D6D] hover:bg-[#a15656] text-white py-2 rounded-full transition duration-200"
                        :disabled="form.processing">
                        Register
                    </button>
                </form>
            </div>
        </div>
    </page-layout>
</template>

<script setup>
import {useForm} from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/register')
}
</script>
