<template>
    <AdminLayout
        :navigation="navigation"
        :title="resource.label"
        :description="resource.description"
        eyebrow="Керування даними"
    >
        <section class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <form class="flex flex-1 flex-col gap-3" @submit.prevent="applyFilters">
                    <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap">
                        <input
                            v-if="resource.canSearch"
                            v-model="localFilters.search"
                            type="text"
                            placeholder="Пошук по таблиці"
                            class="min-w-[260px] flex-1 rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] placeholder:text-[#c08da0] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >

                        <select
                            v-if="resource.sorts?.length"
                            v-model="localFilters.sort"
                            class="rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="">Сортування за замовчуванням</option>
                            <option v-for="sort in resource.sorts" :key="sort.name" :value="sort.name">
                                {{ sort.label }}
                            </option>
                        </select>

                        <select
                            v-if="resource.sorts?.length"
                            v-model="localFilters.direction"
                            class="rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="desc">За спаданням</option>
                            <option value="asc">За зростанням</option>
                        </select>

                        <select
                            v-for="filter in resource.filters"
                            :key="filter.name"
                            v-model="localFilters[filter.name]"
                            class="rounded-full border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="">{{ filter.label }}</option>
                            <option v-for="option in filter.options" :key="option.value" :value="String(option.value)">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button
                            type="submit"
                            :disabled="isFiltering"
                            class="rounded-full bg-[#b46d6d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ isFiltering ? 'Оновлюємо...' : 'Застосувати' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#8f6674] transition hover:bg-[#fff1f5]"
                            @click="resetFilters"
                        >
                            Скинути
                        </button>
                    </div>
                </form>

                <div class="flex flex-wrap gap-3">
                    <Link
                        v-if="resource.key === 'orders'"
                        :href="route('admin.reports.orders')"
                        class="inline-flex rounded-full border border-[#efcfdb] px-5 py-3 text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                    >
                        Сформувати звіт
                    </Link>
                    <Link
                        v-if="resource.permissions.create"
                        :href="route('admin.resources.create', { resource: resource.key })"
                        class="inline-flex rounded-full bg-[#b46d6d] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757]"
                    >
                        Створити запис
                    </Link>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-[#f6e3ea] text-xs uppercase tracking-[0.25em] text-[#bc8da0]">
                            <th class="px-4 py-3">Запис</th>
                            <th v-for="column in resource.columns" :key="column.key" class="px-4 py-3">
                                {{ column.label }}
                            </th>
                            <th class="px-4 py-3 text-right">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="records.data.length === 0">
                            <td :colspan="resource.columns.length + 2" class="px-4 py-10 text-center text-sm text-[#9b7482]">
                                За поточними фільтрами записи не знайдено.
                            </td>
                        </tr>
                        <tr
                            v-for="record in records.data"
                            :key="record.id"
                            class="border-b border-[#faedf2] align-top last:border-b-0"
                        >
                            <td class="px-4 py-4">
                                <p class="font-semibold text-[#7f485b]">{{ record.title }}</p>
                                <p class="mt-1 text-xs text-[#b28494]">ID: {{ record.id }}</p>
                            </td>
                            <td
                                v-for="cell in record.cells"
                                :key="`${record.id}-${cell.key}`"
                                class="px-4 py-4 text-[#7f626d]"
                            >
                                {{ cell.value }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        v-if="resource.permissions.update"
                                        :href="route('admin.resources.edit', { resource: resource.key, record: record.id })"
                                        class="rounded-full border border-[#efcfdb] px-4 py-2 text-xs font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                                    >
                                        Редагувати
                                    </Link>
                                    <button
                                        v-if="resource.permissions.delete"
                                        type="button"
                                        class="rounded-full border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                        @click="destroy(record)"
                                    >
                                        Видалити
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="records.links.length > 3" class="mt-6 flex flex-wrap gap-2">
                <button
                    v-for="(link, index) in records.links"
                    :key="index"
                    :disabled="!link.url"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition"
                    :class="{
                        'border-[#b46d6d] bg-[#b46d6d] text-white': link.active,
                        'border-[#efcfdb] text-[#c8a6b3]': !link.url,
                        'border-[#efcfdb] text-[#9e5757] hover:bg-[#fff1f5]': link.url && !link.active,
                    }"
                    v-html="link.label"
                    @click.prevent="goToPage(link.url)"
                />
            </nav>
        </section>
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    navigation: Array,
    resource: Object,
    filters: Object,
    records: Object,
})

const localFilters = ref(buildFilters(props.filters))
const isFiltering = ref(false)

watch(() => props.filters, (filters) => {
    localFilters.value = buildFilters(filters)
}, { deep: true })

function buildFilters(filters = {}) {
    const state = {
        search: filters.search ?? '',
        sort: filters.sort ?? '',
        direction: filters.direction ?? 'desc',
    }

    for (const filter of props.resource.filters ?? []) {
        state[filter.name] = filters[filter.name] ?? ''
    }

    return state
}

function applyFilters() {
    router.get(route('admin.resources.index', { resource: props.resource.key }), normalizedFilters(), {
        preserveScroll: true,
        preserveState: true,
        onStart: () => {
            isFiltering.value = true
        },
        onFinish: () => {
            isFiltering.value = false
        },
    })
}

function resetFilters() {
    localFilters.value = buildFilters({})
    applyFilters()
}

function normalizedFilters() {
    return Object.fromEntries(
        Object.entries(localFilters.value).filter(([, value]) => value !== '' && value !== null)
    )
}

function goToPage(url) {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

function destroy(record) {
    if (!window.confirm(`Видалити запис "${record.title}"?`)) {
        return
    }

    router.delete(route('admin.resources.destroy', {
        resource: props.resource.key,
        record: record.id,
    }), {
        preserveScroll: true,
    })
}
</script>
