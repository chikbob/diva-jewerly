<template>
    <div
        class="pointer-events-none fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-3"
        aria-live="polite"
        aria-atomic="true"
    >
        <transition-group name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto rounded-2xl border px-4 py-3 shadow-lg backdrop-blur"
                :class="toastClass(toast.type)"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 h-2.5 w-2.5 rounded-full" :class="dotClass(toast.type)" />
                    <div class="min-w-0 flex-1 text-sm font-medium">{{ toast.message }}</div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-current opacity-70 transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current"
                        @click="dismiss(toast.id)"
                        aria-label="Закрити повідомлення"
                    >
                        ×
                    </button>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()

function toastClass(type) {
    return {
        'border-emerald-200 bg-emerald-50 text-emerald-800': type === 'success',
        'border-red-200 bg-red-50 text-red-800': type === 'error',
        'border-slate-200 bg-white/90 text-slate-800': type !== 'success' && type !== 'error',
    }
}

function dotClass(type) {
    return {
        'bg-emerald-500': type === 'success',
        'bg-red-500': type === 'error',
        'bg-slate-500': type !== 'success' && type !== 'error',
    }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
