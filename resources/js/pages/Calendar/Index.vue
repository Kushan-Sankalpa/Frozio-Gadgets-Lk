<template>
    <div
        class="relative flex h-[calc(100vh-24px)] flex-col overflow-hidden bg-neutral-50 text-neutral-900"
        @click="handleRootClick"
    >
     <!-- REBOOK TOP BAR -->
        <Transition name="rebook-bar">
           <div
    v-if="rebookBannerShow"
    class="sticky top-0 z-[40] flex items-center justify-center
           bg-orange-100 border-b border-orange-200
           px-6 py-3 text-orange-600 shadow-sm"
>

                <span class="text-sm md:text-base font-semibold pointer-events-none">
                    Select a time to book
                </span>
<button
    type="button"
    class="absolute cursor-pointer right-4 flex h-8 w-8 items-center justify-center
           rounded-full hover:bg-orange-100"
    @click.stop="cancelRebook"
>
    <span class="sr-only">Close rebook bar</span>
    <i class="bx bx-x text-lg text-orange-600"></i>
</button>

            </div>
        </Transition>
        <!-- toolbar -->
    <div
    class="sticky top-0 z-[30] hidden md:flex flex items-center gap-3 border-b bg-white px-4 py-3 shadow-sm"
>
    <button
        class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium transition-colors hover:bg-neutral-50 cursor-pointer"
        @click="goToday"
    >
        Today
    </button>

    <div
        class="relative flex items-center overflow-hidden rounded-full border border-neutral-300 bg-white"
    >
        <!-- previous day -->
        <button
            type="button"
            class="hidden lg:inline px-2 sm:px-3 py-2 hover:bg-neutral-50 border-r border-neutral-200 cursor-pointer"
            @click="moveDays(-1)"
        >
            ‹
        </button>

        <!-- Datepicker-->
        <div class="relative flex-1">
            <button
                type="button"
                class="flex w-full items-center justify-center gap-2 px-4 py-2 text-center text-sm font-semibold tabular-nums hover:bg-neutral-50 cursor-pointer"
                @click="openDesktopDatePicker"
            >

                <i class="bx bx-calendar text-base text-zinc-600"></i>

                <span class="hidden lg:inline tabular-nums">
                    {{ formattedToolbarDate }}
                </span>

                <!-- <span class="hidden md:inline lg:hidden tabular-nums">
                    {{ formattedShortDate }}
                </span> -->

            </button>

            <!-- hidden native date input -->
            <input
                ref="desktopDateInput"
                type="date"
                class="absolute inset-0 opacity-0 pointer-events-none"
                :value="isoDate"
                @change="onDatePicked"
            />
        </div>

        <!-- next day -->
        <button
            type="button"
            class="hidden lg:inline px-2 sm:px-3 py-2 hover:bg-neutral-50 border-l border-neutral-200 cursor-pointer"
            @click="moveDays(1)"
        >
            ›
        </button>
    </div>
        <div class="ml-auto flex items-center gap-2">
        <button
         v-if="$root.hasPermission('waitlist.view')"
    type="button"
    class="relative ml-3 inline-flex items-center gap-2 cursor-pointer rounded-full border border-neutral-300 bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-neutral-50"
    @click="waitlistShow = true"
  >
    <!-- red beep dot -->
    <span
      v-if="waitlistCount > 0"
      class="pointer-events-none absolute -top-0.5 -right-0.5 flex h-3 w-3 items-center justify-center"
    >
      <span
        class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"
      ></span>
      <span
        class="relative inline-flex h-2 w-2 rounded-full bg-rose-600"
      ></span>
    </span>

    <i class="bx bx-time-five text-base text-zinc-600"></i>

    <span class="flex items-center gap-1">
      <span>Waitlist</span>
      <span
        v-if="waitlistCount > 0"
        class="inline-flex items-center justify-center rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-600"
      >
        {{ waitlistCount }}
      </span>
    </span>
  </button>

              <!-- team filter -->
            <div class="relative ml-2">
                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-1 rounded-full border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50"
                    @click.stop="toggleTeamMenu"
                >
                    <i class="bx bx-group text-lg text-zinc-600"></i>
                    <span>Team</span>
                    <i class="bx bx-chevron-down text-base text-zinc-500"></i>
                </button>

                <Transition name="dropdown">
                    <div
                        v-if="teamMenuOpen"
                        class="absolute top-full right-0 z-[120] mt-2 max-h-80 w-64 origin-top overflow-auto rounded-2xl border border-neutral-200 bg-white shadow-xl"
                    >
                                <!-- Select All option -->
            <label
                class="flex cursor-pointer items-center gap-2 border-b border-neutral-100 px-3 py-3 text-sm hover:bg-neutral-50"
            >
                <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-neutral-300 text-red-600 focus:ring-red-500"
                    :checked="areAllStaffSelected"
                    @change="toggleSelectAllStaff"
                />
                <span class="font-medium">Select All</span>
            </label>
                        <div v-if="staff && staff.length" class="py-2">
                            <label
                                v-for="s in staff"
                                :key="'team-' + s.id"
                                class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-sm hover:bg-neutral-50"
                            >

                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-neutral-300 text-red-600 focus:ring-red-500"
                                    :value="s.id"
                                    v-model="selectedStaffIds"
                                />
                                <img
                                    v-if="s.avatar"
                                    :src="s.avatar"
                                    :alt="s.name"
                                    class="h-5 w-5 rounded-full object-cover"
                                />
                                <span class="truncate">{{ s.name }}</span>
                            </label>
                        </div>
                        <div v-else class="px-3 py-3 text-xs text-neutral-500">
                            No employees available.
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- branch filter -->
            <div class="relative z-50">
                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-1 rounded-full border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50"
                    @click.stop="toggleBranchMenu"
                >
                    <span>{{ branchLabel }}</span>
                    <i class="bx bx-chevron-down text-base text-zinc-500"></i>
                </button>

    <Transition name="dropdown">
        <div
            v-if="branchMenuOpen"
            class="absolute top-full right-0 z-[120] mt-2 w-64 origin-top rounded-2xl border border-neutral-200 bg-white shadow-xl"
        >
            <div v-if="branches && branches.length" class="py-2">
                <label
                    v-for="b in branches"
                    :key="'branch-' + b.id"
                    class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-sm hover:bg-neutral-50"
                    @click="selectBranch(b.id)"
                >
                    <!-- Custom checkbox -->
                    <div class="relative flex h-4 w-4 items-center justify-center">
                        <input
                            type="checkbox"
                            class="absolute h-4 w-4 cursor-pointer opacity-0"
                            :checked="String(currentBranchId) === String(b.id)"
                            @change="selectBranch(b.id)"
                        />
                        <!-- Custom checkbox appearance -->
                        <div
                            class="h-4 w-4 rounded border border-neutral-300 transition-colors"
                            :class="{
                                'border-[var(--brand,_var(--brand-fallback))] bg-[var(--brand,_var(--brand-fallback))]':
                                    String(currentBranchId) === String(b.id),
                                'bg-white': String(currentBranchId) !== String(b.id)
                            }"
                        >
                            <!-- Checkmark icon -->
                            <svg
                                v-if="String(currentBranchId) === String(b.id)"
                                class="absolute inset-0 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="3"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </div>
                    <span class="truncate">{{ b.name }}</span>
                </label>
            </div>
            <div v-else class="px-3 py-3 text-xs text-neutral-500">
                No branches available.
            </div>
        </div>
    </Transition>
            </div>

            <!-- <div class="ml-auto flex items-center gap-2">
                <button
                    class="cursor-pointer rounded-full bg-[var(--brand,_var(--brand-fallback))] px-4 py-2 text-white shadow hover:opacity-90"
                    @click="onAdd"
                >
                    Add
                </button>
            </div> -->
        </div>
        </div>

<!-- MOBILE  -->
<div
    class="sticky top-0 z-[30] flex md:hidden items-center justify-between border-b bg-white px-3 py-2 shadow-sm relative"
    @click.self="mobileDateOpen = false"
>
    <!-- Left side-->
    <div class="flex items-center gap-2">
        <button
            class="rounded-full border border-neutral-300 px-3 py-1.5 text-sm font-medium"
            @click="goToday"
        >
            Today
        </button>

        <!-- Calendar -->
      <!-- Calendar (iOS-safe) -->
<!-- Calendar (iOS-safe, fully clickable) -->
<div class="relative cursor-pointer" @click="$refs.mobileDateInput?.showPicker?.()">
  <!-- Real clickable input -->
  <input
    ref="mobileDateInput"
    type="date"
    class="absolute inset-0 z-20 h-full w-full cursor-pointer opacity-0 pointer-events-auto"
    :value="isoDate"
    @change="onMobileDatePicked"
  />

  <!-- Visual UI -->
  <div
    class="pointer-events-none flex items-center rounded-full
           border border-neutral-300 px-3 py-1.5
           text-sm font-semibold"
  >
    <i class="bx bx-calendar text-lg text-zinc-600"></i>
    <span class="tabular-nums">{{ formattedMobileDate }}</span>
  </div>
</div>


    </div>

    <!-- Right side -->
    <div class="flex items-center gap-2">
        <!-- Waitlist Button -->
        <button
         v-if="$root.hasPermission('waitlist.view')"
            type="button"
            class="rounded-full border border-neutral-300 p-2"
            @click="waitlistShow = true"
        >
            <i class="bx bx-time-five text-xl text-zinc-600"></i>
        </button>

        <!-- Filter Button -->
        <button
            class="rounded-full border border-neutral-300 p-2"
            @click="mobileFiltersOpen = true"
        >
            <i class="bx bx-filter text-xl text-zinc-600"></i>
        </button>
    </div>
</div>


<!-- MOBILE FILTER -->
<Transition name="slide-right">
    <div
        v-if="mobileFiltersOpen"
        class="fixed inset-0 z-[300] flex"
    >
        <div
            class="flex-1 bg-black/40"
            @click="mobileFiltersOpen = false"
        ></div>

        <div
            class="w-72 max-w-[80%] bg-white h-full shadow-xl p-4 overflow-y-auto"
        >
            <h3 class="mb-4 text-lg font-semibold">All Filters</h3>

            <div class="mb-6">
                <h4 class="mb-2 font-medium">Team</h4>

                    <!-- Select All option for mobile -->
    <label
        class="flex items-center gap-3 border-b border-neutral-200 py-3 text-sm cursor-pointer"
    >
        <input
            type="checkbox"
            class="h-4 w-4"
            :checked="areAllStaffSelected"
            @change="toggleSelectAllStaff"
        />
        <span class="font-medium">Select All</span>
    </label>

                <div v-if="staff && staff.length">
                    <label
                        v-for="s in staff"
                        :key="'mobile-team-' + s.id"
                        class="flex items-center gap-3 py-1.5 text-sm cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            class="h-4 w-4"
                            :value="s.id"
                            v-model="selectedStaffIds"
                        />
                        <img
                            v-if="s.avatar"
                            :src="s.avatar"
                            :alt="s.name"
                            class="h-6 w-6 rounded-full object-cover"
                        />
                        <span class="truncate">{{ s.name }}</span>
                    </label>
                </div>

                <div v-else class="text-xs text-neutral-500">
                    No employees available.
                </div>
            </div>

            <div>
                <h4 class="mb-2 font-medium">Branch</h4>
                    <div v-if="branches && branches.length">
        <label
            v-for="b in branches"
            :key="'mobile-branch-' + b.id"
            class="flex items-center gap-3 py-1.5 text-sm cursor-pointer"
            @click="selectBranch(b.id)"
        >
            <!-- Custom checkbox -->
            <div class="relative flex h-4 w-4 items-center justify-center">
                <input
                    type="checkbox"
                    class="absolute h-4 w-4 cursor-pointer opacity-0"
                    :checked="String(currentBranchId) === String(b.id)"
                    @change="selectBranch(b.id)"
                />
                <!-- Custom checkbox appearance -->
                <div
                    class="h-4 w-4 rounded border border-neutral-300 transition-colors"
                    :class="{
                        'border-[var(--brand,_var(--brand-fallback))] bg-[var(--brand,_var(--brand-fallback))]':
                            String(currentBranchId) === String(b.id),
                        'bg-white': String(currentBranchId) !== String(b.id)
                    }"
                >
                    <!-- Checkmark icon -->
                    <svg
                        v-if="String(currentBranchId) === String(b.id)"
                        class="absolute inset-0 h-4 w-4 text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
            </div>
            <span class="truncate">{{ b.name }}</span>
        </label>
    </div>


            </div>

            <button
                class="mt-6 w-full rounded-full bg-[var(--brand,_var(--brand-fallback))] py-2 text-white font-medium"
                @click="mobileFiltersOpen = false"
            >
                Apply Filters
            </button>
        </div>
    </div>
