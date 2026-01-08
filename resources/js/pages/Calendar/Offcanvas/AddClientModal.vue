<template>
    <transition name="client-modal">

        <div v-if="show"
            class="pointer-events-auto fixed inset-0 z-[150] flex justify-end bg-black/40 backdrop-blur-sm">
            <!-- FULL-SCREEN PANEL -->
            <div
                class="client-modal-panel relative ml-auto flex h-full w-full max-w-xl md:max-w-2xl flex-col bg-white shadow-2xl overflow-y-auto">
                <div>
                    <!-- HEADER -->
                    <header class="flex items-center justify-between border-b px-5 py-4">
                        <div>
                            <h2 class="text-xl font-semibold text-zinc-900">
    {{ isEditMode ? 'Update client' : 'Add a new client' }}
</h2>
<p class="text-sm text-zinc-500">
    {{
        isEditMode
            ? 'Update this client without leaving the booking flow.'
            : 'Create a profile without leaving the booking flow.'
    }}
</p>

                        </div>

                        <button type="button"
                            class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-zinc-300 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800"
                            @click="handleClose">
                            ✕
                        </button>
                    </header>

                    <!-- BODY: scrollable form -->
                    <div class="flex-1 overflow-y-auto px-5 py-5  mx-auto w-full max-w-6xl  ">
                        <form autocomplete="off" class="space-y-8" @submit.prevent="submit">
                            <!-- PROFILE -->
                            <section>
                                <div class="flex items-center justify-between gap-3 border-zinc-200">
                                    <!-- <div>
                                        <h5 class="text-lg font-semibold text-zinc-900">
                                            Profile
                                        </h5>
                                        <p class="text-sm text-zinc-500">
                                            Manage your client's personal profile.
                                        </p>
                                    </div> -->
                                </div>

                                <!-- Avatar -->

<div class="mt-6 flex items-center">
    <div
        class="relative h-24 w-24 overflow-hidden rounded-full bg-rose-100 ring-1 ring-rose-200 ring-inset"
    >

        <div
            v-if="isLoading"
            class="absolute inset-0 rounded-full bg-zinc-200 animate-pulse"
        ></div>


        <template v-else>
            <img
                v-if="avatarPreview"
                :src="avatarPreview"
                alt="Avatar"
                class="absolute inset-0 h-full w-full object-cover"
                @error="($event.target as HTMLImageElement).style.display = 'none'"
            />

            <div v-else class="absolute inset-0 flex items-center justify-center">
                <i class="bx bx-user text-5xl text-rose-500/80"></i>
            </div>

            <input
                ref="avatarInput"
                type="file"
                accept="image/png,image/jpeg"
                class="hidden"
                @change="onPickAvatar"
            />

            <button
                type="button"
                class="absolute inset-0 cursor-pointer"
                aria-label="Pick avatar"
                @click="pickAvatar"
            ></button>
        </template>
    </div>
</div>



                                <!-- FIELDS -->
                               <div class="mt-6">
    <!-- 🔹 Skeleton while loading -->
    <div v-if="isLoading" class="space-y-4 animate-pulse">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="h-11 rounded-md bg-zinc-100"></div>
            <div class="h-11 rounded-md bg-zinc-100"></div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="h-11 rounded-md bg-zinc-100"></div>
            <div class="h-11 rounded-md bg-zinc-100"></div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="h-11 rounded-md bg-zinc-100"></div>
            <div class="h-11 rounded-md bg-zinc-100"></div>
            <div class="h-11 rounded-md bg-zinc-100"></div>
        </div>
    </div>

    <!-- 🔹 Actual fields once loaded -->
    <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <!-- Full name -->
        <Input
            id="client_first_name"
            label="Full name"
            v-model="form.first_name"
            placeholder="e.g. John Hancock"
            class="text-base"
            :error="form.errors?.first_name"
        />

        <!-- Phone -->
        <div>
            <label class="mb-1 block text-base font-medium text-zinc-700">
                Phone
            </label>
            <VueTelInput
                v-model="form.phone_e164"
                mode="international"
                :defaultCountry="(form.country_iso2 || 'lk').toLowerCase()"
                :preferredCountries="['lk', 'us', 'gb']"
                :dropdownOptions="{
                    showDialCodeInList: true,
                    showDialCodeInSelection: true,
                    showFlags: true,
                    showSearchBox: true,
                }"
                :inputOptions="{
                    placeholder: '77 351 6451',
                    maxlength: 25,
                }"
                @validate="onPhoneValidate"
                @country-changed="onCountryChanged"
            />
            <div v-if="form.errors?.phone" class="mt-1 text-sm text-rose-600">
                {{ form.errors.phone }}
            </div>
        </div>

        <!-- Birthday + year -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Birthday -->
            <div class="w-full">
                <div class="mb-1 flex items-center gap-1">
                    <label class="block text-base font-medium text-zinc-700">
                        Birthday
                    </label>
                    <span class="text-xs font-medium text-zinc-400">(optional)</span>
                </div>
                <DatePicker
                    v-model="birthdayDate"
                    :popover="{ visibility: 'click' }"
                    :masks="{ input: 'MMM D' }"
                >
                    <template #default="{ inputValue, inputEvents }">
                        <div class="relative w-full">
                            <input
                                :value="inputValue"
                                v-on="inputEvents"
                                placeholder="Day and month"
                                readonly
