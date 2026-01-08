<template>
  <Transition name="waitlist-overlay" appear>
    <div
      v-if="show"
      class="fixed inset-0 z-[250] flex justify-end bg-black/40 backdrop-blur-[2px]"
      @click.self="closeAll"
    >
      <Transition name="waitlist-slide" appear>
        <div
          class="relative flex h-full w-[460px] sm:w-[520px] max-w-full flex-col bg-white/95 shadow-[0_24px_80px_rgba(15,23,42,0.35)] backdrop-blur-sm"
        >
          <!-- LIST MODE -->
          <template v-if="mode === 'list'">
            <!-- header -->
            <div
              class="flex items-center justify-between border-b px-5 py-4 sm:px-6 sm:py-5"
            >
              <div class="flex flex-col gap-0.5">
                <h2
                  class="text-lg sm:text-xl font-semibold tracking-tight text-slate-900"
                >
                  Pending bookings
                </h2>
                <p class="text-[11px] sm:text-xs text-neutral-500">
                  Bookings with status
                  <span class="font-semibold text-slate-800">pending</span>
                </p>
              </div>

              <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-all duration-200 ease-out active:scale-95 cursor-pointer"
                @click="closeAll"
              >
                ✕
              </button>
            </div>

            <!-- filters -->
            <div class="border-b px-5 pb-3 pt-3 sm:px-6">
              <div class="flex flex-wrap items-center gap-2">
                <!-- Upcoming filter -->
                <div class="min-w-[140px]">
                  <SelectInputComponent
                    v-model="selectedUpcoming"
                    :options="upcomingOptions"
                    placeholder="Upcoming"
                  />
                </div>

                <!-- Sort filter -->
                <div class="min-w-[160px]">
                  <SelectInputComponent
                    v-model="selectedSort"
                    :options="sortOptions"
                    placeholder="Sort by"
                  />
                </div>
              </div>
            </div>

            <!-- list body -->
            <div
              class="flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5 space-y-3"
            >
              <!-- loading skeleton -->
              <div v-if="loading" class="space-y-3">
                <div
                  v-for="i in 3"
                  :key="'skeleton-' + i"
                  class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 sm:p-5 animate-pulse-soft"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full skeleton"></div>
                      <div class="space-y-1">
                        <div class="h-3.5 w-32 rounded-full skeleton"></div>
                        <div
                          class="h-3 w-24 rounded-full skeleton skeleton-soft"
                        ></div>
                      </div>
                    </div>
                    <div class="h-4 w-4 rounded-full skeleton"></div>
                  </div>
                  <div class="mt-3 h-3 w-28 rounded-full skeleton"></div>
                  <div class="mt-3 flex gap-2">
                    <div class="h-8 flex-1 rounded-full skeleton"></div>
                    <div
                      class="h-8 flex-1 rounded-full skeleton skeleton-soft"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- empty state -->
              <div
                v-else-if="visibleItems.length === 0"
                class="mt-8 flex flex-col items-center justify-center gap-2 text-center"
              >
                <div
                  class="flex h-10 w-10 items-center justify-center rounded-full bg-neutral-100 text-neutral-400 text-xl"
                >
                  ☺
                </div>
                <p class="text-sm font-medium text-neutral-700">
                  No pending bookings
                </p>
                <p class="text-xs text-neutral-500 max-w-xs">
                  When a client joins the waitlist, their request will appear
                  here so you can schedule it in.
                </p>
              </div>

              <!-- items -->
              <div
                v-else
                v-for="b in visibleItems"
                :key="b.id"
                class="group rounded-2xl border border-neutral-200 bg-white p-4 sm:p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition-all duration-250 ease-out hover:shadow-[0_20px_45px_rgba(15,23,42,0.12)] hover:-translate-y-[1px]"
              >
                <!-- header row -->
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div
                      class="grid h-10 w-10 place-items-center rounded-full bg-[var(--brand,_var(--brand-fallback))]/10 text-sm font-semibold text-[var(--brand,_var(--brand-fallback))]"
                    >
                      {{ initials(b.client_name) }}
                    </div>
                    <div class="min-w-0">
                      <div
                        class="truncate text-sm sm:text-[15px] font-semibold text-neutral-900"
                      >
                        {{ b.client_name || 'Walk-in' }}
                      </div>
                      <div class="mt-0.5 text-[11px] text-neutral-500">
                        {{ formatDateTime(b.starts_at || b.date) }}
                      </div>
                    </div>
                  </div>

                  <!-- close / reject button -->
                <button
  v-if="canCloseWaitlist"
  type="button"
  class="inline-flex items-center justify-center rounded-full border border-neutral-300 px-3 py-1.5 text-[11px] sm:text-xs font-semibold text-neutral-700 hover:bg-neutral-50 cursor-pointer"
  @click="openRejectDialog(b)"
>
  <i class="bx bx-x-circle text-[13px] mr-1"></i>
  Close