</Transition>



        <!-- main scroll area -->
        <div
            ref="scrollArea"
            class="relative flex min-h-0 flex-1 overflow-auto"
            style="--gutter-w: 64px"
            @scroll="onScrollCloseSlotMenu"
        >
            <!-- time gutter -->
          <div
    ref="timeGutter"
    class="sticky left-0 z-30 w-[var(--gutter-w)] shrink-0 bg-white"
    :style="{ height: headerPx + calendarBodyHeight + 'px' }"
>
    <div
        class="sticky top-0 z-30 border-b bg-white"
        :style="{ height: headerPx + 'px' }"
    ></div>

    <div class="relative">


        <!-- existing gutter rows -->
        <div
            v-for="r in rows"
            :key="'tg-' + r"
            :style="{ height: slotPx + 'px' }"
            :class="[
                'flex items-center pr-2 text-right text-[11px] leading-none tabular-nums',
                isHourRow(r - 1)
                    ? 'border-b border-neutral-300 text-neutral-600'
                    : 'border-b border-neutral-100 text-neutral-500',
                rowIsInsideOpening(r - 1) ? 'working-slot' : '',
            ]"
        >
            <span class="block w-full translate-y-[1px]">
                {{ labelForRow(r - 1) }}
            </span>
        </div>
    </div>
</div>

            <!-- columns -->
            <div class="min-w-0 flex-1">
                <div class="relative">
                    <!-- staff header -->
                    <div
                        class="sticky top-0 z-[29] min-h-0 cursor-pointer"
                        :class="
                            filteredStaff.length >=staffHeaderFlexThreshold
                                ? 'flex'
                                : 'flex-1'
                        "
                    >
                        <div
                            class="grid auto-cols-[minmax(220px,1fr)] grid-flow-col bg-white/95 shadow-md backdrop-blur"
                        >
                            <div
                                v-for="s in filteredStaff"
                                :key="'sh-' + s.id"
                                class="flex h-[88px] items-center justify-start gap-2 md:gap-3 border-b border-l border-neutral-200 px-4 md:px-4 first:border-l-0"
                            >
                                <div class="relative">
                                    <img
                                        v-if="s.avatar"
                                        :src="s.avatar"
                                        :alt="s.name"
                                        class="size-10 md:size-12 rounded-full border-2 border-[var(--brand,_var(--brand-fallback))] object-cover"
                                    />
                                    <div
                                        v-else
                                        class="grid size-10 md:size-12 place-items-center rounded-full border-2 border-[var(--brand,_var(--brand-fallback))] bg-neutral-100 text-sm font-semibold"
                                    >
                                        {{ initials(s.name) }}
                                    </div>
                                </div>
                                <div class="truncate text-sm font-semibold">
                                    {{ s.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- staff columns + events -->
                    <div
                        v-if="hasVisibleStaff"
                        class="relative grid auto-cols-[minmax(220px,1fr)] grid-flow-col"
                    >
                       <div
    v-for="(s, idx) in filteredStaff"
    :key="'col-' + s.id"
    :data-staff-id="s.id"
    class="relative border-l border-neutral-200 first:border-l-0"
    @mouseleave="onStaffColumnMouseLeave"
>

                            <!-- now line -->
                            <div
                                v-if="isToday"
                                class="pointer-events-none absolute inset-x-0 z-28"
                                :style="{ top: nowLineTop + 'px' }"
                            >
                                <div class="relative">
                                    <div
                                        class="absolute top-1/2 -translate-y-1/2"
                                        :style="{
                                            left: '0px',
                                            right: '0px',
                                            height: '2px',
                                            background:
                                                'var(--brand, var(--brand-fallback))',
                                        }"
                                    ></div>
                                    <div
                                        v-if="idx === 0"
                                        class="absolute top-1/2 -translate-y-1/2"
                                        :style="{ left: '8px' }"
                                    >
                                        <span
                                            class="inline-flex items-center rounded-full border bg-white px-2 py-0.5 text-[11px] font-semibold shadow-sm"
                                            :style="{
                                                borderColor:
                                                    'var(--brand, var(--brand-fallback))',
                                                color: 'var(--brand, var(--brand-fallback))',
                                            }"
                                        >
                                            {{ nowLabel }}
                                        </span>
                                    </div>
                                </div>
                            </div>

<!-- clickable slots -->
<div
    class="relative cursor-pointer"
    @mouseleave="hoverRowIndex = null; hoverStaffId = null"
>
    <!-- real slots / hit areas -->
    <div
        v-for="r in rows"
        :key="'row-' + s.id + '-' + r"
        :style="{ height: slotPx + 'px' }"
        :class="rowClass(r - 1)"
        class="relative"
    >
        <button
            type="button"
            class="absolute inset-0 w-full cursor-pointer outline-none bg-transparent"
            :aria-label="slotAriaLabel(s, r - 1)"
            @mouseenter="
                hoverRowIndex = r - 1;
                hoverStaffId = s.id;
            "
            @click.stop="openSlotMenu(s, r - 1, $event)"
        />
    </div>

   <!-- SMOOTH, CURVED, FULL-SLOT HOVER INDICATOR -->
<Transition name="slot-hover">
    <div
        v-if="hoverRowIndex !== null && hoverStaffId === s.id"
        class="absolute left-0 right-0 pointer-events-none slot-hover-indicator"
        :style="{
            top: hoverRowIndex * slotPx + 'px',
            height: slotPx + 'px',
        }"
    >
        <div
            class="mx-[2px] my-[1px] flex h-[calc(100%-2px)] items-center justify-center rounded-md border border-orange-400/70 bg-orange-100/50 shadow-sm"
        >
            <span
                class="text-[11px] font-semibold tabular-nums"
                style="color: #9a3412;"
            >
                {{ hoverTimeLabel }}
            </span>
        </div>
    </div>
</Transition>

</div>


                            <!-- events -->
                          <div
    class="pointer-events-none absolute inset-x-0 top-0 cursor-pointer z-10"
    :style="{ height: rows * slotPx + 'px' }"
>

    <div
    v-for="ev in allEventsForStaff(s.id)"
    :key="ev.id"
    class="calendar-event pointer-events-auto"
      :style="eventStyle(ev)"
    @touchstart.stop="handleCalendarEventClick(ev, $event)"
    :class="[
        {
            'blocked-time':
                ev.status === 'blocked_time' || ev.isBlockedTime,
        },
        {
            'calendar-event--shrunk':
                shrunkenEventId === ev.id &&
                !(ev.status === 'blocked_time' || ev.isBlockedTime),
        },
    ]"
    @click.stop="onEventClick(ev)"
    @mouseenter="onEventMouseEnter(ev, $event)"
    @mousemove="updateHoverSlotFromEvent(ev, $event)"
    @mouseleave="onEventMouseLeave"
>

    <div class="calendar-event-content relative">

        <div
            class="calendar-event-colorbar"
            :style="{
                backgroundColor:
                    ev.color ||
                    'var(--brand,_var(--brand-fallback))',
            }"
        ></div>
        <div class="calendar-event-body">

            <!-- TOP-RIGHT STATUS ICON (payment) + no-show dot -->
 <div class="pointer-events-none absolute right-1.5 top-1.5 sm:right-2 sm:top-2 flex items-center gap-1" aria-hidden="true">
  <!-- payment icon -->
  <span v-if="paymentIconKind(ev)">
    <!-- COMPLETED / FULLY PAID (filled tag) -->
   <img
    v-if="paymentIconKind(ev) === 'full'"
    src="/price-tag.png"
    alt="Paid"
    class="h-3 w-3 sm:h-3.5 sm:w-3.5 md:h-4 md:w-4 opacity-90 object-contain"
  />

  <!-- PART PAID -->
  <img
    v-else-if="paymentIconKind(ev) === 'part'"
    src="/tag.png"
    alt="Part paid"
     class="h-3 w-3 sm:h-3.5 sm:w-3.5 md:h-4 md:w-4 opacity-90 object-contain"
  />
  </span>

  <!-- keep your no-show dot (now it won’t overlap) -->
  <span v-if="isNoShowPending(ev)" class="relative flex h-2 w-2 sm:h-2.5 sm:w-2.5">
    <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
   <span class="relative inline-flex h-2 w-2 sm:h-2.5 sm:w-2.5 rounded-full bg-rose-600 no-show-glow"></span>
  </span>
</div>


            <!-- <span
  v-if="paymentBadgeLabel(ev)"
  class="pointer-events-none absolute bottom-2 right-2 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold leading-none"
  :class="paymentBadgeClass(ev)"
>
  {{ paymentBadgeLabel(ev) }}
</span> -->

            
            <!-- <div
  v-if="isNoShowPending(ev)"
  class="pointer-events-none absolute right-2 top-2"
  aria-hidden="true"
>
  <span class="relative flex h-2.5 w-2.5">
    <span
      class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"
    ></span>
    <span
      class="relative inline-flex h-2.5 w-2.5 rounded-full bg-rose-600 no-show-glow"
    ></span>
  </span>
  
</div> -->
           <div class="truncate font-semibold leading-tight text-[12px] sm:text-[13px] md:text-[14px] text-black">
                {{ ev.clientName }}
            </div>
            <div class="truncate leading-tight text-[11px] sm:text-[12px] text-black/90">
                {{ ev.label }}
            </div>
            <div class="truncate leading-tight text-[11px] sm:text-[12px] text-black/90">
                {{ fmtTime(ev.startISO) }} –
                {{ fmtTime(ev.endISO) }}
            </div>
        </div>
    </div>
