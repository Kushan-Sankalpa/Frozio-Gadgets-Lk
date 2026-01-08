<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="pageTitle" />

        <div class="container mx-auto  mx-auto px-4 py-6 ">
            <!-- MAIN FORM: Profile + Address -->

            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <!-- Header -->


            
                <div class="px-5 py-6">

                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
                    <!-- PROFILE -->
                    <section>
                        <div
                            class="flex items-center justify-between gap-3 border-b border-zinc-200  dark:border-zinc-700/60">
                            <div>
                                <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                    Profile
                                </h5>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Manage your client's personal profile.
                                </p>
                            </div>
                        </div>



                       <div class="mt-6 flex items-center">
  
  <div class="relative h-27 w-26">
    <!-- Inner circle that actually clips the image -->
    <div
      class="h-28 w-28 overflow-hidden rounded-full bg-rose-100 ring-1 ring-inset ring-rose-200
             dark:bg-rose-500/20 dark:ring-rose-400/30"
    >
      <img
        v-if="avatarPreview"
        :src="avatarPreview"
        alt="Avatar"
        class="h-full w-full object-cover"
        @error="$event.target.style.display = 'none'"
      />

      <div v-else class="flex h-full w-full items-center justify-center">
        <i class="bx bx-user text-6xl text-rose-500/80 dark:text-rose-300/80"></i>
      </div>
    </div>

    <!-- Hidden file input -->
    <input
      ref="avatarInput"
      type="file"
      accept="image/png,image/jpeg"
      class="hidden"
      @change="onPickAvatar"
    />

    <!-- Plus badge (sits on top of the circle, not clipped) -->
    <div
      class="pointer-events-none absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center
             rounded-full bg-zinc-900 text-white shadow-md border-2 border-white
             dark:bg-zinc-100 dark:text-zinc-900 dark:border-zinc-900"
    >
      <i class="bx bx-plus text-lg leading-none"></i>
    </div>

    <!-- Full clickable area -->
    <button
      type="button"
      class="absolute inset-0 cursor-pointer"
      aria-label="Pick avatar"
      @click="pickAvatar"
    ></button>
  </div>
</div>
<div v-if="form.errors?.avatar" class="mt-2 text-sm text-rose-600">
  {{ form.errors.avatar }}