</button>

                </div>

                <!-- history chips -->
                <div
                  v-if="b.completed_count || b.cancel_count || b.no_show_count || (b.pending_count && b.pending_count > 1)"
                  class="mt-3 text-sm text-neutral-500 flex flex-wrap gap-2 items-center"
                >
                  <!-- Completed -->
                  <span
                    v-if="b.completed_count && b.completed_count > 0"
                    class="flex items-center gap-1"
                  >
                    <i class="bx bx-check-circle text-green-500 text-[12px]"></i>
                    <span>{{ b.completed_count }} completed</span>
                  </span>

                  <!-- Cancelled -->
                  <span
                    v-if="b.cancel_count && b.cancel_count > 0"
                    class="flex items-center gap-1"
                  >
                    <i class="bx bx-x-circle text-red-500 text-[12px]"></i>
                    <span>{{ b.cancel_count }} cancelled</span>
                  </span>

                  <!-- No-show -->
                  <span
                    v-if="b.no_show_count && b.no_show_count > 0"
                    class="flex items-center gap-1"
                  >
                    <i class="bx bx-user-x text-orange-500 text-[12px]"></i>
                    <span>{{ b.no_show_count }} no-show</span>
                  </span>

                  <!-- Pending -->
                  <span
                    v-if="b.pending_count && b.pending_count > 1"
                    class="flex items-center gap-1"
                  >
                    <i class="bx bx-time-five text-sky-500 text-[12px]"></i>
                    <span>{{ b.pending_count }} pending</span>
                  </span>
                </div>

                <!-- risk score -->
                <div
                  v-if="b.risk_score != null"
                  class="mt-2 flex items-center gap-2"
                >
                  <div class="flex-1 h-1.5 bg-neutral-200 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all"
                      :class="{
                        'bg-green-500': (b.risk_score || 0) <= 30,
                        'bg-yellow-500':
                          (b.risk_score || 0) > 30 &&
                          (b.risk_score || 0) <= 60,
                        'bg-red-500': (b.risk_score || 0) > 60,
                      }"
                      :style="{
                        width:
                          Math.min(Math.max(b.risk_score || 0, 0), 100) + '%',
                      }"
                    ></div>
                  </div>

                  <span
                    class="text-[10px] font-semibold"
                    :class="{
                      'text-green-600': (b.risk_score || 0) <= 30,
                      'text-yellow-600':
                        (b.risk_score || 0) > 30 &&
                        (b.risk_score || 0) <= 60,
                      'text-red-600': (b.risk_score || 0) > 60,
                    }"
                  >
                    {{ b.risk_score }}%
                  </span>
                </div>

                <!-- summary -->
                <div class="mt-3 text-xs sm:text-[13px] text-neutral-700">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-900">
                      {{ servicesCount(b) }} services
                    </span>

                    <span
                      v-if="totalDurationMinutes(b)"
                      class="inline-flex items-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-[11px] font-medium text-neutral-700"
                    >
                      {{ formatDuration(totalDurationMinutes(b) || 0) }}
                    </span>
                  </div>

                  <div
                    class="mt-2 flex items-center gap-2 text-[11px] text-neutral-500"
                  >
                    <i class="bx bx-user text-sm"></i>
                    <span>{{ b.staff_name || 'Any employee' }}</span>
                  </div>
                </div>

                <!-- actions -->
                <div class="mt-4 flex gap-2">
                <button
  v-if="canAcceptWaitlist"
  type="button"
  class="flex-1 cursor-pointer rounded-full bg-[var(--brand,_var(--brand-fallback))] py-2.5 text-xs sm:text-[13px] font-semibold text-white shadow-sm hover:shadow-md hover:brightness-105 transition-all duration-200 ease-out active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
  :disabled="acceptingId === b.id || (b.unassigned_staff_count ?? 0) > 0"
  @click="accept(b)"
>
  <span v-if="acceptingId === b.id">Accepting…</span>
  <span v-else>Accept</span>
</button>



                 <button
  v-if="canEditWaitlist"
  type="button"
  class="flex-1 cursor-pointer rounded-full border border-neutral-300 bg-white py-2.5 text-xs sm:text-[13px] font-semibold text-neutral-800 hover:bg-neutral-50 hover:border-neutral-400 transition-all duration-200 ease-out active:scale-95"
  @click="openBooking(b)"
>
  Edit &amp; View
</button>

                </div>
<p
  v-if="(b.unassigned_staff_count ?? 0) > 0"
  class="mt-2 text-[11px] sm:text-xs font-semibold text-rose-600"
>
  Please select staff member