</div>


                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex h-full items-center justify-center px-4 text-sm text-neutral-500"
                    >
                        No employees available for the selected branch.
                    </div>
                </div>
            </div>
        </div>

        <!-- slot menu -->
        <Transition name="slot-menu">
            <div
                v-if="slotMenuShow"
                class="fixed z-[200] w-72 max-w-[90vw] overflow-hidden rounded-2xl border border-neutral-200 bg-white/95 shadow-[0_18px_45px_rgba(15,23,42,0.25)] backdrop-blur-sm transition-transform"
                :class="
                    slotMenuPlacement === 'up'
                        ? 'slot-menu-up origin-bottom'
                        : 'slot-menu-down origin-top'
                "
                :style="{ left: slotMenuX + 'px', top: slotMenuY + 'px' }"
                @click.stop
            >
                <!-- header -->
                <div
                    class="flex items-center justify-between bg-neutral-100 px-4 py-3"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-8 place-items-center rounded-full bg-black text-xs font-semibold text-white tabular-nums"
                        >
                            <ClockIcon class="h-4 w-4" />
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-[13px] font-semibold text-neutral-900 tabular-nums"
                            >
                                {{ slotMenuLabel }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex size-7 cursor-pointer items-center justify-center rounded-full text-neutral-500 hover:bg-white hover:text-neutral-800"
                        @click="closeSlotMenu"
                    >
                        ✕
                    </button>
                </div>

                <!-- actions -->
                <div class="py-2">
  <button
    v-if="canAddBooking"
    type="button"
    class="flex w-full cursor-pointer items-center gap-3 px-4 py-3 text-sm transition-colors hover:bg-neutral-50"
    @click="onSlotMenuAddAppointment"
  >
    <span
      class="grid size-8 place-items-center rounded-full bg-[var(--brand,_var(--brand-fallback))]/10 text-[var(--brand,_var(--brand-fallback))]"
    >
      <CalendarDaysIcon class="h-4 w-4" />
    </span>
    <div class="flex flex-col items-start">
      <span class="font-medium text-neutral-900">Add appointment</span>
      <span class="text-[11px] text-neutral-500">Book a new service for a client</span>
    </div>
  </button>

  <button
    v-if="canAddBlocktime"
    type="button"
    class="flex w-full cursor-pointer items-center gap-3 px-4 py-3 text-sm transition-colors hover:bg-neutral-50"
    @click="onSlotMenuAddBlockedTime"
  >
    <span class="grid size-8 place-items-center rounded-full bg-rose-100 text-rose-600">
      <NoSymbolIcon class="h-4 w-4" />
    </span>
    <div class="flex flex-col items-start">
      <span class="font-medium text-neutral-900">Add blocked time</span>
      <span class="text-[11px] text-neutral-500">Mark this time as unavailable</span>
    </div>
  </button>

  <div v-if="!canAddBooking && !canAddBlocktime" class="px-4 py-3 text-xs text-neutral-500">
    No actions available.
  </div>
</div>

            </div>
        </Transition>

        <!-- booking hover card -->
        <Transition name="booking-hover">
            <div
                v-if="bookingHoverShow && bookingHoverEvent"
                class="booking-hover-card fixed z-[100] w-[380px] max-w-[380px] text-xs"
                :class="
                    bookingHoverPlacement === 'up'
                        ? 'booking-hover-card-up origin-bottom'
                        : 'booking-hover-card-down origin-top'
                "
                :style="{
                    left: bookingHoverX + 'px',
                    top: bookingHoverY + 'px',
                    '--hover-color':
                        bookingHoverEvent.color ||
                        'var(--brand, var(--brand-fallback))',
                }"
            >
                <!-- top bar -->
                <div class="booking-hover-header">
                    <span class="booking-hover-time">
                        {{ fmtTime(bookingHoverEvent.startISO) }} –
                        {{ fmtTime(bookingHoverEvent.endISO) }}
                    </span>
                    <span
                        v-if="bookingHoverStatus(bookingHoverEvent)"
                        class="booking-hover-status"
                    >
                        {{ bookingHoverStatus(bookingHoverEvent) }}
                    </span>
                </div>

                <!-- body -->
               <div class="booking-hover-body">
    <!-- skeleton while loading -->
    <div v-if="bookingHoverLoading" class="booking-hover-skeleton">
        <div class="booking-hover-skel-row">
            <div class="booking-hover-skel-avatar shimmer"></div>
            <div class="booking-hover-skel-lines">
                <div class="shimmer line-lg"></div>
                <div class="shimmer line-sm"></div>
            </div>
        </div>

        <div class="booking-hover-skel-service">
            <div class="shimmer line-md"></div>
            <div class="shimmer line-xs"></div>
        </div>
    </div>

    <!-- actual hover content -->
    <div v-else>
        <!-- client row -->
       <div class="booking-hover-client-row">
    <div class="booking-hover-avatar overflow-hidden">
        <template v-if="bookingHoverEvent.clientAvatarUrl">
            <img
                :src="bookingHoverEvent.clientAvatarUrl"
                :alt="bookingHoverEvent.clientName"
                class="h-full w-full object-cover rounded-full"
            />
        </template>
        <template v-else>
            <span>
                {{ initials(bookingHoverEvent.clientName || '') }}
            </span>
        </template>
    </div>
    <div class="booking-hover-client-meta">
        <div class="booking-hover-client-name truncate">
            {{ bookingHoverEvent.clientName }}
        </div>
        <div
            v-if="bookingHoverEvent.clientEmail"
            class="booking-hover-client-email truncate"
        >
            {{ bookingHoverEvent.clientEmail }}
        </div>
    </div>
</div>


        <!-- service row -->
        <div class="booking-hover-service-row">
            <div class="booking-hover-service-main">
                <div class="booking-hover-service-name truncate">
                    {{
                        bookingHoverEvent.serviceLabel ||
                        bookingHoverEvent.label
                    }}
                </div>
                <div class="booking-hover-service-meta">
                    <span
                        v-if="staffNameFor(bookingHoverEvent.staffId)"
                    >
                        {{ staffNameFor(bookingHoverEvent.staffId) }}
                    </span>
                    <span v-if="bookingHoverEvent.categoryName">
                        <span
                            v-if="
                                staffNameFor(bookingHoverEvent.staffId)
                            "
                        >
                            ·
                        </span>
                        {{ bookingHoverEvent.categoryName }}
                    </span>
                    <span
                        v-if="bookingHoverDuration(bookingHoverEvent)"
                    >
                        <span
                            v-if="
                                staffNameFor(bookingHoverEvent.staffId) ||
                                bookingHoverEvent.categoryName
                            "
                        >
                            ·
                        </span>
                        {{ bookingHoverDuration(bookingHoverEvent) }}
                    </span>
                </div>
            </div>
            <div
    v-if="bookingHoverTotal(bookingHoverEvent)"
    class="booking-hover-price tabular-nums"
>
    From {{ currencySymbol }}
    {{ bookingHoverTotal(bookingHoverEvent) }}
</div>

        </div>

        <!-- SALE / PAYMENT ROW (bottom like the screenshot) -->
<div
  v-if="bookingHoverSaleText(bookingHoverEvent)"
  class="mt-3 border-t border-neutral-200 pt-3 flex items-center justify-between"
>
  <div class="text-[12px] font-semibold text-neutral-900">Sale</div>

  <div class="flex items-center gap-2">
    <div class="text-[12px] text-neutral-700 tabular-nums">
      {{ bookingHoverSaleText(bookingHoverEvent) }}
    </div>
    <i class="bx bx-purchase-tag text-neutral-400"></i>
  </div>
</div>

    </div>
</div>

            </div>
        </Transition>

        <SelectService />
        <SelectTip />
        <SuccessPayment />
        <BookingDetails />

      <CompletedBooking
  :show="completedBookingShow"
  :loading="bookingPanelLoading"
  :booking="bookingPanel.booking"
  :sale="bookingPanel.sale"
  :sales="bookingPanel.sales"
  :summary="bookingPanel.summary"
  :currency-symbol="currencySymbol"
  @close="closeCompletedBooking"
  @rebook="handleRebook"
  @edit-sales="openSaleEdit"
/>

<SaleEditOffcanvas
  :show="saleEditShow"
  :loading="saleEditLoading"
  :booking="bookingPanel.booking"
  :sale="bookingPanel.sale"
  :sales="bookingPanel.sales"
  :currency-symbol="currencySymbol"
  :staff="staff"    
  @close="closeSaleEdit"
  @saved="onSaleEdited"
/>


<Booking
    :show="bookingPanelShow"
    :loading="bookingPanelLoading"
    :booking="bookingPanel.booking"
    :sale="bookingPanel.sale"
    :sales="bookingPanel.sales"
    :summary="bookingPanel.summary"
    :currency-symbol="currencySymbol"
    @close="closeBookingPanel"
     @client-updated="handleBookingClientUpdated"
/>

        <BlockedTime
            :show="blockedTimeShow"
            :data="blockedTimeData"
            @close="blockedTimeShow = false"
            @saved="
                () => {
                    blockedTimeShow = false;
                    reloadCalendar();
                }
            "
        />
<Waitlist
 v-if="$root.hasPermission('waitlist.view')"
 ref="waitlistComponent"
  :show="waitlistShow"
  :branch-id="currentBranchId"
  :currency-symbol="currencySymbol"
  :services="services"
  @close="waitlistShow = false"
   @accepted="() => { reloadCalendar(); reloadWaitlist(); }"
  @count-changed="waitlistCount = $event"
/>



    </div>
</template>

<script lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { defineComponent } from 'vue';

import BlockedTime from './Offcanvas/BlockedTime.vue';
import BookingDetails from './Offcanvas/BookingDetails.vue';
import Booking from './Offcanvas/BookingPreview.vue';
import SelectService from './Offcanvas/SelectService.vue';
import SelectTip from './Offcanvas/SelectTip.vue';
import SuccessPayment from './Offcanvas/SuccessPayment.vue';
import Waitlist from './Offcanvas/Waitlist.vue';
import CompletedBooking from './Offcanvas/CompletedBooking.vue';
import SaleEditOffcanvas from './Offcanvas/SaleEditOffcanvas.vue';



import {
    CalendarDaysIcon,
    ClockIcon,
    NoSymbolIcon,
} from '@heroicons/vue/24/outline';

