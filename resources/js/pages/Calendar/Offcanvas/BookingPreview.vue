<template>
    <transition name="fade">
        <div
            v-if="show"
            class="pointer-events-none fixed inset-0 z-[100] flex"
        >
            <!-- Backdrop -->
            <div
                class="hidden flex-1 cursor-pointer bg-neutral-900/40 md:block"
                @click="$emit('close')"
            ></div>

            <!-- Panel -->
            <aside
                class="pointer-events-auto relative flex h-full w-full flex-col bg-white shadow-2xl md:h-full md:max-h-none md:max-w-4xl md:ml-auto md:rounded-l-2xl"
            >
                <!-- Close -->

                <button
                    type="button"
                    class="absolute right-4 top-4 inline-flex size-9 z-10 cursor-pointer items-center justify-center rounded-full border border-neutral-300 bg-white/80 text-neutral-500 shadow-sm backdrop-blur hover:bg-neutral-100 hover:text-neutral-800"
                    @click="$emit('close')"
                >
                    <span class="sr-only">Close</span>
                    ✕
                </button>

                <!-- BODY -->
                <main class="flex min-h-0 flex-1 flex-col md:flex-row">
                    <!-- LOADING (SKELETON) -->
                    <template v-if="loading">
                                              <!-- LEFT SIDE – CLIENT / CLIENT LIST -->
                        <aside
                            class="flex w-full flex-col bg-neutral-50 md:w-72 md:border-r"
                        >
                            <!-- MODE: CLIENT LIST (same idea as SelectService) -->
                            <div
                                v-if="showClientList"
                                class="flex h-full flex-col bg-white md:bg-neutral-50"
                            >
                                <!-- Header -->
                                <div
                                    class="flex items-center justify-between border-b px-4 py-3 sm:px-5 sm:py-4"
                                >
                                    <div>
                                        <h2
                                            class="text-base font-semibold text-neutral-900 sm:text-lg"
                                        >
                                            Change client
                                        </h2>
                                        <p
                                            class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                                        >
                                            Select an existing client or switch
                                            to a walk-in booking.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="cursor-pointer text-xs font-medium text-orange-600 hover:text-orange-700 sm:text-sm"
                                        @click="showClientList = false"
                                    >
                                        Done
                                    </button>
                                </div>

                                <!-- Search + actions -->
                                <div
                                    class="border-b px-4 pt-4 pb-3 sm:px-5"
                                >
                                    <div class="relative">
                                        <input
                                            v-model="clientSearch"
                                            type="text"
                                            class="w-full rounded-full border border-orange-200 bg-white px-3 py-2 text-xs placeholder:text-neutral-400 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:outline-none sm:text-sm"
                                            placeholder="Search name, email, or phone"
                                        />
                                    </div>

                                    <div
                                        class="mt-3 flex flex-wrap gap-2 text-xs sm:text-sm"
                                    >
                                       <button
  type="button"
  class="inline-flex cursor-pointer items-center justify-center rounded-full border border-orange-500 bg-orange-500 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-orange-600 sm:text-xs"
  @click="createClient"
  v-if="!isPaymentPending"
>
  + New client
</button>


                                        <button
                                            type="button"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-full border border-neutral-300 px-3 py-1.5 text-[11px] font-medium text-neutral-800 hover:bg-neutral-50 sm:text-xs"
                                            :class="{
                                                'bg-neutral-900 text-white border-neutral-900':
                                                    !client,
                                            }"
                                            @click="setWalkIn"
                                        >
                                            Walk-in (no client)
                                        </button>
                                    </div>
                                </div>

                                <!-- Client list -->
                                <div
                                    class="flex-1 overflow-y-auto px-2 py-4"
                                >
                                    <div
                                        v-for="c in filteredClients"
                                        :key="c.id"
                                        class="mb-1"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-4 text-left text-xs hover:bg-neutral-100 sm:text-sm"
                                            :class="{
                                                'bg-neutral-900 text-white hover:bg-neutral-900':
                                                    client && client.id === c.id,
                                            }"
                                            @click="selectClientRow(c)"
                                        >
                                            <div
                                                class="flex size-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-semibold text-neutral-700 sm:text-sm"
                                            >
                                                {{
                                                    initials(
                                                        c.name ||
                                                            c.full_name ||
                                                            '',
                                                    )
                                                }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="truncate text-xs font-medium sm:text-sm"
                                                >
                                                    {{ c.name || c.full_name }}
                                                </div>
                                                <div
                                                    class="truncate text-[11px] text-neutral-500 sm:text-xs"
                                                >
                                                    <span v-if="c.email">
                                                        {{ c.email }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            c.email && c.phone
                                                        "
                                                    >
                                                        ·
                                                    </span>
                                                    <span v-if="c.phone">
                                                        {{ c.phone }}
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </div>

                                    <p
                                        v-if="!filteredClients.length"
                                        class="px-3 py-4 text-center text-xs text-neutral-400 sm:text-sm"
                                    >
                                        No clients found.
                                    </p>
                                </div>
                            </div>

                            <!-- MODE: NORMAL (current client / walk-in) -->
                            <template v-else>
                                <!-- Client present -->
                                <div
                                    v-if="client"
                                    class="flex h-full flex-col bg-white md:bg-neutral-50"
                                >
                                    <div
                                        class="flex flex-col items-center gap-3 border-b px-6 py-6 text-center"
                                    >
                                        <!-- Avatar circle -->
<div v-if="client && client.avatar_url"
     class="grid size-12 place-items-center rounded-full bg-neutral-100 md:size-16 overflow-hidden">
    <img
        :src="client.avatar_url"
        :alt="client.name || client.full_name"
        class="h-full w-full object-cover"
    />
</div>
<div v-else
     class="grid size-12 place-items-center rounded-full bg-neutral-100 text-lg font-semibold text-neutral-500 md:size-16 md:text-2xl">
    {{ initials(client?.name || client?.full_name) }}
</div>


                                        <div>
                                            <div
                                                class="text-base font-semibold text-neutral-900 sm:text-lg"
                                            >
                                                {{
                                                    client.name ||
                                                    client.full_name
                                                }}
                                            </div>
                                            <div
                                                v-if="client.email"
                                                class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                                            >
                                                {{ client.email }}
                                            </div>
                                            <div
                                                v-if="client.phone"
                                                class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                                            >
                                                {{ client.phone }}
                                            </div>
                                        </div>

                                        <div
                                            v-if="noShowLabel"
                                            class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-[11px] font-medium text-rose-700 sm:text-xs"
                                        >
                                            {{ noShowLabel }}
                                        </div>

                                        <button
                                            type="button"
                                            class="mt-1 cursor-pointer text-[11px] font-medium text-orange-600 hover:text-orange-700 sm:text-xs"
                                        >
                                            View profile
                                        </button>

                                        <!-- client actions -->
                                        <div
                                            class="mt-3 flex flex-wrap justify-center gap-2"
                                        >
                                           <button
  type="button"
  class="inline-flex cursor-pointer items-center justify-center rounded-full border border-neutral-300 px-3 py-1.5 text-[11px] font-medium text-neutral-800 hover:bg-neutral-50 sm:text-xs"
  @click="openClientSelector"
  :disabled="isPaymentPending"
  :class="{ 'opacity-40 cursor-not-allowed': isPaymentPending }"
>
  Change client
</button>


                                            <button
                                                type="button"
                                                class="inline-flex cursor-pointer items-center justify-center rounded-full border border-orange-500 bg-orange-500 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-orange-600 sm:text-xs"
                                                @click="createClient"
                                            >
                                                + New client
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- No client -->
                                <div
                                    v-else
                                    class="flex flex-1 flex-col items-center justify-center px-6 py-6 text-center text-sm text-neutral-500"
                                >
                                    <i class="bx bx-user text-2xl"></i>
                                    <p class="font-medium text-neutral-900">
                                        No client added
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-neutral-500"
                                    >
                                        This booking is a walk-in, or the
                                        client profile is missing.
                                    </p>

  <button
  v-if="!isPaymentPending"
  type="button"
  class="mt-4 inline-flex cursor-pointer items-center justify-center rounded-full bg-orange-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-orange-600"
  @click="openClientSelector"
>
  + Add client
</button>


                                </div>
                            </template>
                        </aside>

                        <section
                            class="flex min-w-0 flex-1 flex-col overflow-hidden"
                        >
                            <div
                                class="h-28 bg-[#216CFF]/10 px-6 py-4 animate-pulse"
                            >
                                <div
                                    class="h-5 w-40 rounded bg-neutral-200"
                                ></div>
                                <div
                                    class="mt-3 h-4 w-64 rounded bg-neutral-200"
                                ></div>
                            </div>

                            <div
                                class="flex-1 space-y-4 overflow-y-auto bg-neutral-50 px-6 py-4"
                            >
                                <div
                                    class="h-32 w-full rounded-2xl bg-neutral-100"
                                ></div>
                                <div
                                    class="h-32 w-full rounded-2xl bg-neutral-100"
                                ></div>
                                <div
                                    class="h-24 w-full rounded-2xl bg-neutral-100"
                                ></div>
                            </div>

                            <footer
                                class="border-t bg-white px-6 py-4 sm:px-8"
                            >
                                <div
                                    class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="space-y-0.5">
                                        <div
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-400"
                                        >
                                            Cart total
                                        </div>
                                        <div
                                            class="h-5 w-24 rounded bg-neutral-100"
                                        ></div>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 sm:min-w-[220px]"
                                    >
                                        <div
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-neutral-200 bg-neutral-50"
                                        ></div>
                                        <div
                                            class="h-10 flex-1 rounded-full bg-neutral-900/10"
                                        ></div>
                                    </div>
                                </div>
                            </footer>
                        </section>
                    </template>

                    <!-- CONTENT -->
                    <template v-else>

                                <!-- MOBILE: Booking Meta -->
        <div class="md:hidden relative px-4 py-4 text-white" :class="headerBgClass">

                <div class="flex items-start justify-between gap-3 mb-0">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">
                            {{ bookingNumberLabel }}
                        </div>
                        <div class="mt-1 text-xl font-semibold">
                            {{ dateLabel || 'Booking' }}
                        </div>
                        <div
                            v-if="timeRangeLabel"
                            class="mt-1 text-sm text-white/80 tabular-nums"
                        >
                            {{ timeRangeLabel }}
                            <span>
                                {{
                                    (booking as any)
                                        ?.repeat_label ||
                                    "Doesn't repeat"
                                }}
                            </span>
                        </div>

                                                                                                        <div
                                            class="space-y-0.5 text-[11px] text-white/80"
                                        >
                                            <div v-if="booking?.created_at">
                                                Created
                                                {{
                                                    fmtDateTime(
                                                        booking.created_at,
                                                    )
                                                }}
                                            </div>
                                            <div v-if="booking?.updated_at">
                                                Updated
                                                {{
                                                    fmtDateTime(
                                                        booking.updated_at,
                                                    )
                                                }}
                                            </div>
                                        </div>
                    </div>
                    </div>

                    <!-- Status chip for mobile -->
                        <div v-if="statusChip" class="absolute top-4 right-14">
                            <div class="relative">
                          <button
  type="button"
  class="inline-flex items-center rounded-full bg-white/10 px-3 py-3 text-xs font-medium capitalize text-white shadow-sm ring-1 ring-white/40 cursor-pointer mr-4"
  @click.stop="toggleStatusMenu"
  :disabled="statusLocked || !canStatusChange"
  :class="{
    'opacity-60 cursor-not-allowed': statusLocked || !canStatusChange
  }"
>

                                <span class="mr-2 inline-flex items-center gap-0.5">
                                    <component
                                        v-if="currentStatusOption"
                                        :is="currentStatusOption.icon"
                                        class="h-3 w-3"
                                    />
                                    <span>{{ statusChip }}</span>
                                </span>
                                <span
                                    :class="{
                                        'bg-amber-200': ['scheduled', 'booked', 'payment_pending'].includes(currentStatus),
                                        'bg-emerald-200': ['arrived', 'started', 'completed'].includes(currentStatus),
                                        'bg-neutral-200': ['no_show', 'noshow'].includes(currentStatus),
                                        'bg-rose-200': ['cancel', 'cancelled', 'canceled'].includes(currentStatus),
                                    }"
                                ></span>
                                <span class="ml-0.5 text-[10px] opacity-80">
                                    ▾
                                </span>
                            </button>


                                                                        <!-- Dropdown -->
                                            <transition name="slide-down">
                                                <div
                                                    v-if="statusMenuOpen && canStatusChange"
                                                    class="absolute right-0 mt-2 w-44 rounded-2xl border border-neutral-200 bg-white py-1 text-[11px] text-neutral-800 shadow-xl z-20"
                                                    @click.stop
                                                >
                                                    <button
                                                        v-for="opt in statusOptions"
                                                        :key="opt.value"
                                                        type="button"
                                                        class="flex w-full items-center justify-between px-3 py-2 hover:bg-neutral-50 cursor-pointer"
                                                        :class="{
                                                            'text-rose-600': opt.destructive,
                                                        }"
                                                        @click="changeStatus(opt.value)"
                                                    >
                                                        <span
                                                            class="flex items-center gap-2"
                                                        >
                                                           <component
    :is="opt.icon"
    class="h-3.5 w-3.5"
