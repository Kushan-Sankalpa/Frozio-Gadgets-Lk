<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 container mx-auto">

            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

                <!-- Header -->
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ user ? "Edit User" : "Create User" }}
                        </h5>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Manage user details.</p>
                    </div>
                </div>

                <!-- Body with Sidebar & Content -->
                <div class="px-4 sm:px-5 py-6 grid grid-cols-12 gap-6 md:gap-8">

                    <!-- LEFT SIDEBAR -->
                    <aside
                        class="col-span-12 md:col-span-3 border-r border-zinc-200 dark:border-zinc-700/60 pr-4 space-y-1">

                        <button @click="activeTab = 'profile'" :class="tabClasses('profile')" class="cursor-pointer">
                            User Profile
                        </button>

                        <button @click="activeTab = 'services'" :class="tabClasses('services')" class="cursor-pointer">
                            Services
                        </button>

                        <button @click="activeTab = 'locations'" :class="tabClasses('locations')" class="cursor-pointer">
                            Locations
                        </button>

                    </aside>

                    <!-- RIGHT SIDE CONTENT -->
                    <section class="col-span-12 md:col-span-9">

                        <!-- PROFILE TAB -->
                        <section v-if="activeTab === 'profile'">
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                User Details
                            </h6>

                            <div class="mt-6 flex items-center">
                                <div
                                    class="relative h-28 w-28 overflow-hidden rounded-full bg-rose-100 ring-1 ring-inset ring-rose-200 dark:bg-rose-500/20 dark:ring-rose-400/30">

                                    <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        @error="$event.target.style.display = 'none'" />

                                    <div v-else class="absolute inset-0 flex items-center justify-center">
                                        <i class="bx bx-user text-6xl text-rose-500/80 dark:text-rose-300/80"></i>
                                    </div>

                                    <input ref="avatarInput" type="file" accept="image/png,image/jpeg" class="hidden"
                                        @change="onPickAvatar" />

                                    <button type="button" class="absolute inset-0 cursor-pointer"
                                        aria-label="Pick avatar" @click="pickAvatar"></button>
                                </div>
                            </div>
                            <br>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                <Input id="name" label="Full Name" v-model="form.name" :is-required="true"
                                    :error="form.errors.name" />

                                <Input id="email" type="email" label="Email" v-model="form.email" :is-required="true"
                                    :error="form.errors.email" />

                                <SelectInputComponent id="role_id" label="Role" v-model="form.role_id"
                                    :options="allRoles" :error="form.errors.role_id" />

                            </div>

                            <!-- Security Fields for NEW user -->
                            <section v-if="!user" class="mt-8">
                                <h6
                                    class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                    Security
                                </h6>
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <Input id="password" type="password" label="Password" v-model="form.password"
                                        :error="form.errors.password" />

                                    <Input id="password_confirmation" type="password" label="Confirm Password"
                                        v-model="form.password_confirmation"
                                        :error="form.errors.password_confirmation" />
                                </div>
                            </section>

                            <!-- Password Update (EXISTING user) -->
                            <section v-else class="mt-10">
                                <h6
                                    class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                    Update Password
                                </h6>

                                <!-- SEPARATE FORM -->
                                <form @submit.prevent="showConfirmModal = true"
                                    class="flex flex-col md:grid md:grid-cols-2 gap-5">

                                    <Input id="new_password" type="password" label="New Password"
                                        v-model="password.password" :error="password.errors.password" />

                                    <Input id="new_password_confirmation" type="password" label="Confirm New Password"
                                        v-model="password.password_confirmation"
                                        :error="password.errors.password_confirmation" />

                                    <div class="md:col-span-2 flex justify-end mt-2">
                                        <button type="submit" class="btn-primary w-full md:w-auto"
                                            :disabled="!(password.password && password.password_confirmation)">
                                            Update Password
                                        </button>
                                    </div>

                                </form>
                            </section>

                        </section>

                        <!-- SERVICES TAB -->
                        <section v-if="activeTab === 'services'">
                            <h6
                                class="text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400 mb-4">
                                Assigned Services
                            </h6>

                            <!-- Search -->
                            <input type="text" v-model="serviceSearch" placeholder="Search services..."
                                class="w-full mb-4 rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-2 text-sm" />

                            <!-- Service List -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                <div v-for="srv in filteredServices" :key="srv.id" class="cursor-pointer border rounded-xl p-4 flex items-center justify-between
                                        hover:border-[var(--primary)] hover:bg-[color-mix(in_srgb,var(--primary)_10%,transparent)]
                                        transition-all" @click="toggleService(srv.id)">
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ srv.name }}
                                    </span>

                                    <CheckboxField size="lg" :value="srv.id" v-model="form.selected_services"
                                        @click.stop />
                                </div>

                                
                            </div>
                        </section>

                        <section v-if="activeTab === 'locations'">
                            <h6
                                class="text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400 mb-4">
                                Assigned Locations
                            </h6>

                            <!-- OPTIONAL: Search bar for branches -->
                            <input type="text" v-model="locationSearch" placeholder="Search locations..."
                                class="w-full mb-4 rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-2 text-sm" />

                            <!-- Branch List -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                <div v-for="b in filteredBranches" :key="b.id" class="cursor-pointer border rounded-xl p-4 flex items-center justify-between
                   hover:border-[var(--primary)] hover:bg-[color-mix(in_srgb,var(--primary)_10%,transparent)]
                   transition-all" @click="toggleBranch(b.id)">
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ b.name }}
                                    </span>

                                    <!-- Your Checkbox Component -->
                                    <CheckboxField size="lg" :value="b.id" v-model="form.branches" @click.stop />
                                </div>

                            </div>
                        </section>


                        <!-- Save Buttons -->
                        <div class="flex items-center gap-3 justify-end mt-10">
                            <button type="submit" :disabled="form.processing" class="btn-primary" @click="submit">
                                {{ user ? "Update" : "Save" }}
                            </button>

                            <Link class="btn-secondary" :href="route('users')">Cancel</Link>
                        </div>

                    </section>

                </div>
            </div>
        </div>

        <!-- PASSWORD UPDATE SECTION (Only for existing user) -->
        <!-- <div class="container mx-auto px-4 py-6" v-if="user">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Update Password</h5>
                </div>

                <div class="px-5 py-6">
                    <form autocomplete="off" class="space-y-8">

                        <section>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input id="password" type="password" label="Password" v-model="password.password"
                                    :error="password.errors.password" />

                                <Input id="password_confirmation" type="password" label="Confirm Password"
                                    v-model="password.password_confirmation"
                                    :error="password.errors.password_confirmation" />
                            </div>
                        </section>

                        <div class="flex items-center gap-3 justify-end">
                            <button type="button" class="btn-primary"
                                :disabled="!(password.password && password.password_confirmation)"
                                @click="showConfirmModal = true">
                                Update
                            </button>

                            <Link class="btn-secondary" :href="route('users')">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div> -->

        <!-- CONFIRM PASSWORD MODAL -->
        <Transition name="fade">
            <div v-if="showConfirmModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">

                <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-lg dark:bg-zinc-900">
                    <h5 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        Enter Your Password
                    </h5>

                    <form @submit.prevent="changePassword">
                        <input type="password" v-model="password.confirm_password"
                            placeholder="Enter your current password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm
                                dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />

                        <div v-if="password.errors.confirm_password" class="mt-1 text-sm text-rose-600">
                            {{ password.errors.confirm_password }}
                        </div>

                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" class="btn-secondary" @click="showConfirmModal = false">
                                Close
                            </button>

                            <button type="submit" class="btn-primary">Confirm</button>
                        </div>
                    </form>
                </div>

            </div>
        </Transition>

    </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";