class="h-11 w-full rounded-md border border-zinc-300 bg-white px-3 pr-10 text-base
       text-zinc-900 placeholder-zinc-400 focus:border-black focus:ring-2 focus:ring-black focus:outline-none"
                            />
                            <i
                                class="bx bx-calendar absolute top-1/2 right-3 -translate-y-1/2 text-xl text-zinc-400"
                            ></i>
                        </div>
                    </template>
                </DatePicker>
                <div v-if="form.errors?.birthday_daymonth" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.birthday_daymonth }}
                </div>
            </div>

            <!-- Year -->
             <div class="mb-1 flex items-center gap-1">
            <div>
                <Input
                    id="client_birthday_year"
                    type="number"
                    label="Year"
                    v-model="form.birthday_year"
                    placeholder="Year"
                    class="block text-base font-medium text-zinc-700"
                    :error="form.errors?.birthday_year"
                />
            </div>
            </div>
        </div>

        <!-- Email -->
        <div>
            <div class="mb-1 flex items-center gap-1">
                <label for="client_email" class="block text-base font-medium text-zinc-700">
                    Email
                </label>
                <span class="text-xs font-medium text-zinc-400">(optional)</span>
            </div>
            <Input
                id="client_email"
                type="email"
                label=""
                v-model="form.email"
                placeholder="example@domain.com"
                class="text-base"
                autocomplete="off"
                :error="form.errors?.email"
            />
        </div>

        <!-- Gender -->
        <div>
            <div class="mb-1 flex items-center gap-1">
                <label for="client_gender" class="block text-base font-medium text-zinc-700">
                    Gender
                </label>
                <span class="text-xs font-medium text-zinc-400">(optional)</span>
            </div>
            <SelectInputComponent
                id="client_gender"
                label=""
                v-model="form.gender"
                :options="genderOptions"
                placeholder="Select an option"
                :error="form.errors?.gender"
            />
        </div>

        <!-- Country -->
        <div>
            <div class="mb-1 flex items-center gap-1">
                <label for="client_country_id" class="block text-base font-medium text-zinc-700">
                    Country
                </label>
                <span class="text-xs font-medium text-zinc-400">(optional)</span>
            </div>
            <SelectInputComponent
                id="client_country_id"
                label=""
                v-model="form.country_id"
                :options="countrySelectOpts"
                placeholder="Select an option"
                :error="form.errors?.country_id"
            />
        </div>
    </div>
</div>


                            </section>





                            <div class="my-6 border-t border-zinc-200"></div>
<!-- OTHER INFORMATION TOGGLE -->
<section class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-4">
  <div class="flex items-center justify-between gap-4">
    <div>
      <div class="text-sm font-semibold text-zinc-900">Other Information</div>
      <div class="text-xs text-zinc-500">Show/hide Security & Address fields</div>
    </div>

   <button
  type="button"
  role="switch"
  :aria-checked="showOtherInfo"
  class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full border transition-colors
         focus:outline-none focus:ring-2 focus:ring-black/30 cursor-pointer"
  :class="showOtherInfo ? 'bg-black border-black' : 'bg-zinc-200 border-zinc-400'"
  @click="toggleOtherInfo"