/>
<span>{{ opt.label }}</span>

                                                        </span>

                                                        <span
                                                            v-if="
                                                                currentStatus ===
                                                                opt.value
                                                            "
                                                            class="text-[10px] text-emerald-500"
                                                        >
                                                            ✓
                                                        </span>
                                                    </button>
                                                </div>
                                            </transition>
                        </div>
                        </div>
                    </div>

                        <!-- LEFT SIDE – CLIENT -->
                        <aside
                            class="flex w-full flex-col bg-neutral-50 md:w-72 md:border-r"
                        >
                            <!-- Client present -->
                            <div
                                v-if="client"
                                class="flex h-full flex-col bg-white md:bg-neutral-50"
                            >
                                <div
                                    class="flex flex-col items-center gap-3 border-b px-6 py-6 text-center"
                                >
                                    <div v-if="client && client.avatar_url"
     class="grid size-12 place-items-center rounded-full bg-neutral-100 md:size-16 overflow-hidden">
    <img
        :src="client.avatar_url"
        :alt="client.name || client.full_name"
        class="h-full w-full object-cover"
    />
</div>
<div v-else
     class="grid size-12 place-items-center rounded-full bg-neutral-100 text-lg font-semibold text-neutral-500 md:size-16 md:text-2xl">
    {{ initials(client.name || client.full_name) }}
</div>


                                    <div>
                                        <div
                                            class="text-base font-semibold text-neutral-900 sm:text-lg"
                                        >
                                            {{ client.name || client.full_name }}
                                        </div>
                                        <div
                                            v-if="client.email"
                                            class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                                        >
                                            {{ client.email }}
                                        </div>
                                        <div
                                            v-if="client.phone"
                                            class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                                        >
                                            {{ client.phone }}
                                        </div>
                                    </div>

                                    <div
                                        v-if="noShowLabel"
                                        class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-[11px] font-medium text-rose-700 sm:text-xs"
                                    >
                                        {{ noShowLabel }}
                                    </div>

                                  <!-- <button
    type="button"
    class="mt-1 cursor-pointer text-[11px] font-medium text-orange-600 hover:text-orange-700 sm:text-xs"
>
    View profile
</button> -->

<!-- NEW: client actions -->
<div class="mt-3 flex flex-wrap justify-center gap-2">
   <button
  type="button"
  class="inline-flex cursor-pointer items-center justify-center rounded-full border border-neutral-300 px-3 py-1.5 text-[11px] font-medium text-neutral-800 hover:bg-neutral-50 sm:text-xs"
  @click="openClientSelector"
  :disabled="isPaymentPending"
  :class="{ 'opacity-40 cursor-not-allowed': isPaymentPending }"
>
  Change client
</button>

<button
  v-if="!isPaymentPending"
  type="button"
  class="inline-flex cursor-pointer items-center justify-center rounded-full border border-orange-500 bg-orange-500 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-orange-600 sm:text-xs"
  @click="createClient"
>
  + New client
</button>