import Input from "@/components/InputField.vue";
import SelectInputComponent from "@/components/SelectInputComponent.vue";
import CheckboxField from "@/components/CheckboxField.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

export default {
    components: {
        AppLayout,
        Input,
        SelectInputComponent,
        CheckboxField,
        Link,
    },

    props: {
        user: Object,
        allRoles: Array,
        services: Array,
        branches: Array,
    },

    data() {
        return {
            activeTab: "profile",
            serviceSearch: "",
            showConfirmModal: false,
            locationSearch: "",
            avatarObjectUrl: null,
            form: useForm({
                id: "",
                name: "",
                email: "",
                role_id: "",
                password: "",
                password_confirmation: "",
                selected_services: [],
                branches: [],
                avatar: null,
            }),

            password: useForm({
                id: "",
                confirm_password: "",
                password: "",
                password_confirmation: "",
            }),

            breadcrumbs: [
                { title: "Users", href: route("users") },
                { title: this.user ? "Edit" : "Create", href: "#" },
            ],
        };
    },

    mounted() {
        if (this.user) {
            this.form.id = this.user.id;
            this.form.name = this.user.name;
            this.form.email = this.user.email;
            this.form.role_id = this.user.roles?.[0]?.id || "";
            this.form.selected_services = this.user.services?.map(s => s.id) || [];
            this.form.branches = this.user?.branches?.map(b => b.id) || [];
        }
    },

    beforeUnmount() {
        if (this.avatarObjectUrl) URL.revokeObjectURL(this.avatarObjectUrl);
    },


    computed: {
        filteredServices() {
            if (!this.serviceSearch.trim()) return this.services;
            return this.services.filter(s =>
                s.name.toLowerCase().includes(this.serviceSearch.toLowerCase())
            );
        },
        filteredBranches() {
            if (!this.locationSearch.trim()) return this.branches;
            return this.branches.filter(b =>
                b.name.toLowerCase().includes(this.locationSearch.toLowerCase())
            );
        },
        avatarPreview() {
            if (this.avatarObjectUrl) return this.avatarObjectUrl;

            if (this.user?.avatar_url) return this.user.avatar_url;

            const m = this.user?.media || [];
            const avatarItem = m.find(x => x?.collection_name === "avatar") || m[0];
            return avatarItem?.original_url || "";
        },
    },

    methods: {
        tabClasses(tab) {
            return [
                "w-full text-left px-3 py-2 rounded-lg text-sm font-medium transition",
                this.activeTab === tab
                    ? "bg-[var(--primary)]/10 text-[var(--primary)]"
                    : "hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300",
            ];
        },

        toggleService(id) {
            const arr = this.form.selected_services;
            this.form.selected_services = arr.includes(id)
                ? arr.filter(x => x !== id)
                : [...arr, id];
        },
        toggleBranch(id) {
            const arr = this.form.branches;
            this.form.branches = arr.includes(id)
                ? arr.filter(x => x !== id)
                : [...arr, id];
        },

        pickAvatar() {
            this.$refs.avatarInput?.click();
        },


        onPickAvatar(e) {
            const file = e.target.files?.[0];
            if (!file) return;

            this.form.avatar = file;

            if (this.avatarObjectUrl) {
                URL.revokeObjectURL(this.avatarObjectUrl);
            }
            this.avatarObjectUrl = URL.createObjectURL(file);
        },

        submit() {
            this.form.post(
                this.user ? route("users.update") : route("users.store"),
                {
                    forceFormData: true,
                    onSuccess: () => {
                        this.$root.showMessage("success", "Success", "Saved successfully!");
                    },
                }
            );
        },

        changePassword() {
            this.password.transform(data => ({ ...data, id: this.form.id }))
                .put(route("users.change.password"), {
                    onSuccess: () => {
                        this.showConfirmModal = false;
                        this.password.reset();
                        this.$root.showMessage("success", "Password Updated");
                    },
                });
        },
    },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