>
  <span class="sr-only">Other Information</span>

  <!-- optional: tiny ON/OFF hint (remove if you want it cleaner) -->
  <span
    class="absolute left-1 text-[9px] font-semibold tracking-wide"
    :class="showOtherInfo ? 'text-black/0' : 'text-zinc-600'"
  >
    OFF
  </span>
  <span
    class="absolute right-1 text-[9px] font-semibold tracking-wide"
    :class="showOtherInfo ? 'text-white/90' : 'text-white/0'"
  >
    ON
  </span>

  <span
    class="inline-block h-4 w-4 rounded-full shadow transition-transform"
    :class="showOtherInfo
      ? 'translate-x-6 bg-white'
      : 'translate-x-1 bg-white ring-1 ring-zinc-500'"
  />
</button>

  </div>
</section>

<!-- COLLAPSED CONTENT -->
<transition name="collapse-height">
  <div v-if="showOtherInfo" class="mt-6 space-y-8">
    <div class="border-t border-zinc-200"></div>

    <!-- SECURITY (always expanded when Other Information is ON) -->
    <section>
      <div class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-3">
        <div>
          <div class="flex items-center gap-2">
            <h5 class="text-lg font-semibold text-zinc-900">Security</h5>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-500">
              Optional
            </span>
          </div>
          <p class="text-sm text-zinc-500">Set a password for this client.</p>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
        <Input
          id="client_password"
          type="password"
          label="Password"
          v-model="form.password"
          placeholder="Set a password"
          class="text-base"
          autocomplete="new-password"
          :error="form.errors?.password"
        />

        <Input
          id="client_password_confirmation"
          type="password"
          label="Confirm Password"
          v-model="form.password_confirmation"
          placeholder="Re-enter password"
          class="text-base"
          autocomplete="new-password"
          :error="form.errors?.password_confirmation"
        />
      </div>
    </section>

    <div class="border-t border-zinc-200"></div>

    <!-- ADDRESS (always expanded when Other Information is ON) -->
    <section>
      <div class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-3">
        <div>
          <div class="flex items-center gap-2">
            <h5 class="text-lg font-semibold text-zinc-900">Address</h5>
            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-medium text-zinc-500">
              Optional
            </span>
          </div>
          <p class="text-sm text-zinc-500">Store your client's primary address.</p>
        </div>
      </div>

      <div class="mt-4">
        <div v-if="isLoading" class="space-y-3 animate-pulse">
          <div class="h-10 rounded-md bg-zinc-100"></div>
          <div class="h-10 rounded-md bg-zinc-100"></div>
          <div class="h-10 rounded-md bg-zinc-100"></div>
          <div class="h-10 rounded-md bg-zinc-100"></div>
        </div>

        <AddressPanel
          v-else
          v-model="addressOne"
          :countries="countries"
          :errors="form.errors"
          :hide-header="true"
        />
      </div>
    </section>
  </div>
</transition>

                        </form>
                    </div>

                    <!-- FOOTER -->
                    <footer class="flex items-center justify-end gap-3 border-t bg-white px-5 py-3">
                       <button
    type="button"
    class="btn-primary rounded-full bg-black px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-zinc-900 disabled:cursor-default disabled:opacity-40"
    :disabled="form.processing || isLoading"
    @click="submit"
>
    <span v-if="isLoading">
        Loading…
    </span>
    <span v-else-if="!form.processing">
        {{ isEditMode ? 'Update client' : 'Save client' }}
    </span>
    <span v-else>
        {{ isEditMode ? 'Updating…' : 'Saving…' }}
    </span>
</button>

                    </footer>
                </div>
            </div>
        </div>
    </transition>
</template>

<script lang="ts">
import Input from '@/components/InputField.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import AddressPanel from '@/Pages/Clients/Address.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { DatePicker } from 'v-calendar';
import 'v-calendar/style.css';
import { defineComponent } from 'vue';
import { VueTelInput } from 'vue3-tel-input';
import 'vue3-tel-input/dist/vue3-tel-input.css';
import { route } from 'ziggy-js';