</div>


                        <!-- FIELDS -->
                        <div class="mt-8 grid grid-cols-2 gap-5 md:grid-cols-2">
                            <Input id="client_first_name" label="First name" v-model="form.first_name"
                                placeholder="e.g. John" class="text-base" :error="form.errors?.first_name" />
                            <Input id="client_last_name" label="Last name" v-model="form.last_name"
                                placeholder="e.g. Hancock" class="text-base" :error="form.errors?.last_name" />

                            <div class="grid grid-cols-2 gap-3 items-center">
                                <!-- Birthday -->
                                <div class="w-full">
                                    <label class="mb-1 block text-base font-medium text-zinc-700 dark:text-zinc-300">
                                        Birthday
                                    </label>
                                    <DatePicker v-model="birthdayDate" :popover="{ visibility: 'click' }"
                                        :masks="{ input: 'MMM D' }">
                                        <template #default="{ inputValue, inputEvents }">
                                            <div class="relative w-full">
                                                <input :value="inputValue" v-on="inputEvents"
                                                    placeholder="Day and month" readonly
                                                    class="w-full h-11 rounded-md border border-zinc-300 bg-white px-3 pr-10 text-base
                                                            text-zinc-900 placeholder-zinc-400
                                                            focus:outline-none focus:ring-2 focus:ring-black-500 focus:border-black-500
                                                            dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                                <i
                                                    class="bx bx-calendar absolute right-3 top-1/2 -translate-y-1/2 text-xl text-zinc-400">
                                                </i>
                                            </div>
                                        </template>
                                    </DatePicker>
                                    <div v-if="form.errors?.birthday_daymonth" class="mt-1 text-base text-rose-600">
                                        {{ form.errors.birthday_daymonth }}
                                    </div>
                                </div>

                                <!-- Year -->
                                <div>
                                    <Input id="client_birthday_year" type="number" label="Year"
                                        v-model="form.birthday_year" placeholder="Year" class="text-base" 
                                        :error="form.errors?.birthday_year" />
                                </div>
                            </div>

                            <Input id="client_email" type="email" label="Email" v-model="form.email"
                                placeholder="example@domain.com" class="text-base" autocomplete="off"
                                :error="form.errors?.email" />

                            <!-- Phone -->
                            <div>
                                <label class="mb-1 block text-base font-medium text-zinc-700 dark:text-zinc-300">
                                    Phone
                                </label>
                                <vue-tel-input v-model="form.phone_e164" mode="international"
                                    :defaultCountry="(form.country_iso2 || 'lk').toLowerCase()"
                                    :preferredCountries="['lk', 'us', 'gb']" :dropdownOptions="{
                                        showDialCodeInList: true,
                                        showDialCodeInSelection: true,
                                        showFlags: true,
                                        showSearchBox: true
                                    }" :inputOptions="{
                                            placeholder: '77 351 6451',
                                            maxlength: 25,
                                            type: 'number'
                                        }" @validate="onPhoneValidate" @country-changed="onCountryChanged" />
                                <div v-if="form.errors?.phone" class="mt-1 text-base text-rose-600">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <SelectInputComponent id="client_gender" label="Gender" v-model="form.gender"
                                :options="genderOptions" placeholder="Select an option" :error="form.errors?.gender" />

                            <SelectInputComponent id="client_country_id" label="Country" v-model="form.country_id"
                                :options="countrySelectOpts" placeholder="Select an option"
                                :error="form.errors?.country_id" />
                        </div>
                    </section>

                    <div class="my-8 border-t border-zinc-200 dark:border-zinc-700/60"></div>

                    
                    <section v-if="!client" class="mt-8">
                        <div
                            class="flex items-center justify-between gap-3 border-b border-zinc-200  dark:border-zinc-700/60">
                            <div>
                                <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                    Security
                                </h5>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Set a password for this client.
                                </p>
                            </div>
                        </div>



                        <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <Input id="client_password" type="password" label="Password" v-model="form.password"
                                placeholder="Set a password" class="text-base" autocomplete="new-password"
                                :error="form.errors?.password" />

                            <Input id="client_password_confirmation" type="password" label="Confirm Password"
                                v-model="form.password_confirmation" placeholder="Re-enter password" class="text-base"
                                autocomplete="new-password" :error="form.errors?.password_confirmation" />
                        </div>
                    </section>

                    <!-- Divider -->
                    <div class="my-8 border-t border-zinc-200 dark:border-zinc-700/60"></div>

                    <!-- ADDRESS -->
                    <AddressPanel v-model="addressOne" :countries="countries" :errors="form.errors" />

                    <div class="flex items-center gap-3 justify-end">
                        <button type="submit" :disabled="form.processing" class="btn-primary">
                            {{ client ? 'Update' : 'Save' }}
                        </button>

                        <Link class="btn-secondary" :href="route('clients.index')">
                        Cancel
                        </Link>
                    </div>

                    </form>
                </div>
            </div>

        </div>

        <!-- UPDATE PASSWORD (only on Edit) -->
        <div class="container mx-auto px-4 py-6" v-if="client">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        Update Password
                    </h5>
                </div>

                <div class="px-5 py-6">
                    <form autocomplete="off" class="space-y-8" @submit.prevent="openPasswordConfirm">
                        <section>
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <Input id="client_update_password" type="password" label="Password"
                                        v-model="passwordForm.password" autocomplete="new-password"
                                        :error="passwordForm.errors.password" />
                                </div>

                                <div>
                                    <Input id="client_update_password_confirmation" type="password"
                                        label="Confirm Password" v-model="passwordForm.password_confirmation"
                                        autocomplete="new-password"
                                        :error="passwordForm.errors.password_confirmation" />
                                </div>
                            </div>
                        </section>

                        <div class="flex flex-col gap-2
           sm:flex-row sm:items-center sm:justify-end">
           <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2">
                            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="!(
                                passwordForm.password.length > 0 &&
                                passwordForm.password_confirmation.length > 0
                            )">
                                Update
                            </button>

                            <Link class="btn-secondary w-full sm:w-auto " :href="route('clients.index')">
                            Cancel
                            </Link>
                        </div>
</div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CONFIRM MODAL FOR PASSWORD UPDATE -->
        <Transition name="fade">
            <div v-if="showConfirmModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-lg dark:bg-zinc-900">
                    <h5 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        Enter Your Password
                    </h5>
                    <form @submit.prevent="changePassword">
                        <input type="password" v-model="passwordForm.confirm_password"
                            placeholder="Enter your current password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-800
                                focus:border-indigo-500 focus:ring focus:ring-indigo-200
                                dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                        <div v-if="passwordForm.errors.confirm_password" class="mt-1 text-sm text-rose-600">
                            {{ passwordForm.errors.confirm_password }}
                        </div>

                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" class="btn-secondary" @click="showConfirmModal = false">
                                Close
                            </button>
                            <button type="submit" class="btn-primary">
                                Confirm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/InputField.vue'
