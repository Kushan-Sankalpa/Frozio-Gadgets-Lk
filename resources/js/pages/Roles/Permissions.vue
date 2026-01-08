<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 container mx-auto">
            <div
                class="mx-auto overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            Update Permissions
                        </h5>

                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
                        <table class="min-w-full border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-zinc-100/60 dark:bg-zinc-800/60 text-sm text-zinc-700 dark:text-zinc-300">
                                    <th class="text-left px-4 py-3 rounded-l-lg w-40 font-medium">Section</th>
                                    <th class="text-center px-4 py-3 font-medium w-32">Select All</th>
                                    <th class="text-left px-4 py-3 rounded-r-lg font-medium">Permissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(section, index) in allPermissions" :key="index"
                                    class="bg-white dark:bg-zinc-900/50 hover:bg-zinc-50 dark:hover:bg-zinc-800/70 transition-colors rounded-lg shadow-sm">
                                    <td
                                        class="px-4 py-4 text-sm font-semibold text-zinc-800 dark:text-zinc-200 capitalize">
                                        {{ index[0].toUpperCase() + index.slice(1).replace('-', " ") }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <CheckboxField :id="'checkbox_' + index.replaceAll(' ', '')"
                                            @click="toggleCheckboxes(index.replaceAll(' ', ''))" size="sm"
                                            class="cursor-pointer" />
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                            <div v-for="perm in section" :key="perm.id"
                                                class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                                                <CheckboxField :class="'perm_checkbox ' + index.replaceAll(' ', '')"
                                                    :id="perm.name" v-model="form.permissions" :value="perm.name"
                                                    size="sm" />
                                                <label class="cursor-pointer text-[13px] capitalize" :for="perm.name">
                                                    {{ perm.name.replace(index + ".", "") }}
                                                </label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>


                        <div class="flex items-center gap-3 justify-end"
                            v-if="$root.hasPermission('roles-permissions.edit')">
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                Update
                            </button>


                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>

import CheckboxField from '@/components/CheckboxField.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export default {
    components: {
        AppLayout,
        CheckboxField,
    },
    props: {
        errors: Object,
        role: Object,
        allPermissions: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                id: null,
                permissions: [],
            }),


        };
    },
    mounted() {
        if (this.role) {
            this.form.id = this.role.id;
            this.checkCheckboxes();
        }
    },

    methods: {
        submit() {

            this.form.post(
                route("roles.permissions.update"),
                {
                    onSuccess: () => {
                        this.resetForm();
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
        resetForm() {
            var self = this;
            Object.keys(this.$data.form).forEach(function (key, index) {
                self.$data.form[key] = "";
            });
            Object.keys(this.$props.errors).forEach(function (key, index) {
                self.$props.errors[key] = "";
            });
        },

        toggleCheckboxes(section_name) {
            const parentCheckbox = document.querySelector(`#checkbox_${section_name}`);
            const sectionPermissions = this.allPermissions[section_name];

            if (!sectionPermissions) return;

            if (parentCheckbox.checked) {
                sectionPermissions.forEach(perm => {
                    if (!this.form.permissions.includes(perm.name)) {
                        this.form.permissions.push(perm.name);
                    }
                });
            } else {
                this.form.permissions = this.form.permissions.filter(
                    name => !sectionPermissions.some(perm => perm.name === name)
                );
            }
        }
        ,

        checkCheckboxes() {
            if (!this.role || !this.role.permissions) return;

            const rolePermNames = this.role.permissions.map(p => p.name);

            this.form.permissions = [...rolePermNames];

            Object.keys(this.allPermissions).forEach(sectionName => {
                const sectionPermissions = this.allPermissions[sectionName].map(p => p.name);
                const parentCheckbox = document.querySelector(`#checkbox_${sectionName.replaceAll(' ', '')}`);
                if (!parentCheckbox) return;

                const allSelected = sectionPermissions.every(p => this.form.permissions.includes(p));
                parentCheckbox.checked = allSelected;
            });
        }

    },
};
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    -webkit-text-fill-color: inherit;
}

table {
    border-collapse: separate;
    border-spacing: 0 0.5rem;
}

tbody tr {
    border-radius: 0.5rem;
}

tbody tr td:first-child {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}

tbody tr td:last-child {
    border-top-right-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
}
</style>