export default defineComponent({
    name: 'AddClientModal',
    components: {
        Input,
        SelectInputComponent,
        AddressPanel,
        VueTelInput,
        DatePicker,
    },
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        countries: {
            type: Array,
            default: () => [],
        },
        mode: {
            type: String,
            default: 'create', // 'create' | 'edit'
        },
        client: {
            type: Object,
            default: null,
        },
         clientId: {
        type: [Number, String],
        default: null,
    },
    },
    emits: ['close', 'saved'],
    data() {
        return {
             showOtherInfo: false,
            birthdayDate: null as Date | null,
            avatarObjectUrl: null as string | null,
            showSecurity: false,
            showAddress: false,
            isLoading: false,
            form: useForm({
                id: null as number | null,
                first_name: '',
                last_name: '',
                email: '',
                phone_e164: '',
                phone_code: '+94',
                phone: '',
                country_iso2: 'lk',
                country_id: null as number | null,
                birthday_year: '',
                birthday_daymonth: '',
                gender: '',
                pronouns: '',
                addresses: [] as any[],
                avatar: null as File | null,
                password: '',
                password_confirmation: '',

            }),
        };
    },
    computed: {
        countryOptions(): any[] {
            return (this.countries || [])
                .slice()
                .sort((a: any, b: any) =>
                    String(a.name).localeCompare(String(b.name)),
                );
        },
        countrySelectOpts(): any[] {
            return this.countryOptions.map((c: any) => ({
                value: c.id,
                label: c.name,
            }));
        },
        isEditMode(): boolean {
    return this.mode === 'edit' && !!this.form.id;
},

        countryById(): Map<any, any> {
            const map = new Map();
            (this.countries || []).forEach((c: any) => map.set(c.id, c));
            return map;
        },
        genderOptions(): any[] {
            return [
                { value: 'Female', label: 'Female' },
                { value: 'Male', label: 'Male' },
                { value: 'Non-binary', label: 'Non-binary' },
                { value: 'Prefer not to say', label: 'Prefer not to say' },
                { value: 'Other', label: 'Other' },
            ];
        },
        avatarPreview(): string {
            if (this.avatarObjectUrl) return this.avatarObjectUrl;
            return '';
        },
        addressOne: {
            get(): any {
                return (
                    this.form.addresses[0] || {
                        type: 'home',
                        address: '',
                        district: '',
                        city: '',
                        postcode: '',
                        country_id: null,
                    }
                );
            },
            set(val: any) {
                if (!Array.isArray(this.form.addresses)) {
                    this.form.addresses = [];
                }
                if (this.form.addresses.length === 0) {
                    this.form.addresses.push({ ...val });
                } else {
                    this.form.addresses.splice(0, 1, { ...val });
                }
            },
        },
    },
    watch: {
        'form.country_id': {
            immediate: true,
            handler(val: any) {
                const c = this.countryById.get(val);
                if (c && c.code) {
                    this.form.phone_code = `+${String(c.code).replace(
                        /^\+/,
                        '',
                    )}`;
                }
            },
        },
        birthdayDate(val: Date | null) {
            if (!val) {
                this.form.birthday_daymonth = '';
                return;
            }
            const mm = String(val.getMonth() + 1).padStart(2, '0');
            const dd = String(val.getDate()).padStart(2, '0');
            this.form.birthday_daymonth = `${mm}-${dd}`;
        },
          show(val: boolean) {
        if (val) {
            // When opening
            if (this.mode === 'edit' && this.clientId) {
                this.loadClient();
            } else {
                // create mode → clean form
                this.resetForm();
            }
        } else {
            // When closing
            this.resetForm();
        }
    },
    },
    beforeUnmount() {
        if (this.avatarObjectUrl) URL.revokeObjectURL(this.avatarObjectUrl);
    },
    methods: {
          toggleOtherInfo() {
    this.showOtherInfo = !this.showOtherInfo;

    if (!this.showOtherInfo) {
      // prevent accidental password updates when hidden
      this.form.password = '';
      this.form.password_confirmation = '';

      // clear errors for hidden fields so they don't block UX
      if (typeof (this.form as any).clearErrors === 'function') {
        (this.form as any).clearErrors(
          'password',
          'password_confirmation',
          'addresses',
          'addresses.0.address',
          'addresses.0.district',
          'addresses.0.city',
          'addresses.0.postcode',
          'addresses.0.country_id',
        );
      }
    }},
        handleClose() {
            if (!this.form.processing) {
                this.$emit('close');
            }
        },
        pickAvatar() {
            (this.$refs.avatarInput as HTMLInputElement | undefined)?.click();
        },
        onPickAvatar(e: Event) {
            const input = e.target as HTMLInputElement;
            const file = input.files?.[0];
            if (!file) return;
            this.form.avatar = file;
            if (this.avatarObjectUrl) {
                URL.revokeObjectURL(this.avatarObjectUrl);
            }
            this.avatarObjectUrl = URL.createObjectURL(file);
        },
        onCountryChanged(country: any) {
            const iso2 = (country?.iso2 || '').toLowerCase();
            const dial = country?.countryCallingCode || country?.dialCode || '';
            if (iso2) this.form.country_iso2 = iso2;
            if (dial)
                this.form.phone_code = dial.startsWith('+') ? dial : `+${dial}`;
        },

        async loadClient() {
        if (!this.clientId) return;

        this.isLoading = true;
        this.form.clearErrors();
        this.form.reset();
        this.birthdayDate = null;

          this.showOtherInfo = false;
        // clear avatar preview
        if (this.avatarObjectUrl) {
            URL.revokeObjectURL(this.avatarObjectUrl);
            this.avatarObjectUrl = null;
        }

        try {
            const response = await axios.get(route('clients.show', this.clientId), {
                headers: {
                    Accept: 'application/json',
                },
            });

            const client = response.data?.client ?? null;
            if (client) {
                this.initFormFromClient(client);
            } else {
                // fallback: blank form
                this.resetForm();
            }
        } catch (error) {
            console.error('Failed to load client', error);
            this.resetForm();
        } finally {
            this.isLoading = false;
        }
    },
        onPhoneValidate(payload: any) {
            if (!payload) return;
            this.form.phone_e164 =
                payload.e164 || payload.number || this.form.phone_e164;
            if (payload.nationalNumber) {
                this.form.phone = String(payload.nationalNumber);
            }
            const iso2 = (payload.country?.iso2 || '').toLowerCase();
            if (iso2) this.form.country_iso2 = iso2;
            const dial =
                payload.country?.countryCallingCode ||
                payload.country?.dialCode ||
                '';
            if (dial)
                this.form.phone_code = dial.startsWith('+') ? dial : `+${dial}`;
        },
        async submit() {
  if (this.form.processing || this.isLoading) return;

    this.form.clearErrors();

    const formData = new FormData();
    const raw = this.form.data();

   Object.entries(raw).forEach(([key, value]) => {
  if (!this.showOtherInfo && (key === 'password' || key === 'password_confirmation' || key === 'addresses')) {
    return;
  }

  if (value === null || value === '' || typeof value === 'undefined') return;

  if (key === 'addresses' && Array.isArray(value)) {
    value.forEach((addr: any, idx: number) => {
      Object.entries(addr || {}).forEach(([aKey, aVal]) => {
        formData.append(`addresses[${idx}][${aKey}]`, aVal ?? '');
      });
    });
    return;
  }

  if (value instanceof File) formData.append(key, value);
  else formData.append(key, value as any);
});


    // EDIT MODE: make sure id is sent
    if (this.isEditMode && this.form.id != null) {
        formData.set('id', String(this.form.id));
    }

    this.form.processing = true;

    try {
        const url = this.isEditMode && this.form.id
            ? route('clients.update')  // make sure this route exists and points to ClientController@update
            : route('clients.store');

        const response = await axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                Accept: 'application/json',
            },
        });

        const clientPayload = response.data?.client ?? null;

        this.$emit('saved', clientPayload);
        this.$emit('close');

        this.resetForm();
  } catch (error: any) {
  if (error.response && error.response.status === 422) {
    const errors = error.response.data.errors || {};
    this.form.errors = errors;

    const hasOtherInfoErrors =
      !!errors.password ||
      !!errors.password_confirmation ||
      Object.keys(errors).some((k: string) => k.startsWith('addresses'));

    if (hasOtherInfoErrors) {
      this.showOtherInfo = true;
    }
  } else {
    console.error(error);
  }
}
 finally {
        this.form.processing = false;
    }
},


       initFormFromClient(client: any | null = null) {
    this.form.clearErrors();

    const c: any = client || this.client;

    // EDIT MODE – fill from client
    if (this.mode === 'edit' && c) {
        this.form.id = c.id ?? null;

        // IMPORTANT: full name input only shows first name
        this.form.first_name =
            c.first_name ??
            (c.name ? String(c.name).split(' ')[0] : '');
        this.form.last_name = c.last_name ?? '';

        this.form.email = c.email ?? '';

        this.form.gender = c.gender ?? '';
        this.form.country_id = c.country_id ?? c.address_country_id ?? null;

        this.form.phone_e164 = c.phone_e164 ?? null;
        this.form.phone = c.phone ?? '';
        this.form.phone_code = c.phone_code ?? '+94';

        this.form.addresses = Array.isArray(c.addresses) ? c.addresses : [];

        this.form.birthday_year = c.birthday_year ?? '';
        this.form.birthday_daymonth = c.birthday_daymonth ?? '';

        if (c.birthday_daymonth) {
            const [mm, dd] = String(c.birthday_daymonth).split('-');
            const y = c.birthday_year || new Date().getFullYear();
            const month = parseInt(mm || '1', 10) - 1;
            const day = parseInt(dd || '1', 10);
            this.birthdayDate = new Date(y, month, day);
        } else {
            this.birthdayDate = null;
        }

        if (c.avatar_url) {
            if (this.avatarObjectUrl) {
                URL.revokeObjectURL(this.avatarObjectUrl);
            }
            this.avatarObjectUrl = c.avatar_url;
        }
    } else {
        // CREATE MODE – empty form
        this.resetForm();
    }
},