export default defineComponent({
    name: 'CalendarIndex',
    layout: AppLayout,
    components: {
        Head,
        SelectService,
        SelectTip,
        ClockIcon,
        CalendarDaysIcon,
        NoSymbolIcon,
        SuccessPayment,
        BookingDetails,
        Booking,
        BlockedTime,
        Waitlist,
        CompletedBooking,
        SaleEditOffcanvas,

    },

    props: {
        date: { type: String, required: true },
        step: { type: Number, default: 15 },
        staff: { type: Array, default: () => [] },
        events: { type: Array, default: () => [] },
        brand: { type: String, default: '#B21600' },
        branches: { type: Array, default: () => [] },
        selectedBranchId: { type: [Number, String], default: null },
        booking: { type: Object, default: () => null },
         services: { type: Array, default: () => [] },
         countries: { type: Array, default: () => [] },
    },

   data() {
    const [y, m, d] = this.date.split('-').map((n: string) => parseInt(n, 10));
    const initial = new Date();
    initial.setFullYear(y, m - 1, d);
    initial.setHours(0, 0, 0, 0);

    const branchesProp = (this as any).branches as any[] | undefined;
    const defaultBranchId = this.selectedBranchId
        ? String(this.selectedBranchId)
        : branchesProp && branchesProp.length
          ? String(branchesProp[0].id)
          : '';

    return {
         shrunkenEventId: null as number | string | null,
        selectedDate: initial as Date,
        slotPx: 22,
        headerPx: 88,
        nowTickMs: Date.now(),
        nowTimer: null as number | null,

        hoverRowIndex: null as number | null,
        hoverStaffId: null as number | string | null,

        teamMenuOpen: false,
        branchMenuOpen: false,
        currentBranchId: defaultBranchId,
        selectedStaffIds: [] as any[],
        waitlistShow: false,
        waitlistCount: 0,

        rebookBannerShow: false,
        rebookBookingId: null as number | string | null,

        slotMenuShow: false,
        slotMenuX: 0,
        slotMenuY: 0,
        slotMenuStaff: null as any | null,
        slotMenuRowIndex: null as number | null,
        slotMenuLabel: '',
        slotMenuPlacement: 'down' as 'down' | 'up',

        bookingPanelShow: false,
        bookingPanelLoading: false,
        completedBookingShow: false,
        bookingPanel: {
            booking: null as any | null,
            sale: null as any | null,
            sales: [] as any[],
            summary: null as any | null,
        },

        bookingHoverShow: false,
        bookingHoverX: 0,
        bookingHoverY: 0,
        bookingHoverPlacement: 'down' as 'down' | 'up',
        bookingHoverEvent: null as any | null,
        isCompactViewport: false as boolean,

        bookingHoverLoading: false as boolean,
        bookingHoverLoadingTimeout: null as number | null,

        blockedTimeShow: false,
        saleEditShow: false,
saleEditLoading: false,

        blockedTimeData: null as any,
        mobileDateOpen: false,
        mobileFiltersOpen: false,
       hoverPaymentCache: {} as Record<
  string,
  {
    total: number;
    paid: number;
    remaining: number;
    status: 'fully_paid' | 'part_paid' | 'pending';
    fetchedAt: number; // NEW
  }
>,

hoverPaymentLoading: {} as Record<string, boolean>,


           eventsLocal: Array.isArray((this as any).events)
            ? ((this as any).events as any[]).map((e: any) => ({ ...e }))
            : ([] as any[]),
    };


},


    computed: {
        canAddBooking(): boolean {
  return !!this.$root?.hasPermission?.('calendar.AddBooking');
},
canAddBlocktime(): boolean {
  return !!this.$root?.hasPermission?.('calendar.AddBlocktime');
},
canOpenSlotMenu(): boolean {
  return this.canAddBooking || this.canAddBlocktime;
},

       hoverTimeLabel(): string {
    if (this.hoverRowIndex === null) return '';

    const minutes = this.hoverRowIndex * this.slotMinutes;
    const hour24 = Math.floor(minutes / 60);
    const mm = String(minutes % 60).padStart(2, '0');

    const am = hour24 < 12;
    let hour12 = hour24 % 12;
    if (hour12 === 0) hour12 = 12;

    const hh = String(hour12).padStart(2, '0');
    const suffix = am ? 'AM' : 'PM';

    return `${hh}:${mm} ${suffix}`;
},


        branchOpeningHours(): any[] {
            const list: any[] = (this.branches as any[]) || [];
            if (!list.length) return [];

            let raw: any;

            if (this.currentBranchId) {
                const match = list.find(
                    (b: any) => String(b.id) === String(this.currentBranchId),
                );
                raw = match?.opening_hours;
            } else {
                raw = list[0]?.opening_hours;
            }

            return this.normalizeOpeningHours(raw);
        },

        calendarBodyHeight(): number {
            return this.rows * this.slotPx;
        },

        todaysOpeningIntervals(): { start: number; end: number }[] {
            const hours = this.branchOpeningHours;
            if (!hours || !hours.length) {
                return [
                    {
                        start: 0,
                        end: 24 * 60,
                    },
                ];
            }

            const dayNames = [
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            ];
            const dayName = dayNames[this.selectedDate.getDay()];

            const day = hours.find((h: any) => h.name === dayName);

            if (
                !day ||
                day.enabled === false ||
                !Array.isArray(day.intervals) ||
                !day.intervals.length
            ) {
                return [];
            }

            return day.intervals.map((interval: any) => ({
                start: this.timeOfDayToMinutes(interval.open),
                end: this.timeOfDayToMinutes(interval.close),
            }));
        },

        isoDate(): string {
            const d = this.selectedDate;
            if (!(d instanceof Date) || isNaN(d.getTime())) return this.date;
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        },

        staffHeaderFlexThreshold(): number {
            return this.isCompactViewport ? 2 : 8;
        },

        formattedToolbarDate(): string {
            try {
                return new Date(this.isoDate + 'T00:00:00').toLocaleDateString(
                    undefined,
                    {
                        weekday: 'short',
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                    },
                );
            } catch {
                return this.isoDate;
            }
        },

        slotMinutes(): number {
            return (this.step as number) || 15;
        },

        rows(): number {
            return 24 * (60 / this.slotMinutes);
        },

        isToday(): boolean {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const d = new Date(this.selectedDate);
            d.setHours(0, 0, 0, 0);
            return d.getTime() === today.getTime();
        },

        nowLineTop(): number {
            const n = new Date(this.nowTickMs);
            const mins = n.getHours() * 60 + n.getMinutes();
            return (mins / this.slotMinutes) * this.slotPx;
        },

        nowLabel(): string {
            return new Date(this.nowTickMs).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
        },

        currencySymbol(): string {
            return (
                ((this.$page.props as any).currency_symbol as string) || 'LKR'
            );
        },

        // Vuex-related computed (select service)
        selectServiceShow(): boolean {
            return this.$store.getters.selectServiceShow;
        },
        selectServiceSlotState(): any | null {
            return this.$store.getters.selectServiceSlot;
        },
        selectServiceStaffState(): any | null {
            return this.$store.getters.selectServiceStaff;
        },
        clientMode(): string {
            return this.$store.getters.selectServiceClientMode;
        },
        selectedClientId(): number | null {
            return this.$store.getters.selectServiceClientId;
        },
        clients(): any[] {
            return (this.$page.props as any).clients || [];
        },
        cartItems(): any[] {
            const items = this.$store.getters.selectServiceSelectedServices;
            return Array.isArray(items) ? items : [];
        },

        draftEvents(): any[] {
            if (
                !this.selectServiceShow ||
                !this.selectServiceSlotState ||
                !this.selectServiceStaffState
            ) {
                return [];
            }

            const slot = this.selectServiceSlotState;
            const staff = this.selectServiceStaffState;
            const services = this.cartItems;
            if (!services.length || !staff) return [];

            const baseStart = slot.startIso
                ? new Date(slot.startIso)
                : new Date(this.isoDate + 'T00:00:00');

            const clientName =
                this.clients.find((c: any) => c.id === this.selectedClientId)
                    ?.name || 'Walk-in';

            const events: any[] = [];
            let cursor = new Date(baseStart);

            services.forEach((item: any, index: number) => {
                const rawDuration =
                    item.duration ?? item.durationMinutes ?? this.slotMinutes;
                const extra = Number(item.extraMinutes) || 0;
                const duration =
                    Number(rawDuration) + extra || this.slotMinutes;

                let start: Date;
                if (item.startTimeLabel) {
                    start = this.timeLabelToDate(item.startTimeLabel);
                } else if (index === 0) {
                    start = new Date(baseStart);
                } else {
                    start = new Date(cursor);
                }

                const end = new Date(start);
                end.setMinutes(end.getMinutes() + duration);
                cursor = new Date(end);

                events.push({
                    id: `draft-${index}`,
                    staffId: staff.id,
                    start,
                    end,
                    startISO: start.toISOString(),
                    endISO: end.toISOString(),
                    color: item.color || '#2563eb',
                    label: item.label,
                    clientName,
                });
            });

            return events;
        },

        normalizedEvents(): any[] {
    const raw: any[] = Array.isArray(this.eventsLocal) ? this.eventsLocal : [];

            return raw
                .map((e: any) => {
                    const startISO = e.startISO || e.starts_at || e.start;
                    const endISO = e.endISO || e.ends_at || e.end;
                    if (!startISO || !endISO) return null;

                    const start = new Date(startISO);
                    const end = new Date(endISO);

                    let durationMinutes: number | null = null;
                    if (typeof e.duration_minutes === 'number') {
                        durationMinutes = e.duration_minutes;
                    } else if (typeof e.duration === 'number') {
                        durationMinutes = e.duration;
                    } else {
                        const diff =
                            (end.getTime() - start.getTime()) / (60 * 1000);
                        durationMinutes = diff > 0 ? Math.round(diff) : null;
                    }
                    if (e.status === 'blocked_time') {
                        const serviceLabel =
                            e.block_type === 'lunch'
                                ? 'Lunch Break'
                                : 'Blocked Time';
                        return {
                            id: e.id,
                            bookingId: e.booking_id ?? e.bookingId ?? null,
                            staffId: e.staff_id ?? e.staffId,
                            start,
                            end,
                            startISO: start.toISOString(),
                            endISO: end.toISOString(),
                            color: '#4a5568',
                            label: serviceLabel,
                            clientName: 'Blocked Time',
                            clientEmail: '',
                            status: 'blocked_time',
                            total: 0,
                            categoryName: 'Blocked Time',
                            durationMinutes,
                            serviceLabel,
                            isBlockedTime: true,
                            blockType: e.block_type || 'general',
                            paymentStatus: e.payment_status || e.paymentStatus || null,

                        };
                    }

                    // Regular booking events
                    const serviceLabel =
                        e.service_label ||
                        e.service_name ||
                        e.service ||
                        e.label ||
                        '';

                    const status =
                        e.booking_status ?? e.status ?? e.state ?? null;

                        const paymentStatus =
  e.payment_status ?? e.paymentStatus ?? null;

                    return {
                        id: e.id,
                        bookingId: e.booking_id ?? e.bookingId ?? null,
                        staffId: e.staff_id ?? e.staffId,
                        start,
                        end,
                        startISO: start.toISOString(),
                        endISO: end.toISOString(),
                        color: e.color_code || e.color || '#2563eb',
                        label: serviceLabel || '',
                        clientName: e.client_name || e.clientName || 'Walk-in',
                        clientEmail: e.client_email || e.clientEmail || '',
                         clientAvatarUrl:
        e.client_avatar_url ||
        e.client_avatar ||
        e.client?.avatar_url ||
        null,
                        status,
                        total:
                            e.total ??
                            e.total_amount ??
                            e.price ??
                            e.amount ??
                            null,
                        categoryName: e.category_name || e.category || '',
                        durationMinutes,
                        serviceLabel,
                          paymentStatus,
  payment_status: paymentStatus,

                        checkedStatus: e.checked_status ?? e.checkedStatus ?? e.check_status ?? null,
  checked_in: e.checked_in ?? e.is_checked_in ?? null,
  arrivedAt: e.arrived_at ?? e.checked_in_at ?? null,
  
                    };
                })
                .filter((ev: any) => {
                    if (!ev || isNaN(ev.start.getTime())) return false;

                    const s = (ev.status || '').toString().toLowerCase();
                     const hiddenStatuses = ['cancel', 'cancelled', 'canceled', 'pending','rejected'];

    return !hiddenStatuses.includes(s);
                })
                .sort(
                    (a: any, b: any) => a.start.getTime() - b.start.getTime(),
                );
        },

        branchLabel(): string {
            const list = this.branches as any[];
            const match = list.find(
                (b: any) => String(b.id) === String(this.currentBranchId),
            );
            return match?.name || (list[0]?.name ?? '');
        },

        filteredStaff(): any[] {
            const staffList: any[] = Array.isArray(this.staff)
                ? (this.staff as any[])
                : [];
            if (!staffList.length) return [];
            const ids = new Set(
                this.selectedStaffIds.map((v: any) => String(v)),
            );
            if (!ids.size) return [];
            return staffList.filter((s: any) => ids.has(String(s.id)));
        },

        hasVisibleStaff(): boolean {
            return this.filteredStaff.length > 0;
        },
         tipShow(): boolean {
        return this.$store.getters.tipShow;
    },
    tipMeta(): any {
        return this.$store.getters.tipMeta || null;
    },

    quickSaleDraftEvents(): any[] {
        if (!this.tipShow) return [];
        const meta = this.tipMeta;
        if (!meta || !meta.staff || !Array.isArray(meta.services)) return [];

        const services = meta.services;
        if (!services.length) return [];

        const staff = meta.staff;
        const booking = meta.booking || {};

        const baseIso =
            booking.slot_start ||
            booking.slotStart ||
            booking.starts_at ||
            booking.startsAt ||
            null;

        // start from booking slot_start or from current calendar day at 00:00
        const baseDate = baseIso
            ? new Date(baseIso)
            : new Date(this.isoDate + 'T00:00:00');

        baseDate.setSeconds(0, 0, 0);

        const clientName =
            meta.client?.name ||
            this.clients.find((c: any) => c.id === meta.client?.id)?.name ||
            'Walk-in';

        const events: any[] = [];
        let cursor = new Date(baseDate);

        services.forEach((item: any, index: number) => {
            const rawDuration = Number(
                item.duration_minutes ??
                    item.duration ??
                    this.slotMinutes,
            );
            const extraMinutes = Number(
                item.extra_minutes ?? item.extraMinutes ?? 0,
            );

            const duration =
                (isNaN(rawDuration) ? this.slotMinutes : rawDuration) +
                (isNaN(extraMinutes) ? 0 : extraMinutes);

            const start =
                index === 0 ? new Date(baseDate) : new Date(cursor);
            const end = new Date(start);
            end.setMinutes(end.getMinutes() + duration);
            cursor.setTime(end.getTime());

            events.push({
                id: `qs-draft-${index}`,
                staffId: staff.id,
                start,
                end,
                startISO: start.toISOString(),
                endISO: end.toISOString(),
                color:
                    item.color ||
                    booking.color_code ||
                    'var(--brand, var(--brand-fallback))',
                label: item.label || item.name || 'Service',
                clientName,
                status: booking.status || 'draft',
            });
        });

        return events;
    },
        areAllStaffSelected(): boolean {
        if (!Array.isArray(this.staff) || this.staff.length === 0) {
            return false;
        }
        return this.selectedStaffIds.length === this.staff.length;
    },
    },

   watch: {
    staff: {
        immediate: true,
        handler(newStaff: any[]) {
            if (Array.isArray(newStaff)) {
                this.selectedStaffIds = newStaff.map((s: any) => s.id);
            } else {
                this.selectedStaffIds = [];
            }
        },
    },

    events: {
        immediate: true,
        deep: true,
        handler(newEvents: any[]) {
            if (Array.isArray(newEvents)) {
                this.eventsLocal = newEvents.map((e: any) => ({ ...e }));
            } else {
                this.eventsLocal = [];
            }
        },
    },
},


    mounted() {
        const root = document.documentElement;
        root.style.setProperty('--brand-fallback', this.brand as string);
        root.style.setProperty(
            '--brand',
            'color-mix(in srgb, #ff2000 70%, black)',
        );

        this.nowTimer = window.setInterval(() => {
            this.nowTickMs = Date.now();
        }, 30 * 1000);

        this.updateViewportMode();
        window.addEventListener('resize', this.updateViewportMode);

        window.Echo.channel('bookings')
            .listen('BookingCreated', (event: any) => {
                // console.log('Booking Created:', event.booking);
                this.reloadCalendar();
                  this.reloadWaitlist();
            })
            .listen('BookingUpdated', (event: any) => {
                // console.log('Booking Updated: ', event.booking);
                this.reloadCalendar();
                  this.reloadWaitlist();
            })
            .listen('BookingCancel', (event: any) => {
                // console.log('Booking Cancel: ', event.booking);
                this.reloadCalendar();
                  this.reloadWaitlist();
            });
            document.addEventListener('click', this.handleDocumentClick);

    },

  beforeUnmount() {
    if (this.nowTimer) {
        clearInterval(this.nowTimer as number);
        this.nowTimer = null;
    }
    window.removeEventListener('resize', this.updateViewportMode);

    try {
        (window as any).Echo?.leave?.('bookings');
    } catch {}
    document.removeEventListener('click', this.handleDocumentClick);
},


    methods: {
        isNoShowPending(ev: any): boolean {
  if (!ev) return false;

  const bookingId = ev.bookingId ?? ev.booking_id ?? null;
  if (!bookingId) return false;

  const status = String(ev.status || '').toLowerCase();
  const stopStatuses = new Set([
    'blocked_time',
    'completed',
    'done',
    'cancel',
    'cancelled',
    'canceled',
    'no_show',
    'noshow',
  ]);
  if (stopStatuses.has(status) || ev.isBlockedTime) return false;

  const checkedRaw = (ev.checkedStatus ?? ev.checked_status ?? ev.check_status ?? '')
    .toString()
    .toLowerCase();

  if (
    checkedRaw === 'checked_in' ||
    checkedRaw === 'arrived' ||
    (checkedRaw.includes('checked') && checkedRaw.includes('in')) ||
    ev.checked_in === true ||
    ev.isCheckedIn === true ||
    !!(ev.arrivedAt || ev.arrived_at || ev.checked_in_at)
  ) {
    return false;
  }

  const startMs =
    ev.start instanceof Date
      ? ev.start.getTime()
      : new Date(ev.startISO || ev.starts_at || ev.start || '').getTime();

  if (!startMs || Number.isNaN(startMs)) return false;

  const nowMs = this.nowTickMs;
  const diff = nowMs - startMs;

  if (diff < 0) return false;

  const graceMs = 10 * 60 * 1000;
  return diff < graceMs;
},
handleCalendarEventClick(ev, event) {
    console.log(' Calendar event clicked/touched');
    console.log('Event type:', event.type);
    
    // Important for touch events on mobile
    if (event.type === 'touchstart') {
        event.preventDefault();
        event.stopPropagation();
        
        // Call click handler
        setTimeout(() => {
            this.onEventClick(ev);
        }, 50);
    } else {
        this.onEventClick(ev, event);
    }
},

paymentIconKind(ev: any): '' | 'part' | 'full' {
  const p = String(ev?.paymentStatus ?? ev?.payment_status ?? '').toLowerCase();

  // completed / fully paid
  if (['fully_paid', 'paid', 'full', 'fullypaid', 'completed'].includes(p)) {
    return 'full';
  }

  // part paid
  if (['part_paid', 'partial', 'partially_paid', 'partial_paid'].includes(p)) {
    return 'part';
  }

  return '';
},


        updateHoverSlotFromEvent(ev: any, evt: MouseEvent) {
    const target = evt.currentTarget as HTMLElement | null;
    if (!target) return;

    // find the staff column
    const colEl = target.closest('[data-staff-id]') as HTMLElement | null;
    if (!colEl) return;

    const rect = colEl.getBoundingClientRect();
    const offsetY = evt.clientY - rect.top;

    let row = Math.floor(offsetY / this.slotPx);
    row = Math.max(0, Math.min(this.rows - 1, row));

    this.hoverRowIndex = row;
    this.hoverStaffId = ev.staffId;
},

        onStaffColumnMouseLeave() {
    this.shrunkenEventId = null;
},

openSaleEdit(saleId: number | string) {
  // close completed booking offcanvas first
  this.completedBookingShow = false;

  // open sale edit offcanvas
  this.saleEditShow = true;
},

formatMoney0(val: number): string {
  const n = Number(val || 0);
  if (!n) return '0';
  return n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
},

bookingIdFromEvent(ev: any): string | null {
  const id = ev?.bookingId ?? ev?.booking_id ?? null;
  return id != null ? String(id) : null;
},

_applyPaymentInfoToUI(bookingId: string, info: { total: number; paid: number; remaining: number; status: any }) {
  const payload = { ...info, fetchedAt: Date.now() };

  // cache
  this.hoverPaymentCache[bookingId] = payload;

  // update hover card if it is currently showing the same booking
  if (this.bookingHoverEvent && this.bookingIdFromEvent(this.bookingHoverEvent) === bookingId) {
    this.bookingHoverEvent = {
      ...this.bookingHoverEvent,
      _paymentInfo: payload,
      paymentStatus: info.status,
      payment_status: info.status,
    };
  }

  // update the calendar event badge immediately (eventsLocal)
  const list = Array.isArray(this.eventsLocal) ? this.eventsLocal : [];
  list.forEach((e: any) => {
    const bid = e.booking_id ?? e.bookingId ?? e.booking?.id ?? null;
    if (bid && String(bid) === bookingId) {
      e.payment_status = info.status;
      e.paymentStatus = info.status;
    }
  });
},

_extractPaymentInfoFromDetails(evOrId: any, data: any) {
  const summary = data?.summary || {};
  const sale = data?.sale || {};
  const booking = data?.booking || {};

  const total = Number(summary.total ?? sale.total ?? booking.total_amount ?? booking.total ?? 0) || 0;
  const paid = Number(summary.total_paid ?? sale.paid_amount ?? sale.paid ?? 0) || 0;
  const remaining = Number(summary.remaining ?? Math.max(0, total - paid)) || 0;

  let status: 'fully_paid' | 'part_paid' | 'pending' = 'pending';
  if (total > 0 && remaining <= 0) status = 'fully_paid';
  else if (paid > 0) status = 'part_paid';

  return { total, paid, remaining, status };
},

bookingHoverSaleText(ev: any): string {
  if (!ev) return '';

  const bid = this.bookingIdFromEvent(ev);
  if (!bid) return '';

  const info = (ev as any)._paymentInfo || this.hoverPaymentCache[bid];
  if (!info) return '';

  // Only show for part-paid or fully-paid (like your requirement)
  if (info.status === 'fully_paid') {
    const amount = info.total > 0 ? info.total : info.paid;
    if (!amount) return '';
    return `${this.currencySymbol} ${this.formatMoney0(amount)} Fully paid`;
  }

  if (info.status === 'part_paid') {
    const amount = info.paid;
    if (!amount) return '';
    return `${this.currencySymbol} ${this.formatMoney0(amount)} Part paid`;
  }

  return '';
},

async ensureHoverPaymentLoaded(ev: any) {
  // don’t load for blocked / drafts
  const status = String(ev?.status || '').toLowerCase();
  if (status === 'blocked_time' || ev?.isBlockedTime) return;

  const bookingId = this.bookingIdFromEvent(ev);
  if (!bookingId) return;

  // cached?
  const cached = this.hoverPaymentCache[bookingId];

// use cache only if it is fresh (e.g., 2 seconds). otherwise refetch.
if (cached) {
  const age = Date.now() - Number(cached.fetchedAt || 0);
  if (age < 2000) {
    if (this.bookingHoverEvent && this.bookingIdFromEvent(this.bookingHoverEvent) === bookingId) {
      this.bookingHoverEvent = { ...this.bookingHoverEvent, _paymentInfo: cached };
    }
    return;
  }
}


  // prevent duplicate calls
  if (this.hoverPaymentLoading[bookingId]) return;
  this.hoverPaymentLoading[bookingId] = true;

  try {
    // you already use this endpoint elsewhere
    const res = await axios.get(`/bookings/${bookingId}/details`);
    const data = res.data || {};
    const summary = data.summary || {};
    const sale = data.sale || {};
    const booking = data.booking || {};

    const total =
      Number(summary.total ?? sale.total ?? booking.total ?? booking.total_amount ?? ev.total ?? 0) || 0;

    const paid =
      Number(summary.total_paid ?? sale.paid_amount ?? sale.paid ?? booking.paid_amount ?? 0) || 0;

    const remaining =
      Number(summary.remaining ?? booking.remaining_amount ?? Math.max(0, total - paid)) || 0;

    // normalize status
    let norm: 'fully_paid' | 'part_paid' | 'pending' = 'pending';
    if (total > 0 && remaining <= 0) norm = 'fully_paid';
    else if (paid > 0) norm = 'part_paid';

    const info = { total, paid, remaining, status: norm };

    this._applyPaymentInfoToUI(bookingId, info);


    // If the hover card is still showing the same booking, update it immediately
    if (this.bookingHoverEvent && this.bookingIdFromEvent(this.bookingHoverEvent) === bookingId) {
      this.bookingHoverEvent = {
        ...this.bookingHoverEvent,
        _paymentInfo: info,
        // optional: sync badge source too
        paymentStatus: norm,
        payment_status: norm,
      };
    }
  } catch (e) {
    // ignore silently (hover should not break UI)
  } finally {
    this.hoverPaymentLoading[bookingId] = false;
  }
},


closeSaleEdit() {
  this.saleEditShow = false;

  // reopen completed booking offcanvas
  this.completedBookingShow = true;
},

async onSaleEdited() {
 
  const bookingId = this.bookingPanel.booking?.id;
  if (!bookingId) return;

  try {
    this.saleEditLoading = true;
    await this.loadBookingDetails(bookingId, { preferCompleted: true });
  } finally {
    this.saleEditLoading = false;
  }
},


        onAddAppointmentSameSlot(ev: any) {
    // find full staff object
    const staffList: any[] = Array.isArray(this.staff)
        ? (this.staff as any[])
        : [];
    const staffObj =
        staffList.find(
            (s: any) => String(s.id) === String(ev.staffId),
        ) || null;

    if (!staffObj) return;

    // normalise start/end as Date
    const start: Date =
        ev.start instanceof Date
            ? ev.start
            : new Date(ev.startISO || ev.start || '');
    const end: Date =
        ev.end instanceof Date
            ? ev.end
            : new Date(ev.endISO || ev.end || '');

    if (!start || isNaN(start.getTime()) || !end || isNaN(end.getTime())) {
        return;
    }

    // reuse your existing booking flow
    this.$store.commit('OPEN_SELECT_SERVICE', {
        staff: staffObj,
        dateIso: this.isoDate,
        startIso: this.formatLocalDateTime(start),
        endIso: this.formatLocalDateTime(end),
    });
},

handleBookingClientUpdated(payload: {
        bookingId: number | string;
        client: any | null;
    }) {
        if (!payload || !payload.bookingId) return;

        const bookingIdStr = String(payload.bookingId);
        const client = payload.client || null;

        const name =
            client?.name ||
            client?.full_name ||
            'Walk-in';
        const email = client?.email || '';

        const list = Array.isArray(this.eventsLocal)
            ? this.eventsLocal
            : [];

        list.forEach((e: any) => {
            const evBookingId =
                e.booking_id ?? e.bookingId ?? e.booking?.id ?? null;
            if (!evBookingId) return;

            if (String(evBookingId) === bookingIdStr) {
                e.client_name = name;
                e.clientName = name;
                e.client_email = email;
                e.clientEmail = email;
            }
        });

        if (this.bookingHoverEvent) {
            const hoverBookingId =
                this.bookingHoverEvent.bookingId ??
                this.bookingHoverEvent.booking_id ??
                null;

            if (
                hoverBookingId &&
                String(hoverBookingId) === bookingIdStr
            ) {
                this.bookingHoverEvent = {
                    ...this.bookingHoverEvent,
                    clientName: name,
                    clientEmail: email,
                };
            }
        }
    },

        async reloadWaitlist() {
    try {
        if (this.$refs.waitlistComponent?.fetchPending) {
            await this.$refs.waitlistComponent.fetchPending();
        }
    } catch (e) {
        // console.error("Waitlist reload failed", e);
    }
},


        closeCompletedBooking() {
    this.completedBookingShow = false;
},
 handleRebook(bookingId: number | string) {
        this.completedBookingShow = false;

        this.rebookBookingId = bookingId;

        this.rebookBannerShow = true;
    },

    cancelRebook() {
        this.rebookBannerShow = false;
        this.rebookBookingId = null;
    },
 async loadBookingDetails(
  bookingId: number | string,
  opts?: { preferCompleted?: boolean },
) {
  if (!bookingId) return;

  this.bookingPanelLoading = true;


  if (opts?.preferCompleted === true) {
    this.completedBookingShow = true;
    this.bookingPanelShow = false;
  } else if (opts?.preferCompleted === false) {
    this.completedBookingShow = false;
    this.bookingPanelShow = true;
  }

  try {
    const response = await axios.get(`/bookings/${bookingId}/details`);
    const data = response.data || {};

    this.bookingPanel.booking = data.booking || null;
    this.bookingPanel.sale = data.sale || null;
    this.bookingPanel.sales = Array.isArray(data.sales) ? data.sales : [];
    this.bookingPanel.summary = data.summary || null;

    const rawStatus = this.bookingPanel.booking?.status
      ? String(this.bookingPanel.booking.status).toLowerCase()
      : '';

    const isCompleted = rawStatus === 'completed' || rawStatus === 'done';

    this.completedBookingShow = isCompleted;
    this.bookingPanelShow = !isCompleted;
  } catch (e) {
    this.bookingPanel.booking = null;
    this.bookingPanel.sale = null;
    this.bookingPanel.sales = [];
    this.bookingPanel.summary = null;
  } finally {
    this.bookingPanelLoading = false;
  }
},


        openDesktopDatePicker() {
    const input = this.$refs.desktopDateInput as HTMLInputElement | undefined;
    if (!input) return;

    if (typeof (input as any).showPicker === 'function') {
        (input as any).showPicker();
    } else {
        input.click();
    }
},
async openBookingById(bookingId: number | string) {
    await this.loadBookingDetails(bookingId);
},


       onSlotMenuAddBlockedTime() {
  if (!this.canAddBlocktime) return;
  if (!this.slotMenuStaff || this.slotMenuRowIndex === null) return;

  const { startISO, endISO } = this.isoRangeForRow(this.slotMenuRowIndex);
  this.blockedTimeData = {
    staff: this.slotMenuStaff,
    date: this.isoDate,
    startIso: startISO,
    endIso: endISO,
  };
  this.blockedTimeShow = true;
  this.closeSlotMenu();
},

        onBlockedTimeSaved() {
            // console.log('Blocked time saved successfully');
        },
        reloadCalendar() {
            this.$inertia.reload();
        },
      async onEventClick(ev: any) {
    const status = (ev.status || '').toString().toLowerCase();

    // 1) Blocked time → open BlockedTime offcanvas only
    if (status === 'blocked_time' || ev.isBlockedTime) {
        const staffList: any[] = Array.isArray(this.staff)
            ? (this.staff as any[])
            : [];
        const staffObj =
            staffList.find(
                (s: any) => String(s.id) === String(ev.staffId),
            ) || {
                id: ev.staffId,
                name: this.staffNameFor(ev.staffId),
            };

        const startIso: string =
            ev.startISO || ev.startIso || ev.starts_at || '';
        const endIso: string =
            ev.endISO || ev.endIso || ev.ends_at || '';

        const dateIso =
            startIso && !Number.isNaN(Date.parse(startIso))
                ? new Date(startIso).toISOString().slice(0, 10)
                : this.isoDate;

        this.blockedTimeData = {
            id: ev.id,
            block_type: ev.blockType || ev.block_type || 'custom',
            description:
                ev.status === 'blocked_time' || ev.isBlockedTime
                    ? ev.label && !['Blocked Time', 'Lunch Break'].includes(ev.label)
                        ? ev.label
                        : ''
                    : ev.label || '',
            staff: staffObj,
            date: dateIso,
            startIso,
            endIso,
        } as any;

        this.blockedTimeShow = true;
        return;
    }

   const bookingId = ev.bookingId || ev.booking_id;
  if (!bookingId) return;

  const preCompleted = status === 'completed' || status === 'done';

  this.bookingPanelLoading = true;
  this.bookingPanel = { booking: null, sale: null, sales: [], summary: null };

  this.completedBookingShow = preCompleted;
  this.bookingPanelShow = !preCompleted;

  await this.loadBookingDetails(bookingId, { preferCompleted: preCompleted });
},



        closeBookingPanel() {
            this.bookingPanelShow = false;
        },

        bookingHoverStatus(ev: any): string {
            if (!ev || !ev.status) return 'Booked';

            if (ev.status === 'blocked_time' || ev.isBlockedTime) {
                return ev.blockType === 'lunch'
                    ? 'Lunch Break'
                    : 'Blocked Time';
            }

            const raw = String(ev.status).toLowerCase();
            const map: Record<string, string> = {
                booked: 'Booked',
                scheduled: 'Booked',
                confirmed: 'Booked',
                completed: 'Completed',
                done: 'Completed',
                cancelled: 'Cancelled',
                canceled: 'Cancelled',
                no_show: 'No-show',
                noshow: 'No-show',
                pending: 'Pending',
                pending_payment: 'Pending payment',
            };
            if (map[raw]) return map[raw];
            const pretty = raw.replace(/_/g, ' ');
            return pretty.charAt(0).toUpperCase() + pretty.slice(1);
        },

        bookingHoverTotal(ev: any): string {
    if (!ev || ev.status === 'blocked_time' || ev.isBlockedTime) {
        return '';
    }

    const rawVal =
        ev.total ??
        ev.total_amount ??
        ev.price ??
        ev.amount ??
        null;

    if (rawVal == null || rawVal === '') return '';

    if (typeof rawVal === 'number') {
        return rawVal.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    }

    const rawStr = String(rawVal).trim();

    const numeric = Number(rawStr.replace(/[^\d.-]/g, ''));
    if (!Number.isNaN(numeric)) {
        return numeric.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    }

    return rawStr;
},

 quickSaleDraftEventsForStaff(staffId: any): any[] {
        return this.quickSaleDraftEvents.filter(
            (ev: any) => String(ev.staffId) === String(staffId),
        );
    },
        bookingHoverDuration(ev: any): string {
            const mins =
                ev && ev.durationMinutes ? Number(ev.durationMinutes) : 0;
            if (!mins) return '';
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            if (h && m) return `${h}h ${m}min`;
            if (h) return `${h}h`;
            return `${m}min`;
        },

        onEventMouseEnter(ev: any, evt: MouseEvent) {
     if (ev.status !== 'blocked_time' && !ev.isBlockedTime) {
        this.shrunkenEventId = ev.id;
    }
this.updateHoverSlotFromEvent(ev, evt);
    const CARD_WIDTH = 380;
    const CARD_HEIGHT = 220;
    const GAP = 16;
    const PADDING = 12;

    if (this.bookingHoverLoadingTimeout) {
        clearTimeout(this.bookingHoverLoadingTimeout as number);
        this.bookingHoverLoadingTimeout = null;
    }

    this.bookingHoverLoading = true;

    const target =
        (evt.currentTarget as HTMLElement) ||
        ((evt.target as HTMLElement | null)?.closest(
            '.calendar-event',
        ) as HTMLElement | null);

    const singleStaffMode = this.filteredStaff.length === 1;

    let x = evt.clientX + GAP;
    let y = evt.clientY + GAP;
    let placement: 'down' | 'up' = 'down';

    if (target) {
        const rect = target.getBoundingClientRect();

              if (singleStaffMode) {
            // When only one staff member
            x = rect.left + rect.width / 2 - CARD_WIDTH / 2;
            y = rect.bottom + GAP;
            placement = 'down';
        } else {
            //  multiple staff members
            x = rect.left - CARD_WIDTH - GAP;
            y = rect.top + rect.height / 2 - CARD_HEIGHT / 2;

        if (x < PADDING) {
            x = rect.right + GAP;
        }

        if (y + CARD_HEIGHT > window.innerHeight - PADDING) {
            placement = 'up';
            y = Math.min(
                rect.bottom - CARD_HEIGHT,
                window.innerHeight - CARD_HEIGHT - PADDING,
            );
        } else if (y < PADDING) {
            y = PADDING;
        }
    }
    } else {
        if (y + CARD_HEIGHT > window.innerHeight - PADDING) {
            placement = 'up';
            y = evt.clientY - CARD_HEIGHT - GAP;
        }
        if (x + CARD_WIDTH > window.innerWidth - PADDING) {
            x = window.innerWidth - CARD_WIDTH - PADDING;
        }
    }

        // Ensure the card stays within viewport bounds
    x = Math.max(PADDING, Math.min(x, window.innerWidth - CARD_WIDTH - PADDING));
    
    if (singleStaffMode) {
        // Additional boundary checks for single staff mode
        if (placement === 'down') {
            y = Math.min(y, window.innerHeight - CARD_HEIGHT - PADDING);
        } else {
            y = Math.max(PADDING, y);
        }
    } else {
        y = Math.max(PADDING, Math.min(y, window.innerHeight - CARD_HEIGHT - PADDING));
    }


    this.bookingHoverEvent = ev;
    this.bookingHoverX = Math.max(PADDING, x);
    this.bookingHoverY = Math.max(PADDING, y);
    this.bookingHoverPlacement = placement;
    this.bookingHoverShow = true;
    this.ensureHoverPaymentLoaded(ev);


    this.bookingHoverLoadingTimeout = window.setTimeout(() => {
        this.bookingHoverLoading = false;
        this.bookingHoverLoadingTimeout = null;
    }, 220);
},


        onEventMouseLeave() {
    if (this.bookingHoverLoadingTimeout) {
        clearTimeout(this.bookingHoverLoadingTimeout as number);
        this.bookingHoverLoadingTimeout = null;
    }
    this.bookingHoverLoading = false;
    this.bookingHoverShow = false;
    this.bookingHoverEvent = null;
},


        staffNameFor(staffId: any): string {
            const list: any[] = Array.isArray(this.staff)
                ? (this.staff as any[])
                : [];
            const match = list.find(
                (s: any) => String(s.id) === String(staffId),
            );
            return match?.name || '';
        },

        onScrollCloseSlotMenu() {
    if (this.slotMenuShow) this.closeSlotMenu();
    if (this.bookingHoverShow) {
        this.bookingHoverShow = false;
        this.bookingHoverEvent = null;
    }
    this.shrunkenEventId = null;
},

        closeSlotMenu() {
            this.slotMenuShow = false;
            this.slotMenuStaff = null;
            this.slotMenuRowIndex = null;
        },

        openSlotMenu(staff: any, rowIndex: number, event: MouseEvent) {
            // Close hover card when opening slot menu
             if (!this.canOpenSlotMenu) return;
            this.bookingHoverShow = false;
            this.bookingHoverEvent = null;

            this.slotMenuStaff = staff;
            this.slotMenuRowIndex = rowIndex;
            this.slotMenuLabel = this.slotRangeLabel(rowIndex);

            const MENU_WIDTH = 288;
            const MENU_HEIGHT = 160;
            const PADDING = 12;

            let x = event.clientX;
            let yBelow = event.clientY + 8;
            let placement: 'down' | 'up' = 'down';

            if (yBelow + MENU_HEIGHT > window.innerHeight - PADDING) {
                placement = 'up';
                yBelow = event.clientY - MENU_HEIGHT - 8;
            }

            if (x + MENU_WIDTH > window.innerWidth - PADDING) {
                x = window.innerWidth - MENU_WIDTH - PADDING;
            }

            this.slotMenuX = Math.max(PADDING, x);
            this.slotMenuY = Math.max(PADDING, yBelow);
            this.slotMenuPlacement = placement;
            this.slotMenuShow = true;
        },

     paymentBadgeLabel(ev: any): string {
  const p = String(ev?.paymentStatus ?? ev?.payment_status ?? '').toLowerCase();

  if (['fully_paid', 'paid', 'full', 'fullypaid'].includes(p)) return 'Completed';
  if (['part_paid', 'partial', 'partially_paid', 'partial_paid'].includes(p)) return 'Part paid';

  return '';
},

paymentBadgeClass(ev: any): string {
  const p = String(ev?.paymentStatus ?? ev?.payment_status ?? '').toLowerCase();

  if (['fully_paid', 'paid', 'full', 'fullypaid'].includes(p))
    return 'bg-emerald-100 text-emerald-700 border-emerald-200';

  if (['part_paid', 'partial', 'partially_paid', 'partial_paid'].includes(p))
    return 'bg-amber-100 text-amber-700 border-amber-200';

  return 'bg-neutral-100 text-neutral-700 border-neutral-200';
},



     handleRootClick() {
    if (this.slotMenuShow) this.slotMenuShow = false;
    if (this.bookingHoverShow) {
        this.bookingHoverShow = false;
        this.bookingHoverEvent = null;
    }
    this.shrunkenEventId = null;
        if (this.teamMenuOpen) this.teamMenuOpen = false;
        if (this.branchMenuOpen) this.branchMenuOpen = false;
},

        normalizeOpeningHours(raw: any): any[] {
            if (!raw) return [];
            if (Array.isArray(raw)) return raw;

            if (typeof raw === 'string') {
                try {
                    const parsed = JSON.parse(raw);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    // console.error('Invalid opening_hours JSON', e);
                    return [];
                }
            }

            return [];
        },

        timeOfDayToMinutes(time: string): number {
            if (!time) return 0;
            const [hStr, mStr] = time.split(':');
            const h = parseInt(hStr || '0', 10);
            const m = parseInt(mStr || '0', 10);
            if (Number.isNaN(h) || Number.isNaN(m)) return 0;
            return h * 60 + m;
        },

        rowIsInsideOpening(rowIndex: number): boolean {
            const intervals = this.todaysOpeningIntervals;
            if (!intervals.length) return false;

            const slotStart = rowIndex * this.slotMinutes;
            const slotEnd = slotStart + this.slotMinutes;

            return intervals.some(
                (iv) => slotEnd > iv.start && slotStart < iv.end,
            );
        },

        rowClass(rowIndex: number): string {
            const base = this.isHourRow(rowIndex)
                ? 'relative border-b border-neutral-300'
                : 'relative border-b border-neutral-100';

            return this.rowIsInsideOpening(rowIndex)
                ? base
                : `${base} closed-slot`;
        },

        layoutEvents(events: any[]): any[] {
            if (!events || !events.length) return [];

            const sorted = [...events].sort((a, b) => {
                const aStart = (a.start as Date).getTime();
                const bStart = (b.start as Date).getTime();
                if (aStart !== bStart) return aStart - bStart;
                return (a.end as Date).getTime() - (b.end as Date).getTime();
            });

            const result: any[] = [];
            let cluster: any[] = [];
            let clusterEnd: number | null = null;

            const flushCluster = () => {
                if (!cluster.length) return;

                const lanesEnd: number[] = [];
                const decorated: { ev: any; lane: number }[] = [];

                cluster.forEach((ev) => {
                    const startMs = (ev.start as Date).getTime();
                    const endMs = (ev.end as Date).getTime();

                    let laneIndex = lanesEnd.findIndex((end) => startMs >= end);
                    if (laneIndex === -1) {
                        laneIndex = lanesEnd.length;
                        lanesEnd.push(endMs);
                    } else {
                        lanesEnd[laneIndex] = endMs;
                    }

                    decorated.push({ ev, lane: laneIndex });
                });

                const colCount = lanesEnd.length || 1;

                decorated.forEach(({ ev, lane }) => {
                    result.push({
                        ...ev,
                        _colIndex: lane,
                        _colCount: colCount,
                    });
                });

                cluster = [];
                clusterEnd = null;
            };

            for (const ev of sorted) {
                const startMs = (ev.start as Date).getTime();
                const endMs = (ev.end as Date).getTime();

                if (!cluster.length) {
                    cluster.push(ev);
                    clusterEnd = endMs;
                    continue;
                }

                if (startMs < (clusterEnd as number)) {
                    cluster.push(ev);
                    if (endMs > (clusterEnd as number)) {
                        clusterEnd = endMs;
                    }
                } else {
                    flushCluster();
                    cluster.push(ev);
                    clusterEnd = endMs;
                }
            }

            flushCluster();

            return result;
        },

        updateViewportMode() {
            if (typeof window === 'undefined') return;
            const w = window.innerWidth;
            const h = window.innerHeight;

            this.isCompactViewport = w <= 1000 || h <= 478;
        },

        formatLocalDateTime(date: Date): string {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            const hh = String(date.getHours()).padStart(2, '0');
            const mm = String(date.getMinutes()).padStart(2, '0');
            const ss = String(date.getSeconds()).padStart(2, '0');
            return `${y}-${m}-${d}T${hh}:${mm}:${ss}`;
        },

        isoRangeForRow(rowIndex: number) {
            const [y, m, d] = this.isoDate
                .split('-')
                .map((n) => parseInt(n, 10));
            const start = new Date(y, m - 1, d, 0, 0, 0, 0);
            start.setMinutes(start.getMinutes() + rowIndex * this.slotMinutes);
            const end = new Date(start);
            end.setMinutes(end.getMinutes() + this.slotMinutes);
            return {
                startISO: this.formatLocalDateTime(start),
                endISO: this.formatLocalDateTime(end),
            };
        },

        onDatePicked(event: Event) {
            const value = (event.target as HTMLInputElement).value;
            if (!value) return;

            const [y, m, d] = value.split('-').map((n) => parseInt(n, 10));
            const dt = new Date();
            dt.setFullYear(y, m - 1, d);
            dt.setHours(0, 0, 0, 0);
            this.selectedDate = dt;

            const params: any = { date: value };
            if (this.currentBranchId) params.branch_id = this.currentBranchId;
            router.visit(route('calendar.index', params));
        },

        scrollToStaffColumn(staffId: any) {
            const refs = this.$refs as any;
            const scrollEl = refs.scrollArea as HTMLElement | undefined;
            const gutterEl = refs.timeGutter as HTMLElement | undefined;
            if (!scrollEl || !gutterEl) return;

            const col = scrollEl.querySelector(
                `[data-staff-id="${staffId}"]`,
            ) as HTMLElement | null;
            if (!col) return;

            const containerRect = scrollEl.getBoundingClientRect();
            const gutterRect = gutterEl.getBoundingClientRect();
            const columnRect = col.getBoundingClientRect();

            const currentOffset = columnRect.left - containerRect.left;
            const targetOffset = gutterRect.width;

            const delta = currentOffset - targetOffset;
            scrollEl.scrollLeft += delta;
        },

        onAdd() {
            alert(`Add appointment on ${this.isoDate}`);
        },

        moveDays(delta: number) {
            const d = new Date(this.selectedDate);
            d.setDate(d.getDate() + delta);
            d.setHours(0, 0, 0, 0);
            this.selectedDate = d;

            const params: any = { date: this.isoDate };
            if (this.currentBranchId) params.branch_id = this.currentBranchId;
            router.visit(route('calendar.index', params));
        },

        goToday() {
            const d = new Date();
            d.setHours(0, 0, 0, 0);
            this.selectedDate = d;

            const params: any = { date: this.isoDate };
            if (this.currentBranchId) params.branch_id = this.currentBranchId;
            router.visit(route('calendar.index', params));
        },

        fmtTimeFull(iso: string): string {
            return new Date(iso).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            });
        },

       onSlotMenuAddAppointment() {
  if (!this.canAddBooking) return;
  if (this.slotMenuStaff == null || this.slotMenuRowIndex == null) return;

  const { startISO, endISO } = this.isoRangeForRow(this.slotMenuRowIndex);

  this.$store.commit('OPEN_SELECT_SERVICE', {
    staff: this.slotMenuStaff,
    dateIso: this.isoDate,
    startIso: startISO,
    endIso: endISO,
  });

  this.closeSlotMenu();
},

        slotAriaLabel(staff: any, rowIndex: number): string {
            const { startISO, endISO } = this.isoRangeForRow(rowIndex);
            return `Create at ${staff.name}, ${this.fmtTime(startISO)}–${this.fmtTime(
                endISO,
            )}`;
        },

        slotRangeLabel(rowIndex: number): string {
            const { startISO, endISO } = this.isoRangeForRow(rowIndex);
            return `${this.fmtTime(startISO)} – ${this.fmtTime(endISO)}`;
        },

        initials(name: string): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 3);
        },

        isHourRow(rowIndex: number): boolean {
            return (rowIndex * this.slotMinutes) % 60 === 0;
        },

        labelForRow(rowIndex: number): string {
            if (!this.isHourRow(rowIndex)) return '';
            const minutes = rowIndex * this.slotMinutes;
            const hh = String(Math.floor(minutes / 60)).padStart(2, '0');
            return `${hh}:00`;
        },

        fmtTime(iso: string): string {
            return new Date(iso).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        timeLabelToDate(label: string): Date {
            const trimmed = String(label).trim();
            const [timePart, meridianRaw] = trimmed.split(' ');
            const [hStr, mStr] = timePart.split(':');
            let h = parseInt(hStr || '0', 10);
            const m = parseInt(mStr || '0', 10);
            const meridian = meridianRaw ? meridianRaw.toUpperCase() : null;

            if (meridian === 'PM' && h < 12) h += 12;
            if (meridian === 'AM' && h === 12) h = 0;

            const d = new Date(this.isoDate + 'T00:00:00');
            d.setHours(h, m, 0, 0);
            return d;
        },

        draftEventsForStaff(staffId: any): any[] {
            return this.draftEvents.filter(
                (ev: any) => String(ev.staffId) === String(staffId),
            );
        },

        savedEventsForStaff(staffId: any): any[] {
            const dayStart = new Date(this.isoDate + 'T00:00:00');
            const dayEnd = new Date(this.isoDate + 'T23:59:59.999');
            const sid = String(staffId);

            return this.normalizedEvents.filter((ev: any) => {
                return (
                    String(ev.staffId) === sid &&
                    ev.start >= dayStart &&
                    ev.start <= dayEnd
                );
            });
        },
   openMobileDatePicker() {
  const input = this.$refs.mobileDateInput as HTMLInputElement | undefined;
  if (!input) return;
  input.focus();
  input.click();
},


    onMobileDatePicked(event: Event) {
        const value = (event.target as HTMLInputElement).value;
        if (!value) return;

        const [y, m, d] = value.split('-').map((n) => parseInt(n, 10));
        const dt = new Date();
        dt.setFullYear(y, m - 1, d);
        dt.setHours(0, 0, 0, 0);
        this.selectedDate = dt;

        const params: any = { date: value };
        if (this.currentBranchId) params.branch_id = this.currentBranchId;
        router.visit(route('calendar.index', params));

        this.mobileDateOpen = false;
    },

       allEventsForStaff(staffId: any): any[] {
    const events = [
        ...this.savedEventsForStaff(staffId),
        ...this.draftEventsForStaff(staffId),
        ...this.quickSaleDraftEventsForStaff(staffId),
    ];

    return this.layoutEvents(events);
},

        // === slot color depends on booking status ===
        eventBackgroundColor(ev: any): string {
            const raw = (ev.status || '').toString().toLowerCase();

            if (raw === 'blocked_time' || ev.isBlockedTime) {
                return '#4a5568';
            }

            // Completed events
            if (raw === 'completed' || raw === 'done') {
                return '#e5e7eb';
            }

            // Regular events
            return ev.color || 'var(--brand, var(--brand-fallback))';
        },

        eventBorderColor(ev: any): string {
            const raw = (ev.status || '').toString().toLowerCase();
            if (raw === 'completed' || raw === 'done') {
                return '#9ca3af'; // tailwind gray-400
            }
            return ev.color || 'var(--brand, var(--brand-fallback))';
        },

        eventStyle(ev: any): Record<string, string> {
            const start = ev.start as Date;
            const end = ev.end as Date;

            const startMinutes = start.getHours() * 60 + start.getMinutes();
            const endMinutes = end.getHours() * 60 + end.getMinutes();
            const durationMinutes = Math.max(
                this.slotMinutes,
                endMinutes - startMinutes,
            );

            const topPx = (startMinutes / this.slotMinutes) * this.slotPx;
            const heightPx =
                (durationMinutes / this.slotMinutes) * this.slotPx - 2;

            const colCount = ev._colCount || 1;
            const colIndex = ev._colIndex || 0;

            const gapPx = 3;
            const width = `calc(100% / ${colCount} - ${gapPx}px)`;
            const left = `calc((100% / ${colCount}) * ${colIndex} + ${
                gapPx / 2
            }px)`;

            const rawStatus = (ev.status || '').toString().toLowerCase();
            const isCompleted =
                rawStatus === 'completed' || rawStatus === 'done';
            const isBlockedTime =
                rawStatus === 'blocked_time' || ev.isBlockedTime;
            const bg = this.eventBackgroundColor(ev);
            const border = this.eventBorderColor(ev);

            return {
                top: topPx + 'px',
                height: Math.max(heightPx, this.slotPx) + 'px',
                width,
                left,
                backgroundColor: bg,
                borderColor: border,
                borderWidth: isCompleted ? '0px' : '1px',
                opacity: isBlockedTime ? '0.9' : '1',
            };
        },

        toggleTeamMenu() {
            this.teamMenuOpen = !this.teamMenuOpen;
            if (this.teamMenuOpen) this.branchMenuOpen = false;
        },

        toggleBranchMenu() {
            this.branchMenuOpen = !this.branchMenuOpen;
            if (this.branchMenuOpen) this.teamMenuOpen = false;
        },

        selectBranch(id: number | string | null) {
            this.currentBranchId = id ? String(id) : '';
            const params: any = { date: this.isoDate };
            if (this.currentBranchId) params.branch_id = this.currentBranchId;
            this.branchMenuOpen = false;
            router.visit(route('calendar.index', params), {
                preserveScroll: true,
            });
        },
            toggleSelectAllStaff(event: Event) {
        const isChecked = (event.target as HTMLInputElement).checked;
        
        if (!Array.isArray(this.staff)) {
            this.selectedStaffIds = [];
            return;
        }

        if (isChecked) {
            // Select all staff members
            this.selectedStaffIds = this.staff.map((s: any) => s.id);
        } else {
            // Deselect all
            this.selectedStaffIds = [];
        }
    },
        handleDocumentClick(event) {
        const teamButton = this.$el.querySelector('[data-team-button]');
        const teamDropdown = this.$el.querySelector('[data-team-dropdown]');
        
        if (teamDropdown && teamButton) {
            const isClickInside = teamDropdown.contains(event.target) || teamButton.contains(event.target);
            if (!isClickInside && this.teamMenuOpen) {
                this.teamMenuOpen = false;
            }
        }
        
        // Same for branch dropdown
        const branchButton = this.$el.querySelector('[data-branch-button]');
        const branchDropdown = this.$el.querySelector('[data-branch-dropdown]');
        
        if (branchDropdown && branchButton) {
            const isClickInside = branchDropdown.contains(event.target) || branchButton.contains(event.target);
            if (!isClickInside && this.branchMenuOpen) {
                this.branchMenuOpen = false;
            }
        }
    },
    },
});
</script>

