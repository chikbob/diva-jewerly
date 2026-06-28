<template>
    <page-layout>
        <section class="mx-auto w-full max-w-[1480px] px-4 py-12 sm:px-6 xl:px-8">
            <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Account Area</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-wide text-[#B46D6D]">User Profile</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[#8D6767]">
                        Manage profile details, account security, and session controls in one place.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[28rem]">
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Name</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ user.name }}</p>
                    </div>
                    <div class="rounded-[1.6rem] border border-[#E9CFCF] bg-[#FFF8F8] px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#C49B9B]">Email</p>
                        <p class="mt-2 text-base font-bold text-[#B46D6D]">{{ user.email }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
                <form @submit.prevent="updateProfile" class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-[0_18px_50px_rgba(180,109,109,0.07)] space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Basic Details</p>
                        <h2 class="mt-2 text-2xl font-bold text-[#B46D6D]">Contact Information</h2>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Name</label>
                        <input
                            v-model="profileForm.name"
                            type="text"
                            class="w-full rounded-2xl border px-4 py-3 transition focus:outline-none focus:ring-2"
                            :class="fieldClass(profileForm.errors.name)"
                        />
                        <p v-if="profileForm.errors.name" class="mt-2 text-sm text-red-600">{{ profileForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Email</label>
                        <input
                            v-model="profileForm.email"
                            type="email"
                            class="w-full rounded-2xl border px-4 py-3 transition focus:outline-none focus:ring-2"
                            :class="fieldClass(profileForm.errors.email)"
                        />
                        <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-600">{{ profileForm.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-[#B46D6D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9E5757] disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="profileForm.processing"
                    >
                        {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                    </button>

                    <p v-if="profileSuccessMessage" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                        {{ profileSuccessMessage }}
                    </p>
                </form>

                <form @submit.prevent="updatePassword" class="rounded-[2rem] border border-[#E7C5C5] bg-white p-8 shadow-[0_18px_50px_rgba(180,109,109,0.07)] space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Security</p>
                        <h2 class="mt-2 text-2xl font-bold text-[#B46D6D]">Update Password</h2>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Current Password</label>
                        <input
                            v-model="passwordForm.current_password"
                            type="password"
                            class="w-full rounded-2xl border px-4 py-3 transition focus:outline-none focus:ring-2"
                            :class="fieldClass(passwordForm.errors.current_password)"
                        />
                        <p v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">New Password</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            class="w-full rounded-2xl border px-4 py-3 transition focus:outline-none focus:ring-2"
                            :class="fieldClass(passwordForm.errors.password)"
                        />
                        <p v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Confirm New Password</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            class="w-full rounded-2xl border border-[#E3BEBE] px-4 py-3 transition focus:outline-none focus:ring-2 focus:border-[#B46D6D] focus:ring-[#E7B7B7]"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-[#6D4C4C] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#5A3B3B] disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="passwordForm.processing"
                    >
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>

                    <p v-if="passwordSuccessMessage" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                        {{ passwordSuccessMessage }}
                    </p>
                </form>

                <form @submit.prevent="deleteAccount" class="rounded-[2rem] border border-rose-200 bg-white p-8 shadow-[0_18px_50px_rgba(180,109,109,0.07)] space-y-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-rose-400">Warning</p>
                        <h2 class="mt-2 text-2xl font-bold text-rose-700">Delete Account</h2>
                        <p class="mt-3 text-sm leading-7 text-[#8D6767]">
                            After password confirmation, the account will be deleted. Use this action only if you are sure.
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#6D4C4C]">Confirm password to delete the account</label>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            class="w-full rounded-2xl border border-rose-200 px-4 py-3 transition focus:outline-none focus:ring-2 focus:border-rose-300 focus:ring-rose-100"
                        />
                        <p v-if="deleteForm.errors.password" class="mt-2 text-sm text-red-600">{{ deleteForm.errors.password }}</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-full bg-rose-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="deleteForm.processing"
                    >
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete Account' }}
                    </button>
                </form>

                <form @submit.prevent="logout" class="rounded-[2rem] border border-[#E7C5C5] bg-[#FFF9F9] p-8 text-center shadow-[0_18px_50px_rgba(180,109,109,0.05)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#D09A9A]">Session</p>
                    <h2 class="mt-2 text-2xl font-bold text-[#B46D6D]">Log Out of Account</h2>
                    <p class="mt-3 text-sm leading-7 text-[#8D6767]">
                        End the current session on this device when you are done using your account.
                    </p>
                    <button
                        type="submit"
                        class="mt-6 rounded-full border border-[#E3BEBE] px-6 py-3 text-sm font-semibold text-[#B46D6D] transition hover:bg-[#FFF1F1]"
                    >
                        Log Out
                    </button>
                </form>
            </div>
        </section>
    </page-layout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import PageLayout from '@/Components/page-layout.vue'

const props = defineProps({ user: Object })

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
            profileSuccessMessage.value = 'Profile updated successfully!'
        },
    })
}

function updatePassword() {
    passwordForm.put(route('password.update'), {
        onSuccess: () => {
            passwordSuccessMessage.value = 'Password updated successfully!'
            passwordForm.reset()
        },
    })
}

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'))
}

function logout() {
    router.post(route('logout'))
}

function fieldClass(hasError) {
    return hasError
        ? 'border-red-300 focus:border-red-400 focus:ring-red-200'
        : 'border-[#E3BEBE] focus:border-[#B46D6D] focus:ring-[#E7B7B7]'
}
</script>