</div>

                                </div>

                                <!-- <div
                                    class="flex items-center justify-center gap-2 border-b px-4 py-3"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex cursor-pointer items-center justify-center gap-1 rounded-full border border-neutral-300 px-3 py-1.5 text-xs font-medium text-neutral-800 hover:bg-neutral-50 sm:text-sm"
                                    >
                                        Actions
                                        <span class="text-[10px]">▾</span>
                                    </button>
                                </div> -->

                                <!-- <div class="flex-1 overflow-y-auto px-6 py-4">
                                    <div
                                        class="space-y-3 text-xs text-neutral-700 sm:text-sm"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full cursor-pointer items-center justify-between rounded-lg px-0.5 py-1.5 text-left hover:bg-neutral-50"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                            <i class="bx bx-male-female text-lg leading-none text-neutral-500"></i>
                                                <span>Add pronouns</span>
                                            </div>
                                            <span
                                                class="text-[11px] text-neutral-400"
                                                >Optional</span
                                            >
                                        </button>

                                        <button
                                            type="button"
                                            class="flex w-full cursor-pointer items-center justify-between rounded-lg px-0.5 py-1.5 text-left hover:bg-neutral-50"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                            <i class="bx bx-cake text-lg leading-none text-neutral-500"></i>
                                                <span>Add date of birth</span>
                                            </div>
                                            <span
                                                class="text-[11px] text-neutral-400"
                                                >Optional</span
                                            >
                                        </button>

                                        <div
                                            class="mt-2 flex items-center gap-2 text-[11px] text-neutral-500 sm:text-xs"
                                        >
                                        <i class="bx bx-user text-lg leading-none text-neutral-500"></i>

                                            <span>
                                                Created
                                                <span
                                                    v-if="createdLabel"
                                                    class="font-medium"
                                                    >{{ createdLabel }}</span
                                                >
                                                <span v-else
                                                    >client profile</span
                                                >
                                            </span>
                                        </div>
                                    </div>
                                </div> -->
                            </div>

                          <!-- No client -->
<div
    v-else
    class="flex flex-1 flex-col items-center justify-center px-6 py-6 text-center text-sm text-neutral-500"
>
    <i class="bx bx-user text-2xl"></i>
    <p class="font-medium text-neutral-900">
        No client added
    </p>
    <p class="mt-1 text-xs text-neutral-500">
        This booking is a walk-in, or the client
        profile is missing.
    </p>

    <!-- NEW: call the same client selector you use in "Select service" canvas -->
   <button
  v-if="!isPaymentPending"
  type="button"
  class="mt-4 inline-flex cursor-pointer items-center justify-center rounded-full bg-orange-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-orange-600"
  @click="openClientSelector"
>
  + Add client
</button>

</div>

                        </aside>

                        <section
                            class="flex min-w-0 flex-1 flex-col overflow-hidden md:flex"
                        >
                            <!-- Booking meta  -->
                            <div
                                class="hidden md:block px-4 py-3 text-white sm:px-6 sm:py-4 md:px-6 md:py-5"
                                :class="headerBgClass"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <div
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70"
                                        >
                                            {{ bookingNumberLabel }}
                                        </div>
                                        <div
                                            class="mt-1 text-lg font-semibold sm:text-xl md:text-3xl"
                                        >
                                            {{ dateLabel || 'Booking' }}
                                        </div>
                                        <div
                                            v-if="timeRangeLabel"
                                            class="mt-1 text-sm text-white/80 tabular-nums sm:text-base"
                                        >
                                            {{ timeRangeLabel }}
                                            <span class="mx-1">•</span>
                                            <span>
                                                {{
                                                    (booking as any)
                                                        ?.repeat_label ||
                                                    "Doesn't repeat"
                                                }}
                                            </span>
                                        </div>

                                                                                <div
                                            class="space-y-0.5 text-[11px] text-white/80"
                                        >
                                            <div v-if="booking?.created_at">
                                                Created
                                                {{
                                                    fmtDateTime(
                                                        booking.created_at,
                                                    )
                                                }}
                                            </div>
                                            <div v-if="booking?.updated_at">
                                                Updated
                                                {{
                                                    fmtDateTime(
                                                        booking.updated_at,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="flex flex-col items-end gap-2 text-right"
                                    >
                                        <!-- Status chip + dropdown -->
                                        <div
                                            v-if="statusChip"
                                            class="absolute top-18 right-0"
                                        >
                                        <div class="relative">
                                           <button
  type="button"
  class="inline-flex items-center rounded-full bg-white/10 px-3 py-3 text-xs font-medium capitalize text-white shadow-sm ring-1 ring-white/40 cursor-pointer mr-4"
  @click.stop="toggleStatusMenu"
  :disabled="statusLocked || !canStatusChange"
  :class="{
    'opacity-60 cursor-not-allowed': statusLocked || !canStatusChange
  }"
>

                                                <span
                                                    class="mr-2 inline-flex items-center gap-1"
                                                >
                                                   <span class="mr-2 inline-flex items-center gap-1">
    <component
        v-if="currentStatusOption"
        :is="currentStatusOption.icon"
        class="h-3.5 w-3.5"
    />
    <span>{{ statusChip }}</span>
</span>

                                                </span>

                                                <!-- <span
                                                    class="mr-1 inline-block h-1.5 w-1.5 rounded-full "
                                                    :class="{
                                                        'bg-amber-200': ['scheduled', 'booked', 'payment_pending'].includes(currentStatus),
                                                        'bg-emerald-200': ['arrived', 'started', 'completed'].includes(currentStatus),
                                                        'bg-neutral-200': ['no_show', 'noshow'].includes(currentStatus),
                                                        'bg-rose-200': ['cancel', 'cancelled', 'canceled'].includes(currentStatus),
                                                    }"
                                                ></span> -->

                                                <span
                                                    class="ml-0 text-[14px] opacity-100"
                                                >
                                                    ▾
                                                </span>
                                            </button>

                                            <!-- Dropdown -->
                                            <transition name="slide-down">
                                               <div
  v-if="statusMenuOpen && canStatusChange"
  class="absolute right-4 mt-2 w-48 rounded-2xl border border-neutral-200 bg-white py-1 text-s text-neutral-800 shadow-xl z-20"
  @click.stop
>

                                                    <button
                                                        v-for="opt in statusOptions"
                                                        :key="opt.value"
                                                        type="button"
                                                        class="flex w-full items-center justify-between px-4 py-3 hover:bg-neutral-50 cursor-pointer"
                                                        :class="{
                                                            'text-rose-600': opt.destructive,
                                                        }"
                                                        @click="changeStatus(opt.value)"
                                                    >
                                                        <span
                                                            class="flex items-center gap-2"
                                                        >
                                                           <component
    :is="opt.icon"
    class="h-4 w-4"