<style scoped>
:root {
    --brand-fallback: #b21600;
}

:root {
    --brand: color-mix(in srgb, #ff2000 70%, black);
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}



.calendar-event-content {
    display: flex;
    height: 100%;
    padding: 4px 6px;
}

.calendar-event-colorbar {
    width: 3px;
    border-radius: 6px 0 0 6px;
    margin-right: 4px;
    background-color: rgba(255, 255, 255, 0.4);
}

.calendar-event-body {
  flex: 1;
  min-width: 0;
  position: relative;   
  padding-right: 22px;   
}


.dropdown-enter-active,
.dropdown-leave-active {
    transition:
        opacity 0.18s ease,
        transform 0.18s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
}

.closed-slot {
    background-image: repeating-linear-gradient(
        60deg,
        rgba(0, 0, 0, 0.015) 0px,
        rgba(0, 0, 0, 0.015) 4px,
        rgba(0, 0, 0, 0.04) 4px,
        rgba(0, 0, 0, 0.04) 6px
    );
}

/* slot menu transition */
.slot-menu-enter-active,
.slot-menu-leave-active {
    transition:
        opacity 0.16s ease,
        transform 0.16s ease;
}

.slot-menu-enter-from,
.slot-menu-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

.slot-menu-down.slot-menu-enter-from {
    transform: translateY(-4px) scale(0.96);
}

.slot-menu-up.slot-menu-enter-from {
    transform: translateY(4px) scale(0.96);
}

/* Hover card transition */
.booking-hover-enter-active,
.booking-hover-leave-active {
    transition:
        opacity 0.16s ease,
        transform 0.16s ease;
}

.booking-hover-enter-from,
.booking-hover-leave-to {
    opacity: 0;
    transform: translateY(4px) scale(0.97);
}

.booking-hover-card-up.booking-hover-enter-from {
    transform: translateY(-4px) scale(0.97);
}

.booking-hover-card {
    border-radius: 20px;
    background-color: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.35);
    overflow: hidden;
    backdrop-filter: blur(14px);
    position: fixed;
}