</p>

                <!-- price -->
                <div
                  v-if="b.total_price != null"
                  class="mt-3 text-right text-[11px] sm:text-xs text-neutral-500"
                >
                  <span class="font-semibold text-neutral-800">
                    {{ currencySymbol }}
                    {{ formatNumber(b.total_price || 0) }}
                  </span>
                </div>
              </div>
            </div>
          </template>

          <!-- EDIT MODE -->
          <template v-else-if="mode === 'edit'">
            <!-- header -->
            <div
              class="flex items-center justify-between border-b px-5 py-4 sm:px-6 sm:py-5"
            >
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 transition-all duration-200 ease-out active:scale-95 cursor-pointer"
                  @click="closeEdit"
                >
                  ←
                </button>
                <div>
                  <h2
                    class="text-lg sm:text-xl font-semibold tracking-tight text-slate-900"
                  >
                    Edit waitlist entry
                  </h2>
                  <p
                    class="mt-0.5 text-[11px] sm:text-xs text-neutral-500"
                  >
                    Update branch, time and services for this pending booking.
                  </p>
                </div>
              </div>

              <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-all duration-200 ease-out active:scale-95 cursor-pointer"
                @click="closeEdit"
              >
                ✕
              </button>
            </div>

            <!-- body -->
            <div
              class="flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5 space-y-6"
            >
              <!-- edit loading skeleton -->
              <div v-if="editLoading" class="space-y-4">
                <div class="h-4 w-32 rounded-full skeleton"></div>
                <div class="h-9 w-full rounded-lg skeleton"></div>

                <div class="h-4 w-40 rounded-full skeleton mt-4"></div>
                <div class="grid grid-cols-2 gap-3 mt-2">
                  <div class="h-9 w-full rounded-lg skeleton"></div>
                  <div class="h-9 w-full rounded-lg skeleton skeleton-soft"></div>
                </div>

                <div class="h-4 w-36 rounded-full skeleton mt-4"></div>
                <div class="h-20 w-full rounded-xl skeleton skeleton-soft"></div>
              </div>

              <!-- edit form -->
              <template v-else>
                <!-- branch -->
                <section>
                  <h3
                    class="text-xs sm:text-[13px] font-semibold text-neutral-800 mb-1.5"
                  >
                    Branch
                  </h3>
                  <SelectInputComponent
                    v-model="editForm.branch_id"
                    :options="branchOptions"
                    placeholder="Select branch"
                  />
                  <p class="mt-1 text-[11px] text-neutral-500">
                    Select the branch where this booking will take place.
                  </p>
                </section>

                <!-- date/time -->
                <section>
                  <h3
                    class="text-xs sm:text-[13px] font-semibold text-neutral-800 mb-2"
                  >
                    Preferred date and time
                  </h3>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label
                        class="mb-1 block text-[11px] text-neutral-500"
                      >
                        Date
                      </label>
                      <input
                        type="date"
                        v-model="editForm.date"
                        class="w-full rounded-lg border border-neutral-200 px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900/20 transition-all duration-200 ease-out"
                      />
                    </div>
                    <div>
                      <label
                        class="mb-1 block text-[11px] text-neutral-500"
                      >
                        Start time
                      </label>
                      <SelectInputComponent
                        v-model="editForm.time"
                        :options="timeOptionsSelect"
                        placeholder="Select time"
                      />
                    </div>
                  </div>
                </section>

                <!-- services -->
                <section>
                  <h3
                    class="text-xs sm:text-[13px] font-semibold text-neutral-800 mb-2"
                  >
                    Services
                  </h3>

                  <div
                    v-for="(svc, idx) in editForm.services"
                    :key="svc.localKey"
                    class="mb-3 flex gap-3 rounded-xl border border-neutral-200 bg-white px-3 py-2.5 sm:px-3.5 sm:py-3 shadow-sm"
                  >
                    <div class="mt-0.5 h-full w-1 rounded-full bg-sky-500/80"></div>

                    <div class="flex-1 min-w-0">
                      <div
                        class="flex items-start justify-between gap-2"
                      >
                        <div>
                          <div
                            class="text-xs sm:text-[13px] font-medium text-neutral-900"
                          >
                            {{ svc.label }}
                          </div>
                          <div
                            class="mt-0.5 text-[11px] text-neutral-500"
                          >
                            {{ formatDuration(svc.duration_minutes) }}
                          </div>
                        </div>
                        <div
                          class="text-xs sm:text-[13px] font-semibold text-neutral-800 whitespace-nowrap"
                        >
                          {{ currencySymbol }}
                          {{ formatNumber(svc.final_price) }}
                        </div>
                      </div>

                      <div class="mt-2">
                        <SelectInputComponent
                          v-model="svc.staff_id"
                          :options="employeeOptions"
                          placeholder="Any team member"
                        />
                      </div>
                    </div>

                    <button
                      type="button"
                      class="mt-1 flex h-7 w-7 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 text-xs cursor-pointer transition-all duration-200 ease-out active:scale-95"
                      @click="removeService(idx)"
                    >
                      ✕
                    </button>
                  </div>

                  <button
                    type="button"
                    class="mt-1 inline-flex items-center justify-center rounded-full border border-neutral-300 px-4 py-1.5 text-xs sm:text-[13px] font-medium text-neutral-800 bg-white hover:bg-neutral-50 hover:border-neutral-400 transition-all duration-200 ease-out active:scale-95"
                    @click="openServiceSelector"
                  >
                    + Add service
                  </button>
                </section>

                <!-- notes -->
                <section>
                  <h3
                    class="text-xs sm:text-[13px] font-semibold text-neutral-800 mb-2"
                  >
                    Notes
                  </h3>
                  <textarea
                    v-model="editForm.note"
                    rows="4"
                    class="w-full rounded-lg border border-neutral-200 px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900/20 transition-all duration-200 ease-out"
                    placeholder="Add a note from the client or internal instructions…"
                  ></textarea>
                </section>
              </template>
            </div>

            <!-- footer -->
            <div class="border-t px-5 py-3 sm:px-6 sm:py-4">
              <button
                type="button"
                class="w-full rounded-full bg-slate-900 py-2.75 sm:py-3 text-sm sm:text-[15px] font-semibold text-white shadow-sm hover:shadow-md hover:bg-slate-900/95 transition-all duration-200 ease-out active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="savingEdit || !isEditValid"
                @click="saveEdit"
              >
                {{ savingEdit ? 'Saving…' : 'Save changes' }}
              </button>
            </div>
          </template>

          <!-- SERVICES MODE -->
          <template v-else>
            <!-- header -->
            <div
              class="flex items-center justify-between border-b px-5 py-4 sm:px-6 sm:py-5"
            >
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 transition-all duration-200 ease-out active:scale-95 cursor-pointer"
                  @click="closeServiceSelector"
                >
                  ←
                </button>
                <div>
                  <h2
                    class="text-lg sm:text-xl font-semibold tracking-tight text-slate-900"
                  >
                    Select services
                  </h2>
                  <p
                    class="mt-0.5 text-[11px] sm:text-xs text-neutral-500"
                  >
                    Choose one or more services to add to this booking.
                  </p>
                </div>
              </div>

              <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-all duration-200 ease-out active:scale-95 cursor-pointer"
                @click="closeServiceSelector"
              >
                ✕
              </button>
            </div>

            <!-- search -->
            <div class="border-b px-5 py-3 sm:px-6">
              <input
                type="text"
                v-model="serviceSearch"
                class="w-full rounded-full border border-neutral-200 px-3.5 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-900/20 transition-all duration-200 ease-out"
                placeholder="Search services…"
              />
            </div>

            <!-- services list -->
            <div
              class="flex-1 overflow-y-auto px-5 py-3 sm:px-6 sm:py-4 space-y-2"
            >
              <div
                v-if="filteredServices.length === 0"
                class="mt-4 text-center text-xs text-neutral-500"
              >
                No services found.
              </div>

              <label
                v-for="svc in filteredServices"
                :key="svc.id"
                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-neutral-200 bg-white px-3.5 py-2.5 hover:bg-neutral-50 hover:border-neutral-300 transition-all duration-200 ease-out active:scale-[0.99]"
              >
                <input
                  type="checkbox"
                  class="mt-1.5 h-4 w-4 cursor-pointer rounded border-neutral-300 text-[var(--brand,_var(--brand-fallback))] focus:ring-[var(--brand,_var(--brand-fallback))]"
                  :value="svc.id"
                  v-model="selectedServiceIds"
                />

                <div class="flex-1 min-w-0">
                  <div
                    class="flex items-start justify-between gap-2"
                  >
                    <div>
                      <div
                        class="text-xs sm:text-[13px] font-medium text-neutral-900"
                      >
                        {{ svc.name }}
                      </div>
                      <div
                        v-if="svc.duration_minutes"
                        class="mt-0.5 text-[11px] text-neutral-500"
                      >
                        {{ formatDuration(svc.duration_minutes) }}
                      </div>
                    </div>
                    <div
                      class="text-xs sm:text-[13px] font-semibold text-neutral-900 whitespace-nowrap"
                    >
                      {{ currencySymbol }}
                      {{ formatNumber(svc.price) }}
                    </div>
                  </div>
                </div>
              </label>
            </div>

            <!-- footer -->
            <div
              class="border-t px-5 py-3 sm:px-6 sm:py-4 flex gap-2"
            >
              <button
                type="button"
                class="flex-1 rounded-full border border-neutral-300 bg-white py-2.5 text-sm font-semibold text-neutral-800 hover:bg-neutral-50 hover:border-neutral-400 transition-all duration-200 ease-out active:scale-95"
                @click="closeServiceSelector"
              >
                Cancel
              </button>
              <button
                type="button"
                class="flex-1 rounded-full bg-slate-900 py-2.5 text-sm font-semibold text-white shadow-sm hover:shadow-md hover:bg-slate-900/95 transition-all duration-200 ease-out active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="selectedServiceIds.length === 0"
                @click="addSelectedServices"
              >
                Add selected
              </button>
            </div>
          </template>
        </div>
      </Transition>
    </div>
  </Transition>

  <!-- Confirm reject modal -->
  <Transition name="fade">
    <div
      v-if="rejectDialogShow"
      class="fixed inset-0 z-[260] flex items-center justify-center bg-black/40"
      @click.self="closeRejectDialog"
    >
      <div
        class="w-[360px] max-w-[90vw] rounded-2xl bg-white p-5 shadow-[0_18px_50px_rgba(15,23,42,0.4)]"
      >
        <h3 class="text-sm sm:text-base font-semibold text-slate-900 mb-2">
          Close waitlist booking?
        </h3>
        <p class="text-xs sm:text-[13px] text-neutral-600 mb-4">
          This will mark the booking as
          <span class="font-semibold">rejected</span>. It will no longer appear
          in the waitlist or on the calendar.
        </p>

        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full border border-neutral-300 px-3 py-1.5 text-[11px] sm:text-xs font-medium text-neutral-700 hover:bg-neutral-50 cursor-pointer"
            @click="closeRejectDialog"
            :disabled="rejectLoading"
          >
            Cancel
          </button>

          <button
            type="button"
            class="inline-flex items-center justify-center rounded-full bg-rose-600 px-3 py-1.5 text-[11px] sm:text-xs font-semibold text-white shadow-sm hover:bg-rose-700 cursor-pointer disabled:opacity-60"
            @click="confirmReject"
            :disabled="rejectLoading"
          >
            <span v-if="!rejectLoading">Confirm</span>
            <span v-else>Processing…</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import axios from 'axios';