/>
<span>{{ opt.label }}</span>

                                                        </span>

                                                        <span
                                                            v-if="
                                                                currentStatus ===
                                                                opt.value
                                                            "
                                                            class="text-[14px] text-emerald-500"
                                                        >
                                                            ✓
                                                        </span>
                                                    </button>
                                                </div>
                                            </transition>
                                        </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sections -->
                            <div
                                class="flex-1 space-y-4 overflow-y-auto bg-neutral-50 px-4 py-3 sm:px-6 sm:py-4 md:px-8 md:py-5"
                            >
                                <!-- Services -->
                                <section
                                    v-if="services && services.length"
                                    class="rounded-xl border border-neutral-100 bg-white px-3 py-3 shadow-sm md:rounded-2xl md:px-4 md:py-4"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <div
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500"
                                        >
                                            Services
                                        </div>
                                        <div
                                            class="text-xs text-neutral-400 tabular-nums"
                                        >
                                            {{ services.length }} item<span
                                                v-if="services.length !== 1"
                                                >s</span
                                            >
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div
                                            v-for="svc in services"
                                            :key="svc.id || svc.uid"
                                            class="flex items-start justify-between gap-4 rounded-2xl bg-neutral-50 px-3 py-3 text-sm ring-1 ring-transparent transition hover:bg-neutral-100 hover:ring-neutral-200 sm:px-4"
                                        >
                                            <div class="flex min-w-0 flex-1">
                                                <div
                                                    class="my-1 mr-3 w-[3px] self-stretch rounded-full"
                                                    :style="{
                                                        backgroundColor:
                                                            serviceColor(svc),
                                                    }"
                                                ></div>

                                                <div class="min-w-0">
                                                    <div
                                                        class="truncate text-sm font-medium text-neutral-900 sm:text-base"
                                                    >
                                                        {{
                                                            svc.label ||
                                                            svc.name
                                                        }}
                                                    </div>

                                                    <div
                                                        class="mt-0.5 text-xs text-neutral-500 tabular-nums sm:text-sm"
                                                    >
                                                        <span
                                                            v-if="
                                                                serviceTimeRangeLabel(
                                                                    svc,
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                serviceTimeRangeLabel(
                                                                    svc,
                                                                )
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="
                                                                serviceTimeRangeLabel(
                                                                    svc,
                                                                ) &&
                                                                serviceDurationLabel(
                                                                    svc,
                                                                )
                                                            "
                                                        >
                                                            ·
                                                        </span>
                                                        <span
                                                            v-if="
                                                                serviceDurationLabel(
                                                                    svc,
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                serviceDurationLabel(
                                                                    svc,
                                                                )
                                                            }}
                                                        </span>
                                                        <span
                                                            v-if="
                                                                (serviceTimeRangeLabel(
                                                                    svc,
                                                                ) ||
                                                                    serviceDurationLabel(
                                                                        svc,
                                                                    )) &&
                                                                svc.staff_name
                                                            "
                                                        >
                                                            ·
                                                        </span>
                                                        <span
                                                            v-if="svc.staff_name"
                                                        >
                                                            {{
                                                                svc.staff_name
                                                            }}
                                                        </span>
                                                    </div>

                                                    <div
                                                        v-if="svc.warning"
                                                        class="mt-1 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 sm:text-xs"
                                                    >
                                                        {{ svc.warning }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col items-end gap-1"
                                            >
                                                <div
                                                    class="text-right text-sm font-semibold text-neutral-900 tabular-nums sm:text-base"
                                                >
                                                    {{ currencySymbol }}
                                                    {{
                                                        formatNumber(
                                                            svc.final_price ??
                                                                svc.price ??
                                                                0,
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    v-if="svc.discount_value"
                                                    class="text-[11px] text-emerald-600"
                                                >
                                                    –{{ svc.discount_value }}
                                                    <span
                                                        v-if="
                                                            svc.discount_type ===
                                                            'percent'
                                                        "
                                                        >%</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Forms (optional) -->
                                <section
                                    v-if="forms && forms.length"
                                    class="rounded-2xl border border-neutral-100 bg-white px-4 py-4 shadow-sm"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <div
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500"
                                        >
                                            Forms
                                        </div>
                                        <div
                                            class="text-xs text-neutral-400 tabular-nums"
                                        >
                                            {{ forms.length }} form<span
                                                v-if="forms.length !== 1"
                                                >s</span
                                            >
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-xs sm:text-sm">
                                        <div
                                            v-for="f in forms"
                                            :key="f.id"
                                            class="flex items-center justify-between rounded-xl bg-neutral-50 px-3 py-2"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span>📄</span>
                                                <div>
                                                    <div
                                                        class="font-medium text-neutral-900"
                                                    >
                                                        {{
                                                            f.title || 'Form'
                                                        }}
                                                    </div>
                                                    <div
                                                        class="text-[11px] text-neutral-500"
                                                    >
                                                        {{
                                                            f.status_label ||
                                                            'To be completed'
                                                        }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="text-[11px] text-neutral-500 tabular-nums"
                                            >
                                                {{ f.date_label }}
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Payment summary -->
                                <section
                                    v-if="summary"
                                    class="space-y-2 rounded-2xl border border-neutral-100 bg-white px-4 py-4 shadow-sm"
                                >
                                    <div
                                        class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500"
                                    >
                                        Payment summary
                                    </div>

                                    <div
                                        class="flex justify-between text-sm text-neutral-500"
                                    >
                                        <span>Total price</span>
                                        <span class="tabular-nums">
                                            {{ currencySymbol }}
                                            {{
                                                formatNumber(
                                                    summary.total_price ??
                                                        bookingTotal ??
                                                        0,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex justify-between text-sm text-neutral-500"
                                    >
                                        <span>Total paid</span>
                                        <span class="tabular-nums">
                                            {{ currencySymbol }}
                                            {{
                                                formatNumber(
                                                    summary.total_paid ?? 0,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-2 flex justify-between text-base font-semibold text-neutral-900"
                                    >
                                        <span>Remaining</span>
                                        <span class="tabular-nums">
                                            {{ currencySymbol }}
                                            {{
                                                formatNumber(
                                                    summary.remaining ?? 0,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </section>

                                <!-- Sales history -->
                                <section
                                    v-if="sales && sales.length"
                                    class="rounded-2xl border border-neutral-100 bg-white px-4 py-4 shadow-sm"
                                >
                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500"
                                    >
                                        Sales history
                                    </div>
                                    <div class="space-y-2 text-xs sm:text-sm">
                                        <div
                                            v-for="s in sales"
                                            :key="s.id"
                                            class="space-y-1 rounded-xl bg-neutral-50 px-3 py-2"
                                        >
                                            <div
                                                class="flex justify-between text-[11px] text-neutral-500"
                                            >
                                                <span>
                                                    {{
                                                        paymentLabelFor(
                                                            s.payment_method,
                                                        )
                                                    }}
                                                </span>
                                                <span class="tabular-nums">
                                                    {{
                                                        fmtDateTime(
                                                            s.created_at,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between text-sm font-medium text-neutral-900"
                                            >
                                                <span>Total with tip</span>
                                                <span class="tabular-nums">
                                                    {{ currencySymbol }}
                                                    {{
                                                        formatNumber(
                                                            s.total_with_tip ??
                                                                s.total ??
                                                                0,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between text-[11px] text-neutral-500 tabular-nums"
                                            >
                                                <span>Paid</span>
                                                <span>
                                                    {{ currencySymbol }}
                                                    {{
                                                        formatNumber(
                                                            s.total_paid ?? 0,
                                                        )
                                                    }}
                                                    · Remaining:
                                                    {{ currencySymbol }}
                                                    {{
                                                        formatNumber(
                                                            s.remaining ?? 0,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section
                                    v-if="!booking && !loading"
                                    class="text-sm text-neutral-500"
                                >
                                    Booking details not available.
                                </section>


<!-- Notes -->
<section
  class="rounded-2xl border border-neutral-100 bg-white px-4 py-4 shadow-sm"
>
  <div class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500">
    Notes
  </div>

  <button
    type="button"
    class="w-full rounded-2xl bg-indigo-50 px-4 py-4 text-left text-sm text-neutral-900
           hover:bg-indigo-100/70"
    @click="openNoteModal"
  >
    <span v-if="bookingNotes" class="whitespace-pre-wrap break-words">
      {{ bookingNotes }}
    </span>
    <span v-else class="text-neutral-400">
      Add a note…
    </span>
  </button>
</section>
<!-- EDIT NOTE MODAL -->
<transition name="modal-fade" appear>
  <div
    v-if="showNoteModal"
    class="fixed inset-0 z-[220] flex items-center justify-center"
  >
    <!-- Backdrop -->
    <div
      class="absolute inset-0 bg-black/40 cursor-pointer"
      @click="closeNoteModal"
    ></div>

    <!-- Panel -->
    <div
      class="modal-panel relative w-[92vw] max-w-4xl rounded-2xl bg-white shadow-2xl overflow-hidden"
      @click.stop
    >
      <!-- Close -->
      <button
        type="button"
        class="absolute right-4 top-4 inline-flex size-9 items-center justify-center rounded-full
               border border-neutral-300 bg-white/80 text-neutral-500 shadow-sm backdrop-blur
               hover:bg-neutral-100 hover:text-neutral-800 cursor-pointer"
        @click="closeNoteModal"
      >
        <span class="sr-only">Close</span>
        ✕
      </button>

      <div class="p-6">
        <h2 class="text-xl font-semibold text-neutral-900">Edit note</h2>

        <textarea
          v-model="noteDraft"
          class="mt-4 w-full min-h-[160px] rounded-xl border border-neutral-300 px-4 py-3 text-sm
                 text-neutral-900 placeholder:text-neutral-400
                 focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 outline-none"
          placeholder="Type a note…"
        ></textarea>

        <div class="mt-2 text-sm text-neutral-500">
          This note will be visible only to your team members
        </div>

        <div v-if="noteError" class="mt-2 text-sm text-rose-600">
          {{ noteError }}
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            class="inline-flex cursor-pointer items-center justify-center rounded-full border border-neutral-300
                   px-6 py-2.5 text-sm font-semibold text-rose-600 hover:bg-neutral-50
                   disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="isSavingNote || isDeletingNote || !bookingNotes"
            @click="deleteNote"
          >
            <span v-if="isDeletingNote" class="spinner mr-2"></span>
            Delete
          </button>

          <button
            type="button"
            class="inline-flex cursor-pointer items-center justify-center rounded-full bg-neutral-900
                   px-6 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800
                   disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="isSavingNote || isDeletingNote"
            @click="saveNote"
          >
            <span v-if="isSavingNote" class="spinner mr-2"></span>
            Update
          </button>
        </div>
      </div>
    </div>
  </div>
</transition>


                            </div>

                            <!-- FOOTER – Cart total + primary action -->
                            <footer
                                class="border-t bg-white px-4 py-3 sm:px-6 md:px-8 md:py-4"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div class="space-y-0.5">
                                        <div
                                            class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-500"
                                        >
                                            Cart total
                                        </div>
                                        <div
                                            class="tabular-nums text-base font-semibold text-neutral-900"
                                        >
                                            {{ currencySymbol }}
                                            {{ formatNumber(cartTotal) }}
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-2"
                                    >
                                        <!-- <button
                                            type="button"
                                            class="circle-btn"
                                        >
                                            ⋯
                                        </button> -->

                                        <!-- Scheduled: primary checkout  -->
                                       <button
  v-if="isScheduled && canContinueCheckout"
  type="button"
  class="btn-pointer h-11 flex-1 rounded-full border border-neutral-300 bg-white px-5 text-base font-semibold text-neutral-900 hover:bg-neutral-100 disabled:opacity-50"
  @click="openCheckoutTip"
>
  Continue checkout
</button>

                                        <!-- Completed with sales: view sale CTA -->
                                       <button
  v-else-if="isCompleted && hasSales && canContinueCheckout"
  type="button"
  class="inline-flex cursor-pointer items-center justify-center rounded-full border border-neutral-300 px-5 py-2.5 text-sm font-semibold text-neutral-900 hover:bg-neutral-50"
  @click="viewSale"
>
  View sale
</button>


                                 <button
  v-else-if="canContinueCheckout"
  type="button"
  class="btn-pointer h-11 flex-1 rounded-full border border-neutral-300 bg-white px-5 text-base font-semibold text-neutral-900 hover:bg-neutral-100 disabled:opacity-50"
  @click="openCheckoutTip('preview_take_payment')"
  :disabled="isOpeningTip || isUpdatingStatus"
  :class="{ 'opacity-70 cursor-not-allowed': isOpeningTip || isUpdatingStatus }"
>
  <span
    v-if="isOpeningTip"
    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
    aria-hidden="true"
  ></span>
  <span>{{ isOpeningTip ? 'Opening…' : 'Take payment' }}</span>
</button>



                                    </div>
                                </div>
                            </footer>
                        </section>
                    </template>
                 <transition name="modal-fade" appear>
  <div
    v-if="showCancelModal"
    class="fixed inset-0 z-[200] flex items-center justify-center"
  >
    <!-- Backdrop -->
    <div
      class="absolute inset-0 bg-black/40 cursor-pointer"
      @click="closeCancelModal"
    ></div>

    <!-- Panel -->
    <div
      class="modal-panel relative w-[92vw] max-w-4xl rounded-2xl bg-white shadow-2xl"
      @click.stop
    >
      <!-- Close button -->
      <button
        type="button"
        class="absolute right-4 top-4 inline-flex size-9 items-center justify-center rounded-full border border-neutral-300 bg-white/80 text-neutral-500 shadow-sm backdrop-blur hover:bg-neutral-100 hover:text-neutral-800 cursor-pointer"
        @click="closeCancelModal"
      >
        <span class="sr-only">Close</span>
        ✕
      </button>

      <div class="flex flex-col gap-6 p-6 md:flex-row md:items-start">
        <div class="flex-1">
          <h2 class="text-3xl font-semibold text-neutral-900">
            Are you sure you want to cancel?
          </h2>

          <div class="mt-4 rounded-xl bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
            <span class="font-medium">ℹ</span>
            <span class="ml-2">No policy was applied to this appointment</span>
          </div>

<!--  cancel modal -->
<div class="mt-4">
    <label class="block text-sm font-medium text-neutral-800 mb-2">
        Cancellation reason
    </label>

    <div class="relative dropdown-cancel-reason">
        <button 
            type="button" 
            @click="showCancelReasonDropdown = !showCancelReasonDropdown"
            class="flex w-full items-center justify-between rounded-xl border border-neutral-300 bg-white px-3 py-3 text-left text-sm text-neutral-900 hover:bg-neutral-50 cursor-pointer"
            :class="{ 'border-neutral-400 ring-1 ring-neutral-300': showCancelReasonDropdown }"
        >
            <span>{{ cancelReason || 'No reason provided' }}</span>
            <span class="text-neutral-400 transition-transform duration-200" 
                  :class="{ 'rotate-180': showCancelReasonDropdown }">
                ▼
            </span>
        </button>

        <transition name="fade">
            <div 
                v-if="showCancelReasonDropdown"
                class="absolute z-20 mt-1 w-full rounded-xl border border-neutral-300 bg-white shadow-lg max-h-48 overflow-y-auto"
                @click.stop
            >
                <div class="py-1">
                    <button
                        v-for="r in cancelReasons" 
                        :key="r" 
                        type="button"
                        @click="selectCancelReason(r)"
                        class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm text-neutral-700 hover:bg-neutral-50 cursor-pointer"
                        :class="{ 'bg-orange-50 text-orange-700': cancelReason === r }"
                    >
                        <span>{{ r }}</span>
                    </button>
                </div>
            </div>
        </transition>
    </div>

    <p v-if="!cancelReason" class="mt-2 text-xs text-rose-600">
        Cancellation reason is required.
    </p>
</div>
        </div>

        <div class="w-full md:w-[420px] rounded-2xl border border-neutral-200 p-6">
          <div class="text-lg font-semibold text-neutral-900">
            Cancellation details
          </div>

          <div class="mt-4 flex items-center justify-between text-sm text-neutral-700">
            <span>Appointment total</span>
            <span class="font-medium tabular-nums">
              {{ currencySymbol }} {{ formatNumber(cartTotal) }}
            </span>
          </div>

          <div class="mt-3 border-t pt-3 text-sm text-neutral-500">
            No fee will be charged
          </div>

          <button
            type="button"
            class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
            :disabled="!cancelReason || isCancelling"
            @click="confirmCancel()"
          >
            <span v-if="isCancelling" class="spinner mr-2"></span>
            Cancel appointment
          </button>
        </div>
      </div>
    </div>
  </div>
</transition>


                </main>
            </aside>

            <ClientPickerOffcanvas
    :show="showClientPicker"
    :clients="clients"
    :selected-client-id="client?.id || null"
    :is-walk-in="!client"
    @close="showClientPicker = false"
    @select-client="handleClientSelected"
    @set-walk-in="handleWalkInSelected"
    @add-new-client="handleAddNewClientFromPicker"
/>
            <AddClientModal
    :show="showAddClientModal"
     :countries="countries"
    @close="showAddClientModal = false"
    @saved="handleClientSaved"
/>

        </div>

    </transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AddClientModal from './AddClientModal.vue';
import ClientPickerOffcanvas from './ClientPickerOffcanvas.vue';

import {
    CalendarDaysIcon,
    MapPinIcon,
    PlayIcon,
    EyeSlashIcon,
    XMarkIcon,

} from '@heroicons/vue/24/outline';

type StatusOption = {
    label: string;
    value: string;
    icon: any;
    destructive?: boolean;
};

export default defineComponent({
    name: 'BookingOffcanvas',
    components: {
        CalendarDaysIcon,
        MapPinIcon,
        PlayIcon,
        EyeSlashIcon,
        XMarkIcon,
        AddClientModal,
        ClientPickerOffcanvas,
    },
    props: {
        show: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        booking: { type: Object, default: () => null },
        sale: { type: Object, default: () => null },
        sales: { type: Array, default: () => [] },
        summary: { type: Object, default: () => null },
        currencySymbol: { type: String, default: 'LKR' },
    },
  emits: ['close', 'open-client-selector', 'create-client', 'client-updated'],
   data() {
  return {
     showNoteModal: false,
  noteDraft: '' as string,
  noteError: '' as string,
  isSavingNote: false,
  isDeletingNote: false,
        isAutoClosing: false,
    showCancelReasonDropdown: false,
    cancelReasons: [
      'Duplicate appointment',
      'Appointment made by mistake',
      'Client not available',
    ],
    showCancelModal: false,
    cancelReason: '',
    isCancelling: false,

    showAddClientModal: false,
    clientsLocal: [] as any[],
    clientSearch: '',
    showClientList: false,
    statusMenuOpen: false,
    isUpdatingStatus: false,
    showClientPicker: false,
    isOpeningTip: false,
  };
},

    mounted() {
    this.clientsLocal = (this.$page.props.clients || []).slice();
},

    computed: {
         canContinueCheckout(): boolean {
  return !!this.$root?.hasPermission?.('bookingpreview.ContineCheckout');
},
   canStatusChange(): boolean {
  return !!this.$root?.hasPermission?.('bookingpreview.statusChange');
},
//         canContinueCheckout(): boolean {
//   const perms =
//     (this.$page.props?.auth?.permissions ||
//       this.$page.props?.permissions ||
//       this.$page.props?.auth?.abilities ||
//       []) as string[];

//   return perms.includes('bookingpreview.ContineCheckout');
// },

        bookingNotes(): string {
  const b: any = this.booking || {};
  return String(b.notes ?? b.note ?? b.description ?? '').trim();
},

 client(): any | null {
  const b: any = this.booking || {};
  let c: any = b.client ?? null;
  if (!c) return null;

  // 1) avatar directly on the booking's client object
  let avatar =
    c.avatar_url ||
    c.profile_photo_url ||
    c.client_avatar_url ||
    c.client_avatar ||
    c.avatar ||             // 👈 add this
    null;

  // 2) Fallback: pull avatar from global clients list if missing
  if (!avatar && c.id) {
    const allClients: any[] =
      this.clientsLocal && this.clientsLocal.length
        ? this.clientsLocal
        : (this.$page.props.clients || []);

    const fromList =
      allClients.find((x: any) => String(x.id) === String(c.id)) || null;

    if (fromList) {
      avatar =
        fromList.avatar_url ||
        fromList.profile_photo_url ||
        fromList.client_avatar_url ||
        fromList.client_avatar ||
        fromList.avatar ||    // 👈 and this
        null;
    }
  }

  return {
    ...c,
    avatar_url: avatar,
  };
},

isPaymentPending(): boolean {
  return this.currentStatus === 'payment_pending';
},

statusLocked(): boolean {
  return this.isPaymentPending || this.isUpdatingStatus;
},




    successPaymentVisible(): boolean {
        return this.$store?.getters?.successPaymentShow === true;
    },

      countries(): any[] {
        // Adjust the prop name if your Inertia page uses a different key
        return (this.$page.props.countries || []) as any[];
    },

    clients(): any[] {
        if (this.clientsLocal && this.clientsLocal.length) {
            return this.clientsLocal;
        }
        return this.$page?.props?.clients || [];
    },

    filteredClients(): any[] {
        const q = this.clientSearch.trim().toLowerCase();
        if (!q) return this.clients;

        return this.clients.filter((c: any) => {
            const name = (c.name || c.full_name || '').toLowerCase();
            const email = (c.email || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();

            return (
                name.includes(q) ||
                (email && email.includes(q)) ||
                (phone && phone.includes(q))
            );
        });
    },

        services(): any[] {
            const b: any = this.booking || {};
            return Array.isArray(b.services) ? b.services : [];
        },
        forms(): any[] {
            const b: any = this.booking || {};
            return Array.isArray(b.forms) ? b.forms : [];
        },
        bookingTotal(): number {
            const b: any = this.booking || {};
            return Number(b.total_price ?? b.total ?? 0) || 0;
        },
        cartTotal(): number {
            const s: any = this.summary || null;
            if (s && typeof s.total_price !== 'undefined') {
                return Number(s.total_price) || 0;
            }
            return this.bookingTotal;
        },
        dateLabel(): string {
            const b: any = this.booking || {};
            const src =
                b.date ||
                b.date_formatted ||
                b.starts_at ||
                b.slot_start ||
                null;
            if (!src) return '';
            try {
                const d = new Date(src);
                return d.toLocaleDateString(undefined, {
                    weekday: 'short',
                    day: '2-digit',
                    month: 'short',
                });
            } catch {
                return String(src);
            }
        },
        timeRangeLabel(): string {
            const b: any = this.booking || {};
            const start = b.starts_at || b.slot_start;
            const end = b.ends_at || b.slot_end;
            if (!start && !end) return '';
            const fmt: Intl.DateTimeFormatOptions = {
                hour: '2-digit',
                minute: '2-digit',
            };
            const s = start ? new Date(start).toLocaleTimeString([], fmt) : '—';
            const e = end ? new Date(end).toLocaleTimeString([], fmt) : '—';
            return `${s} – ${e}`;
        },

        // current DB status (lowercased)
        currentStatus(): string {
  const b: any = this.booking || {};
  const s = String(b.status || '').toLowerCase();
  const p = String(b.payment_status || '').toLowerCase();

  // Force payment pending to override “scheduled/booked”
  if (p === 'payment_pending') return 'payment_pending';

  return s || p || 'scheduled';
}
,

        // Status options with Heroicons
        statusOptions(): StatusOption[] {
            return [
                { label: 'Booked', value: 'scheduled', icon: CalendarDaysIcon },
                { label: 'Arrived', value: 'arrived', icon: MapPinIcon },
                { label: 'Started', value: 'started', icon: PlayIcon },

                 { label: 'Payment pending', value: 'payment_pending', icon: CalendarDaysIcon },
                {
                    label: 'No-show',
                    value: 'no_show',
                    icon: EyeSlashIcon,
                    destructive: true,
                },
                {
                    label: 'Cancel',
                    value: 'cancel',
                    icon: XMarkIcon,
                    destructive: true,
                },
            ];
        },

        currentStatusOption(): StatusOption | null {
            return (
                this.statusOptions.find(
                    (opt) => opt.value === this.currentStatus,
                ) || null
            );
        },

        statusChip(): string | null {
            const opt = this.currentStatusOption;
            if (opt) return opt.label;

            const raw = this.currentStatus;
            if (!raw) return null;

            const map: Record<string, string> = {
                booked: 'Booked',
                scheduled: 'Booked',
                arrived: 'Arrived',
                started: 'Started',
                no_show: 'No-show',
                noshow: 'No-show',
                cancel: 'Cancelled',
                cancelled: 'Cancelled',
                canceled: 'Cancelled',
                payment_pending: 'Payment pending',
                completed: 'Completed',
            };

            return (
                map[raw] ??
                raw
                    .replace(/_/g, ' ')
                    .replace(/^\w/, (c) => c.toUpperCase())
            );
        },

        headerBgClass(): string {
            const s = this.currentStatus;
            if (['scheduled', 'booked'].includes(s)) return 'bg-amber-500';
            if (s === 'arrived') return 'bg-emerald-500';
            if (s === 'started') return 'bg-indigo-600';
            if (s === 'payment_pending') return 'bg-sky-600';
            if (s === 'completed') return 'bg-emerald-600';
            if (s === 'no_show' || s === 'noshow') return 'bg-neutral-600';
            if (['cancel', 'cancelled', 'canceled'].includes(s))
                return 'bg-rose-600';
            return 'bg-[#216CFF]';
        },

        bookingNumberLabel(): string {
            const b: any = this.booking || {};
            if (!b.id) return 'Booking';
            return `Booking #${b.id}`;
        },
        createdLabel(): string {
            if (!this.client || !(this.client as any).created_at) return '';
            try {
                const d = new Date((this.client as any).created_at);
                return d.toLocaleDateString(undefined, {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });
            } catch {
                return (this.client as any).created_at;
            }
        },
        noShowLabel(): string | null {
            const count = (this.client as any)?.no_show_count;
            if (!count || count <= 0) return null;
            if (count === 1) return '1 no-show';
            return `${count} no-shows`;
        },

        // Footer CTAs
        isScheduled(): boolean {
            const b: any = this.booking || {};
            return String(b.status || '').toLowerCase() === 'scheduled';
        },
        isCompleted(): boolean {
            const b: any = this.booking || {};
            return String(b.status || '').toLowerCase() === 'completed';
        },
        hasSales(): boolean {
            return (
                (Array.isArray(this.sales) && this.sales.length > 0) ||
                (this.summary as any)?.has_sales === true
            );
        },
    },

    watch: {
         booking: {
    deep: true,
    handler() {
      if (!this.showNoteModal) this.noteDraft = this.bookingNotes;
    },
  },
        show(val: boolean) {
    if (!val) {
      this.showClientList = false;
      this.clientSearch = '';
      this.statusMenuOpen = false;
      this.showCancelModal = false;
      this.cancelReason = '';
      this.isCancelling = false;
    }
  },

         successPaymentVisible(val: boolean) {
        if (val && this.show) {
            this.$emit('close');
        }
    },
    },

    methods: {
        openNoteModal(): void {
  const b: any = this.booking || null;
  if (!b || !b.id) return;

  this.noteError = '';
  this.noteDraft = this.bookingNotes;
  this.showNoteModal = true;
},

closeNoteModal(): void {
  this.showNoteModal = false;
  this.noteError = '';
},

async saveNote(): Promise<void> {
  const b: any = this.booking || null;
  if (!b || !b.id) return;

  this.isSavingNote = true;
  this.noteError = '';

  const url =
    (window as any).route
      ? (window as any).route('bookings.update-note', b.id)
      : `/bookings/${b.id}/note`;

  try {
    const res = await axios.post(url, { notes: this.noteDraft || null }, {
      headers: { Accept: 'application/json' },
    });

    // Update local booking object (you already mutate props elsewhere)
    const updated = res.data?.booking ?? null;
    (this.booking as any).notes = updated?.notes ?? this.noteDraft ?? null;

    this.showNoteModal = false;
  } catch (e: any) {
    if (e?.response?.status === 422) {
      this.noteError = e.response?.data?.message || 'Invalid note.';
    } else {
      this.noteError = 'Failed to update note.';
      console.error(e);
    }
  } finally {
    this.isSavingNote = false;
  }
},

async deleteNote(): Promise<void> {
  const b: any = this.booking || null;
  if (!b || !b.id) return;

  this.isDeletingNote = true;
  this.noteError = '';

  const url =
    (window as any).route
      ? (window as any).route('bookings.delete-note', b.id)
      : `/bookings/${b.id}/note`;

  try {
    await axios.delete(url, { headers: { Accept: 'application/json' } });

    (this.booking as any).notes = null;
    this.noteDraft = '';
    this.showNoteModal = false;
  } catch (e) {
    this.noteError = 'Failed to delete note.';
    console.error(e);
  } finally {
    this.isDeletingNote = false;
  }
},

        closeAfterStatusUpdate(): void {
    if (this.isAutoClosing) return;
    this.isAutoClosing = true;

    this.closeStatusMenu();

    window.setTimeout(() => {
      this.$emit('close');
      this.isAutoClosing = false;
    }, 140);
  },
        closeCancelModal() {
  this.showCancelModal = false;
  this.cancelReason = '';
  this.showCancelReasonDropdown = false;
},
        async confirmCancel(): Promise<void> {
  const booking: any = this.booking || null;
  if (!booking || !booking.id || !this.cancelReason) return;

  const url = (window as any).route
    ? (window as any).route('bookings.update-status', booking.id)
    : `/bookings/${booking.id}/status`;

  this.isCancelling = true;

  try {
    await axios.post(url, { status: 'cancel', cancel_reson: this.cancelReason });
    (this.booking as any).status = 'cancel';
    (this.booking as any).cancel_reson = this.cancelReason;

    this.showCancelModal = false;
    this.$emit('close'); // close offcanvas after cancel
  } catch (e) {
    console.error('Failed to cancel booking', e);
  } finally {
    this.isCancelling = false;
  }
},

              async selectClientRow(c: any): Promise<void> {
                if (this.isPaymentPending) return;
    const booking: any = this.booking || null;
    if (!booking || !booking.id || !c || !c.id) {
        return;
    }

    const previousClient: any = this.client
        ? { ...(this.client as any) }
        : null;

    // Optimistic update
    booking.client = {
        id: c.id,
        name: c.name || c.full_name,
        full_name: c.full_name || c.name,
        email: c.email || null,
        phone: c.phone || null,
        no_show_count: c.no_show_count ?? 0,
        avatar_url: c.avatar_url || c.profile_photo_url || null,
    };
    booking.client_id = c.id;

    try {
        const url = (window as any).route
            ? (window as any).route('bookings.update-client', booking.id)
            : `/bookings/${booking.id}/client`;

        await axios.post(url, { client_id: c.id });

        this.$emit('client-updated', {
            bookingId: booking.id,
            client: booking.client,
        });
    } catch (e) {
        // Rollback if backend fails
        if (previousClient) {
            booking.client = previousClient;
            booking.client_id = previousClient.id ?? null;
        } else {
            booking.client = null;
            booking.client_id = null;
        }
        console.error('Failed to update booking client', e);
    } finally {
        this.showClientList = false;
        this.clientSearch = '';
    }
},


       async setWalkIn(): Promise<void> {
         if (this.isPaymentPending) return;
    const booking: any = this.booking || null;
    if (!booking || !booking.id) {
        return;
    }

    const previousClient: any = this.client ? { ...(this.client as any) } : null;

    const previousClientId = booking.client_id ?? null;

    this.setClientOnBooking(null);

    try {
        const url = (window as any).route
            ? (window as any).route('bookings.update-client', booking.id)
            : `/bookings/${booking.id}/client`;

        await axios.post(url, { client_id: null });

        // 🔴 NEW: notify parent that client is now "walk-in"
        this.$emit('client-updated', {
            bookingId: booking.id,
            client: null,
        });
    } catch (e) {
        // Roll back if anything breaks
        if (previousClient) {
            this.setClientOnBooking(previousClient);
            booking.client_id = previousClientId;
        }
        console.error('Failed to clear booking client', e);
    } finally {
        this.showClientList = false;
        this.clientSearch = '';
        this.showClientPicker = false;
    }
},

handleClientSaved(newClient: any) {
    if (!newClient || !newClient.id) {
        this.showAddClientModal = false;
        return;
    }

    if (!Array.isArray(this.clientsLocal)) {
        this.clientsLocal = [];
    }


    const existingIndex = this.clientsLocal.findIndex(
        (c: any) => c.id === newClient.id,
    );
    if (existingIndex === -1) {
        this.clientsLocal.push(newClient);
    } else {
        this.clientsLocal.splice(existingIndex, 1, newClient);
    }


    const booking: any = this.booking || null;
    if (booking) {
        booking.client = {
            id: newClient.id,
            name: newClient.name,
            full_name: newClient.full_name || newClient.name,
            email: newClient.email || null,
            phone: newClient.phone || null,
            no_show_count: newClient.no_show_count ?? 0,
        };
        booking.client_id = newClient.id;
    }


    this.selectClientRow(newClient);


    this.showClientList = false;
    this.clientSearch = '';
    this.showAddClientModal = false;
    this.showClientPicker = false;
}
,

     toggleStatusMenu(): void {
  if (!this.canStatusChange) return;
  if (this.isPaymentPending || this.isUpdatingStatus) return;
  this.statusMenuOpen = !this.statusMenuOpen;
},


           openClientSelector(): void {
             if (this.isPaymentPending) return;
    const booking: any = this.booking || null;


    this.showClientPicker = true;
    this.clientSearch = '';


    this.$emit('open-client-selector', {
        bookingId: booking?.id ?? null,
        currentClientId: (this.client as any)?.id ?? null,
    });
},

 setClientOnBooking(c: any | null): void {
        const booking: any = this.booking || null;
        if (!booking) return;

        if (!c) {
            booking.client = null;
            booking.client_id = null;
            return;
        }

        booking.client = {
            id: c.id,
            name: c.name || c.full_name,
            full_name: c.full_name || c.name,
            email: c.email || null,
            phone: c.phone || null,
            no_show_count: c.no_show_count ?? 0,
            created_at: c.created_at ?? booking.client?.created_at ?? null,
            avatar_url: c.avatar_url || c.profile_photo_url || null,
        };
        booking.client_id = c.id;
    },


    handleClientSelected(selected: any) {
        if (this.isPaymentPending) return;
        if (!selected || !selected.id) {
            this.showClientPicker = false;
            return;
        }


        if (!Array.isArray(this.clientsLocal)) {
            this.clientsLocal = [];
        }
        const idx = this.clientsLocal.findIndex((c: any) => c.id === selected.id);
        if (idx === -1) {
            this.clientsLocal.push(selected);
        } else {
            this.clientsLocal.splice(idx, 1, selected);
        }


        this.selectClientRow(selected);

        this.showClientPicker = false;
    },


handleWalkInSelected() {
    // reuse your existing backend logic to clear client
      if (this.isPaymentPending) return;
    this.setWalkIn();
    this.showClientPicker = false;
},

handleAddNewClientFromPicker() {
     if (this.isPaymentPending) return;
    // open the existing create-client modal from inside the picker
    this.showAddClientModal = true;
},


createClient() {
    if (this.isPaymentPending) return;
            this.showAddClientModal = true;
        },

        closeStatusMenu(): void {
            this.statusMenuOpen = false;
        },

     async changeStatus(newStatus: string): Promise<void> {

         if (this.isPaymentPending) {
    this.closeStatusMenu();
    return;
  }
  const booking: any = this.booking || null;
  if (!booking || !booking.id) return;

  if (this.currentStatus === newStatus) {
    this.closeStatusMenu();
    return;
  }

  if (newStatus === 'cancel') {
    this.cancelReason = '';
    this.showCancelModal = true;
    this.closeStatusMenu();
    return;
  }

  const url = (window as any).route
    ? (window as any).route('bookings.update-status', booking.id)
    : `/bookings/${booking.id}/status`;

  const previousStatus = this.currentStatus;

  (this.booking as any).status = newStatus;

  this.isUpdatingStatus = true;

  try {
    await axios.post(url, { status: newStatus });

    this.closeAfterStatusUpdate();
  } catch (e) {
    (this.booking as any).status = previousStatus;
    console.error(e);
    this.closeStatusMenu();
  } finally {
    this.isUpdatingStatus = false;
  }
},


        formatNumber(v: number | string | null | undefined): string {
            const n = Number(v) || 0;
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
        fmtTime(iso: string | null | undefined): string {
            if (!iso) return '—';
            try {
                return new Date(iso).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            } catch {
                return '—';
            }
        },
        fmtDateTime(iso: string | null | undefined): string {
            if (!iso) return '';
            try {
                const d = new Date(iso);
                return d.toLocaleDateString(undefined, {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });
            } catch {
                return String(iso);
            }
        },
        initials(name: string | null | undefined): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 2);
        },
        paymentLabelFor(method: string | null | undefined): string {
            const m = (method || '').toLowerCase();
            switch (m) {
                case 'cash':
                    return 'Cash';
                case 'card':
                    return 'Card';
                case 'gift-card':
                    return 'Gift card';
                case 'split':
                    return 'Split payment';
                case 'other':
                    return 'Other';
                default:
                    return method || 'Payment';
            }
        },
        serviceColor(svc: any): string {
            return (
                svc?.category?.color_code ||
                svc?.color_code ||
                '#4f46e5'
            );
        },
        serviceDurationLabel(svc: any): string {
            const minutes =
                svc.duration_minutes ??
                svc.duration ??
                (svc.extra_minutes
                    ? (svc.base_duration ?? 0) + svc.extra_minutes
                    : null);
            if (!minutes) return '';
            const m = Number(minutes) || 0;
            const h = Math.floor(m / 60);
            const rem = m % 60;
            if (h && rem) return `${h}h · ${rem}min`;
            if (h) return `${h}h`;
            return `${rem}min`;
        },
        serviceTimeRangeLabel(svc: any): string {
            const start = svc.starts_at;
            const end = svc.ends_at;
            if (!start && !end) return '';
            const fmt: Intl.DateTimeFormatOptions = {
                hour: '2-digit',
                minute: '2-digit',
            };
            const s = start
                ? new Date(start).toLocaleTimeString([], fmt)
                : '—';
            const e = end ? new Date(end).toLocaleTimeString([], fmt) : '—';
            return `${s} – ${e}`;
        },

   openCheckoutTip(origin: string | null = null): void {
  if (this.isOpeningTip) return;

  const booking: any = this.booking || null;
  if (!booking || !booking.id) return;

  this.isOpeningTip = true;

  const url = (window as any).route
    ? (window as any).route('bookings.mark-payment-pending', booking.id)
    : `/bookings/${booking.id}/payment-pending`;

  const payload: any = {};
  const clientId = (this.client as any)?.id ?? booking.client_id ?? null;
  if (clientId) payload.client_id = clientId;

  const total = Number(this.bookingTotal) || 0;
  const summaryRemainingRaw = Number((this.summary as any)?.remaining ?? NaN);
  const isPending = this.currentStatus === 'payment_pending';

  const due =
    isPending && Number.isFinite(summaryRemainingRaw)
      ? Math.max(0, summaryRemainingRaw)
      : total;

  const alreadyPaid = Math.max(0, total - due);

  const buildMeta = () => ({
    ...(origin ? { origin } : {}),
    bookingId: booking.id,
    booking,
    client: this.client,
    staff: (booking as any).staff || null,
    branch_id: (booking as any).branch_id ?? null,
    currencySymbol: this.currencySymbol || 'LKR',
    amount_due: due,
    lock_amount: isPending,
    base_amount: due,
    tax_amount: 0,
    tip_amount: 0,
    total_with_tip: due,
    payments: [],
    total_paid: alreadyPaid,
    remaining: due,
    services: this.services,
    original_total: total,
  });

  const openPanel = () => {
    this.$store.commit('OPEN_TIP_PANEL', buildMeta());

    // ✅ wait a tick so the UI has a chance to open the offcanvas
    this.$nextTick(() => {
      this.isOpeningTip = false;
    });
  };

  router.post(url, payload, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: openPanel,
    onError: openPanel,

    // ✅ safety: if you prefer spinner only while POST is running,
    // you can instead move "isOpeningTip=false" here.
    // onFinish: () => { this.isOpeningTip = false; },
  });
},


viewSale(): void {
    const booking: any = this.booking || null;
    if (!booking || !booking.id) return;

    const sale: any = this.sale || null;
    const summary: any = this.summary || {};
    const baseAmount =
        Number(summary?.total_price ?? booking.total_price ?? 0) || 0;

    const meta = {
        bookingId: booking.id,
        booking,
        sale,
        sales: this.sales,
        summary,
        client: this.client,
        staff: (booking as any).staff || null,
        branch_id: (booking as any).branch_id ?? null,
        currencySymbol: this.currencySymbol || 'LKR',
        base_amount: baseAmount,
        tax_amount: Number(sale?.tax_amount ?? 0) || 0,
        tip_amount: Number(sale?.tip_amount ?? 0) || 0,
        total_with_tip: Number(sale?.total_with_tip ?? baseAmount) || 0,
        payment_method: sale?.payment_method ?? null,
        payments: Array.isArray(sale?.payments)
            ? sale.payments
            : sale?.payments ?? [],
        total_paid:
            Number(sale?.total_paid ?? (summary?.total_paid ?? 0)) || 0,
        remaining:
            Number(sale?.remaining ?? (summary?.remaining ?? 0)) || 0,
        services:
            Array.isArray(sale?.services) && sale.services.length
                ? sale.services
                : this.services,
    };

    this.$store.commit('OPEN_BOOKING_DETAILS', meta);

    this.$emit('close');
},
selectCancelReason(reason: string) {
    this.cancelReason = reason;
    this.showCancelReasonDropdown = false;
},
handleClickOutside(event) {
    if (this.showCancelReasonDropdown && !event.target.closest('.dropdown-cancel-reason')) {
        this.showCancelReasonDropdown = false;
    }
}

    },
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease-out,
        transform 0.2s ease-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(24px);
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
.slide-down-enter-active {
  transition: max-height 0.8s ease-in-out, margin-bottom 0.8s ease-in-out;
  overflow: hidden;
}

.slide-down-enter-from {
  max-height: 0;
  margin-bottom: 0;
}
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.18s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* panel pop animation */
.modal-fade-enter-active .modal-panel,
.modal-fade-leave-active .modal-panel {
  transition: transform 0.18s ease, opacity 0.18s ease;
}
.modal-fade-enter-from .modal-panel,
.modal-fade-leave-to .modal-panel {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}

.spinner {
  width: 14px;
  height: 14px;
  border-radius: 9999px;
  border: 2px solid rgba(0, 0, 0, 0.2);
  border-top-color: rgba(0, 0, 0, 0.7);
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}


</style>
