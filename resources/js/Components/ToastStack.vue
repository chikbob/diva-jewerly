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
                class="pointer-events-auto rounded-[1.35rem] border px-5 py-4 shadow-[0_16px_40px_rgba(46,128,106,0.18)] backdrop-blur"
                :class="toastClass(toast.type)"
                role="status"
            >
                <div class="flex items-center gap-3">
                    <div class="h-2.5 w-2.5 shrink-0 rounded-full" :class="dotClass(toast.type)" />
                    <div class="min-w-0 flex-1 text-sm font-medium leading-6">{{ toast.message }}</div>
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-current opacity-70 transition hover:bg-white/40 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current"
                        @click="dismiss(toast.id)"
                        aria-label="Закрити повідомлення"
                    >
                        <span class="text-lg leading-none">×</span>
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
        'border-emerald-200 bg-emerald-50/95 text-emerald-800': type === 'success',
        'border-red-200 bg-red-50/95 text-red-800': type === 'error',
        'border-slate-200 bg-white/95 text-slate-800': type !== 'success' && type !== 'error',
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