import SelectInputComponent from '@/components/SelectInputComponent.vue';

interface PendingBooking {
  id: number;
  status?: string;
  date?: string | null;
  starts_at?: string | null;
  created_at?: string | null;
  total_price?: number | null;
  client_name?: string | null;
  staff_name?: string | null;
  services_count?: number | null;
  duration_minutes?: number | null;
  unassigned_staff_count?: number | null;
  needs_staff?: boolean | null;
  // history / risk
  cancel_count?: number | null;
  completed_count?: number | null;
  no_show_count?: number | null;
  total_bookings?: number | null;
  pending_count?: number | null;
  risk_score?: number | null;
}

interface EditService {
  localKey: string;
  id?: number | null;
  service_id: number;
  service_variant_id?: number | null;
  label: string;
  duration_minutes: number;
  extra_minutes: number;
  price: number;
  discount_type: string | null;
  discount_value: number;
  final_price: number;
  color_code?: string | null;
  staff_id?: number | null;
}

interface ServiceCatalogItem {
  id: number;
  name: string;
  price?: number | null;
  duration_minutes?: number | null;
  default_duration?: number | null;
  base_price?: number | null;
  variants?: Array<{ duration_minutes?: number | null; price?: number | null }>;
}

export default defineComponent({
  name: 'Waitlist',
  components: {
    SelectInputComponent,
  },
  props: {
    show: { type: Boolean, default: false },
    branchId: {
      type: [String, Number] as unknown as PropType<string | number | null>,
      default: null,
    },
    currencySymbol: { type: String, default: 'LKR' },
    services: {
      type: Array as PropType<ServiceCatalogItem[]>,
      default: () => [],
    },
  },
  emits: ['close', 'accepted', 'count-changed'],
  data() {
    return {
      mode: 'list' as 'list' | 'edit' | 'services',

      loading: false,
      items: [] as PendingBooking[],
      acceptingId: null as number | null,

      upcomingOptions: [
        { label: 'Today', value: 'today', days: 0 },
        { label: 'Next 3 days', value: 'next_3', days: 3 },
        { label: 'Next 7 days', value: 'next_7', days: 7 },
        { label: 'Next 30 days', value: 'next_30', days: 30 },
        { label: 'All upcoming', value: 'all', days: null as number | null },
      ],
      sortOptions: [
        { label: 'Created (oldest first)', value: 'created_asc' },
        { label: 'Created (newest first)', value: 'created_desc' },
        { label: 'Price (highest first)', value: 'price_desc' },
        { label: 'Price (lowest first)', value: 'price_asc' },
        { label: 'Requested date (nearest first)', value: 'requested_nearest' },
        { label: 'Requested date (furthest first)', value: 'requested_furthest' },
      ],
      selectedUpcoming: 'all',
      selectedSort: 'created_desc',

      editLoading: false,
      savingEdit: false,
      editBookingId: null as number | null,
      editForm: {
        branch_id: null as number | null,
        date: '',
        time: '',
        note: null as string | null,
        services: [] as EditService[],
      },

      branches: [] as Array<{ id: number; name: string }>,
      employees: [] as Array<{ id: number; name: string }>,

      // services selector
      serviceSearch: '',
      selectedServiceIds: [] as number[],

      // reject dialog
      rejectDialogShow: false,
      rejectLoading: false,
      rejectBooking: null as PendingBooking | null,
    };
  },
  computed: {
    visibleItems(): PendingBooking[] {
      let list = [...this.items];

      const opt = this.upcomingOptions.find(
        (o) => o.value === this.selectedUpcoming,
      );
      if (opt && opt.value !== 'all') {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const start = new Date(today);
        const end = new Date(today);
        const days = opt.days ?? 0;
        end.setDate(end.getDate() + days);
        end.setHours(23, 59, 59, 999);

        list = list.filter((b) => {
          const d = this.parseDate(b);
          return d >= start && d <= end;
        });
      } else {
        // "All upcoming" – only from today onwards
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        list = list.filter((b) => this.parseDate(b) >= today);
      }

      const getCreated = (b: PendingBooking) =>
        b.created_at ? new Date(b.created_at).getTime() : this.parseDate(b).getTime();
      const getPrice = (b: PendingBooking) => Number(b.total_price ?? 0);
      const getRequested = (b: PendingBooking) => this.parseDate(b).getTime();

      const sortVal = this.selectedSort;
      list.sort((a, b) => {
        switch (sortVal) {
          case 'created_asc':
            return getCreated(a) - getCreated(b);
          case 'created_desc':
            return getCreated(b) - getCreated(a);
          case 'price_desc':
            return getPrice(b) - getPrice(a);
          case 'price_asc':
            return getPrice(a) - getPrice(b);
          case 'requested_nearest':
            return getRequested(a) - getRequested(b);
          case 'requested_furthest':
            return getRequested(b) - getRequested(a);
          default:
            return getCreated(b) - getCreated(a);
        }
      });

      return list;
    },

    timeOptions(): string[] {
      const arr: string[] = [];
      let h = 6;
      let m = 0;
      while (h < 22 || (h === 22 && m === 0)) {
        const hh = String(h).padStart(2, '0');
        const mm = String(m).padStart(2, '0');
        arr.push(`${hh}:${mm}`);
        m += 15;
        if (m >= 60) {
          m = 0;
          h += 1;
        }
      }
      return arr;
    },

    timeOptionsSelect() {
      return this.timeOptions.map((t) => ({ label: t, value: t }));
    },

    branchOptions() {
      return this.branches.map((br) => ({ label: br.name, value: br.id }));
    },

    employeeOptions() {
      return this.employees.map((emp) => ({ label: emp.name, value: emp.id }));
    },

    isEditValid(): boolean {
      return (
        !!this.editForm.date &&
        !!this.editForm.time &&
        this.editForm.services.length > 0
      );
    },

    canAcceptWaitlist(): boolean {
    const root: any = this.$root;
    return typeof root?.hasPermission === 'function'
      ? !!root.hasPermission('waitlist.accept')
      : false;
  },

  canEditWaitlist(): boolean {
    const root: any = this.$root;
    return typeof root?.hasPermission === 'function'
      ? !!root.hasPermission('waitlist.edit&view')
      : false;
  },

  canCloseWaitlist(): boolean {
    const root: any = this.$root;
    return typeof root?.hasPermission === 'function'
      ? !!root.hasPermission('waitlist.close')
      : false;
  },

    serviceList(): Array<{
      id: number;
      name: string;
      duration_minutes: number;
      price: number;
    }> {
      return (this.services || []).map((s: any) => {
        const duration =
          s.duration_minutes ??
          s.default_duration ??
          (s.variants && s.variants[0]?.duration_minutes) ??
          0;
        const price =
          s.price ?? s.base_price ?? (s.variants && s.variants[0]?.price) ?? 0;

        return {
          id: Number(s.id),
          name: String(s.name || 'Service'),
          duration_minutes: Number(duration || 0),
          price: Number(price || 0),
        };
      });
    },

    filteredServices() {
      const term = this.serviceSearch.trim().toLowerCase();
      let list = this.serviceList;
      if (term) {
        list = list.filter((s) => s.name.toLowerCase().includes(term));
      }
      return list;
    },
  },
  watch: {
    show(val: boolean) {
      if (val) {
        this.mode = 'list';
        this.fetchPending();
      }
    },
    branchId() {
      this.fetchPending();
    },
    'editForm.branch_id'(id: number | null) {
      if (id != null) {
        this.fetchEmployees(id as number);
      }
    },
  },
  mounted() {
    this.fetchPending();
  },
  methods: {
    async fetchPending() {
      this.loading = true;
      try {
        const response = await axios.get('/bookings/pending', {
          params: { branch_id: this.branchId || undefined },
        });
        const data = (response.data && response.data.data) || response.data || [];
        this.items = Array.isArray(data) ? data : [];
      } catch (e) {
        console.error('Failed to load pending bookings', e);
        this.items = [];
      } finally {
        this.loading = false;

        // emit count of upcoming pending bookings (from today onwards)
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const upcomingCount = this.items.filter((b) => {
          const d = this.parseDate(b);
          return d >= today;
        }).length;

        this.$emit('count-changed', upcomingCount);
      }
    },

   async accept(booking: PendingBooking) {
  if (!this.canAcceptWaitlist) return;
  if (!booking?.id) return;

  if ((booking.unassigned_staff_count ?? 0) > 0) {
    return; // button is disabled anyway
  }

  this.acceptingId = booking.id;

  try {
    await axios.post(`/bookings/${booking.id}/status`, { status: 'scheduled' });

    this.items = this.items.filter((b) => b.id !== booking.id);
    this.$emit('accepted', booking.id);

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const upcomingCount = this.items.filter((b) => this.parseDate(b) >= today).length;
    this.$emit('count-changed', upcomingCount);
  } catch (e: any) {
    // If backend blocks because staff_id is missing
    if (e?.response?.status === 422) {
      alert(e?.response?.data?.message || 'Please select staff member');
      await this.fetchPending(); // refresh flags
    } else {
      console.error('Failed to accept booking', e);
    }
  } finally {
    this.acceptingId = null;
  }
},

    closeAll() {
      this.mode = 'list';
      this.$emit('close');
    },

    

    initials(name: string | null | undefined): string {
      return String(name || '')
        .trim()
        .split(/\s+/)
        .map((p) => p[0]?.toUpperCase())
        .join('')
        .slice(0, 3);
    },

    formatDateTime(iso: string | null | undefined): string {
      if (!iso) return '';
      const d = new Date(iso);
      if (Number.isNaN(d.getTime())) return '';
      return d.toLocaleString([], {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    },

    formatDuration(mins: number | null | undefined): string {
      if (!mins || mins <= 0) return '';
      const h = Math.floor(mins / 60);
      const m = mins % 60;
      if (h && m) return `${h} hr ${m} min`;
      if (h) return `${h} hr${h > 1 ? 's' : ''}`;
      return `${m} min`;
    },

    formatNumber(value: number | string): string {
      const n =
        typeof value === 'number'
          ? value
          : Number(String(value).replace(/[^\d.-]/g, ''));
      if (Number.isNaN(n)) return String(value);
      return n.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      });
    },

    parseDate(b: PendingBooking): Date {
      const raw = b.starts_at || b.date || b.created_at;
      const d = raw ? new Date(raw) : new Date();
      if (Number.isNaN(d.getTime())) return new Date();
      return d;
    },

    servicesCount(b: PendingBooking): number {
      return typeof b.services_count === 'number' && b.services_count > 0
        ? b.services_count
        : 1;
    },

    totalDurationMinutes(b: PendingBooking): number | null {
      return typeof b.duration_minutes === 'number' && b.duration_minutes > 0
        ? b.duration_minutes
        : null;
    },

    async fetchBranches() {
      try {
        const { data } = await axios.get('/branches/select');
        this.branches = data.data || data || [];
      } catch (e) {
        console.error('Failed to load branches', e);
      }
    },

    async fetchEmployees(branchId: number | null) {
      try {
        const { data } = await axios.get('/employees/select', {
          params: { branch_id: branchId || undefined },
        });
        this.employees = data.data || data || [];
      } catch (e) {
        console.error('Failed to load employees', e);
        this.employees = [];
      }
    },

    async openBooking(booking: PendingBooking) {
      if (!booking?.id) return;

      this.editBookingId = booking.id;
      this.mode = 'edit';
      this.editLoading = true;

      try {
        await this.fetchBranches();

        const { data } = await axios.get(`/bookings/${booking.id}/waitlist`);
        const b = data.booking;

        this.editForm.branch_id =
          b.branch_id ?? (this.branchId as any) ?? null;
        this.editForm.date = b.date || '';
        this.editForm.time = b.time || '';
        this.editForm.note = b.note || null;
        this.editForm.services = (b.services || []).map(
          (s: any): EditService => ({
            localKey: `svc-${s.id ?? Math.random()}`,
            id: s.id,
            service_id: s.service_id,
            service_variant_id: s.service_variant_id ?? null,
            label: s.label,
            duration_minutes: Number(s.duration_minutes ?? 0),
            extra_minutes: Number(s.extra_minutes ?? 0),
            price: Number(s.price ?? 0),
            discount_type: s.discount_type ?? 'none',
            discount_value: Number(s.discount_value ?? 0),
            final_price: Number(s.final_price ?? 0),
            color_code: s.color_code ?? null,
            staff_id: s.staff_id ?? null,
          }),
        );

        if (this.editForm.branch_id != null) {
          await this.fetchEmployees(this.editForm.branch_id as number);
        }
      } catch (e) {
        console.error('Failed to load booking for waitlist edit', e);
      } finally {
        this.editLoading = false;
      }
    },

    closeEdit() {
      this.mode = 'list';
      this.editBookingId = null;
    },

    removeService(index: number) {
      this.editForm.services.splice(index, 1);
    },

    openServiceSelector() {
      this.mode = 'services';
      this.serviceSearch = '';
      this.selectedServiceIds = [];
    },

    closeServiceSelector() {
      this.mode = 'edit';
    },

    addSelectedServices() {
      if (this.selectedServiceIds.length === 0) return;

      const now = Date.now();
      this.selectedServiceIds.forEach((id, idx) => {
        const svcDef = this.serviceList.find((s) => s.id === id);
        if (!svcDef) return;

        this.editForm.services.push({
          localKey: `new-${id}-${now}-${idx}`,
          id: null,
          service_id: svcDef.id,
          service_variant_id: null,
          label: svcDef.name,
          duration_minutes: svcDef.duration_minutes || 30,
          extra_minutes: 0,
          price: svcDef.price || 0,
          discount_type: 'none',
          discount_value: 0,
          final_price: svcDef.price || 0,
          color_code: '#0ea5e9',
          staff_id: null,
        });
      });

      this.closeServiceSelector();
    },

    async saveEdit() {
      if (!this.editBookingId || !this.isEditValid) return;

      this.savingEdit = true;
      try {
        const payload = {
          branch_id: this.editForm.branch_id,
          date: this.editForm.date,
          time: this.editForm.time,
          note: this.editForm.note,
          services: this.editForm.services.map((s) => ({
            id: s.id,
            service_id: s.service_id,
            service_variant_id: s.service_variant_id,
            label: s.label,
            duration_minutes: s.duration_minutes,
            extra_minutes: s.extra_minutes,
            price: s.price,
            discount_type: s.discount_type,
            discount_value: s.discount_value,
            final_price: s.final_price,
            color_code: s.color_code,
            staff_id: s.staff_id,
          })),
        };

        await axios.put(`/bookings/${this.editBookingId}/waitlist`, payload);

        await this.fetchPending();
        this.mode = 'list';
        this.editBookingId = null;
      } catch (e) {
        console.error('Failed to save waitlist edit', e);
      } finally {
        this.savingEdit = false;
      }
    },

    // reject / close booking
    openRejectDialog(booking: PendingBooking) {
      this.rejectBooking = booking;
      this.rejectDialogShow = true;
      this.rejectLoading = false;
    },

    closeRejectDialog() {
      if (this.rejectLoading) return;
      this.rejectDialogShow = false;
      this.rejectBooking = null;
    },

    async confirmReject() {
      if (!this.rejectBooking?.id) return;
      this.rejectLoading = true;
      try {
        await axios.post(`/bookings/${this.rejectBooking.id}/status`, {
          status: 'rejected',
        });

        // remove from list
        this.items = this.items.filter(
          (b) => b.id !== this.rejectBooking?.id,
        );

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const upcomingCount = this.items.filter((b) => {
          const d = this.parseDate(b);
          return d >= today;
        }).length;
        this.$emit('count-changed', upcomingCount);

        this.rejectDialogShow = false;
        this.rejectBooking = null;
      } catch (e) {
        console.error('Failed to reject booking', e);
      } finally {
        this.rejectLoading = false;
      }
    },
  },
});
</script>