.booking-hover-card::before {
    content: '';
    position: absolute;
    left: 18px;
    right: 18px;
    top: -6px;
    height: 18px;
    border-radius: 999px;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.02),
        rgba(0, 0, 0, 0.08)
    );
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.25);
    z-index: -2;
}

.booking-hover-card::after {
    content: '';
    position: absolute;
    left: 40px;
    right: 40px;
    top: -9px;
    height: 20px;
    border-radius: 999px;
    background: #ffffff;
    z-index: -1;
}

.booking-hover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 18px;
    background: var(--hover-color, var(--brand, var(--brand-fallback)));
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
}

.booking-hover-time {
    font-variant-numeric: tabular-nums;
    font-size: 13px;
}

.booking-hover-status {
    padding: 2px 10px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.18);
    font-size: 10px;
    font-weight: 600;
}

.booking-hover-client-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.booking-hover-service-name {
    font-size: 15px;
    font-weight: 500;
    color: #111827;
}

.booking-hover-price {
    font-size: 15px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.booking-hover-skeleton {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.booking-hover-skel-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.booking-hover-skel-avatar {
    width: 40px;
    height: 40px;
    border-radius: 999px;
}

.booking-hover-skel-lines {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.booking-hover-skel-service {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.shimmer {
    position: relative;
    overflow: hidden;
    background: #e5e7eb;
    border-radius: 999px;
}

.line-lg {
    height: 14px;
    width: 70%;
}

.line-md {
    height: 12px;
    width: 60%;
}

.line-sm {
    height: 10px;
    width: 45%;
}

.line-xs {
    height: 8px;
    width: 35%;
}

.shimmer::before {
    content: '';
    position: absolute;
    inset: 0;
    transform: translateX(-100%);
    background: linear-gradient(
        120deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.9) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: shimmer 1.2s ease-in-out infinite;
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}

.booking-hover-body {
    position: relative;
    padding: 14px 18px 16px 22px;
    background: #ffffff;
}

.booking-hover-body::before {
    content: '';
    position: absolute;
    inset-y: 10px;
    left: 6px;
    width: 3px;
    border-radius: 999px;
    background: var(--hover-color, var(--brand, var(--brand-fallback)));
}

.booking-hover-client-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.booking-hover-avatar {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    display: grid;
    place-items: center;
    font-size: 15px;
    font-weight: 600;
    background: color-mix(
        in srgb,
        var(--hover-color, var(--brand, var(--brand-fallback))) 10%,
        white
    );
    color: color-mix(
        in srgb,
        var(--hover-color, var(--brand, var(--brand-fallback))) 70%,
        black
    );
}

.booking-hover-client-meta {
    min-width: 0;
}

.booking-hover-client-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.booking-hover-client-email {
    margin-top: 2px;
    font-size: 12px;
    color: #6b7280;
}

.booking-hover-service-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-top: 14px;
}

.booking-hover-service-main {
    min-width: 0;
}

.booking-hover-service-name {
    font-size: 13px;
    font-weight: 500;
    color: #111827;
}

.booking-hover-service-meta {
    margin-top: 2px;
    font-size: 11px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.booking-hover-price {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.calendar-event.blocked-time {
    opacity: 0.9;
}

.calendar-event.blocked-time .calendar-event-colorbar {
    background-color: #2d3748 !important;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-right-enter-active,
.slide-right-leave-active {
    transition: transform 0.25s ease-out;
}
.slide-right-enter-from,
.slide-right-leave-to {
    transform: translateX(100%);
}
.rebook-bar-enter-active,
.rebook-bar-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.rebook-bar-enter-from,
.rebook-bar-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
/* :deep(.relative) {
    overflow: visible !important;
}

:deep([class*="relative"]) {
    overflow: visible !important;
} */

/* Red checkbox styling */
input[type="checkbox"] {
    accent-color: #dc2626;
}

/* Fallback for older browsers */
input[type="checkbox"]:checked {
    background-color: #dc2626;
    border-color: #dc2626;
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

/* Focus ring */
input[type="checkbox"]:focus {
    --tw-ring-color: rgba(220, 38, 38, 0.5);
    ring-width: 2px;
}

/* Hover state */
input[type="checkbox"]:hover {
    border-color: #dc2626;
}
.calendar-event {
    position: absolute;
    border-radius: 6px;
    border-width: 1px;
    border-style: solid;
    overflow: hidden;
    background-color: var(--brand, var(--brand-fallback));
    transform-origin: left center;
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease;
}

/* applied via Vue when the event is active */
.calendar-event--shrunk {
    transform: scaleX(0.86); /* tweak to get the gap size you like */
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.25);
}
.slot-hover-indicator {
    z-index: 5;
}
.no-show-glow {
  box-shadow:
    0 0 0 2px rgba(244, 63, 94, 0.22),
    0 0 10px rgba(244, 63, 94, 0.9);
}


</style>
