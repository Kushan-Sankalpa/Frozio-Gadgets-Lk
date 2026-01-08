<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 container mx-auto">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ role ? 'Edit Role' : 'Create Role' }}
                        </h5>

                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
                        <section>


                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <Input id="name" label="Role Name" v-model="form.name" placeholder="Role Name"
                                        :is-required="true" :error="form.errors.name" />

                                </div>
                                <div>
                                    <SelectInputComponent id="dashboard_type" label="Dashboard Type"
                                        :options="[{ id: 'super-admin', name: 'Super Admin Dashboard' }, { id: 'system-admin', name: 'System Admin Dashboard' }, { id: 'owner', name: 'Owner Dashboard' }, { id: 'manager', name: 'Manager Dashboard' }, { id: 'staff', name: 'Staff Dashboard' }]"
                                        :error="form.errors.dashboard_type" v-model="form.dashboard_type" />
                                </div>
                            </div>
                        </section>



                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                {{ role ? 'Update' : 'Save' }}
                            </button>
                        <Link class="btn-secondary" :href="route('roles')">
                            Cancel
                            </Link>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>

import Input from '@/components/InputField.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';


export default {
    components: {
        Link,
        AppLayout,
        Input,
        SelectInputComponent

    },
    props: {
        role: Array,
        errors: Object,
    },
    data() {
        return {
            form: useForm({
                id: '',
                name: '',
                dashboard_type: '',
            }),
        };
    },
    mounted() {
        if (this.role) {
            this.form.id = this.role.id;
            this.form.name = this.role.name;
            this.form.dashboard_type = this.role.dashboard_type;
        }
    },
    methods: {
        submit() {
            this.form.post(
                this.role
                    ? route("roles.update")
                    : route("roles.store"),
                {
                    onSuccess: () => {
                        // this.form.reset();
                        this.$root.showMessage(
                            "success",
                            '<span class="text-success">Success</span><br/>',
                            "A Record Was Created Successfully! "
                        );
                    },
                    onError: () => {
                        this.$root.showMessage(
                            "error",
                            '<span class="text-danger">Error</span><br>',
                            "Something went wrong! "
                        );
                    },
                }
            );
        },
    },
};
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    -webkit-text-fill-color: inherit;
}
</style>
