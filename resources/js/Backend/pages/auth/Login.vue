<script setup lang="ts">
import InputError from '@/Backend/components/InputError.vue'
import TextLink from '@/Backend/components/TextLink.vue'
import { Button } from '@/Backend/components/ui/button'
import { Checkbox } from '@/Backend/components/ui/checkbox'
import { Input } from '@/Backend/components/ui/input'
import { Label } from '@/Backend/components/ui/label'
import { Head, useForm, router } from '@inertiajs/vue3'
import { LoaderCircle, Mail, Lock } from 'lucide-vue-next'
import { ref } from 'vue'
import { Eye, EyeOff } from 'lucide-vue-next'

const showPassword = ref(false)

const props = defineProps<{
  status?: string
  canResetPassword: boolean
  canRegister: boolean
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/admin/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
    onSuccess: () => {
      // ensure landing on dashboard
      router.visit('/admin/dashboard', { replace: true })
    },
  })
}
</script>

<template>
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 lg:gap-0 bg-black text-white relative overflow-hidden">
    <Head>
      <title>Log in</title>
      <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&display=swap" rel="stylesheet" />
    </Head>

    <div
      class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,32,0,0.16)_0%,transparent_70%),radial-gradient(circle_at_bottom_right,rgba(255,32,0,0.18)_0%,transparent_80%)] blur-3xl">
    </div>

    <section class="relative z-10 order-2 lg:order-1 flex items-center justify-end px-4 sm:px-6 lg:px-8 py-10 lg:py-0">
      <div class="w-full max-w-xl rounded-2xl border border-white/10 bg-white/5 backdrop-blur-xl shadow-[0_0_50px_rgba(255,32,0,0.18)] animate-slideUpSoft">
        <div class="px-7 sm:px-8 pt-9 pb-3">
          <h2 class="text-3xl font-bold tracking-wide text-white">Welcome back</h2>
          <p class="mt-2 text-base text-white">Enter your credentials to continue.</p>

          <div
            v-if="props.status"
            class="mt-5 rounded-md border border-emerald-500/30 bg-emerald-500/10 p-2.5 text-center text-sm font-medium text-emerald-200"
          >
            {{ props.status }}
          </div>

          <!-- ✅ FIXED: real Inertia form state with v-model -->
          <form @submit.prevent="submit" class="mt-7 space-y-6">
            <!-- Email -->
            <div>
              <Label for="email" class="mb-2 block text-white">Email address</Label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white" />
                <Input
                  id="email"
                  type="email"
                  name="email"
                  required
                  autofocus
                  autocomplete="email"
                  placeholder="email@example.com"
                  :tabindex="1"
                  v-model="form.email"
                  class="h-12 pl-11 text-white placeholder-white bg-black/40 border-white/20 focus:border-[#FF2000] focus:ring-0"
                />
              </div>
              <InputError :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div>
              <div class="mb-2 flex items-center justify-between">
                <Label for="password" class="text-white">Password</Label>
                <TextLink
                  v-if="props.canResetPassword"
                  href="/admin/forgot-password"
                  class="text-sm text-white"
                >
                  Forgot password?
                </TextLink>
              </div>

              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white" />

                <Input
                  id="password"
                  :type="showPassword ? 'text' : 'password'"
                  name="password"
                  required
                  autocomplete="current-password"
                  placeholder="••••••••"
                  :tabindex="2"
                  v-model="form.password"
                  class="h-12 pl-11 pr-11 text-white placeholder-white bg-black/40 border-white/20 focus:border-[#FF2000] focus:ring-0"
                />

                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  :aria-pressed="showPassword ? 'true' : 'false'"
                  :title="showPassword ? 'Hide password' : 'Show password'"
                  class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-md text-white/80 hover:text-white hover:bg-white/10 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FF2000]/60"
                >
                  <Eye v-if="!showPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                  <span class="sr-only">{{ showPassword ? 'Hide password' : 'Show password' }}</span>
                </button>
              </div>

              <InputError :message="form.errors.password" />
            </div>

            <!-- Remember -->
            <div class="flex items-center justify-between">
              <label for="remember" class="flex cursor-pointer select-none items-center gap-3 text-base text-white">
                <Checkbox
                  id="remember"
                  name="remember"
                  :tabindex="3"
                  :checked="form.remember"
                  @update:checked="(v: boolean) => (form.remember = v)"
                />
                <span>Remember me</span>
              </label>
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              class="mt-2 w-full cursor-pointer h-12 rounded-full bg-[#FF2000] text-base font-semibold shadow-[0_0_22px_rgba(255,32,0,0.5)] transition-all btn-animated"
              :tabindex="4"
              :disabled="form.processing"
              data-test="login-button"
            >
              <LoaderCircle v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
              Log in
            </Button>
          </form>
        </div>

        <div class="h-2 w-full rounded-b-2xl bg-gradient-to-r from-transparent via-[#FF2000]/40 to-transparent"></div>
      </div>
    </section>

    <section class="relative z-10 order-1 lg:order-2 flex items-center justify-start px-4 sm:px-6 lg:px-8 py-10 lg:py-0">
      <div class="relative flex w-full max-w-3xl flex-col items-center justify-center mb-6 lg:mb-0">
        <h1
          class="neon-stroke neon-pulse text-center text-[2.5rem] sm:text-[3.5rem] md:text-[5rem] lg:text-[6rem] xl:text-[7rem] font-extrabold uppercase tracking-wider animate-fadeIn font-['Orbitron',sans-serif]"
        >
          L<span class="flicker">O</span>G<span class="flicker-2">I</span>N
        </h1>
        <img
          src="/assets/images/baber_romove_bg.png"
          alt="Drift Barber"
          class="mt-6 w-40 sm:w-48 md:w-56 object-contain drop-shadow-[0_0_36px_rgba(255,32,0,0.55)]"
        />
      </div>
    </section>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.8s ease forwards; }

