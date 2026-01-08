<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            SMS Gateway
                        </h5>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Manage your SMS gateway configuration.
                        </p>
                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submitGateway" autocomplete="off" class="space-y-8">
                        <section>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input id="sender_id" label="Sender ID" v-model="form.sender_id" :is-required="true"
                                    :error="form.errors.sender_id" />

                                <Input id="url" label="Gateway URL" v-model="form.url" :is-required="true"
                                    :error="form.errors.url" />

                                <Input id="api_key" label="API Key" v-model="form.api_key" :is-required="true"
                                    :error="form.errors.api_key" />

                                <MultiSelect id="supported_countries" label="Supported Countries" :options="countries"
                                    v-model="form.supported_countries" />
                            </div>
                        </section>

                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                Save
                            </button>

                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- TEST SMS -->
        <div class="container mx-auto px-4 py-6">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Test SMS Gateway
                        </h5>
                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="sendTestSMS" autocomplete="off" class="space-y-8">
                        <section>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input label="Phone Number" placeholder="+94XXXXXXXXX" v-model="testForm.phone"
                                    :error="testForm.errors.phone" />

                                <Input label="Message" v-model="testForm.message" :error="testForm.errors.message" />
                            </div>
                        </section>

                        <div v-if="smsResponse"
                            class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-2 border border-green-300">
                            {{ smsResponse }}
                        </div>

                        <div v-if="smsError"
                            class="mb-4 rounded-lg bg-red-100 text-red-700 px-4 py-2 border border-red-300">
                            {{ smsError }}
                        </div>


                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" class="btn-primary" :disabled="smsLoading">
                                {{ smsLoading ? 'Sending…' : 'Send Test SMS' }}
                            </button>


                        </div>
                    </form>

                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import Input from '@/components/InputField.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import CheckboxField from '@/components/CheckboxField.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';


export default {
    components: { AppLayout, Input, MultiSelect, CheckboxField, Link },

    props: {
        gateway: Object,
        countries: Array,
    },

    data() {
        return {
            smsLoading: false,
            smsResponse: null,
            smsError: null,
            form: useForm({
                sender_id: this.gateway?.sender_id || '',
                url: this.gateway?.url || '',
                api_key: this.gateway?.api_key || '',
                supported_countries: this.gateway?.supported_countries || [],
            }),

            testForm: useForm({
                phone: '',
                message: 'Test message from your SMS gateway.',
            }),

            breadcrumbs: [
                { title: "Settings", href: "#" },
                { title: "SMS Gateway", href: "#" }
            ],
        };
    },

    methods: {
        submitGateway() {
            this.form.post(route('sms.gateway.save'));
        },

        sendTestSMS() {
            this.smsLoading = true;
            this.smsResponse = null;
            this.smsError = null;

            axios.post(route('sms.gateway.test'), {
                phone: this.testForm.phone,
                message: this.testForm.message
            })
                .then(res => {
                    this.smsResponse = res.data.message ?? "SMS sent successfully.";
                })
                .catch(err => {
                    if (err.response?.data?.message) {
                        this.smsError = err.response.data.message;
                    } else {
                        this.smsError = "Failed to send SMS.";
                    }
                })
                .finally(() => {
                    this.smsLoading = false;
                });
        }

    }
};
</script>