import SelectInputComponent from '@/components/SelectInputComponent.vue'
import AddressPanel from '@/Pages/Clients/Address.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { VueTelInput } from 'vue3-tel-input'
import 'vue3-tel-input/dist/vue3-tel-input.css'
import { DatePicker } from 'v-calendar'
import 'v-calendar/style.css'

export default {
    components: { AppLayout, Head, Link, Input, SelectInputComponent, AddressPanel, VueTelInput, DatePicker },
    props: { client: Object, countries: Array },
    data() {
        return {
            birthdayDate: null,
            avatarObjectUrl: null,
            showConfirmModal: false,
            form: useForm({
                id: this.client?.id || null,
                first_name: this.client?.first_name || '',
                last_name: this.client?.last_name || '',
                email: this.client?.email || '',

                phone_e164: this.client?.phone_e164 || '',
                phone_code: this.client?.phone_code || '+94',
                phone: this.client?.phone || '',
                country_iso2: this.client?.country_iso2 || 'LK',
                country_id: this.client?.country_id ?? null,

                birthday_year: this.client?.birthday_year || '',
                birthday_daymonth: this.client?.birthday_daymonth || '',

                gender: this.client?.gender || '',
                pronouns: this.client?.pronouns || '',


                addresses: this.client?.addresses ?? [],

                avatar: null,


                password: '',
                password_confirmation: '',
            }),

            // Separate form for password updates when editing
            passwordForm: useForm({
                id: '',
                confirm_password: '',
                password: '',
                password_confirmation: '',
            }),
        }
    },

    mounted() {
        // Init phone_e164 from old phone fields if needed
        if (!this.form.phone_e164 && (this.client?.phone_code || this.client?.phone)) {
            const code = String(this.client?.phone_code || '').replace(/\D/g, '')
            const num = String(this.client?.phone || '').replace(/\D/g, '')
            this.form.phone_e164 = (code || num) ? `+${code}${num}` : ''
        }

        // Init birthdayDate from existing MM-DD string
        if (this.form.birthday_daymonth) {
            this.birthdayDate = this.parseDayMonth(this.form.birthday_daymonth)
        }

        // For edit: setup passwordForm id
        if (this.client?.id) {
            this.passwordForm.id = this.client.id
        }
    },

    computed: {
        pageTitle() { return this.client ? 'Edit client' : 'Add a new client' },
        breadcrumbs() {
            return [
                { title: 'Clients', href: route('clients.index') },
                { title: this.client ? 'Edit' : 'Create', href: '#' },
            ]
        },
        countryOptions() {
            return (this.countries || []).slice().sort((a, b) => a.name.localeCompare(b.name))
        },
        countrySelectOpts() {
            return this.countryOptions.map(c => ({ value: c.id, label: c.name }))
        },
        countryById() {
            const map = new Map()
                ; (this.countries || []).forEach(c => map.set(c.id, c))
            return map
        },
        genderOptions() {
            return [
                { value: 'Female', label: 'Female' },
                { value: 'Male', label: 'Male' },
                { value: 'Non-binary', label: 'Non-binary' },
                { value: 'Prefer not to say', label: 'Prefer not to say' },
                { value: 'Other', label: 'Other' },
            ]
        },
        avatarPreview() {
            if (this.avatarObjectUrl) return this.avatarObjectUrl

            if (this.client?.avatar_url) return this.client.avatar_url

            const m = this.client?.media || []
            const avatarItem = m.find(x => x?.collection_name === 'avatar') || m[0]
            return avatarItem?.original_url || ''
        },

        addressOne: {
            get() {
                return this.form.addresses[0] || {
                    type: 'home',
                    address: '',
                    district: '',
                    city: '',
                    postcode: '',
                    country_id: null,
                }
            },
            set(val) {
                if (!Array.isArray(this.form.addresses)) this.form.addresses = []
                if (this.form.addresses.length === 0) {
                    this.form.addresses.push({ ...val })
                } else {
                    this.form.addresses.splice(0, 1, { ...val })
                }
            },
        },
    },
    watch: {
        'form.country_id': {
            immediate: true,
            handler(val) {
                const c = this.countryById.get(val)
                if (c && c.code) this.form.phone_code = `+${String(c.code).replace(/^\+/, '')}`
            },
        },
        birthdayDate(val) {
            // sync datepicker -> string (MM-DD) for backend
            if (!val) {
                this.form.birthday_daymonth = ''
                return
            }
            const mm = String(val.getMonth() + 1).padStart(2, '0')
            const dd = String(val.getDate()).padStart(2, '0')
            this.form.birthday_daymonth = `${mm}-${dd}`
        },
    },
    beforeUnmount() {
        if (this.avatarObjectUrl) URL.revokeObjectURL(this.avatarObjectUrl)
    },
    methods: {
        route,
        submit() {
            const isEditing = !!this.form.id

            this.form
                .transform(data => {
                    if (isEditing) {
                        // don't send password fields on edit; handled by passwordForm
                        const { password, password_confirmation, ...rest } = data
                        return rest
                    }
                    return data
                })
                .post(this.route(isEditing ? 'clients.update' : 'clients.store'), {
                    forceFormData: true,
                })
        },
        pickAvatar() { this.$refs.avatarInput?.click() },
        onPickAvatar(e) {
            const file = e.target.files?.[0]; if (!file) return
            this.form.avatar = file
            if (this.avatarObjectUrl) URL.revokeObjectURL(this.avatarObjectUrl)
            this.avatarObjectUrl = URL.createObjectURL(file)
        },
        onCountryChanged(country) {
            const iso2 = (country?.iso2 || '').toLowerCase()
            const dial = country?.countryCallingCode || country?.dialCode || ''
            if (iso2) this.form.country_iso2 = iso2
            if (dial) this.form.phone_code = dial.startsWith('+') ? dial : `+${dial}`
        },
        onPhoneValidate(payload) {
            if (!payload) return
            this.form.phone_e164 = payload.e164 || payload.number || this.form.phone_e164
            if (payload.nationalNumber) this.form.phone = String(payload.nationalNumber)
            const iso2 = (payload.country?.iso2 || '').toLowerCase()
            if (iso2) this.form.country_iso2 = iso2
            const dial = payload.country?.countryCallingCode || payload?.country?.dialCode || ''
            if (dial) this.form.phone_code = dial.startsWith('+') ? dial : `+${dial}`
        },

        parseDayMonth(dm) {
            if (!dm || typeof dm !== 'string' || !/^\d{2}-\d{2}$/.test(dm)) return null
            const [mm, dd] = dm.split('-').map(n => parseInt(n, 10))
            const currentYear = new Date().getFullYear()
            let d = new Date(currentYear, mm - 1, dd)
            if (d.getMonth() !== mm - 1 || d.getDate() !== dd) d = new Date(2000, mm - 1, dd)
            return isNaN(d.getTime()) ? null : d
        },

        openPasswordConfirm() {
            if (
                this.passwordForm.password.length > 0 &&
                this.passwordForm.password_confirmation.length > 0
            ) {
                this.showConfirmModal = true
            }
        },

        changePassword() {
            this.passwordForm
                .transform(data => ({
                    ...data,
                    id: this.client?.id || this.form.id,
                }))
                .put(this.route('clients.change.password'), {
                    preserveScroll: true,
                    errorBag: 'changePassword',
                    onSuccess: () => {
                        this.showConfirmModal = false
                        this.passwordForm.reset(
                            'confirm_password',
                            'password',
                            'password_confirmation',
                        )
                        if (this.$root?.showMessage) {
                            this.$root.showMessage(
                                'success',
                                '<span class="text-success">Success</span><br/>',
                                'Password was updated successfully!'
                            )
                        }
                    },
                    onError: () => {
                        if (this.$root?.showMessage) {
                            this.$root.showMessage(
                                'error',
                                '<span class="text-danger">Error</span><br>',
                                'Something went wrong!'
                            )
                        }
                    },
                })
        },
    },
}
</script>

