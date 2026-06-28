<template>
    <AdminLayout
        :navigation="navigation"
        :title="mode === 'create' ? `Create: ${resource.label}` : `Edit: ${record?.title}`"
        :description="resource.description"
        eyebrow="Record Form"
    >
        <section class="rounded-[2rem] border border-[#f0d7e3] bg-white/95 p-6 shadow-[0_18px_45px_rgba(180,109,109,0.08)]">
            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div v-for="field in fields" :key="field.name" :class="field.type === 'textarea' ? 'lg:col-span-2' : ''">
                        <label :for="field.name" class="mb-2 block text-sm font-semibold text-[#7f485b]">
                            {{ field.label }}
                        </label>

                        <textarea
                            v-if="field.type === 'textarea'"
                            :id="field.name"
                            v-model="form[field.name]"
                            rows="5"
                            :placeholder="field.placeholder || ''"
                            class="w-full rounded-2xl border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] placeholder:text-[#c08da0] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        />

                        <select
                            v-else-if="field.type === 'select'"
                            :id="field.name"
                            v-model="form[field.name]"
                            class="w-full rounded-2xl border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >
                            <option value="">Choose a value</option>
                            <option v-for="option in field.options" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>

                        <input
                            v-else
                            :id="field.name"
                            v-model="form[field.name]"
                            :type="inputType(field.type)"
                            :step="field.type === 'number' ? '0.01' : undefined"
                            :placeholder="field.placeholder || ''"
                            class="w-full rounded-2xl border border-[#efcfdb] bg-[#fff7fa] px-5 py-3 text-sm text-[#7f485b] placeholder:text-[#c08da0] focus:border-[#b46d6d] focus:outline-none focus:ring-2 focus:ring-[#f3d3df]"
                        >

                        <p v-if="field.help" class="mt-2 text-xs leading-5 text-[#9b7482]">{{ field.help }}</p>
                        <p v-if="form.errors[field.name]" class="mt-2 text-sm text-rose-600">{{ form.errors[field.name] }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-[#f6e3ea] pt-6 sm:flex-row">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-full bg-[#b46d6d] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#9e5757] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <Link
                        :href="route('admin.resources.index', { resource: resource.key })"
                        class="rounded-full border border-[#efcfdb] px-6 py-3 text-center text-sm font-semibold text-[#9e5757] transition hover:bg-[#fff1f5]"
                    >
                        Back to List
                    </Link>
                </div>
            </form>
        </section>
    </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    navigation: Array,
    resource: Object,
    mode: String,
    record: Object,
    fields: Array,
})

const form = useForm(
    props.fields.reduce((payload, field) => ({
        ...payload,
        [field.name]: field.value ?? '',
    }), {})
)

function inputType(type) {
    return {
        email: 'email',
        password: 'password',
        number: 'number',
        url: 'url',
        'datetime-local': 'datetime-local',
    }[type] ?? 'text'
}

function submit() {
    const routeParams = props.record
        ? { resource: props.resource.key, record: props.record.id }
        : { resource: props.resource.key }

    if (props.mode === 'create') {
        form.post(route('admin.resources.store', routeParams))

        return
    }

    form.put(route('admin.resources.update', routeParams))
}
</script>
