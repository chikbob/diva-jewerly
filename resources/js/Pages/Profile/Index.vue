<template>
    <page-layout>
        <div class="container mx-auto px-4 py-12 max-w-xl">
            <h1 class="text-4xl font-bold text-center mb-10 text-[#B46D6D]">Профіль користувача</h1>

            <form @submit.prevent="updateProfile" class="bg-white rounded-2xl shadow-lg p-8 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ім'я</label>
                    <input
                        v-model="profileForm.name"
                        type="text"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    />
                    <p v-if="profileForm.errors.name" class="mt-2 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input
                        v-model="profileForm.email"
                        type="email"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    />
                    <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-600">{{ profileForm.errors.email }}</p>
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl transition duration-200"
                        :disabled="profileForm.processing"
                    >
                        {{ profileForm.processing ? 'Збереження...' : 'Зберегти профіль' }}
                    </button>
                </div>

                <p v-if="profileSuccessMessage" class="text-green-600 text-center font-medium mt-2">
                    {{ profileSuccessMessage }}
                </p>
            </form>

            <form @submit.prevent="updatePassword" class="bg-white rounded-2xl shadow-lg p-8 mt-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Поточний пароль</label>
                    <input
                        v-model="passwordForm.current_password"
                        type="password"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    />
                    <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Новий пароль</label>
                    <input
                        v-model="passwordForm.password"
                        type="password"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    />
                    <p v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Підтвердіть новий пароль</label>
                    <input
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-6 rounded-xl transition duration-200"
                    :disabled="passwordForm.processing"
                >
                    {{ passwordForm.processing ? 'Оновлення...' : 'Оновити пароль' }}
                </button>

                <p v-if="passwordSuccessMessage" class="text-green-600 text-center font-medium mt-2">
                    {{ passwordSuccessMessage }}
                </p>
            </form>

            <form @submit.prevent="deleteAccount" class="bg-white rounded-2xl shadow-lg p-8 mt-6 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Підтвердіть пароль для видалення акаунту</label>
                    <input
                        v-model="deleteForm.password"
                        type="password"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400 transition"
                    />
                    <p v-if="deleteForm.errors.password" class="mt-2 text-sm text-red-600">{{ deleteForm.errors.password }}</p>
                </div>

                <button
                    type="submit"
                    class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-xl transition duration-200"
                    :disabled="deleteForm.processing"
                >
                    {{ deleteForm.processing ? 'Видалення...' : 'Видалити акаунт' }}
                </button>
            </form>

            <form @submit.prevent="logout" class="text-center mt-6">
                <button
                    type="submit"
                    class="text-red-500 font-semibold hover:underline transition duration-150"
                >
                    Вийти з акаунту
                </button>
            </form>
        </div>
    </page-layout>
</template>

<script setup>
import {ref} from 'vue'
import {router, useForm} from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const props = defineProps({user: Object})

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const deleteForm = useForm({
    password: '',
})

const profileSuccessMessage = ref('')
const passwordSuccessMessage = ref('')

function updateProfile() {
    profileForm.patch(route('profile.update'), {
        onSuccess: () => {
            profileSuccessMessage.value = 'Профіль успішно оновлено!'
        }
    })
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        onSuccess: () => {
            passwordSuccessMessage.value = 'Пароль успішно оновлено!'
            passwordForm.reset()
        }
    })
}

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'))
}

function logout() {
    router.post(route('logout'))
}
</script>
