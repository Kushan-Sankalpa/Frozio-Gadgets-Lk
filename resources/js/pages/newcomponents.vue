<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 py-6">
      <div
        class="mx-auto overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
          <div>
            <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
              {{ user ? 'Edit User' : 'Create User' }}
            </h5>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
              Manage user details.
            </p>
          </div>
        </div>

        <div class="px-5 py-6">
          <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
            <section>
              <h6 class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                User Details
              </h6>

              <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                <Input
  id="name"
  label="Full Name"
  v-model="form.name"
  placeholder="Full Name"
  :is-required="true"
  :error="form.errors.name"
/>
                  <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div>
                 <Input
  id="email"
  type="email"
  label="Email"
  v-model="form.email"
  placeholder="Email"
  :is-required="true"
  :error="form.errors.email"
/>
                  <p v-if="form.errors.email" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.email }}
                  </p>
                </div>

                <div>
                  <SelectInputComponent id="role_id" label="Role" :options="roleOptions" :error="form.errors.role_id"
                    v-model="form.role_id" />
                </div>

                <div>
                  <SelectInputComponent id="status" label="Status" :options="[
                    { id: '1', name: 'Active' },
                    { id: '0', name: 'Inactive' },
                  ]" :error="form.errors.status" v-model="form.status" />
                </div>


    <ToggleSwitch
  id="active"
  label="Active"
  v-model="form.isActive"
  :true-value="1"
  :false-value="0"
  size="md"
  :show-value-text="true"
  on-text="Active"
  off-text="Inactive"
/>

<CheckboxField
  id="agree"
  label="I agree to terms"
  description="You can revoke anytime."
  v-model="form.agree"
  size="xs"
/>

                <div>
                  <label for="avatar" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Avatar</label>
                  <FileInputComponent id="avatar" :isRequired="false" :prvImage="Avatar_Image" v-model="form.avatar"
                    class="mt-1" />
                  <p v-if="form.errors.avatar" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.avatar }}
                  </p>
                </div>

                <!-- Gallery / More Images -->
                <div>
                  <label for="more_images" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">More
                    Images</label>
                  <MultipleFileInputComponent id="more_images" :initial-urls="Gallery_Images" v-model="form.gallery"
                    @remove-initial="
                      (url, index) =>
                        removedInitial.push(url)
                    " />
                  <p v-if="form.errors.gallery" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.gallery }}
                  </p>
                </div>
              </div>
            </section>

            <section>
              <h6 class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                Access
              </h6>
              <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <MultiSelect id="permissions" label="Permissions" placeholder="Pick permissions"
                  :options="permissionOptions" v-model="form.permissions" />
                <MultiSelect id="teams" label="Teams" placeholder="Pick teams" :options="teamOptions"
                  v-model="form.teams" />
              </div>
            </section>

            <section>
              <h6 class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                Security
              </h6>
              <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>

                <Input
  id="password"
  type="password"
  label="Password"
  v-model="form.password"
  autocomplete="new-password"
  clearable
  :error="form.errors.password"
/>

                  <p v-if="form.errors.password" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.password }}
                  </p>
                </div>

                <div>

                  <Input
  id="password_confirmation"
  type="password"
  label="Confirm Password"
  v-model="form.password_confirmation"
  autocomplete="new-password"
  :error="form.errors.password_confirmation"
/>
                  <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.password_confirmation }}
                  </p>
                </div>
              </div>
            </section>

            <section>
              <h6 class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                Contact
              </h6>
              <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>

                <Input
  id="phone"
  type="tel"
  label="Phone (Optional)"
  v-model="form.phone"
  placeholder="+1 555 123 4567"
  :error="form.errors.phone"
/>
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.phone }}
                  </p>
                </div>
                <div>

                 <Input
  id="location"
  label="Location (Optional)"
  v-model="form.location"
  :error="form.errors.location"
  multiline
  rows="3"