<style scoped>
button:focus-visible {
    outline: 2px solid rgb(99 102 241 / 0.65);
    outline-offset: 2px;
}


input:-webkit-autofill,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    -webkit-text-fill-color: inherit;
}

:deep(.vue-tel-input:focus-within) {
    border-color: #000 !important; 
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2) !important; 
    outline: none !important;
}

.dark :deep(.vue-tel-input:focus-within) {
    border-color: #000 !important;
    box-shadow: 0 0 0 2px 0 0 0 2px rgba(0, 0, 0, 0.2) !important;
}

:deep(.vue-tel-input) {
    border-radius: 0.375rem !important; 
    border: 1px solid #d4d4d8 !important; 
    background-color: white !important;
    height: 2.75rem !important; 
    width: 100% !important;
}

:deep(.vue-tel-input .vti__input) {
    border: none !important;
    background-color: transparent !important;
    color: #18181b !important; 
    font-size: 1rem !important; 
    line-height: 1.5rem !important;
    height: 100% !important;
    padding-left: 0.75rem !important; 
    padding-right: 2.5rem !important; 
    border-radius: 0.375rem !important;
}

:deep(.vue-tel-input .vti__input::placeholder) {
    color: #a1a1aa !important; 
}
@media (max-width: 640px) {
  /* Center cancel on mobile */
  .btn-secondary.w-full {
    text-align: center;
    justify-content: center;
  }
}
</style>
