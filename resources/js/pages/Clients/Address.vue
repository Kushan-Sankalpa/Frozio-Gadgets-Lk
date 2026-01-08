<template>
    <section>

       <div
        v-if="!hideHeader"
        class="flex items-center justify-between gap-3 border-b border-zinc-200 dark:border-zinc-700/60"
    >
        <div>
            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                Address
            </h5>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Add the address for this client.
            </p>
        </div>
    </div>

        

        <!-- Address type selector -->
        <div class="mt-3">
            <div class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</div>

            <div class="grid grid-cols-3 gap-3" role="radiogroup" aria-label="Address type">
                <button v-for="opt in typeOptions" :key="opt.value" type="button" role="radio"
                    :aria-checked="local.type === opt.value" @click="local.type = opt.value" class="group relative rounded-xl border bg-white p-4 text-center transition cursor-pointer
                 dark:bg-zinc-900" :class="local.type === opt.value
                    ? 'border-rose-500 ring-2 ring-rose-200 dark:ring-rose-500/30'
                    : 'border-zinc-200 hover:border-zinc-300 dark:border-zinc-700/60 dark:hover:border-zinc-600'">
                    <!-- check badge -->
                    <span v-if="local.type === opt.value"
                        class="absolute -top-2 -left-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-white shadow ring-4 ring-white dark:ring-zinc-900">
                        <i class="bx bx-check text-base leading-none"></i>
                    </span>

                    <!-- icon -->
                    <i :class="['bx', opt.icon, 'text-3xl mb-2 block',
                        local.type === opt.value
                            ? 'text-rose-600 dark:text-rose-400'
                            : 'text-zinc-500 group-hover:text-zinc-700 dark:text-zinc-400 dark:group-hover:text-zinc-200']"></i>

                    <!-- label -->
                    <div :class="[
                        'text-sm font-semibold',
                        local.type === opt.value
                            ? 'text-rose-700 dark:text-rose-200'
                            : 'text-zinc-800 dark:text-zinc-200'
                    ]">
                        {{ opt.label }}
                    </div>

                    <!-- hidden radio for a11y/forms -->
                    <input type="radio" class="sr-only" name="address_type" :value="opt.value"
                        :checked="local.type === opt.value" @change="local.type = opt.value" />
                </button>
            </div>

            <p v-if="err('type')" class="mt-2 text-sm text-rose-600">{{ err('type') }}</p>
        </div>

        <!-- Fields card -->
        <div class="mt-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700/60">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Input id="addr-address" label="Address" v-model="local.address" :error="err('address')" />
                <Input id="addr-district" label="District" v-model="local.district" :error="err('district')" />
                <Input id="addr-city" label="City" v-model="local.city" :error="err('city')" />
                <Input id="addr-postcode" label="Postcode / ZIP" v-model="local.postcode" :error="err('postcode')" />
                <SelectInputComponent id="addr-country" label="Country" v-model="local.country_id"
                    :options="countrySelectOpts" placeholder="Select an option" :error="err('country_id')" />
            </div>
        </div>
    </section>
</template>

<script>
import Input from '@/components/InputField.vue'
import SelectInputComponent from '@/components/SelectInputComponent.vue'

export default {
    name: 'ClientAddress',
    components: { Input, SelectInputComponent },
    props: {

        modelValue: { type: Object, default: () => ({}) },
        countries: { type: Array, default: () => [] },

        errors: { type: Object, default: () => ({}) },
         hideHeader: {
        type: Boolean,
        default: false,
    },
    },
    data() {
        return {
            local: this.normalize(this.modelValue),
            typeOptions: [
                { value: 'home', label: 'Home', icon: 'bx-home' },
                { value: 'work', label: 'Work', icon: 'bx-briefcase' },
                { value: 'other', label: 'Other', icon: 'bx-dots-horizontal-rounded' },
            ],
        }
    },
    computed: {
        countrySelectOpts() {
            const sorted = (this.countries || []).slice().sort((a, b) => a.name.localeCompare(b.name))
            return sorted.map(c => ({ value: c.id, label: c.name }))
        },
    },
    watch: {

        local: {
            deep: true,
            handler(val) {
                this.$emit('update:modelValue', { ...val })
            },
        },

        modelValue: {
            deep: true,
            handler(val) {

                const normalized = this.normalize(val || {})


                Object.assign(this.local, normalized)
            },
        },
    },
    methods: {
        normalize(obj) {
            return {
                type: obj?.type || 'home',
                address: obj?.address || '',
                district: obj?.district || '',
                city: obj?.city || '',
                postcode: obj?.postcode || '',
                country_id: obj?.country_id ?? null,
            }
        },

        err(field) {
            return this.errors?.[field] ?? this.errors?.[`address.${field}`]
        },
    },
}
</script>

<style scoped></style>