/>
                  <p v-if="form.errors.location" class="mt-1 text-sm text-rose-600">
                    {{ form.errors.location }}
                  </p>
                </div>
              </div>
            </section>

            <div class="flex items-center gap-3">
              <button type="submit" :disabled="form.processing"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60">
                {{ user ? 'Update' : 'Save' }}
              </button>

              <Link
                class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2 focus:outline-none dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                :href="route('users.index')">
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
import FileInputComponent from '@/components/FileInputComponent.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import MultipleFileInputComponent from '@/components/MultipleFileInputComponent.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import Input from '@/components/InputField.vue';
import ToggleSwitch from '@/components/ToggleSwitch.vue'
import CheckboxField from '@/components/CheckboxField.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export default {
  components: {
    Link,
    AppLayout,
    FileInputComponent,
    SelectInputComponent,
    MultiSelect,
    MultipleFileInputComponent,
    Input,
    ToggleSwitch,
    CheckboxField,
  },
  props: {
    user: Object,
    roles: Array,
    errors: Object,
  },
  data() {
    return {
      form: useForm({
        id: '',
        name: '',
        email: '',
        role_id: '',
        status: '1',
        avatar: '',
        gallery: [],
        phone: '',
        location: '',
        password: '',
        password_confirmation: '',
        permissions: [],
        teams: [],
        isActive: 1, agree: false,
      }),

      permissionOptions: [
        { id: 'users.view', name: 'View Users' },
        { id: 'users.create', name: 'Create Users' },
        { id: 'users.edit', name: 'Edit Users' },
        { id: 'users.delete', name: 'Delete Users' },
        { id: 'roles.assign', name: 'Assign Roles' },
      ],
      teamOptions: [
        { id: 'ops', name: 'Operations' },
        { id: 'sales', name: 'Sales' },
        { id: 'marketing', name: 'Marketing' },
        { id: 'support', name: 'Support' },
        { id: 'dev', name: 'Engineering' },
      ],
    };
  },
  mounted() {
    if (this.user) {
      this.form.id = this.user.id;
      this.form.name = this.user.name || '';
      this.form.email = this.user.email || '';
      this.form.role_id = String(this.user.role_id ?? '');
      this.form.status = String(this.user.status ?? '1');
      this.form.phone = this.user.phone || '';
      this.form.location = this.user.location || '';
      this.form.permissions = Array.isArray(this.user.permissions)
        ? this.user.permissions
        : [];
      this.form.teams = Array.isArray(this.user.teams)
        ? this.user.teams
        : [];
    }
  },
  computed: {
    roleOptions() {
      return (this.roles || []).map((r) => ({
        id: String(r.id ?? r.value ?? ''),
        name: r.name ?? r.title ?? r.label ?? '—',
      }));
    },
    Avatar_Image() {
      return this.user && this.user.media && this.user.media.length
        ? this.user.media[0].original_url
        : '';
    },
    Gallery_Images() {
      const all = Array.isArray(this.user?.media) ? this.user.media : [];
      const urls = all.map((m) => m?.original_url).filter(Boolean);
      const avatar = this.Avatar_Image;
      return avatar ? urls.filter((u) => u !== avatar) : urls;
    },
  },
  methods: {
    route,
    submit() {
      const url = this.user
        ? route('users.update', this.user.id)
        : route('users.store');

      const req = this.user
        ? this.form.transform((d) => ({ ...d, _method: 'PUT' }))
        : this.form;

      req.post(url, {
        forceFormData: true,
        onSuccess: () => {
          this.form.reset();
          this.$root?.showMessage?.(
            'success',
            '<span class="text-green-600">Success</span><br/>',
            this.user
              ? 'User updated successfully!'
              : 'User created successfully!',
          );
        },
        onError: () => {
          this.$root?.showMessage?.(
            'error',
            '<span class="text-rose-600">Error</span><br>',
            'Something went wrong!',
          );
        },
        onFinish: () => {
          if (this.user) this.form.clearErrors();
        },
      });
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
