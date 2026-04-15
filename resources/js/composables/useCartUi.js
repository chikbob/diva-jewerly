import { ref } from 'vue'

const optimisticCount = ref(null)

export function useCartUi() {
    function sync(count) {
        optimisticCount.value = Math.max(0, Number(count ?? 0))
    }

    function adjust(delta) {
        optimisticCount.value = Math.max(0, Number(optimisticCount.value ?? 0) + Number(delta))
    }

    function set(count) {
        optimisticCount.value = Math.max(0, Number(count ?? 0))
    }

    return {
        count: optimisticCount,
        sync,
        adjust,
        set,
    }
}
