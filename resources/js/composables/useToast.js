import { reactive, readonly } from 'vue'

const state = reactive({
    items: [],
})

let nextId = 1
const timers = new Map()

function pushToast(message, type = 'info', duration = 4000) {
    if (!message) {
        return null
    }

    const id = nextId++

    state.items.push({
        id,
        message,
        type,
    })

    if (duration > 0 && typeof window !== 'undefined') {
        const timeoutId = window.setTimeout(() => dismissToast(id), duration)
        timers.set(id, timeoutId)
    }

    return id
}

function dismissToast(id) {
    const timeoutId = timers.get(id)

    if (timeoutId && typeof window !== 'undefined') {
        window.clearTimeout(timeoutId)
        timers.delete(id)
    }

    const index = state.items.findIndex((toast) => toast.id === id)

    if (index !== -1) {
        state.items.splice(index, 1)
    }
}

export function useToast() {
    return {
        toasts: readonly(state.items),
        show(message, options = {}) {
            return pushToast(message, options.type ?? 'info', options.duration ?? 4000)
        },
        success(message, options = {}) {
            return pushToast(message, 'success', options.duration ?? 4000)
        },
        error(message, options = {}) {
            return pushToast(message, 'error', options.duration ?? 5000)
        },
        dismiss: dismissToast,
    }
}