resetForm() {
    this.form.reset();
    this.form.id = null;
    this.birthdayDate = null;

    if (this.avatarObjectUrl) {
        URL.revokeObjectURL(this.avatarObjectUrl);
        this.avatarObjectUrl = null;
    }

    // 🔹 keep both sections collapsed by default
    this.showSecurity = false;
    this.showAddress = false;
},

    },
});
</script>

<style scoped>
.client-modal-enter-active,
.client-modal-leave-active {
    transition: opacity 0.18s ease-out;
}

.client-modal-enter-from,
.client-modal-leave-to {
    opacity: 0;
}

/* Panel slides in from the right */
.client-modal-panel {
    transition: transform 0.22s ease-out;
}

.client-modal-enter-from .client-modal-panel,
.client-modal-leave-to .client-modal-panel {
    transform: translateX(100%);
}

button:focus-visible {
    outline: 2px solid rgb(99 102 241 / 0.65);
    outline-offset: 2px;
}

input:-webkit-autofill,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    -webkit-text-fill-color: inherit;
}

.collapse-enter-active,
.collapse-leave-active {
    transition: opacity 0.18s ease-out, transform 0.18s ease-out;
}

.collapse-enter-from,
.collapse-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

:deep(.vue-tel-input .vti__dropdown-list) {
    max-width: 389%;
    overflow-x: hidden;
}
.collapse-height-enter-active,
.collapse-height-leave-active {
  overflow: hidden;
  transition: max-height 0.25s ease, opacity 0.2s ease;
}

.collapse-height-enter-from,
.collapse-height-leave-to {
  max-height: 0;
  opacity: 0;
}

.collapse-height-enter-to,
.collapse-height-leave-from {
  max-height: 1200px; /* safe upper bound */
  opacity: 1;
}
/* === Normalize form typography in the offcanvas === */
.client-modal-panel :deep(input),
.client-modal-panel :deep(select),
.client-modal-panel :deep(textarea) {
  font-size: 1rem;       /* text-base */
  line-height: 1.5rem;
}

/* === VueTelInput: match InputField sizing === */
.client-modal-panel :deep(.vue-tel-input) {
  height: 2.75rem;       /* h-11 */
  border-radius: 0.375rem;
  border: 1px solid #d4d4d8;
  background: #fff;
}

.client-modal-panel :deep(.vue-tel-input .vti__input) {
  height: 100%;
  font-size: 1rem;       /* text-base */
  line-height: 1.5rem;
}

</style>