<style scoped>
/* Overlay fade (slower + smoother) */
.waitlist-overlay-enter-active,
.waitlist-overlay-leave-active {
  transition: opacity 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.waitlist-overlay-enter-from,
.waitlist-overlay-leave-to {
  opacity: 0;
}

/* Slide for panels (smoother, not blinding) */
.waitlist-slide-enter-active,
.waitlist-slide-leave-active {
  transition:
    opacity 0.32s cubic-bezier(0.22, 0.61, 0.36, 1),
    transform 0.32s cubic-bezier(0.22, 0.61, 0.36, 1);
}
.waitlist-slide-enter-from,
.waitlist-slide-leave-to {
  opacity: 0;
  transform: translateX(24px);
}

/* Pop transition (kept for future use) */
.waitlist-pop-enter-active,
.waitlist-pop-leave-active {
  transition:
    opacity 0.18s ease-out,
    transform 0.18s ease-out;
}
.waitlist-pop-enter-from,
.waitlist-pop-leave-to {
  opacity: 0;
  transform: translateY(6px) scale(0.98);
}

/* Fade for reject modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Shimmer skeleton */
.skeleton {
  position: relative;
  overflow: hidden;
  background-color: #e5e7eb; /* gray-200 */
}
.skeleton-soft {
  background-color: #f3f4f6; /* gray-100 */
}
.skeleton::after {
  content: '';
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.7) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  animation: skeleton-shimmer 1.4s infinite;
}

@keyframes skeleton-shimmer {
  100% {
    transform: translateX(100%);
  }
}

.animate-pulse-soft {
  animation: pulse-soft 1.4s ease-in-out infinite;
}
@keyframes pulse-soft {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Custom padding utility */
button.py-2\.75 {
  padding-top: 0.7rem;
  padding-bottom: 0.7rem;
}
</style>