@keyframes slideUpSoft {
  from { opacity: 0; transform: translateY(24px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-slideUpSoft { animation: slideUpSoft .7s cubic-bezier(.22, .61, .36, 1) both; }

.neon-stroke {
  color: transparent;
  -webkit-text-stroke: 2px #ff2000;
  text-fill-color: transparent;
  letter-spacing: 3px;
  text-shadow: 0 0 6rem #ff2000;
}

.flicker, .flicker-2 { -webkit-text-stroke: 2px #ff2000; }
.flicker { animation: flickerBlink 2.5s infinite; }
.flicker-2 { animation: flickerBlink 3s infinite; }

@keyframes flickerBlink {
  0%, 18%, 22%, 25%, 53%, 57%, 100% { opacity: 1; }
  20%, 24%, 55% { opacity: 0.2; }
}

.btn-animated {
  background-image: linear-gradient(90deg, #FF2000, #ff3c1a, #FF2000);
  background-size: 200% 100%;
}
.btn-animated:hover { animation: btnShimmer 1.2s linear infinite; }
@keyframes btnShimmer { from { background-position: 0% 50%; } to { background-position: 100% 50%; } }

::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-thumb { background: rgba(255, 32, 0, 0.5); border-radius: 9999px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255, 32, 0, 0.8); }
::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }

:deep(input:-webkit-autofill),
:deep(textarea:-webkit-autofill),
:deep(select:-webkit-autofill) {
  -webkit-box-shadow: 0 0 0 1000px #000 inset !important;
  -webkit-text-fill-color: #ffffff !important;
  caret-color: #ffffff;
  transition: background-color 99999s ease-out;
}
:deep(input:-webkit-autofill:hover),
:deep(input:-webkit-autofill:focus) {
  -webkit-box-shadow: 0 0 0 1000px #000 inset !important;
  -webkit-text-fill-color: #ffffff !important;
}
:deep(input:-moz-autofill),
:deep(textarea:-moz-autofill),
:deep(select:-moz-autofill) {
  box-shadow: 0 0 0 1000px #000 inset !important;
  -moz-text-fill-color: #ffffff !important;
  caret-color: #ffffff;
}
</style>
