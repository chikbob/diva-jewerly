import { reactive, readonly } from 'vue'

const state = reactive({
    items: [],
})

let nextId = 1

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

    if (duration > 0) {
        window.setTimeout(() => dismissToast(id), duration)
    }

    return id
}

function dismissToast(id) {
    state.items = state.items.filter((toast) => toast.id !== id)
}

export function useToast() {
    return {
        toasts: readonly(state).items,
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
