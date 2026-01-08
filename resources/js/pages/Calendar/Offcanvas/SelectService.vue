<template>
    <transition name="fade">
        <div v-if="show" class="pointer-events-none fixed inset-0 z-[100] flex">
<div
  class="hidden flex-1 bg-neutral-900/30 md:block pointer-events-auto"
  @click.stop.prevent="onBackdropClick"
/>
            <div
                class="pointer-events-auto relative flex h-full w-full max-w-4xl flex-col bg-white shadow-2xl md:ml-auto md:rounded-l-2xl">
                <!-- HEADER -->
                <header class="flex items-center justify-between border-b px-4 py-3 sm:px-6 sm:py-4">
                    <div v-if="step === 'summary'">
                        <div class="flex items-center justify-between">
                            <div class="space-y-2">
                                <!-- BEAUTIFUL DATE PICKER BUTTON + POPOVER -->
                                <div class="relative inline-block datepicker-wrapper mb-0">
                                    <button type="button" @click="toggleDatePicker"
                                        class="cursor-pointer inline-flex items-center gap-2 rounded-full bg-white   text-xl sm:text-2xl font-semibold text-neutral-900 ">
                                        <i class="bx bx-calendar text-xl text-orange-500"></i>
                                        <span>
                                            {{ appointmentDateLabel || 'Pick a date' }}
                                            <i class="bx bx-chevron-down text-lg text-neutral-500"></i>
                                        </span>
                                    </button>

                                    <transition name="fade">
                                        <div v-if="showDatePicker"
                                            class="absolute left-0 z-20 mt-3 w-80 rounded-2xl border bg-white p-3 shadow-2xl">
                                            <!-- Month header -->
                                            <div class="mb-2 flex items-center justify-between">
                                                <button type="button"
                                                    class="inline-flex size-8 items-center justify-center rounded-full hover:bg-neutral-100"
                                                    @click="prevMonth">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                        fill="none" class="h-4 w-4 text-neutral-600">
                                                        <path d="M12.5 15 7.5 10l5-5" stroke="currentColor"
                                                            stroke-width="1.6" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                <!-- CLICKABLE MONTH / YEAR -->
                                                <button type="button"
                                                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium text-neutral-900 hover:bg-neutral-50"
                                                    @click.stop="toggleMonthYearPicker">
                                                    <span>{{ monthYearLabel }}</span>
                                                    <i class="bx text-xs"
                                                        :class="isMonthYearPickerOpen ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                                </button>

                                                <button type="button"
                                                    class="inline-flex size-8 items-center justify-center rounded-full hover:bg-neutral-100"
                                                    @click="nextMonth">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                        fill="none" class="h-4 w-4 text-neutral-600">
                                                        <path d="M7.5 5 12.5 10l-5 5" stroke="currentColor"
                                                            stroke-width="1.6" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- MONTH + YEAR PICKER (inside calendar popover) -->
                                            <transition name="fade">
                                                <div v-if="isMonthYearPickerOpen"
                                                    class="mb-2 rounded-xl bg-neutral-50 p-2">
                                                    <!-- Year chooser -->
                                                    <div class="mb-2 flex items-center justify-between">
                                                        <button type="button"
                                                            class="inline-flex size-7 items-center justify-center rounded-full hover:bg-neutral-100"
                                                            @click="changePickerYear(-1)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="none" class="h-4 w-4 text-neutral-600">
                                                                <path d="M11.5 14 8 10l3.5-4" stroke="currentColor"
                                                                    stroke-width="1.6" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </button>

                                                        <div class="text-xs font-semibold text-neutral-700">
                                                            {{ pickerYear ?? new Date().getFullYear() }}
                                                        </div>

                                                        <button type="button"
                                                            class="inline-flex size-7 items-center justify-center rounded-full hover:bg-neutral-100"
                                                            @click="changePickerYear(1)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="none" class="h-4 w-4 text-neutral-600">
                                                                <path d="m8.5 6 3.5 4-3.5 4" stroke="currentColor"
                                                                    stroke-width="1.6" stroke-linecap="round"
                                                                    stroke-linejoin="round" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <!-- Month grid -->
                                                    <div class="grid grid-cols-3 gap-1 text-xs sm:text-sm">
                                                        <button v-for="(m, idx) in monthNames" :key="m" type="button"
                                                            class="cursor-pointer rounded-lg px-2 py-1.5 text-center"
                                                            :class="[
                                                                idx === (pickerMonth ?? new Date().getMonth())
                                                                    ? 'bg-orange-600 text-white'
                                                                    : 'text-neutral-700 hover:bg-white'
                                                            ]" @click="selectPickerMonth(idx)">
                                                            {{ m }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </transition>


                                            <!-- Weekday labels -->
                                            <div
                                                class="grid grid-cols-7 gap-1 text-[11px] font-medium text-neutral-400">
                                                <div class="text-center py-1">Su</div>
                                                <div class="text-center py-1">Mo</div>
                                                <div class="text-center py-1">Tu</div>
                                                <div class="text-center py-1">We</div>
                                                <div class="text-center py-1">Th</div>
                                                <div class="text-center py-1">Fr</div>
                                                <div class="text-center py-1">Sa</div>
                                            </div>

                                            <!-- Days grid -->
                                            <div class="mt-1 grid grid-cols-7 gap-1 text-sm">
                                                <div v-for="day in monthDays" :key="day.key"
                                                    class="flex items-center justify-center">
                                                    <button v-if="!day.isPlaceholder" type="button"
                                                        class="cursor-pointer flex aspect-square w-full items-center justify-center rounded-full text-sm transition-colors"
                                                        :class="[
                                                            day.isCurrent
                                                                ? 'bg-orange-600 text-white hover:bg-orange-600'
                                                                : day.isToday
                                                                    ? 'border border-orange-500 text-orange-600'
                                                                    : 'text-neutral-700 hover:bg-neutral-50',
                                                        ]" @click="selectDate(day.date)">
                                                        {{ day.label }}
                                                    </button>
                                                    <span v-else class="aspect-square w-full"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- BEAUTIFUL TIME PICKER BUTTON + POPOVER -->
                                <div
                                    class="mt-1 flex flex-wrap items-center gap-2 text-sm text-neutral-500 sm:text-base">
                                    <div class="relative timepicker-wrapper">
                                        <button type="button" @click="toggleTimePicker"
                                            class="cursor-pointer inline-flex items-center gap-2 rounded-full  bg-white  text-sm font-medium text-neutral-800  ">
                                            <i class="bx bx-time text-lg text-orange-500"></i>
                                            <span class="hover:underline">{{ appointmentStartTimeLabel }}</span>
                                        </button>

                                        <transition name="fade">
                                            <div v-if="showTimePicker"
                                                class="absolute z-20 mt-2 w-32 max-h-64 overflow-y-auto rounded-2xl border bg-white p-2 shadow-2xl">
                                                <button v-for="t in timeOptions" :key="t" type="button"
                                                    class="cursor-pointer flex w-full items-center justify-between rounded-xl px-3 py-1.5 text-sm text-neutral-800 hover:bg-orange-50 "
                                                    :class="{
                                                        'bg-orange-600 text-white hover:bg-orange-600':
                                                            t === appointmentStartTimeInput,
                                                    }" @click="selectTime(t)">
                                                    <span>{{ formatTimeLabel(t) }}</span>
                                                </button>
                                            </div>
                                        </transition>
                                    </div>

                                    <span v-if="appointmentTimeLabel">
                                        – {{ appointmentTimeLabel.split('–')[1]?.trim() }}
                                    </span>
                                    <span v-if="appointmentRepeatLabel">
                                        · {{ appointmentRepeatLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <h1 class="text-2xl font-semibold text-neutral-900 sm:text-3xl">
                            Select a service
                        </h1>
                        <p v-if="staffMember || timeLabel" class="mt-1 text-sm text-neutral-500 sm:text-base">
                            <span v-if="staffMember">
                                For
                                <span class="font-medium">
                                    {{ staffMember.name }}
                                </span>
                            </span>
                            <span v-if="staffMember && timeLabel"> · </span>
                            <span v-if="timeLabel">{{ timeLabel }}</span>
                                                                    <div class="truncate text-xs text-neutral-500 sm:text-sm">
                                            <span v-if="selectedClient">
                                                Client:
                                                <strong>
                                                    {{ selectedClient.name }}
                                                </strong>
                                            </span>
                                            <span v-else>
                                                Client:
                                                <strong>Walk-in</strong>
                                            </span>
                                        </div>
                        </p>
                    </div>

                   <button
  type="button"
  class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-neutral-300 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
  @click.stop.prevent="onCloseButtonClick"
>
  <span class="sr-only">Close</span>
  ✕
</button>

                </header>

                <main class="flex min-h-0 flex-1 flex-col md:flex-row">
                    <!-- LEFT SIDE (CLIENT) -->
                    <aside class="flex w-full flex-col bg-neutral-50 transition-all duration-200 ease-out md:border-r"
                        :class="leftWidthClass">
                        <transition name="slide-left" mode="out-in">
                            <div :key="showClientList
                                ? 'client-list'
                                : selectedClient
                                    ? 'client-details'
                                    : 'client-empty'
                                " class="flex h-full flex-col">
                                <!-- Empty client -->
                                <div v-if="!showClientList && !selectedClient"
                                    class="flex h-full flex-col border-b md:border-b-0">
                                    <button type="button"
                                        class="flex h-full cursor-pointer flex-col items-center justify-center gap-4 px-4 py-4 text-left hover:bg-neutral-100 sm:px-7 sm:py-6"
                                        @click="openClientList">
                                        <div
                                            class="inline-flex size-12 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                            <span class="text-3xl font-semibold">+</span>
                                        </div>
                                        <div>
                                            <p class="text-center text-lg font-semibold text-neutral-900">
                                                Add client
                                            </p>
                                            <p
                                                class="mt-1 text-center text-sm leading-relaxed text-neutral-500 sm:text-base">
                                                Or leave empty for walk-ins.
                                            </p>
                                        </div>
                                    </button>
                                </div>

                                <!-- Client list -->
                                <div v-else-if="showClientList" class="flex h-full flex-col border-b md:border-b-0">
                                    <div class="flex items-center justify-between border-b px-4 py-3 sm:px-5 sm:py-4">
                                        <div>
                                            <h2 class="text-base font-semibold text-neutral-900 sm:text-lg">
                                                Select a client
                                            </h2>
                                            <p class="mt-0.5 text-xs text-neutral-500 sm:text-sm">
                                                Or keep it as a walk-in.
                                            </p>
                                        </div>
                                        <button type="button"
                                            class="cursor-pointer text-xs font-medium text-orange-600 hover:text-orange-700 sm:text-sm"
                                            @click="closeClientList">
                                            Done
                                        </button>
                                    </div>

                                    <div class="border-b px-4 pt-4 pb-3 sm:px-5">
                                        <div class="relative">
                                            <input v-model="clientSearch" type="text"
                                                class="w-full rounded-full border border-orange-200 bg-white px-3 py-2 text-xs placeholder:text-neutral-400 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:outline-none sm:text-sm"
                                                placeholder="Search client or leave empty" />
                                        </div>

                                        <div class="mt-3 space-y-6 text-xs sm:text-sm">
                                            <button type="button"
                                                class="flex w-full cursor-pointer items-center gap-2 rounded-lg bg-white px-3 py-4 text-left text-orange-700 shadow-sm hover:bg-orange-50"
                                                @click="createClient">
                                                <span
                                                    class="inline-flex size-6 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                                    +
                                                </span>
                                                <span class="font-medium">
                                                    Add new client
                                                </span>
                                            </button>

                                            <button type="button"
                                                class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-4 text-left hover:bg-neutral-100"
                                                :class="{
                                                    'bg-neutral-900 text-white hover:bg-neutral-900':
                                                        isWalkIn &&
                                                        !selectedClientId,
                                                }" @click="setWalkIn">
                                                <span
                                                    class="inline-flex size-6 items-center justify-center rounded-full bg-neutral-900 text-xs font-semibold text-white">
                                                    W
                                                </span>
                                                <div>
                                                    <div class="text-xs font-medium sm:text-sm">
                                                        Walk-in
                                                    </div>
                                                    <!-- <div class="text-[11px] text-neutral-400 sm:text-xs">
                                                        No client profile
                                                    </div> -->
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex-1 overflow-y-auto px-2 py-4">
                                        <div v-for="c in filteredClients" :key="c.id" class="mb-1">
                                            <button type="button"
                                                class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-4 text-left text-xs hover:bg-neutral-100 sm:text-sm"
                                                :class="{
                                                    'bg-neutral-900 text-white hover:bg-neutral-900':
                                                        selectedClientId ===
                                                        c.id,
                                                }" @click="selectClient(c)">
                                                <div
                                                    class="flex size-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-semibold text-neutral-700 sm:text-sm">
                                                    {{ initials(c.name || '') }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="truncate text-xs font-medium sm:text-sm">
                                                        {{ c.name }}
                                                    </div>
                                                    <div class="truncate text-[11px] text-neutral-500 sm:text-xs">
                                                        <span v-if="c.email">
                                                            {{ c.email }}
                                                        </span>
                                                        <span v-if="
                                                            c.email &&
                                                            c.phone
                                                        ">
                                                            ·
                                                        </span>
                                                        <span v-if="c.phone">
                                                            {{ c.phone }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>

                                        <p v-if="!filteredClients.length"
                                            class="px-3 py-4 text-center text-xs text-neutral-400 sm:text-sm">
                                            No clients found.
                                        </p>
                                    </div>
                                </div>

                                <!-- Selected client details -->
                                <div v-else-if="selectedClient" class="flex h-full flex-col bg-white">

                                    <!-- Header -->
                                    <div class="flex md:flex-col items-center gap-3 border-b px-6 py-6 md:text-center
           sm:flex-row sm:text-left sm:items-center sm:gap-4">

                                        <!-- Initials -->
                                        <div class="grid size-16 place-items-center rounded-full bg-neutral-100 text-2xl font-semibold text-neutral-500
             sm:size-14">
                                            {{ initials(selectedClient.name || '') }}
                                        </div>

                                        <!-- Name + Email -->
                                        <div
                                            class="sm:flex sm:flex-col sm:items-start sm:justify-center md:flex md:flex-col md:items-center md:justify-center">
                                            <div
                                                class="text-base font-semibold text-neutral-900 sm:text-lg sm:text-left">
                                                {{ selectedClient.name }}
                                            </div>
                                            <div v-if="selectedClient.email"
                                                class="mt-0.5 text-xs text-neutral-500 sm:text-sm sm:text-left">
                                                {{ selectedClient.email }}
                                            </div>
                                            <div v-if="selectedClient.phone"
                                                class="mt-0.5 text-xs text-neutral-500 sm:text-sm sm:text-left">
                                                {{ selectedClient.phone }}
                                            </div>
                                        </div>

                                        <!-- No-show badge -->
                                        <div v-if="noShowLabel"
                                            class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-[11px] font-medium text-rose-700 sm:text-xs sm:ml-auto">
                                            {{ noShowLabel }}
                                        </div>

                                    </div>

                                    <!-- Change client button -->
                                    <div class="mt-2 flex items-center justify-between px-6">
                                        <button type="button"
                                            class="cursor-pointer text-[12px] font-medium text-orange-600 hover:text-orange-700 sm:text-xs text-left"
                                            @click="openClientList">
                                            Change client
                                        </button>

                                        <button v-if="selectedClient" type="button"
                                            class="cursor-pointer text-[12px] font-medium text-orange-600 hover:text-orange-700 sm:text-xs text-right"
                                            @click="openEditClient">
                                            Edit client
                                        </button>
                                    </div>


                                </div>


                                <div v-else class="flex-1"></div>
                            </div>
                        </transition>
                    </aside>

                    <!-- RIGHT SIDE (SERVICES / SUMMARY / EDIT) -->
                    <section class="flex min-w-0 flex-1 flex-col overflow-hidden">
                        <transition name="slide-step" mode="out-in">
                            <!-- STEP 1: SERVICES -->
                            <div v-if="step === 'services'" key="services" class="flex min-h-0 flex-1 flex-col">
                                <div class="border-b px-4 py-3 sm:px-6">
                                    <div class="relative">
                                       <input
  v-model="serviceSearch"
  type="text"
  :disabled="servicesLoading"
  class="w-full rounded-full border border-orange-200 bg-white px-4 py-2 text-sm placeholder:text-neutral-400 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:outline-none sm:text-base disabled:opacity-60"
  placeholder="Search by service name"
/>

                                    </div>
                                </div>

                             <div class="flex-1 space-y-6 overflow-y-auto px-4 py-4 sm:px-6">
 <!-- LOADING (SKELETON) -->
<div v-if="servicesLoading" class="py-4" role="status" aria-live="polite" aria-busy="true">
  <span class="sr-only">Loading services…</span>

  <div class="space-y-6 animate-pulse">
    <!-- 3 fake groups -->
    <div v-for="g in 3" :key="`skg-${g}`">
      <!-- group header skeleton -->
      <div class="mb-2 flex items-center gap-2">
        <div class="h-4 w-28 rounded bg-neutral-200"></div>
        <div class="h-4 w-10 rounded-full bg-neutral-100"></div>
      </div>

      <!-- group list skeleton -->
      <div class="overflow-hidden rounded-xl border bg-white">
        <!-- 4 fake rows per group -->
        <div
          v-for="i in 4"
          :key="`skr-${g}-${i}`"
          class="flex w-full items-stretch justify-between gap-4 border-b px-4 py-3 last:border-b-0"
        >
          <div class="flex min-w-0 flex-1">
            <div class="my-1 mr-3 w-[3px] self-stretch rounded-full bg-neutral-200"></div>

            <div class="min-w-0 flex-1">
              <div class="h-4 w-3/5 rounded bg-neutral-200"></div>
              <div class="mt-2 h-3 w-2/5 rounded bg-neutral-100"></div>

              <!-- optional pill skeleton -->
              <div class="mt-2 h-5 w-40 rounded-full bg-neutral-100"></div>
            </div>
          </div>

          <div class="flex items-center gap-3 pl-2">
            <div class="h-4 w-16 rounded bg-neutral-200"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- ERROR -->
  <div v-else-if="servicesLoadError" class="py-10 text-center">
    <div class="text-sm text-rose-600">{{ servicesLoadError }}</div>
    <button
      type="button"
      class="mt-3 rounded-full border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-900 hover:bg-neutral-100"
      @click="fetchServices"
    >
      Retry
    </button>
  </div>

  <!-- CONTENT -->
  <template v-else>
                                    <template v-for="group in filteredGroups" :key="group.key">
                                        <div>
                                            <div
                                                class="mb-2 flex items-center gap-2 text-sm font-semibold tracking-wide text-neutral-600 uppercase sm:text-base">
                                                <span class="first-letter:uppercase">
                                                    {{ group.name }}
                                                </span>
                                                <span
                                                    class="rounded-full bg-neutral-100 px-2 py-0.5 text-[11px] font-medium text-neutral-500 sm:text-xs">
                                                    {{ group.rows.length }}
                                                </span>
                                            </div>

                                            <div class="overflow-hidden rounded-xl border bg-white">
                                                <button v-for="row in group.rows" :key="row.uid" type="button"
                                                    @click="toggleRow(row)"
                                                    class="flex w-full cursor-pointer items-stretch justify-between gap-4 border-b px-4 py-3 text-left text-sm transition-colors last:border-b-0 hover:bg-neutral-50 sm:text-base"
                                                    :class="{
                                                        'bg-orange-50':
                                                            isSelected(row),
                                                    }">
                                                    <div class="flex min-w-0 flex-1">
                                                        <div class="my-1 mr-3 w-[3px] self-stretch rounded-full" :style="{
                                                            backgroundColor:
                                                                row.color ||
                                                                '#f97316',
                                                        }"></div>

                                                        <div class="min-w-0 flex-1">
                                                            <div
                                                                class="truncate text-base font-medium text-neutral-900 md:text-lg">
                                                                {{ row.label }}
                                                            </div>
                                                            <div class="mt-0.5 text-xs text-neutral-500 sm:text-sm">
                                                                <span v-if="row.durationLabel">  
                                                                    {{ row.durationLabel }}
                                                                </span>
                                                                <span v-if="row.subtitle"> · </span>
                                                                <span v-if="row.subtitle" class="truncate"
                                                                    :title="row.subtitle">
                                                                    {{ truncateText(row.subtitle, 25) }}
                                                                </span>
                                                            </div>

                                                            <div v-if="
                                                                !row.staffProvides
                                                            "
                                                                class="mt-1 inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 sm:text-xs">
                                                                Team member
                                                                doesn’t provide
                                                                this service
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3 pl-2">
                                                        <div
                                                            class="text-right text-base font-semibold text-neutral-900 tabular-nums md:text-lg">
                                                            <template v-if="
                                                                row.hasDiscount
                                                            ">
                                                                <div class="text-[11px] text-neutral-400 line-through">
                                                                    {{
                                                                        row.basePriceLabel
                                                                    }}
                                                                </div>
                                                                <div>
                                                                    {{
                                                                        row.priceLabel
                                                                    }}
                                                                </div>
                                                                <div v-if="
                                                                    row.discountLabel
                                                                " class="text-[11px] font-medium text-emerald-600">
                                                                    {{
                                                                        row.discountLabel
                                                                    }}
                                                                </div>
                                                            </template>
                                                            <template v-else>
                                                                {{
                                                                    row.priceLabel
                                                                }}
                                                            </template>
                                                        </div>

                                                        <div v-if="
                                                            serviceCount(
                                                                row,
                                                            ) > 0
                                                        "
                                                            class="inline-flex min-w-[22px] items-center justify-center rounded-full bg-orange-600 px-1.5 text-[11px] font-semibold text-white">
                                                            {{
                                                                serviceCount(
                                                                    row,
                                                                )
                                                            }}
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
<p v-if="!filteredGroups.length" class="text-center text-xs text-neutral-400 sm:text-sm">
      No services found.
    </p>
  </template>
                                </div>

                                <!-- <footer
                                    class="flex flex-col gap-3 border-t bg-white px-4 py-3 text-xs sm:px-6 sm:py-3 sm:text-sm md:flex-row md:items-center md:justify-between">
                                    <div class="min-w-0">
                                        <div class="truncate text-xs text-neutral-500 sm:text-sm">
                                            <span v-if="selectedClient">
                                                Client:
                                                <strong>
                                                    {{ selectedClient.name }}
                                                </strong>
                                            </span>
                                            <span v-else>
                                                Client:
                                                <strong>Walk-in</strong>
                                            </span>
                                        </div>
                                        <div class="mt-0.5 truncate text-xs text-neutral-500 sm:text-sm">
                                            {{ cartItems.length }} service<span v-if="cartItems.length !== 1">s</span>
                                            selected
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            class="cursor-pointer rounded-full border border-neutral-300 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100 sm:text-sm"
                                            @click="close">
                                            Close
                                        </button>
                                    </div>
                                </footer> -->
                            </div>

                            <!-- STEP 2: SUMMARY -->
                            <div v-else-if="step === 'summary'" key="summary" class="flex min-h-0 flex-1 flex-col">
                                <!-- <div class="border-b px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-2">

                                            <div class="relative inline-block datepicker-wrapper">
                                                <button type="button" @click="toggleDatePicker"
                                                    class="cursor-pointer inline-flex items-center gap-2 rounded-full border border-neutral-300 bg-white px-4 py-2 text-xl sm:text-2xl font-semibold text-neutral-900 shadow-sm hover:border-orange-400 hover:ring-2 hover:ring-orange-100">
                                                    <i class="bx bx-calendar text-xl text-orange-500"></i>
                                                    <span>
                                                        {{ appointmentDateLabel || 'Pick a date' }}
                                                    </span>
                                                </button>

                                                <transition name="fade">
                                                    <div v-if="showDatePicker"
                                                        class="absolute left-0 z-20 mt-3 w-80 rounded-2xl border bg-white p-3 shadow-2xl">

                                                        <div class="mb-2 flex items-center justify-between">
                                                            <button type="button"
                                                                class="inline-flex size-8 items-center justify-center rounded-full hover:bg-neutral-100"
                                                                @click="prevMonth">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 20 20" fill="none"
                                                                    class="h-4 w-4 text-neutral-600">
                                                                    <path d="M12.5 15 7.5 10l5-5" stroke="currentColor"
                                                                        stroke-width="1.6" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>
                                                            </button>


                                                            <button type="button"
                                                                class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium text-neutral-900 hover:bg-neutral-50"
                                                                @click.stop="toggleMonthYearPicker">
                                                                <span>{{ monthYearLabel }}</span>
                                                                <i class="bx text-xs"
                                                                    :class="isMonthYearPickerOpen ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="inline-flex size-8 items-center justify-center rounded-full hover:bg-neutral-100"
                                                                @click="nextMonth">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    viewBox="0 0 20 20" fill="none"
                                                                    class="h-4 w-4 text-neutral-600">
                                                                    <path d="M7.5 5 12.5 10l-5 5" stroke="currentColor"
                                                                        stroke-width="1.6" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        <transition name="fade">
                                                            <div v-if="isMonthYearPickerOpen"
                                                                class="mb-2 rounded-xl bg-neutral-50 p-2">

                                                                <div class="mb-2 flex items-center justify-between">
                                                                    <button type="button"
                                                                        class="inline-flex size-7 items-center justify-center rounded-full hover:bg-neutral-100"
                                                                        @click="changePickerYear(-1)">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 20 20" fill="none"
                                                                            class="h-4 w-4 text-neutral-600">
                                                                            <path d="M11.5 14 8 10l3.5-4"
                                                                                stroke="currentColor" stroke-width="1.6"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round" />
                                                                        </svg>
                                                                    </button>

                                                                    <div class="text-xs font-semibold text-neutral-700">
                                                                        {{ pickerYear ?? new Date().getFullYear() }}
                                                                    </div>

                                                                    <button type="button"
                                                                        class="inline-flex size-7 items-center justify-center rounded-full hover:bg-neutral-100"
                                                                        @click="changePickerYear(1)">
                                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                                            viewBox="0 0 20 20" fill="none"
                                                                            class="h-4 w-4 text-neutral-600">
                                                                            <path d="m8.5 6 3.5 4-3.5 4"
                                                                                stroke="currentColor" stroke-width="1.6"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round" />
                                                                        </svg>
                                                                    </button>
                                                                </div>


                                                                <div class="grid grid-cols-3 gap-1 text-xs sm:text-sm">
                                                                    <button v-for="(m, idx) in monthNames" :key="m"
                                                                        type="button"
                                                                        class="cursor-pointer rounded-lg px-2 py-1.5 text-center"
                                                                        :class="[
                                                                            idx === (pickerMonth ?? new Date().getMonth())
                                                                                ? 'bg-orange-600 text-white'
                                                                                : 'text-neutral-700 hover:bg-white'
                                                                        ]" @click="selectPickerMonth(idx)">
                                                                        {{ m }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </transition>



                                                        <div
                                                            class="grid grid-cols-7 gap-1 text-[11px] font-medium text-neutral-400">
                                                            <div class="text-center py-1">Su</div>
                                                            <div class="text-center py-1">Mo</div>
                                                            <div class="text-center py-1">Tu</div>
                                                            <div class="text-center py-1">We</div>
                                                            <div class="text-center py-1">Th</div>
                                                            <div class="text-center py-1">Fr</div>
                                                            <div class="text-center py-1">Sa</div>
                                                        </div>


                                                        <div class="mt-1 grid grid-cols-7 gap-1 text-sm">
                                                            <div v-for="day in monthDays" :key="day.key"
                                                                class="flex items-center justify-center">
                                                                <button v-if="!day.isPlaceholder" type="button"
                                                                    class="cursor-pointer flex aspect-square w-full items-center justify-center rounded-full text-sm transition-colors"
                                                                    :class="[
                                                                        day.isCurrent
                                                                            ? 'bg-orange-600 text-white hover:bg-orange-600'
                                                                            : day.isToday
                                                                                ? 'border border-orange-500 text-orange-600'
                                                                                : 'text-neutral-700 hover:bg-neutral-50',
                                                                    ]" @click="selectDate(day.date)">
                                                                    {{ day.label }}
                                                                </button>
                                                                <span v-else class="aspect-square w-full"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </transition>
                                            </div>


                                            <div
                                                class="mt-1 flex flex-wrap items-center gap-2 text-sm text-neutral-500 sm:text-base">
                                                <div class="relative timepicker-wrapper">
                                                    <button type="button" @click="toggleTimePicker"
                                                        class="cursor-pointer inline-flex items-center gap-2 rounded-full border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-800 shadow-sm hover:border-orange-400 hover:ring-2 hover:ring-orange-100">
                                                        <i class="bx bx-time text-lg text-orange-500"></i>
                                                        <span>{{ appointmentStartTimeLabel }}</span>
                                                    </button>

                                                    <transition name="fade">
                                                        <div v-if="showTimePicker"
                                                            class="absolute z-20 mt-2 w-32 max-h-64 overflow-y-auto rounded-2xl border bg-white p-2 shadow-2xl">
                                                            <button v-for="t in timeOptions" :key="t" type="button"
                                                                class="cursor-pointer flex w-full items-center justify-between rounded-xl px-3 py-1.5 text-sm text-neutral-800 hover:bg-orange-50"
                                                                :class="{
                                                                    'bg-orange-600 text-white hover:bg-orange-600':
                                                                        t === appointmentStartTimeInput,
                                                                }" @click="selectTime(t)">
                                                                <span>{{ formatTimeLabel(t) }}</span>
                                                            </button>
                                                        </div>
                                                    </transition>
                                                </div>

                                                <span v-if="appointmentTimeLabel">
                                                    – {{ appointmentTimeLabel.split('–')[1]?.trim() }}
                                                </span>
                                                <span v-if="appointmentRepeatLabel">
                                                    · {{ appointmentRepeatLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->


                                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-4">
                                    <div>
                                        <h2 class="text-sm font-semibold text-neutral-800 sm:text-base">
                                            Services
                                        </h2>

                                        <div class="mt-3 space-y-3">
                                            <div v-for="(
item, index
                                                ) in cartItems" :key="item.instanceUid ||
                                                    item.serviceId +
                                                    '-' +
                                                    (item.variantId ??
                                                        'base') +
                                                    '-' +
                                                    index
                                                    "
                                                class="group relative cursor-pointer rounded-2xl bg-neutral-50 px-4 py-3 text-sm shadow-sm ring-1 ring-transparent transition-all hover:bg-neutral-100 hover:ring-neutral-200 sm:px-5 sm:py-4"
                                                @click="startEdit(item, index)">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex min-w-0 flex-1">
                                                        <div class="my-1 mr-3 w-[3px] self-stretch rounded-full" :style="{
                                                            backgroundColor:
                                                                item.color ||
                                                                '#f97316',
                                                        }"></div>

                                                        <div class="min-w-0">
                                                            <div
                                                                class="truncate text-sm font-medium text-neutral-900 sm:text-base">
                                                                {{ item.label }}
                                                            </div>
                                                            <div class="mt-1 text-xs text-neutral-500 sm:text-sm">
                                                                <span v-if="
                                                                    displayStartTime(
                                                                        item,
                                                                        index,
                                                                    )
                                                                ">
                                                                    {{
                                                                        displayStartTime(
                                                                            item,
                                                                            index,
                                                                        )
                                                                    }}
                                                                </span>
                                                                <span v-if="
                                                                    displayStartTime(
                                                                        item,
                                                                        index,
                                                                    ) &&
                                                                    displayDuration(
                                                                        item,
                                                                    )
                                                                ">
                                                                    ·
                                                                </span>
                                                                <span v-if="
                                                                    displayDuration(
                                                                        item,
                                                                    )
                                                                ">
                                                                    {{
                                                                        displayDuration(
                                                                            item,
                                                                        )
                                                                    }}
                                                                </span>
                                                                <span v-if="
                                                                    (displayStartTime(
                                                                        item,
                                                                        index,
                                                                    ) ||
                                                                        displayDuration(
                                                                            item,
                                                                        )) &&
                                                                    displayStaffName(
                                                                        item,
                                                                    )
                                                                ">
                                                                    ·
                                                                </span>
                                                                <span v-if="
                                                                    displayStaffName(
                                                                        item,
                                                                    )
                                                                ">
                                                                    {{
                                                                        displayStaffName(
                                                                            item,
                                                                        )
                                                                    }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3 pl-2">
                                                        <div
                                                            class="text-right text-sm font-semibold text-neutral-900 tabular-nums sm:text-base">
                                                            <template v-if="
                                                                priceMeta(
                                                                    item,
                                                                )
                                                                    .hasDiscount
                                                            ">
                                                                <div class="text-[11px] text-neutral-400 line-through">
                                                                    {{
                                                                        priceMeta(
                                                                            item,
                                                                        )
                                                                            .basePriceLabel
                                                                    }}
                                                                </div>
                                                                <div>
                                                                    {{
                                                                        priceMeta(
                                                                            item,
                                                                        )
                                                                            .priceLabel
                                                                    }}
                                                                </div>
                                                                <div v-if="
                                                                    priceMeta(
                                                                        item,
                                                                    )
                                                                        .discountLabel
                                                                " class="text-[11px] font-medium text-emerald-600">
                                                                    {{
                                                                        priceMeta(
                                                                            item,
                                                                        )
                                                                            .discountLabel
                                                                    }}
                                                                </div>
                                                            </template>
                                                            <template v-else>
                                                                {{
                                                                    priceMeta(
                                                                        item,
                                                                    ).priceLabel
                                                                }}
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div
                                                    class="pointer-events-none absolute right-3 bottom-2 flex items-center gap-1 opacity-0 transition-opacity group-hover:pointer-events-auto group-hover:opacity-100">
                                                    <button type="button"
                                                        class="flex size-8 cursor-pointer items-center justify-center rounded-full border border-neutral-300 bg-white text-[13px] text-neutral-600 shadow-sm hover:bg-neutral-100"
                                                        @click.stop="
                                                            startEdit(
                                                                item,
                                                                index,
                                                            )
                                                            ">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                            fill="none" class="h-4.5 w-4.5">
                                                            <path d="M4 13.5 12.5 5l2.5 2.5L6.5 16H4v-2.5Z"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                        class="flex size-8 cursor-pointer items-center justify-center rounded-full border border-neutral-300 bg-white text-[13px] text-rose-600 shadow-sm hover:bg-rose-50"
                                                        @click.stop="
                                                            deleteItem(item)
                                                            ">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                            fill="none" class="h-4.5 w-4.5">
                                                            <path
                                                                d="M5 5L6 17H14L15 5M5 5H8M5 5H3M15 5H12M15 5H17M8 5V2H12V5M8 5H12"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <button type="button"
                                                class="mt-4 inline-flex cursor-pointer items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-medium hover:bg-neutral-100 sm:text-sm"
                                                @click="editServices">
                                                <span class="text-lg leading-none">+</span>
                                                <span>Add service</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- NOTES (only if added) -->
                                    <div v-if="hasNote">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-sm font-semibold text-neutral-800 sm:text-base">Notes</h2>

                                            <div class="flex items-center gap-2">
                                                <!-- Edit -->
                                                <button type="button"
                                                    class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100"
                                                    @click.stop="openNoteModal" aria-label="Edit note">
                                                    <i class="bx bx-edit-alt text-lg"></i>
                                                </button>

                                                <!-- Delete -->
                                                <button type="button"
                                                    class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full border border-rose-200 bg-white text-rose-600 hover:bg-rose-50"
                                                    @click.stop="deleteNote" aria-label="Delete note">
                                                    <i class="bx bx-trash text-lg"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Note preview (click to edit as well) -->
                                        <button type="button"
                                            class="mt-3 w-full cursor-pointer rounded-2xl bg-indigo-50/60 px-4 py-3 text-left text-sm text-neutral-800 ring-1 ring-transparent transition hover:bg-indigo-50 hover:ring-neutral-200"
                                            @click.stop="openNoteModal">
                                            {{ noteDisplay }}
                                        </button>
                                    </div>

                                </div>


                                <footer class="border-t bg-white px-4 py-3 sm:px-6 sm:py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="sm:text-base font-semibold text-neutral-900">Total</div>
                                        <div class="sm:text-base font-semibold text-neutral-600">
                                            from <span class="text-neutral-900">{{ totalLabel }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-3">
                                        <!-- kebab -->
                                        <div class="relative" data-quick-actions>
                                            <button type="button"
                                                class="btn-pointer inline-flex size-11 items-center justify-center rounded-full border border-neutral-300 bg-white text-neutral-900 hover:bg-neutral-100 disabled:opacity-50"
                                                @click="toggleQuickActions" :disabled="isSaving || isCheckingOut">
                                                <i class="bx bx-dots-vertical-rounded text-xl"></i>
                                            </button>

                                            <transition name="fade">
                                                <div v-if="showQuickActions"
                                                    class="absolute bottom-full left-0 mb-2 w-56 rounded-2xl border bg-white py-2 text-xs shadow-xl z-20 sm:text-sm"
                                                    data-quick-actions>
                                                    <div
                                                        class="px-4 pb-2 text-[12px] font-semibold uppercase tracking-wide text-neutral-700">
                                                        Quick actions
                                                    </div>

                                                    <button type="button"
                                                        class="btn-pointer flex w-full items-center gap-2 px-4 py-2 text-left hover:bg-neutral-50"
                                                        @click="openNoteFromQuickActions">
                                                        <span
                                                            class="inline-flex size-7 items-center justify-center rounded-full border border-neutral-200 text-neutral-900">
                                                            <i class="bx bx-note text-base"></i>
                                                        </span>
                                                        <span class="text-[13px] sm:text-sm text-neutral-800">
                                                            {{ hasNote ? 'Edit appointment note' : 'Add a note' }}
                                                        </span>
                                                    </button>
                                                </div>
                                            </transition>
                                        </div>

                                        <!-- Checkout -->
                                        <button type="button"
                                            class="btn-pointer h-11 flex-1 rounded-full border border-neutral-300 bg-white px-5 text-base font-semibold text-neutral-900 hover:bg-neutral-100 disabled:opacity-50"
                                            :disabled="!cartItems.length || isCheckingOut || isSaving"
                                            @click="checkout">
                                            <span v-if="!isCheckingOut">Checkout</span>
                                            <span v-else class="inline-flex items-center gap-2">
                                                <span class="btn-spinner"></span>
                                                Checking out…
                                            </span>
                                        </button>

                                        <!-- Save -->
                                        <button type="button"
                                            class="btn-pointer h-11 flex-1 rounded-full bg-neutral-900 px-6 text-base font-semibold text-white hover:bg-black disabled:opacity-50"
                                            :disabled="!cartItems.length || isSaving || isCheckingOut" @click="confirm">
                                            <span v-if="!isSaving">Save</span>
                                            <span v-else class="inline-flex items-center gap-2">
                                                <span class="btn-spinner btn-spinner--light"></span>
                                                Saving…
                                            </span>
                                        </button>
                                    </div>
                                </footer>


                            </div>

                            <!-- STEP 3: EDIT -->
                            <div v-else key="edit" class="flex min-h-0 flex-1 flex-col">
                                <div class="border-b px-6 py-4">
                                    <button type="button"
                                        class="mb-3 inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-neutral-600 hover:text-neutral-900 sm:text-sm"
                                        @click="step = 'summary'">
                                        <span class="text-base">←</span>
                                        <span>Back</span>
                                    </button>
                                    <h2 class="text-2xl font-semibold text-neutral-900 sm:text-3xl">
                                        Edit service
                                    </h2>
                                </div>

                                <div v-if="editingDraft" class="flex-1 space-y-6 overflow-y-auto px-6 py-5">
                                    <button type="button"
                                        class="flex w-full cursor-pointer items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm hover:bg-neutral-50 sm:px-5 sm:py-4 sm:text-base">
                                        <div class="flex items-center gap-3">
                                            <div class="my-1 mr-3 w-[3px] self-stretch rounded-full" :style="{
                                                backgroundColor:
                                                    editingDraft.color ||
                                                    '#f97316',
                                            }"></div>
                                            <div>
                                                <div class="font-medium text-neutral-900">
                                                    {{ editingDraft.label }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-neutral-500 sm:text-sm">
                                                    {{
                                                        formatDuration(
                                                            editingDurationTotal,
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-neutral-400">›</span>
                                    </button>

                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                            Team member
                                        </label>
                                        <div class="relative dropdown-staff">
                                            <button type="button" @click="showStaffDropdown = !showStaffDropdown"
                                                class="flex w-full items-center justify-between rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-left text-sm text-neutral-900 hover:bg-neutral-50 sm:px-4 sm:text-base cursor-pointer">
                                                <div class="flex items-center gap-2">

                                                    <span>{{staffOptions.find(s => s.id === editingDraft.staffId)?.name
                                                        || 'Select team member'
                                                    }}</span>
                                                </div>
                                                <span class="text-neutral-400 transition-transform duration-200"
                                                    :class="{ 'rotate-180': showStaffDropdown }">
                                                    ▼
                                                </span>
                                            </button>

                                           <div
  v-if="showStaffDropdown"
  class="absolute z-10 mt-1 w-full rounded-xl border border-neutral-300 bg-white shadow-lg max-h-48 overflow-y-auto"
>
  <div v-if="staffLoading" class="p-3 text-sm text-neutral-500">
    Loading team members…
  </div>

  <div v-else-if="staffLoadError" class="p-3 text-sm text-rose-600">
    {{ staffLoadError }}
  </div>

  <div v-else class="py-1">
    <button
      v-for="s in staffOptions"
      :key="s.id"
      type="button"
      @click="editingDraft.staffId = s.id; showStaffDropdown = false"
      class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-neutral-700 hover:bg-neutral-50 sm:text-base"
      :class="{ 'bg-orange-50 text-orange-700': editingDraft.staffId === s.id }"
    >
      <span>{{ s.name }}</span>
    </button>

    <div v-if="!staffOptions.length" class="p-3 text-sm text-neutral-500">
      No team members found.
    </div>
  </div>
</div>

                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                                            <div class="space-y-2">
                                                <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                                    Service price
                                                </label>
                                                <div
                                                    class="flex items-center gap-2 rounded-xl border px-3 py-2.5 sm:px-4">
                                                    <span class="text-xs font-semibold text-neutral-500 sm:text-sm">
                                                        {{ currencySymbol }}
                                                    </span>
                                                    <input v-model.number="editingDraft.price
                                                        " type="number" min="0"
                                                        class="block w-full border-none bg-transparent text-sm font-semibold text-neutral-900 focus:ring-0 focus:outline-none sm:text-base" />
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                                    Discount
                                                </label>

                                                <div class="relative dropdown-discount">
                                                    <button type="button"
                                                        @click="showDiscountDropdown = !showDiscountDropdown"
                                                        class="flex w-full items-center justify-between rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-left text-sm text-neutral-900 hover:bg-neutral-50 sm:px-4 sm:text-base cursor-pointer">
                                                        <span>{{ editingDiscountButtonLabel }}</span>

                                                        <span class="text-neutral-400 transition-transform duration-200"
                                                            :class="{ 'rotate-180': showDiscountDropdown }">
                                                            ▼
                                                        </span>
                                                    </button>

                                                    <div v-if="showDiscountDropdown"
                                                        class="absolute z-10 mt-1 w-full rounded-xl border border-neutral-300 bg-white shadow-lg max-h-48 overflow-y-auto">
                                                        <div class="py-1">
                                                            <button v-for="opt in editingDiscountOptions" :key="opt.key"
                                                                type="button"
                                                                @click="editingSelectedDiscountKey = opt.key; onChangeEditingDiscount(); showDiscountDropdown = false"
                                                                class="w-full px-4 py-2.5 text-left text-sm text-neutral-700 hover:bg-neutral-50 sm:text-base"
                                                                :class="{ 'bg-orange-50 text-orange-700': editingSelectedDiscountKey === opt.key }">
                                                                {{ opt.label }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <p class="mt-1 text-[11px] text-neutral-400 sm:text-xs">
                                                    Discounts are configured on the service. You can only apply existing
                                                    discounts here.
                                                </p>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                                Start time
                                            </label>
                                            <div class="flex items-center gap-2 rounded-xl border px-3 py-2.5 sm:px-4">
                                                <input v-model="editingDraft.startTime
                                                    " type="time"
                                                    class="block w-full border-none bg-transparent text-sm text-neutral-900 focus:ring-0 focus:outline-none sm:text-base" />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                                Duration
                                            </label>
                                            <!-- Custom dropdown container -->
                                            <div class="relative">
                                                <button type="button"
                                                    @click="showDurationDropdown = !showDurationDropdown"
                                                    class="flex w-full items-center justify-between rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-left text-sm text-neutral-900 hover:bg-neutral-50 sm:px-4 sm:text-base cursor-pointer">
                                                    <span>{{ formatDuration(editingDraft.durationMinutes) }}</span>
                                                    <span class="text-neutral-400 transition-transform duration-200"
                                                        :class="{ 'rotate-180': showDurationDropdown }">
                                                        ▼
                                                    </span>
                                                </button>

                                                <!-- Dropdown menu -->
                                                <div v-if="showDurationDropdown"
                                                    class="absolute z-10 mt-1 w-full rounded-xl border border-neutral-300 bg-white shadow-lg max-h-48 overflow-y-auto"
                                                    @click.outside="showDurationDropdown = false">
                                                    <div class="py-1">
                                                        <button v-for="opt in durationOptions" :key="opt" type="button"
                                                            @click="editingDraft.durationMinutes = opt; showDurationDropdown = false"
                                                            class="w-full px-4 py-2.5 text-left text-sm text-neutral-700 hover:bg-neutral-50 sm:text-base"
                                                            :class="{ 'bg-orange-50 text-orange-700': editingDraft.durationMinutes === opt }">
                                                            {{ formatDuration(opt) }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- <div class="space-y-2">
                                            <label class="text-xs font-medium text-neutral-700 sm:text-sm">
                                                Extra time
                                            </label>

                                            <button v-if="!editingDraft || !editingDraft.extraMinutes" type="button"
                                                class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-medium hover:bg-neutral-50 sm:text-sm"
                                                @click="adjustExtraTime(15)">
                                                <span class="text-lg leading-none">+</span>
                                                <span>Add extra time</span>
                                            </button>

                                            <div v-else
                                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium sm:text-sm">
                                                <button type="button"
                                                    class="flex size-7 items-center justify-center rounded-full border border-neutral-300 text-neutral-700 hover:bg-neutral-50 cursor-pointer"
                                                    @click="adjustExtraTime(-15)">
                                                    −
                                                </button>

                                                <span class="px-1 text-neutral-700">
                                                    {{ formatDuration(editingDraft.extraMinutes || 0) }}
                                                </span>

                                                <button type="button"
                                                    class="flex size-7 items-center justify-center rounded-full border border-neutral-300 text-neutral-700 hover:bg-neutral-50 cursor-pointer"
                                                    @click="adjustExtraTime(15)">
                                                    +
                                                </button>
                                            </div>
                                        </div> -->

                                    </div>
                                </div>

                                <footer class="border-t bg-white px-4 py-3 sm:px-6 sm:py-4">
                                    <div
                                        class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="text-xs font-medium tracking-wide text-neutral-500 uppercase">
                                                Total
                                            </div>
                                            <div
                                                class="mt-1 text-base font-semibold text-neutral-900 tabular-nums sm:text-lg">
                                                {{ editTotalLabel }}
                                                <span class="ml-1 text-xs font-normal text-neutral-500">
                                                    {{
                                                        formatDuration(
                                                            editingDurationTotal,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-end gap-2"><button type="button"
                                                class="cursor-pointer rounded-full border border-rose-200 px-4 py-1.5 text-[11px] font-semibold tracking-wide text-rose-600 uppercase hover:bg-rose-50 sm:text-xs"
                                                @click="deleteEditingItem">
                                                Delete
                                            </button>


                                            <button type="button"
                                                class="cursor-pointer rounded-full bg-black px-6 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-neutral-900 sm:text-sm"
                                                @click="applyEdit">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </footer>
                            </div>
                        </transition>
                    </section>
                </main>
            </div>
<transition name="fade">
  <div
    v-if="showDiscardModal"
    class="fixed inset-0 z-[140] flex items-center justify-center bg-neutral-900/30 px-4 pointer-events-auto"
    @click.self="goBackToEditing"
  >
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
      <header class="flex items-center justify-between px-8 pt-8">
        <div class="text-xl font-semibold text-neutral-900">
          You have unsaved changes
        </div>

        <button
          type="button"
          class="inline-flex size-9 cursor-pointer items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
          @click="goBackToEditing"
        >
          ✕
        </button>
      </header>

      <div class="px-8 pb-8 pt-4 text-neutral-600">
        If you close the appointment now, the changes will be lost. Do you wish to exit?
      </div>

      <footer class="flex items-center justify-center gap-4 px-8 pb-8">
        <button
          type="button"
          class="h-11 w-40 rounded-full border cursor-pointer border-neutral-300 bg-white text-sm font-semibold text-neutral-900 hover:bg-neutral-100"
          @click="goBackToEditing"
        >
          Go back
        </button>

        <button
          type="button"
          class="h-11 w-40 rounded-full cursor-pointer bg-neutral-900 text-sm font-semibold text-white hover:bg-black"
          @click="discardChangesAndClose"
        >
          Yes, exit
        </button>
      </footer>
    </div>
  </div>
</transition>
            <!-- NOTE MODAL -->
            <transition name="fade">
                <div v-if="showNoteModal"
                    class="fixed inset-0 z-[130] flex items-center justify-center bg-neutral-900/30 px-4 pointer-events-auto"
                    @click.self="closeNoteModal">
                    <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl" data-note-modal>
                        <header class="flex items-center justify-between border-b px-6 py-4">
                            <h2 class="text-lg font-semibold text-neutral-900 sm:text-xl">
                                {{ hasNote ? 'Edit note' : 'Add a note' }}
                            </h2>
                            <button type="button"
                                class="inline-flex size-8 cursor-pointer items-center justify-center rounded-full border border-neutral-300 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                @click="closeNoteModal">
                                <span class="sr-only">Close</span>
                                ✕
                            </button>
                        </header>

                        <div class="px-6 py-4">
                            <textarea v-model="noteDraft" rows="5"
                                class="block w-full rounded-2xl border border-neutral-300 px-3 py-2 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:outline-none focus:ring-2 focus:ring-neutral-900/10 sm:text-base"
                                placeholder="Enter any special instructions or details about the appointment here"></textarea>

                            <p class="mt-2 text-xs text-neutral-500 sm:text-sm">
                                This note will be visible only to your team members.
                            </p>
                        </div>

                        <footer class="flex items-center justify-end gap-2 border-t bg-neutral-50 px-6 py-3">
                            <button type="button"
                                class="rounded-full border border-neutral-300 cursor-pointer px-4 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100 sm:text-sm"
                                @click="closeNoteModal">
                                Cancel
                            </button>

                            <button v-if="hasNote" type="button"
                                class="rounded-full border border-rose-200 cursor-pointer px-4 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 sm:text-sm"
                                @click="deleteNote">
                                Delete
                            </button>

                            <button type="button"
                                class="rounded-full bg-black px-6 cursor-pointer py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-neutral-900 sm:text-base"
                                @click="saveNote">
                                {{ hasNote ? 'Update' : 'Save' }}
                            </button>
                        </footer>
                    </div>
                </div>
            </transition>


            <AddClientModal :show="showAddClientModal" :countries="countries" :mode="editClientMode"
                :client-id="editingClientId" @close="handleClientModalClose" @saved="handleClientSaved" />


        </div>
    </transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import AddClientModal from './AddClientModal.vue';
import axios from 'axios';

export default defineComponent({
    name: 'SelectService',

    components: {
        AddClientModal,
    },

    data() {
        return {
             staffLocal: [] as any[],
  staffLoading: false,
  staffLoadError: '' as string,
  _staffReqToken: 0,
  servicesLocal: [] as any[],
 servicesLoading: false,
    servicesLoadError: '' as string,
  
            noteText: '',
            noteDraft: '',
            clientSearch: '',
            serviceSearch: '',
            showClientList: false,
            step: 'services' as 'services' | 'summary' | 'edit',
            editingIndex: null as number | null,
            editingDraft: null as any | null,

            isSaving: false,
            isCheckingOut: false,
            showAddClientModal: false,
            clientsLocal: [] as any[],
            showQuickActions: false,
            showNoteModal: false,
              showDiscardModal: false,
            editingDiscountOptions: [] as any[],
            editingSelectedDiscountKey: 'none' as string,
            showDurationDropdown: false,
            showStaffDropdown: false,
            showDiscountDropdown: false,
            overrideDate: null as string | null,
            overrideStartTime: null as string | null,
            editingClientId: null as number | null,

            showDatePicker: false,
            showTimePicker: false,
            pickerMonth: null as number | null, // 0–11
            pickerYear: null as number | null,
            editClientMode: 'create' as 'create' | 'edit',
            editingClientData: null as any | null,

            editingOriginalPrice: null as number | null,
            isMonthYearPickerOpen: false,
        };
    },

    computed: {
        hasUnsavedChanges(): boolean {
  return (
    this.cartItems.length > 0 ||
    (this.noteText || '').trim().length > 0 ||
    this.editingDraft !== null ||
    this.overrideDate !== null ||
    this.overrideStartTime !== null ||
    !!this.selectedClientId ||
    this.isWalkIn             
  );
},

        hasNote(): boolean {
            return (this.noteText || '').trim().length > 0;
        },
        noteDisplay(): string {
            return (this.noteText || '').trim();
        },

        editingPriceDelta(): number {
            const current = Number(this.editingDraft?.price ?? 0) || 0;
            const original = Number(this.editingOriginalPrice ?? current) || 0;
            return current - original;
        },

        editingPriceDeltaLabel(): string | null {
            if (!this.editingDraft) return null;

            const delta = this.editingPriceDelta;
            if (!delta) return null;

            const abs = Math.abs(delta);
            const formatted = abs.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });

            return `${delta > 0 ? 'Increase' : 'Decrease'} ${this.currencySymbol} ${formatted}`;
        },

        editingDiscountButtonLabel(): string {
            const selected = this.editingDiscountOptions.find(
                (opt: any) => opt.key === this.editingSelectedDiscountKey,
            );

            // If a backend discount is selected, show it.
            if (this.editingSelectedDiscountKey !== 'none') {
                return selected?.label || 'No discount';
            }

            // If no backend discount is selected, show manual change label (if any).
            return this.editingPriceDeltaLabel || selected?.label || 'No discount';
        },

        monthNames(): string[] {
            const base = new Date(2000, 0, 1);
            const out: string[] = [];
            for (let i = 0; i < 12; i++) {
                const d = new Date(base);
                d.setMonth(i);
                out.push(
                    d.toLocaleDateString(undefined, {
                        month: 'short',
                    }),
                );
            }
            return out;
        },

        appointmentStartTimeLabel(): string {
            const t = this.appointmentStartTimeInput;
            if (!t) return 'Pick a time';

            const [hhStr, mmStr] = t.split(':');
            const hh = parseInt(hhStr || '0', 10);
            const mm = parseInt(mmStr || '0', 10);
            if (!Number.isFinite(hh) || !Number.isFinite(mm)) return t;

            const d = new Date();
            d.setHours(hh, mm, 0, 0);
            return d.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        timeOptions(): string[] {
            const options: string[] = [];
            const step = 15; // minutes

            // full day: 00:00–23:45
            for (let h = 0; h < 24; h++) {
                for (let m = 0; m < 60; m += step) {
                    const hh = String(h).padStart(2, '0');
                    const mm = String(m).padStart(2, '0');
                    options.push(`${hh}:${mm}`);
                }
            }

            return options;
        },


        monthYearLabel(): string {
            const now = new Date();
            const year = this.pickerYear ?? now.getFullYear();
            const month = this.pickerMonth ?? now.getMonth();
            const d = new Date(year, month, 1);

            return d.toLocaleDateString(undefined, {
                month: 'long',
                year: 'numeric',
            });
        },

        monthDays(): any[] {
            const now = new Date();
            const year = this.pickerYear ?? now.getFullYear();
            const month = this.pickerMonth ?? now.getMonth();

            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const days: any[] = [];

            const firstDow = first.getDay(); // 0 = Sunday

            // leading blanks
            for (let i = 0; i < firstDow; i++) {
                days.push({
                    key: `blank-${i}`,
                    isPlaceholder: true,
                });
            }

            const today = new Date();
            const selectedStr = this.appointmentDateInput;

            for (let d = 1; d <= last.getDate(); d++) {
                const date = new Date(year, month, d);

                const isToday =
                    date.getFullYear() === today.getFullYear() &&
                    date.getMonth() === today.getMonth() &&
                    date.getDate() === today.getDate();

                let isCurrent = false;
                if (selectedStr) {
                    const sel = new Date(selectedStr + 'T00:00:00');
                    isCurrent =
                        date.getFullYear() === sel.getFullYear() &&
                        date.getMonth() === sel.getMonth() &&
                        date.getDate() === sel.getDate();
                }

                days.push({
                    key: `day-${d}`,
                    label: d,
                    date,
                    isToday,
                    isCurrent,
                    isPlaceholder: false,
                });
            }

            return days;
        },

        totalDurationMinutes(): number {
            return this.cartItems.reduce((sum, item) => {
                const base = Number(item.duration ?? item.durationMinutes ?? 0);
                const extra = Number(item.extraMinutes ?? 0);
                const total = Math.max(0, base + extra);
                return sum + total;
            }, 0);
        },

        computedSlotRange(): { start: Date | null; end: Date | null } {
            if (!this.slot || !this.slot.startIso) {
                return { start: null, end: null };
            }

            let start = new Date(this.slot.startIso);

            const dateStr = this.appointmentDateInput;       // yyyy-mm-dd
            const timeStr = this.appointmentStartTimeInput || this.defaultStartTime(); // hh:mm

            if (dateStr && timeStr) {
                const [y, m, d] = dateStr.split('-').map((n) => parseInt(n, 10));
                const [hh, mm] = timeStr.split(':').map((n) => parseInt(n, 10));

                start = new Date();
                start.setFullYear(y, m - 1, d);
                start.setHours(hh, mm, 0, 0);   // seconds = 00
            }

            let end = new Date(start);

            if (this.totalDurationMinutes > 0) {
                end.setMinutes(end.getMinutes() + this.totalDurationMinutes);
            } else if (this.slot.endIso) {
                end = new Date(this.slot.endIso);
            } else {
                end.setMinutes(end.getMinutes() + 15);
            }

            return { start, end };
        },
        appointmentDateInput: {
            get(): string {
                if (this.overrideDate) return this.overrideDate;
                if (!this.slot || !this.slot.startIso) return '';
                const d = new Date(this.slot.startIso);
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            },
            set(v: string) {
                this.overrideDate = v || null;
            },
        },

        appointmentStartTimeInput: {
            get(): string {
                if (this.overrideStartTime) return this.overrideStartTime;
                return this.defaultStartTime();
            },
            set(v: string) {
                this.overrideStartTime = v || null;
            },
        },

        show(): boolean {
            return this.$store.getters.selectServiceShow;
        },
        staffMember(): any | null {
            return this.$store.getters.selectServiceStaff;
        },
        slot(): any | null {
            return this.$store.getters.selectServiceSlot;
        },
        clientMode(): string {
            return this.$store.getters.selectServiceClientMode;
        },
        selectedClientId(): number | null {
            return this.$store.getters.selectServiceClientId;
        },
        cartItems(): any[] {
            const items = this.$store.getters.selectServiceSelectedServices;
            return Array.isArray(items) ? items : [];
        },
    countries(): any[] {
        // Adjust the prop name if your Inertia page uses a different key
        return (this.$page.props.countries || []) as any[];
    },
    services(): any[] {
        return this.servicesLocal && this.servicesLocal.length
            ? this.servicesLocal
            : (this.$page.props.services || []);
    },

        clients(): any[] {
            if (this.clientsLocal && this.clientsLocal.length) {
                return this.clientsLocal;
            }
            return this.$page.props.clients || [];
        },

        currencySymbol(): string {
            return this.$page.props.currency_symbol || 'LKR';
        },

        isWalkIn(): boolean {
            return this.clientMode === 'walk-in';
        },

        selectedClient(): any | null {
            if (!this.selectedClientId || this.isWalkIn) return null;
            return (
                this.clients.find((c: any) => c.id === this.selectedClientId) ||
                null
            );
        },

        leftWidthClass(): string {
            if (this.showClientList || this.selectedClient) {
                return 'md:w-80';
            }
            return 'md:w-36';
        },

        timeLabel(): string {
            const range = this.computedSlotRange;
            if (!range.start || !range.end) return '';
            const fmt: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit' };
            return (
                range.start.toLocaleTimeString([], fmt) +
                ' – ' +
                range.end.toLocaleTimeString([], fmt)
            );
        },

        appointmentDateLabel(): string {
            const dateStr = this.appointmentDateInput;
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString(undefined, {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
            });
        },
        appointmentTimeLabel(): string {
            const range = this.computedSlotRange;
            if (!range.start || !range.end) return '';
            const fmt: Intl.DateTimeFormatOptions = { hour: '2-digit', minute: '2-digit' };
            return (
                range.start.toLocaleTimeString([], fmt) +
                ' – ' +
                range.end.toLocaleTimeString([], fmt)
            );
        },
        appointmentRepeatLabel(): string {
            return "Doesn't repeat";
        },

        filteredClients(): any[] {
            const q = this.clientSearch.trim().toLowerCase();
            if (!q) return this.clients;
            return this.clients.filter((c: any) => {
                const name = (c.name || '').toLowerCase();
                const email = (c.email || '').toLowerCase();
                const phone = (c.phone || '').toLowerCase();
                return (
                    name.includes(q) ||
                    (email && email.includes(q)) ||
                    (phone && phone.includes(q))
                );
            });
        },

        groups(): any[] {
            const map: Record<string, any> = {};
            for (const svc of this.services) {
                const catName = (svc.category?.name || 'other').toLowerCase();
                const key = svc.category?.id ?? 'uncategorized';
                const color = svc.category?.color_code || '#f97316';

                if (!map[key]) {
                    map[key] = {
                        key,
                        name: catName,
                        color,
                        rows: [] as any[],
                    };
                }
                map[key].rows.push(...this.rowsForService(svc, color));
            }
            return Object.values(map);
        },

        filteredGroups(): any[] {
            const q = this.serviceSearch.trim().toLowerCase();
            if (!q) return this.groups;
            return this.groups
                .map((g: any) => {
                    const rows = g.rows.filter((r: any) =>
                        r.searchText.includes(q),
                    );
                    return { ...g, rows };
                })
                .filter((g: any) => g.rows.length > 0);
        },

        totalAmount(): number {
            return this.cartItems.reduce((sum, item) => {
                const price =
                    item && item.price != null ? Number(item.price) : 0;
                return sum + (isNaN(price) ? 0 : price);
            }, 0);
        },
        totalLabel(): string {
            const formatted = this.totalAmount.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            return `${this.currencySymbol} ${formatted}`;
        },

        createdLabel(): string {
            if (!this.selectedClient || !this.selectedClient.created_at) {
                return '';
            }
            try {
                const d = new Date(this.selectedClient.created_at);
                return d.toLocaleDateString(undefined, {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                });
            } catch {
                return this.selectedClient.created_at;
            }
        },
        noShowLabel(): string | null {
            const count = this.selectedClient?.no_show_count;
            if (!count || count <= 0) return null;
            if (count === 1) return '1 no-show';
            return `${count} no-shows`;
        },

      staffOptions(): any[] {
  const local = Array.isArray(this.staffLocal) ? this.staffLocal : [];
  if (local.length) return local;

  const fromPage = Array.isArray(this.$page.props.staff) ? this.$page.props.staff : [];
  if (fromPage.length) return fromPage;

  return this.staffMember ? [this.staffMember] : [];
},

        durationOptions(): number[] {
            const out: number[] = [];
            for (let m = 15; m <= 720; m += 15) {
                out.push(m);
            }
            return out;
        },

        editingDurationTotal(): number {
            if (!this.editingDraft) return 0;
            const base = Number(this.editingDraft.durationMinutes) || 0;
            const extra = Number(this.editingDraft.extraMinutes) || 0;
            return base + extra;
        },
        editTotalAmount(): number {
            if (!this.editingDraft) return 0;
            const base = Number(this.editingDraft.price) || 0;
            const type = this.editingDraft.discountType;
            const val = Number(this.editingDraft.discountValue) || 0;
            let discount = 0;
            if (type === 'percent') {
                discount = (base * val) / 100;
            } else if (type === 'amount') {
                discount = val;
            }
            const total = Math.max(0, base - discount);
            return total;
        },
        editTotalLabel(): string {
            const formatted = this.editTotalAmount.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            return `${this.currencySymbol} ${formatted}`;
        },


    },
    mounted() {
        this.clientsLocal = (this.$page.props.clients || []).slice();
        this.fetchServices();
        window.addEventListener('click', this.handleClickOutside);

        const dateStr = this.appointmentDateInput;
        let base = dateStr
            ? new Date(dateStr + 'T00:00:00')
            : this.slot && this.slot.startIso
                ? new Date(this.slot.startIso)
                : new Date();

        this.pickerYear = base.getFullYear();
        this.pickerMonth = base.getMonth();
    },

    beforeUnmount() {
        // Remove click outside handler
        window.removeEventListener('click', this.handleClickOutside);
    },

    watch: {
        show(val: boolean) {
            if (!val) {
                this.clientSearch = '';
                this.serviceSearch = '';
                this.showClientList = false;
                this.step = 'services';
                this.editingIndex = null;
                this.editingDraft = null;

                this.isSaving = false;
                this.isCheckingOut = false;
                this.showAddClientModal = false;
                this.showQuickActions = false;
                this.showNoteModal = false;
                this.noteText = '';
                this.editingOriginalPrice = null;

                this.editingDiscountOptions = [];
                this.editingSelectedDiscountKey = 'none';
            } else {
                this.step = 'services';
            }
        },

        cartItems: {
            handler() {
                this.syncSlotRangeWithCart();
            },
            deep: true,
        },
    },


    methods: {
       async fetchServices() {
  this.servicesLoading = true;
  this.servicesLoadError = '';

  try {
    const res = await axios.get(route('calendar.servicesdata'), {
      headers: { Accept: 'application/json' },
    });
    this.servicesLocal = res.data.services || [];
  } catch (e: any) {
    console.error('Failed to load services:', e);
    this.servicesLocal = [];
    this.servicesLoadError = 'Failed to load services. Please try again.';
  } finally {
    this.servicesLoading = false;
  }
},


        async fetchStaff() {
  const branchId = this.$page.props.selectedBranchId ?? null;

  // Prefer a backend-provided URL (recommended)
  const url =
    (this.$page.props?.staff_fetch_url as string) ||
    `/api/staff?branch_id=${encodeURIComponent(String(branchId ?? ''))}`;

  const token = ++this._staffReqToken;
  this.staffLoading = true;
  this.staffLoadError = '';

  try {
    const res = await axios.get(url, {
      headers: { Accept: 'application/json' },
    });

    // Expect either: { staff: [...] } OR just [...]
    const staff = Array.isArray(res.data) ? res.data : (res.data?.staff ?? []);
    if (token !== this._staffReqToken) return;

    this.staffLocal = Array.isArray(staff) ? staff : [];
  } catch (e: any) {
    if (token !== this._staffReqToken) return;
    this.staffLocal = [];
    this.staffLoadError = 'Failed to load team members.';
  } finally {
    if (token === this._staffReqToken) this.staffLoading = false;
  }
},

        onCloseButtonClick(e?: MouseEvent) {
  e?.stopPropagation?.();
  e?.preventDefault?.();

  if (!this.hasUnsavedChanges) {
    this.close();
    return;
  }

  this.showDiscardModal = true;
},

        onBackdropClick(e?: MouseEvent) {
  e?.stopPropagation?.();
  e?.preventDefault?.();

  if (!this.hasUnsavedChanges) {
    this.close();
    return;
  }

  this.showDiscardModal = true; 
},

discardChangesAndClose() {
  this.showDiscardModal = false;
  this.close(); 
},

goBackToEditing() {
  this.showDiscardModal = false;
},

        openNoteModal() {
            this.noteDraft = this.noteText || '';
            this.showNoteModal = true;
        },
        syncSlotRangeWithCart() {
            if (!this.cartItems.length) {
                // If nothing is selected, do not override the slot
                return;
            }

            // Base date/time: same logic as buildBookingPayload
            let baseDate =
                this.slot && this.slot.startIso
                    ? new Date(this.slot.startIso)
                    : new Date();

            const dateStr = this.appointmentDateInput;
            const timeStr =
                this.appointmentStartTimeInput || this.defaultStartTime();

            if (dateStr && timeStr) {
                const [y, m, d] = dateStr.split('-').map((n) => parseInt(n, 10));
                const [hh, mm, ssRaw] = timeStr
                    .split(':')
                    .map((n) => parseInt(n, 10));
                const ss = Number.isFinite(ssRaw) ? ssRaw : 0;

                baseDate = new Date();
                baseDate.setFullYear(y, m - 1, d);
                baseDate.setHours(hh, mm, ss, 0);
            } else {
                baseDate.setSeconds(0, 0);
            }

            let prevEnd: Date | null = null;
            let slotStart: string | null = null;
            let slotEnd: string | null = null;

            this.cartItems.forEach((item: any, index: number) => {
                let start: Date;

                if (item.startTimeLabel) {
                    // Explicit per-service start time
                    start = this.timeValueToDate(item.startTimeLabel);
                } else if (index === 0) {
                    start = new Date(baseDate);
                } else if (prevEnd) {
                    start = new Date(prevEnd);
                } else {
                    start = new Date(baseDate);
                }

                const raw = item.duration ?? item.durationMinutes ?? 0;
                const extra = Number(item.extraMinutes) || 0;
                const totalMinutes = Math.max(0, Number(raw) + extra);

                const end = new Date(start);
                if (totalMinutes > 0) {
                    end.setMinutes(end.getMinutes() + totalMinutes);
                }

                if (!slotStart) {
                    slotStart = this.formatLocalDateTime(start);
                }
                slotEnd = this.formatLocalDateTime(end);
                prevEnd = end;
            });

            this.$store.commit('SET_SELECT_SERVICE_SLOT_RANGE', {
                startIso: slotStart,
                endIso: slotEnd,
            });

            const first = this.cartItems[0];
            if (first && first.staffId) {
                const staff = this.staffOptions.find(
                    (s: any) => s.id === first.staffId,
                );
                if (staff) {
                    this.$store.commit('SET_SELECT_SERVICE_STAFF', staff);
                }
            }
        },
        toggleMonthYearPicker() {
            this.isMonthYearPickerOpen = !this.isMonthYearPickerOpen;
        },

        changePickerYear(delta: number) {
            const now = new Date();
            const currentYear = this.pickerYear ?? now.getFullYear();
            this.pickerYear = currentYear + delta;
        },

        selectPickerMonth(idx: number) {
            const now = new Date();
            if (this.pickerYear == null) {
                this.pickerYear = now.getFullYear();
            }
            this.pickerMonth = idx;
            this.isMonthYearPickerOpen = false; // close picker
        },

        adjustExtraTime(delta: number) {
            if (!this.editingDraft) return;

            const current = Number(this.editingDraft.extraMinutes) || 0;
            const next = current + delta;

            this.editingDraft.extraMinutes = Math.max(0, next);
        },

        toggleDatePicker() {
            this.showDatePicker = !this.showDatePicker;
            if (!this.showDatePicker) {
                this.isMonthYearPickerOpen = false;
            }
        },


        toggleTimePicker() {
            this.showTimePicker = !this.showTimePicker;
        },

        dateToInput(d: Date): string {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        },

        selectDate(d: Date) {
            this.appointmentDateInput = this.dateToInput(d);
            this.pickerYear = d.getFullYear();
            this.pickerMonth = d.getMonth();
            this.showDatePicker = false;
        },

        selectTime(value: string) {
            this.appointmentStartTimeInput = value;
            this.showTimePicker = false;
        },

        prevMonth() {
            const now = new Date();
            let year = this.pickerYear ?? now.getFullYear();
            let month = (this.pickerMonth ?? now.getMonth()) - 1;

            if (month < 0) {
                month = 11;
                year -= 1;
            }

            this.pickerYear = year;
            this.pickerMonth = month;
        },

        nextMonth() {
            const now = new Date();
            let year = this.pickerYear ?? now.getFullYear();
            let month = (this.pickerMonth ?? now.getMonth()) + 1;

            if (month > 11) {
                month = 0;
                year += 1;
            }

            this.pickerYear = year;
            this.pickerMonth = month;
        },

        formatTimeLabel(t: string): string {
            const [hhStr, mmStr] = String(t || '').split(':');
            const hh = parseInt(hhStr || '0', 10);
            const mm = parseInt(mmStr || '0', 10);

            if (!Number.isFinite(hh) || !Number.isFinite(mm)) return t;

            const d = new Date();
            d.setHours(hh, mm, 0, 0);
            return d.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        prepareEditingDiscountOptions(item: any) {
            const options: any[] = [];

            // Always have a "No discount" option
            options.push({
                key: 'none',
                label: 'No discount',
                type: 'none',
                value: 0,
            });

            // Find the service for this item
            const service = this.services.find(
                (s: any) => s.id === item.serviceId,
            );

            if (service) {
                const discounts = Array.isArray(service.discounts)
                    ? service.discounts
                    : [];

                if (discounts.length && this.slot && this.slot.startIso) {
                    const slotDate = new Date(this.slot.startIso);
                    const slotTs = slotDate.getTime();

                    const active = discounts.filter((d: any) => {
                        const status =
                            d.status === true ||
                            d.status === 1 ||
                            d.status === '1';

                        if (!status) return false;

                        const starts = d.starts_at ? new Date(d.starts_at) : null;
                        const ends = d.ends_at ? new Date(d.ends_at) : null;

                        if (starts && slotTs < starts.getTime()) return false;
                        if (ends && slotTs > ends.getTime()) return false;

                        return true;
                    });

                    // Sort by priority (low number = higher priority) then by amount desc
                    active.sort((a: any, b: any) => {
                        const pa = Number(a.priority) || 0;
                        const pb = Number(b.priority) || 0;
                        if (pa !== pb) return pa - pb;

                        const da = Number(a.discount_amount) || 0;
                        const db = Number(b.discount_amount) || 0;
                        return db - da;
                    });

                    // Map to UI options
                    for (const d of active) {
                        const amount = Number(d.discount_amount) || 0;
                        if (!amount) continue;

                        let type: 'percent' | 'amount' | 'none' = 'none';
                        let label = '';

                        if (d.discount_type === 'percentage') {
                            type = 'percent';
                            label = `${amount}% off`;
                        } else if (d.discount_type === 'fixed') {
                            type = 'amount';
                            const formatted = amount.toLocaleString(undefined, {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0,
                            });
                            label = `${this.currencySymbol} ${formatted} off`;
                        } else {
                            continue;
                        }

                        options.push({
                            key: String(d.id ?? `${type}-${amount}`),
                            label,
                            type,
                            value: amount,
                        });
                    }
                }
            }

            // Now handle any *existing* discount on the item (to preserve older bookings)
            const currentType = item.discountType || 'none';
            const currentValue = Number(item.discountValue) || 0;

            let selectedKey = 'none';

            if (currentType !== 'none' && currentValue > 0) {
                // Try to match to one of the configured options
                const match = options.find(
                    (o: any) =>
                        o.type === currentType &&
                        Number(o.value) === currentValue,
                );

                if (match) {
                    selectedKey = match.key;
                } else {
                    // If there is a discount but it's not in the configured list,
                    // add a special "existing" option so it can be kept.
                    let label: string;
                    if (currentType === 'percent') {
                        label = `${currentValue}% off (existing)`;
                    } else {
                        const formatted = currentValue.toLocaleString(undefined, {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0,
                        });
                        label = `${this.currencySymbol} ${formatted} off (existing)`;
                    }

                    const customKey = 'existing';
                    options.push({
                        key: customKey,
                        label,
                        type: currentType,
                        value: currentValue,
                    });
                    selectedKey = customKey;
                }
            }

            this.editingDiscountOptions = options;
            this.editingSelectedDiscountKey = selectedKey;

            // Sync the editingDraft fields based on the selected option
            this.onChangeEditingDiscount();
        },

        onChangeEditingDiscount() {
            if (!this.editingDraft) return;

            const selected = this.editingDiscountOptions.find(
                (o: any) => o.key === this.editingSelectedDiscountKey,
            );

            if (selected) {
                this.editingDraft.discountType = selected.type;
                this.editingDraft.discountValue = selected.value;
            } else {
                this.editingDraft.discountType = 'none';
                this.editingDraft.discountValue = 0;
            }
        },

        toggleQuickActions() {
            this.showQuickActions = !this.showQuickActions;
        },

        openNoteFromQuickActions() {
            this.showQuickActions = false;
            this.openNoteModal();
        },


        closeNoteModal() {
            this.showNoteModal = false;
            this.noteDraft = this.noteText || '';
        },

        saveNote() {
            const next = (this.noteDraft || '').trim();
            this.noteText = next;
            this.showNoteModal = false;
        },

        deleteNote() {
            this.noteText = '';
            this.noteDraft = '';
            this.showNoteModal = false;
        },
        priceMeta(item: any) {
            const priceType = item.priceType || 'fixed';

            const base =
                item.rawPrice != null
                    ? Number(item.rawPrice)
                    : Number(item.price || 0);

            const final = item.price != null ? Number(item.price) : base;

            const discountType = item.discountType || 'none';
            const discountValue = Number(item.discountValue) || 0;

            let hasDiscount = false;
            let discountLabel: string | null = null;

            if (discountType === 'percent' && discountValue > 0) {
                hasDiscount = true;
                discountLabel = `${discountValue}% off`;
            } else if (discountType === 'amount' && discountValue > 0) {
                hasDiscount = true;
                const formatted = discountValue.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });
                discountLabel = `${this.currencySymbol} ${formatted} off`;
            }

            return {
                hasDiscount,
                basePriceLabel: this.formatPrice(base, priceType),
                priceLabel: this.formatPrice(final, priceType),
                discountLabel,
            };
        },

        bestDiscountForService(service: any): any | null {
            const discounts = Array.isArray(service.discounts)
                ? service.discounts
                : [];

            if (!discounts.length || !this.slot || !this.slot.startIso) {
                return null;
            }

            const slotDate = new Date(this.slot.startIso);
            const slotTs = slotDate.getTime();

            const active = discounts.filter((d: any) => {
                const status =
                    d.status === true || d.status === 1 || d.status === '1';

                if (!status) return false;

                const starts = d.starts_at ? new Date(d.starts_at) : null;
                const ends = d.ends_at ? new Date(d.ends_at) : null;

                if (starts && slotTs < starts.getTime()) return false;
                if (ends && slotTs > ends.getTime()) return false;

                return true;
            });

            if (!active.length) return null;

            active.sort((a: any, b: any) => {
                const pa = Number(a.priority) || 0;
                const pb = Number(b.priority) || 0;
                if (pa !== pb) return pa - pb;

                const da = Number(a.discount_amount) || 0;
                const db = Number(b.discount_amount) || 0;
                return db - da;
            });

            return active[0];
        },

        decoratePriceWithDiscount(service: any, basePrice: any) {
            const base = Number(basePrice) || 0;
            const best = this.bestDiscountForService(service);

            if (!best) {
                return {
                    basePrice: base,
                    finalPrice: base,
                    discountType: 'none',
                    discountValue: 0,
                    hasDiscount: false,
                    discountLabel: null,
                };
            }

            const amount = Number(best.discount_amount) || 0;
            let final = base;
            let uiDiscountType: 'none' | 'percent' | 'amount' = 'none';
            let discountValue = 0;
            let label: string | null = null;

            if (best.discount_type === 'percentage') {
                uiDiscountType = 'percent';
                discountValue = amount;
                final = Math.max(0, base - (base * amount) / 100);
                label = `${amount}% off`;
            } else if (best.discount_type === 'fixed') {
                uiDiscountType = 'amount';
                discountValue = amount;
                final = Math.max(0, base - amount);

                const formatted = amount.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });
                label = `${this.currencySymbol} ${formatted} off`;
            }

            return {
                basePrice: base,
                finalPrice: final,
                discountType: uiDiscountType,
                discountValue,
                hasDiscount: true,
                discountLabel: label,
            };
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

            this.$store.commit('SET_CLIENT_ID', newClient.id);

            this.showClientList = false;
            this.clientSearch = '';
            this.showAddClientModal = false;
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

        close() {
            this.$store.commit('CLOSE_SELECT_SERVICE');
        },

        openClientList() {
            this.showClientList = true;
        },

        closeClientList() {
            this.showClientList = false;
        },

        initials(name: string): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 2);
        },

        displayStartTime(item: any, index: number): string {
            if (
                item.startTimeLabel &&
                typeof item.startTimeLabel === 'string'
            ) {
                return item.startTimeLabel;
            }

            if (!this.slot || !this.slot.startIso) {
                return this.appointmentTimeLabel || '';
            }

            const baseStart = new Date(this.slot.startIso);
            let start = new Date(baseStart);

            for (let i = 0; i < this.cartItems.length; i++) {
                const current = this.cartItems[i];

                if (
                    current.startTimeLabel &&
                    typeof current.startTimeLabel === 'string'
                ) {
                    start = this.timeValueToDate(current.startTimeLabel);
                }

                if (i === index) {
                    return start.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                }

                const raw = current.duration ?? current.durationMinutes ?? 0;
                const extra = Number(current.extraMinutes) || 0;
                const stepMinutes = Math.max(0, Number(raw) + extra);

                if (stepMinutes > 0) {
                    const next = new Date(start);
                    next.setMinutes(next.getMinutes() + stepMinutes);
                    start = next;
                }
            }

            return this.appointmentTimeLabel || '';
        },

        displayDuration(item: any): string {
            if (item.durationLabel) return item.durationLabel;
            if (item.duration) return this.formatDuration(item.duration);
            return item.durationLabel || '';
        },
        displayStaffName(item: any): string {
            return (
                item.staffName ||
                (this.staffMember ? this.staffMember.name : '')
            );
        },

        rowsForService(service: any, color: string): any[] {
            const rows: any[] = [];
            const staffProvides = this.staffProvidesService(service);

            const baseDuration = service.duration_minutes || null;
            const basePrice = service.price ?? null;
            const basePriceType = service.price_type || 'fixed';

            const hasVariants =
                Array.isArray(service.variants) && service.variants.length > 0;

            if (hasVariants) {
                for (const v of service.variants) {
                    const duration = v.duration_minutes || baseDuration;
                    const priceRaw =
                        v.price != null && v.price !== '' ? v.price : basePrice;
                    const priceType = v.price_type || basePriceType;

                    const base = Number(priceRaw) || 0;
                    const label = v.name || service.name;
                    const subtitle = v.description || service.description || '';
                    const durationLabel = duration
                        ? this.formatDuration(duration)
                        : null;
                    const priceLabel = this.formatPrice(base, priceType);

                    rows.push({
                        uid: `svc-${service.id}-v-${v.id}`,
                        serviceId: service.id,
                        variantId: v.id,
                        label,
                        subtitle,
                        duration,
                        durationLabel,

                        // base price only – no default discount
                        basePrice: base,
                        price: base,
                        priceType,
                        priceLabel,
                        hasDiscount: false,
                        basePriceLabel: null,
                        discountType: 'none',
                        discountValue: 0,
                        discountLabel: null,

                        staffProvides,
                        color,
                        searchText:
                            (label + ' ' + subtitle).toString().toLowerCase() || '',
                    });
                }
            } else {
                const duration = baseDuration;
                const priceType = basePriceType;
                const base = Number(basePrice) || 0;

                const label = service.name;
                const subtitle = service.description || '';
                const durationLabel = duration
                    ? this.formatDuration(duration)
                    : null;
                const priceLabel = this.formatPrice(base, priceType);

                rows.push({
                    uid: `svc-${service.id}`,
                    serviceId: service.id,
                    variantId: null,
                    label,
                    subtitle,
                    duration,
                    durationLabel,

                    // base price only – no default discount
                    basePrice: base,
                    price: base,
                    priceType,
                    priceLabel,
                    hasDiscount: false,
                    basePriceLabel: null,
                    discountType: 'none',
                    discountValue: 0,
                    discountLabel: null,

                    staffProvides,
                    color,
                    searchText:
                        (label + ' ' + subtitle).toString().toLowerCase() || '',
                });
            }

            return rows;
        },

        staffProvidesService(service: any): boolean {
            const assigned = Array.isArray(service.user) ? service.user : [];
            if (!assigned.length || !this.staffMember) return true;
            return assigned.some((u: any) => u.id === this.staffMember.id);
        },

        formatDuration(minutes: number | null): string {
            if (!minutes) return '';
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            if (h && m) return `${h}h · ${m}min`;
            if (h) return `${h}h`;
            return `${m}min`;
        },

        formatPrice(price: number | null, type: string): string {
            if (type === 'free') return 'Free';
            if (price == null) return '';
            const num = Number(price) || 0;
            const formatted = num.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            if (type === 'from') {
                return `from ${this.currencySymbol} ${formatted}`;
            }
            return `${this.currencySymbol} ${formatted}`;
        },

        serviceCount(row: any): number {
            const sid = row.serviceId;
            const vid = row.variantId ?? null;
            return this.cartItems.filter(
                (i: any) =>
                    i.serviceId === sid && (i.variantId ?? null) === vid,
            ).length;
        },

        isSelected(row: any): boolean {
            return this.serviceCount(row) > 0;
        },

        toggleRow(row: any) {
            const instanceUid = `svc-${row.serviceId}-${row.variantId ?? 'base'
                }-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;

            const baseDuration = row.duration ?? row.durationMinutes ?? 15;

            const basePrice =
                row.basePrice != null
                    ? Number(row.basePrice)
                    : Number(row.price ?? 0);

            const finalPrice = basePrice; // NO auto discount

            const newItem = {
                instanceUid,
                serviceId: row.serviceId,
                variantId: row.variantId ?? null,
                label: row.label,
                subtitle: row.subtitle,
                duration: baseDuration,
                durationMinutes: baseDuration,
                durationLabel:
                    row.durationLabel ?? this.formatDuration(baseDuration),

                price: finalPrice,
                rawPrice: basePrice,
                priceType: row.priceType || 'fixed',
                priceLabel: this.formatPrice(
                    finalPrice,
                    row.priceType || 'fixed',
                ),

                originalPrice: basePrice,
                discountType: 'none',
                discountValue: 0,

                extraMinutes: 0,
                startTimeLabel: null,
                staffId: this.staffMember ? this.staffMember.id : null,
                staffName: this.staffMember ? this.staffMember.name : null,
                color: row.color,
                staffProvides: row.staffProvides,
                searchText: row.searchText,
            };

            this.$store.commit('ADD_SERVICE_INSTANCE', newItem);

            if (this.step === 'services') {
                this.step = 'summary';
            }
        },


        setWalkIn() {
            this.$store.commit('SET_CLIENT_WALKIN');
            this.showClientList = false;
        },

        selectClient(c: any) {
            this.$store.commit('SET_CLIENT_ID', c.id);
            this.showClientList = false;
        },

        createClient() {
            this.editClientMode = 'create';
            this.editingClientId = null;
            this.editingClientData = null;
            this.showAddClientModal = true;
        },

        async openEditClient() {
            if (!this.selectedClientId) return;
            this.editClientMode = 'edit';
            this.editingClientId = this.selectedClientId;
            this.editingClientData = null;
            this.showAddClientModal = true;
        },

        // async fetchClientForEdit(id: number) {
        //     try {
        //         const response = await axios.get(this.route('clients.show', id), {
        //             headers: {
        //                 Accept: 'application/json',
        //             },
        //         });

        //         const client = response.data?.client ?? null;
        //         if (client) {
        //             this.editingClientData = client;
        //             this.showAddClientModal = true;
        //         }
        //     } catch (error) {
        //         console.error('Failed to load client for edit', error);
        //     }
        // },
        handleClientModalClose() {
            this.showAddClientModal = false;
            this.editClientMode = 'create';
            this.editingClientData = null;
            this.editingClientId = null;
        },


        editServices() {
            this.step = 'services';
        },

        deleteItem(item: any) {
            this.$store.commit('REMOVE_SERVICE_INSTANCE', item);
        },

      timeValueToDate(value: string): Date {
  const base =
    this.slot && this.slot.startIso ? new Date(this.slot.startIso) : new Date();

  const hhmm = this.timeToInput(value); // converts "02:15 PM" -> "14:15"
  const [hStr, mStr] = hhmm.split(':');

  const h = parseInt(hStr || '0', 10);
  const m = parseInt(mStr || '0', 10);

  base.setHours(h, m, 0, 0);
  return base;
},

      timeToInput(label: string): string {
  const trimmed = String(label || '').trim();

  
  const m = trimmed.match(/^(\d{1,2})[:.](\d{2})(?:\s*([AaPp][Mm]))?$/);
  if (!m) return this.defaultStartTime();

  let hh = parseInt(m[1], 10);
  const mm = parseInt(m[2], 10);
  const mer = (m[3] || '').toLowerCase();

  if (mer === 'pm' && hh < 12) hh += 12;
  if (mer === 'am' && hh === 12) hh = 0;

  return `${String(hh).padStart(2, '0')}:${String(mm).padStart(2, '0')}`;
},

        defaultStartTime(): string {
            if (!this.slot || !this.slot.startIso) return '10:00';
            const d = new Date(this.slot.startIso);
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        },

        startEdit(item: any, index: number) {
            this.editingOriginalPrice = Number(
                item.originalPrice ?? item.rawPrice ?? item.price ?? 0
            ) || 0;

            this.editingIndex = index;

            const seqLabel =
                this.displayStartTime(item, index) ||
                this.appointmentTimeLabel ||
                this.defaultStartTime();
            const startTime = item.startTimeLabel || this.timeToInput(seqLabel);

            this.editingDraft = {
                label: item.label,
                price: item.rawPrice ?? item.price ?? 0,
                discountType: item.discountType || 'none',
                discountValue: item.discountValue || 0,
                durationMinutes: item.duration ?? 15,
                extraMinutes: item.extraMinutes ?? 0,
                startTime,
                staffId:
                    item.staffId ??
                    (this.staffMember ? this.staffMember.id : null),
                color: item.color || '#f97316',
            };
            this.prepareEditingDiscountOptions(item);
            this.step = 'edit';
        },

        addExtraTime() {
            if (!this.editingDraft) return;
            const current = Number(this.editingDraft.extraMinutes) || 0;
            this.editingDraft.extraMinutes = current + 15;
        },

        deleteEditingItem() {
            if (
                this.editingIndex == null ||
                this.editingIndex < 0 ||
                this.editingIndex >= this.cartItems.length
            ) {
                return;
            }
            const item = this.cartItems[this.editingIndex];
            this.deleteItem(item);
            this.step = 'summary';
            this.editingIndex = null;
            this.editingDraft = null;
        },

        applyEdit() {
            if (
                this.editingIndex == null ||
                this.editingIndex < 0 ||
                this.editingIndex >= this.cartItems.length ||
                !this.editingDraft
            ) {
                return;
            }

            const oldItem = this.cartItems[this.editingIndex];
            const durationTotal = this.editingDurationTotal;
            const total = this.editTotalAmount;

            const staff = this.staffOptions.find(
                (s: any) => s.id === this.editingDraft.staffId,
            );

            const updated = {
                ...oldItem,
                price: total,
                rawPrice: Number(this.editingDraft.price) || 0,
                originalPrice: oldItem.originalPrice ?? oldItem.rawPrice ?? oldItem.price ?? 0,

                discountType: this.editingDraft.discountType,
                discountValue: this.editingDraft.discountValue,
                duration: durationTotal,
                durationMinutes: durationTotal,
                durationLabel: this.formatDuration(durationTotal),
                priceLabel: this.formatPrice(total, oldItem.priceType || 'fixed'),
                startTimeLabel: this.editingDraft.startTime,
                extraMinutes: this.editingDraft.extraMinutes || 0,
                color: this.editingDraft.color,
                staffId: staff ? staff.id : oldItem.staffId,
                staffName: staff ? staff.name : oldItem.staffName,
            };

            this.$store.commit('UPDATE_SERVICE_INSTANCE', {
                index: this.editingIndex,
                item: updated,
            });



            this.step = 'summary';
            this.editingIndex = null;
            this.editingDraft = null;
        },


        buildBookingPayload(mode: 'save' | 'checkout') {

            let baseDate =
                this.slot && this.slot.startIso
                    ? new Date(this.slot.startIso)
                    : new Date();

            const dateStr = this.appointmentDateInput;
            const timeStr = this.appointmentStartTimeInput || this.defaultStartTime();

            if (dateStr && timeStr) {
                const [y, m, d] = dateStr.split('-').map((n) => parseInt(n, 10));
                const [hh, mm, ssRaw] = timeStr.split(':').map((n) => parseInt(n, 10));
                const ss = Number.isFinite(ssRaw) ? ssRaw : 0;

                baseDate = new Date();
                baseDate.setFullYear(y, m - 1, d);
                baseDate.setHours(hh, mm, ss, 0);
            } else {
                baseDate.setSeconds(0, 0, 0);
            }

            const services: any[] = [];
            let prevEnd: Date | null = null;


            this.cartItems.forEach((item: any, index: number) => {
                let start: Date;

                if (item.startTimeLabel) {
                    start = this.timeValueToDate(item.startTimeLabel);
                } else if (index === 0) {
                    start = new Date(baseDate);
                } else if (prevEnd) {
                    start = new Date(prevEnd);
                } else {
                    start = new Date(baseDate);
                }

                const raw = item.duration ?? item.durationMinutes ?? 0;
                const extra = Number(item.extraMinutes) || 0;
                const totalMinutes = Math.max(0, Number(raw) + extra);

                const end = new Date(start);
                if (totalMinutes > 0) {
                    end.setMinutes(end.getMinutes() + totalMinutes);
                }

                prevEnd = end;

                const basePrice = Number(
                    item.rawPrice != null ? item.rawPrice : item.price || 0,
                );
                const discountType = item.discountType || 'none';
                const discountValue = Number(item.discountValue) || 0;

                let finalPrice = basePrice;
                if (discountType === 'percent') {
                    finalPrice = Math.max(
                        0,
                        basePrice - (basePrice * discountValue) / 100,
                    );
                } else if (discountType === 'amount') {
                    finalPrice = Math.max(0, basePrice - discountValue);
                }

                services.push({
                    service_id: item.serviceId,
                    service_variant_id: item.variantId ?? null,
                    staff_id: item.staffId ?? this.staffMember?.id ?? null,

                    label: item.label,
                    duration_minutes: Number(
                        item.duration ?? item.durationMinutes ?? 0,
                    ),
                    extra_minutes: Number(item.extraMinutes || 0),

                    starts_at: this.formatLocalDateTime(start),
                    ends_at: this.formatLocalDateTime(end),

                    price: basePrice,
                    discount_type: discountType,
                    discount_value: discountValue,
                    final_price: finalPrice,

                    color_code: item.color || null,
                });
            });

            const slotStart =
                services.length > 0
                    ? services[0].starts_at
                    : (this.slot?.startIso ?? null);
            const slotEnd =
                services.length > 0
                    ? services[services.length - 1].ends_at
                    : (this.slot?.endIso ?? null);

            return {
                branch_id: this.$page.props.selectedBranchId ?? null,
                client_id: this.isWalkIn ? null : this.selectedClientId,
                staff_id: this.staffMember ? this.staffMember.id : null,

                status: mode === 'checkout' ? 'pending_payment' : 'scheduled',

                slot_start: slotStart,
                slot_end: slotEnd,

                services,

                note: this.noteText && this.noteText.trim().length
                    ? this.noteText.trim()
                    : null,
            };
        },

        confirm() {
            if (!this.cartItems.length || this.isSaving || this.isCheckingOut) {
                return;
            }

            const payload = this.buildBookingPayload('save');
            this.isSaving = true;

            this.$inertia.post(this.route('bookings.store'), payload, {
                onSuccess: () => {
                    this.$store.commit('CLOSE_SELECT_SERVICE');
                },
                onFinish: () => {
                    this.isSaving = false;
                },
            });
        },

        checkout() {
            if (!this.cartItems.length || this.isSaving || this.isCheckingOut) {
                return;
            }

            const payload = this.buildBookingPayload('checkout');
            this.isCheckingOut = true;

            this.$inertia.post(this.route('bookings.store'), payload, {
                onSuccess: (page: any) => {
                    this.$store.commit('CLOSE_SELECT_SERVICE');

                    const booking =
                        page &&
                            (page as any).props &&
                            (page as any).props.booking
                            ? (page as any).props.booking
                            : null;

                    const tipMeta = {
                        bookingId: booking?.id ?? null,
                        booking,
                        baseAmount: this.totalAmount,
                        client: this.selectedClient,
                        staff: this.staffMember,
                        services: this.cartItems,
                        currencySymbol: this.currencySymbol,
                        branch_id:
                            (page as any).props?.branch_id ??
                            this.$page.props.selectedBranchId ??
                            null,
                    };

                    this.$store.commit('OPEN_TIP_PANEL', tipMeta);
                },
                onFinish: () => {
                    this.isCheckingOut = false;
                },
            });
        },

        truncateText(text: string, limit = 10): string {
            if (!text) return '';
            const str = String(text);
            if (str.length <= limit) return str;
            return str.slice(0, limit) + '...';
        },
        handleClickOutside(event) {
            if (this.showDurationDropdown && !event.target.closest('.relative')) {
                this.showDurationDropdown = false;
            }
            if (this.showStaffDropdown && !event.target.closest('.dropdown-staff')) {
                this.showStaffDropdown = false;
            }
            if (this.showDiscountDropdown && !event.target.closest('.dropdown-discount')) {
                this.showDiscountDropdown = false;
            }
            if (this.showDatePicker && !event.target.closest('.datepicker-wrapper')) {
                this.showDatePicker = false;
                this.isMonthYearPickerOpen = false;
            }
            if (this.showTimePicker && !event.target.closest('.timepicker-wrapper')) {
                this.showTimePicker = false;
            }

            if (this.showQuickActions && !event.target.closest('[data-quick-actions]')) {
                this.showQuickActions = false;
            }
        },

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

.slide-step-enter-active,
.slide-step-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}

.slide-step-enter-from,
.slide-step-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

.slide-left-enter-active,
.slide-left-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}

.slide-left-enter-from,
.slide-left-leave-to {
    opacity: 0;
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}

.btn-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 9999px;
    border-width: 2px;
    border-style: solid;
    border-color: rgba(0, 0, 0, 0.15);
    border-top-color: currentColor;
    animation: btn-spin 0.7s linear infinite;
}

.btn-spinner--light {
    border-color: rgba(255, 255, 255, 0.35);
    border-top-color: #ffffff;
}

@keyframes btn-spin {
    to {
        transform: rotate(360deg);
    }
}

.max-h-48 {
    scrollbar-width: thin;
    scrollbar-color: #d4d4d4 #f5f5f5;
}

.max-h-48::-webkit-scrollbar {
    width: 6px;
}

.max-h-48::-webkit-scrollbar-track {
    background: #f5f5f5;
    border-radius: 3px;
}

.max-h-48::-webkit-scrollbar-thumb {
    background-color: #d4d4d4;
    border-radius: 3px;
}

.max-h-48::-webkit-scrollbar-thumb:hover {
    background-color: #a3a3a3;
}

.btn-pointer {
    cursor: pointer;
}

.btn-pointer:disabled {
    cursor: not-allowed;
}
</style>
