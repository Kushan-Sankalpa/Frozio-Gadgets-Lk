<template>
    <transition name="fade">
        <div v-if="show" class="pointer-events-none fixed inset-0 z-[120] flex">
            <!-- dim background on desktop -->
            <div class="hidden flex-1 bg-neutral-900/30 md:block" @click="close"></div>

            <!-- main right panel -->
            <div
                class="pointer-events-auto relative flex h-full w-full max-w-7xl flex-col bg-white shadow-2xl md:ml-auto md:rounded-l-2xl">
                <!-- HEADER -->
                <header class="flex items-center justify-between border-b px-8 py-5">
                    <div class="space-y-3">
                        <!-- breadcrumbs -->
                        <nav v-if="!hideBreadcrumbs"
                            class="flex items-center gap-3 text-[16px] font-medium text-neutral-500">
                            <button type="button" :class="isCartStep
                                ? 'text-neutral-900'
                                : 'text-neutral-500 hover:text-neutral-900'
                                " @click="step = 'cart'">
                                Cart
                            </button>
                            <span class="text-neutral-400">›</span>

                            <button type="button" :class="isTipStep
                                ? 'text-neutral-900'
                                : 'text-neutral-500 hover:text-neutral-900'
                                " @click="step = 'tip'">
                                Tip
                            </button>
                            <span class="text-neutral-400">›</span>
                            <button type="button" :class="isPaymentStep
                                ? 'text-neutral-900'
                                : 'text-neutral-500 hover:text-neutral-900'
                                " @click="step = 'payment'">
                                Payment
                            </button>
                        </nav>

                        <div>
                            <h1 class="mt-1 text-3xl font-semibold text-neutral-900 sm:text-4xl">
                                {{ headerTitle }}
                            </h1>

                            <!-- subtitle: different for each step -->
                            <p v-if="
                                step === 'tip' &&
                                (staffName || businessLabel)
                            " class="mt-1 text-base text-neutral-500">
                                Select an amount
                                <template v-if="tipRecipientsLabel">
                                    for <span class="font-semibold">{{ tipRecipientsLabel }}</span>
                                </template>


                                <template v-if="businessLabel">
                                    <span class="mx-1">|</span>
                                    <span class="font-semibold">{{
                                        businessLabel
                                        }}</span>
                                </template>
                            </p>

                            <p v-else-if="step === 'payment'" class="mt-1 text-base text-neutral-500"></p>
                        </div>



                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Mobile cart button -->
                        <button v-if="isMobile" type="button"
                            class="flex size-10 items-center justify-center rounded-full border border-neutral-300 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 relative md:hidden"
                            @click="openMobileSummary" aria-label="View order details">
                            <i class="bx bx-cart text-lg"></i>
                            <span v-if="items.length"
                                class="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-orange-500 text-[10px] font-semibold text-white">
                                {{ items.length }}
                            </span>
                        </button>

                        <button type="button"
                            class="flex size-10 items-center justify-center rounded-full border border-neutral-300 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                            @click="close">
                            ✕
                        </button>
                    </div>
                </header>

                <!-- BODY -->
                <main class="flex min-h-0 flex-1 flex-col md:flex-row">
                    <!-- LEFT SIDE: steps (only this changes between Tip / Payment) -->
                    <section class="flex-1 overflow-y-auto border-b px-8 py-7 md:border-r md:border-b-0">
                        <transition name="step-slide" mode="out-in">



                            <!-- STEP 1: Cart -->
                            <div v-if="step === 'cart'" key="cart">
                                <!-- cart nav -->
                                <nav
                                    class="mb-6 flex flex-wrap items-center gap-6 text-sm font-medium text-neutral-700">
                                    <!-- <span class="cursor-default text-neutral-600">
                                        Quick Sale
                                    </span>
                                    <span class="cursor-default text-neutral-600">
                                        Appointments
                                    </span> -->

                                    <!-- ACTIVE + ONLY WORKING TAB -->
                                    <button type="button"
                                        class="rounded-full bg-black px-4 py-1.5 text-sm font-semibold text-white">
                                        Services
                                    </button>

                                    <!-- <span class="cursor-default text-neutral-600">
                                        Products
                                    </span>
                                    <span class="cursor-default text-neutral-600">
                                        Memberships
                                    </span>
                                    <span class="cursor-default text-neutral-600">
                                        Gift cards
                                    </span> -->
                                </nav>


                                <!--  ALL SERVICES – grouped by category -->
                                <div class="space-y-4">
                                    <!-- <div>
                                        <input v-model="search" type="text"
                                            class="w-full rounded-full border border-neutral-300 px-4 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 focus:outline-none"
                                            placeholder="Search services" />
                                    </div> -->
                                    <!-- 1️ PAGE: list of categories -->
                                    <template v-if="!selectedCategoryId">
                                        <div v-if="!serviceCategories.length"
                                            class="rounded-2xl border border-dashed border-neutral-300 px-4 py-6 text-sm text-neutral-500">
                                            <span class="font-medium text-neutral-800"></span>.
                                        </div>

                                        <div v-else class="space-y-3">
                                            <button v-for="cat in serviceCategories" :key="cat.id || cat.name"
                                                type="button"
                                                class="flex w-full items-center justify-between rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-left text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                                @click="openCategory(cat)">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <!-- colored stripe like screenshot -->
                                                    <div class="h-10 w-1.5 rounded-full" :style="{
                                                        backgroundColor:
                                                            cat.color_code || defaultServiceColor,
                                                    }"></div>
                                                    <div class="min-w-0">
                                                        <div class="truncate text-base font-semibold text-neutral-900">
                                                            {{ cat.name }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ml-3 flex items-center gap-2 text-xs text-neutral-500">
                                                    <!-- count badge -->
                                                    <span
                                                        class="inline-flex h-5 min-w-[1.5rem] items-center justify-center rounded-full bg-neutral-100 px-2 text-[11px] font-medium text-neutral-700">
                                                        {{ cat.count }}
                                                    </span>
                                                    <span class="text-lg leading-none text-neutral-400">
                                                        ›
                                                    </span>
                                                </div>
                                            </button>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <!-- header row: back + category name -->
                                        <div class="flex items-center gap-3">
                                            <button type="button"
                                                class="flex h-9 w-9 items-center justify-center rounded-full hover:bg-neutral-100"
                                                @click="selectedCategoryId = null">
                                                <span class="text-xl">‹</span>
                                            </button>
                                            <div>
                                                <div class="text-base font-semibold text-neutral-900">
                                                    {{ selectedCategoryName }}
                                                </div>
                                                <div class="text-[12px] text-neutral-500">
                                                    {{ servicesForSelectedCategory.length }}
                                                    service<span
                                                        v-if="servicesForSelectedCategory.length !== 1">s</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="!servicesForSelectedCategory.length"
                                            class="mt-4 rounded-2xl border border-dashed border-neutral-300 px-4 py-6 text-sm text-neutral-500">
                                            No services in this category.
                                        </div>

                                        <div v-else class="mt-4 space-y-3">
                                            <button v-for="svc in servicesForSelectedCategory"
                                                :key="svc.id || svc.uuid || svc.name" type="button"
                                                class="flex w-full items-center justify-between rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-left text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                                @click="addServiceToCart(svc)">
                                                <div class="flex items-start gap-3 min-w-0">
                                                    <!-- same color stripe as category -->
                                                    <div class="mt-0.5 h-10 w-1.5 rounded-full" :style="{
                                                        backgroundColor:
                                                            selectedCategoryColor || defaultServiceColor,
                                                    }"></div>

                                                    <div class="min-w-0">
                                                        <div class="truncate text-sm font-medium text-neutral-900">
                                                            {{ svc.name || svc.label }}
                                                        </div>

                                                        <div v-if="svc.staff_name || svc.staffName"
                                                            class="mt-0.5 text-[12px] text-neutral-500">
                                                            {{ svc.staff_name || svc.staffName }}
                                                        </div>

                                                        <div v-if="
                                                            svc.duration_label ||
                                                            svc.duration ||
                                                            svc.duration_minutes
                                                        " class="mt-0.5 text-[12px] text-neutral-500">
                                                            {{
                                                                svc.duration_label ||
                                                                svc.duration ||
                                                                ((svc.duration_minutes || 0) + 'min')
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ml-3 text-right text-sm text-neutral-900">
                                                    <div class="text-[11px] uppercase tracking-wide text-neutral-400"
                                                        v-if="svc.from_price">
                                                        from
                                                    </div>
                                                    <div class="text-sm font-semibold tabular-nums">
                                                        {{ currencySymbol }}
                                                        {{
                                                            formatNumber(
                                                                svc.price ??
                                                                svc.amount ??
                                                                svc.from_price ??
                                                                0,
                                                            )
                                                        }}
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                            </div>


                            <!-- STEP 1: TIP -->
                            <div v-else-if="step === 'tip'" key="tip">
                                <p class="mb-5 text-base text-neutral-500">
                                    Base amount:
                                    <span class="font-semibold text-neutral-900">
                                        {{ currencySymbol }}
                                        {{ baseAmountFormatted }}
                                    </span>
                                </p>
                                <!-- Tip recipient selector -->
                                <!-- <div class="mb-5 rounded-2xl border border-neutral-200 bg-white p-4"> -->
                                    <!-- <div class="text-xs font-semibold tracking-wide text-neutral-500 uppercase">
                                        Tip recipient
                                    </div> -->

                                    <!-- Multi: show split info, no selector -->
                                    <!-- <div v-if="tipRecipientOptions.length > 1" class="mt-2 text-sm text-neutral-600">
                                        Tip will be split between:
                                        <span class="font-semibold text-neutral-900">{{ tipRecipientsLabel }}</span>
                                    </div> -->

                                    <!-- Single: show name -->
                                    <!-- <div v-else class="mt-2 text-sm text-neutral-600">
                                        Tip goes to:
                                        <span class="font-semibold text-neutral-900">{{ selectedTipRecipientName
                                            }}</span>
                                    </div> -->
                                <!-- </div> -->


                                <!-- TIP CARDS -->
                                <div class="grid gap-5 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                                    <!-- No tip -->
                                    <button type="button"
                                        class="flex min-h-[140px] flex-col items-start rounded-3xl border px-6 py-5 text-left text-base sm:text-lg"
                                        :class="cardClass('none')" @click="selectNone">
                                        <div class="text-lg font-semibold sm:text-xl">
                                            No tip
                                        </div>
                                        <div class="mt-3 text-sm text-neutral-500">
                                            No extra amount will be added
                                        </div>
                                    </button>

                                    <!-- percentage options -->
                                    <button v-for="opt in percentOptions" :key="opt" type="button"
                                        class="flex min-h-[140px] flex-col items-start rounded-3xl border px-6 py-5 text-left text-base sm:text-lg"
                                        :class="cardClass('percent', opt)" @click="selectPercent(opt)">
                                        <div class="text-xl font-semibold sm:text-2xl">
                                            {{ opt }}%
                                        </div>
                                        <div class="mt-3 text-sm text-neutral-500 sm:text-base">
                                            {{ currencySymbol }}
                                            {{ percentAmount(opt) }}
                                        </div>
                                    </button>

                                    <!-- custom tip -->
                                    <button type="button"
                                        class="flex min-h-[130px] flex-col items-start rounded-3xl border px-6 py-5 text-left text-base col-span-2 sm:col-span-2 sm:text-lg lg:col-span-3"
                                        :class="cardClass('custom')" @click="openCustomModal">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl leading-none">＋</span>
                                            <span class="text-lg font-semibold sm:text-xl">
                                                Custom tip
                                            </span>
                                        </div>
                                        <div class="mt-3 text-sm text-neutral-500 sm:text-base">
                                            {{ customSummaryLabel }}
                                        </div>
                                    </button>
                                </div>

                                                        <div v-if="tipAmount > 0 && tipRecipientOptions.length > 1"
                            class="mt-4 text-sm text-neutral-600">
                            Split tip:
                            <span class="font-medium">
                                {{ splitSummaryLabel }}
                            </span>
<button
  class="text-blue-600 hover:underline ml-2"
  @click.stop.prevent="openSplitTipEditor"
>
  Edit
</button>
                        </div>
                            </div>

                            

                          <!-- STEP 2: PAYMENT -->
<div v-else key="payment">
  <div class="flex h-full flex-col">

    <template v-if="isSplitPaymentView">
      <div class="mb-6 flex items-center gap-3">
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-full border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="paymentMethod = null; $store.commit('SET_TIP_PAYMENT_METHOD', null)"
          aria-label="Back"
          title="Back"
        >
          <i class="bx bx-arrow-back text-xl"></i>
        </button>

        <div class="min-w-0">
          <div class="text-lg font-semibold text-neutral-900">Split payment</div>
          <div class="text-sm text-neutral-500">
            Add one or more payments to cover {{ currencySymbol }} {{ totalWithTipFormatted }}
          </div>
        </div>
      </div>

      <!-- list of split lines -->
      <div class="flex-1 space-y-3">
        <div
          v-if="!payments.length"
          class="rounded-2xl border border-dashed border-neutral-300 bg-white px-4 py-6 text-sm text-neutral-500"
        >
          No payments added yet. Click <span class="font-medium text-neutral-900">Add payment</span> to start.
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="(p, idx) in payments"
            :key="'split-line-' + idx"
            class="flex items-center justify-between rounded-2xl border border-neutral-200 bg-white px-4 py-3"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-50 text-neutral-700">
                <i :class="iconForPayment(p.method)" class="text-xl"></i>
              </div>

              <div class="min-w-0">
                <div class="truncate text-sm font-medium text-neutral-900">
                  {{ paymentLabelFor(p.method) }}
                  <span v-if="splitLineSubtitle" class="text-neutral-500"> · {{ splitLineSubtitle }}</span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="text-sm font-semibold tabular-nums text-neutral-900">
                {{ currencySymbol }} {{ formatNumber(p.amount) }}
              </div>

              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full text-neutral-400 hover:bg-neutral-100 hover:text-red-500"
                @click.stop.prevent="removePaymentAt(idx)"
                aria-label="Remove"
                title="Remove"
              >
                <i class="bx bx-trash text-lg"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- add payment row -->
      <div class="mt-6 flex items-center justify-between">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-900 hover:border-neutral-900 hover:bg-neutral-900 hover:text-white disabled:opacity-40"
          :disabled="isFullyPaid"
          @click="splitSelectModalOpen = true"
        >
          <i class="bx bx-plus text-lg"></i>
          <span>Add payment</span>
        </button>

        <div class="text-sm text-neutral-500">
          Remaining ·
          <span class="font-semibold text-neutral-900 tabular-nums">
            {{ currencySymbol }} {{ leftToPayFormatted }}
          </span>
        </div>
      </div>
    </template>

    <!-- NORMAL PAYMENT VIEW (cash/card/gift/split tiles) -->
    <template v-else>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4">
        <!-- Cash -->
        <button type="button"
          class="flex min-h-[130px] flex-col items-start rounded-3xl border px-4 py-4 text-left text-sm sm:text-lg justify-center disabled:opacity-40 disabled:cursor-not-allowed"
          :class="paymentCardClass('cash')" :disabled="isFullyPaid"
          @click="onCashCardClick">
          <div class="flex flex-col items-center justify-center w-full text-center lg:hidden">
            <i class="bx bx-money text-2xl mb-2"></i>
            <span class="text-lg sm:text-xl lg:text-lg font-semibold">Cash</span>
          </div>
          <div class="hidden lg:flex lg:items-center lg:gap-3">
            <i class="bx bx-money text-2xl"></i>
            <span class="text-lg sm:text-xl lg:text-lg font-semibold">Cash</span>
          </div>
        </button>

        <!-- Card -->
        <button type="button"
          class="flex min-h-[130px] flex-col items-start rounded-3xl border px-4 py-4 text-left text-sm sm:text-lg justify-center disabled:opacity-40 disabled:cursor-not-allowed"
          :class="paymentCardClass('card')" :disabled="isFullyPaid"
          @click="onCardCardClick">
          <div class="flex flex-col items-center justify-center w-full text-center lg:hidden">
            <i class="bx bx-credit-card text-2xl mb-2"></i>
            <span class="text-sm font-semibold block">Card</span>
          </div>
          <div class="hidden lg:flex lg:items-center lg:gap-3">
            <i class="bx bx-credit-card text-2xl"></i>
            <span class="text-lg sm:text-xl lg:text-lg font-semibold">Card</span>
          </div>
        </button>

        <!-- Gift card -->
        <button type="button"
          class="flex min-h-[130px] flex-col items-start rounded-3xl border px-4 py-4 text-left text-sm sm:text-lg justify-center disabled:opacity-40 disabled:cursor-not-allowed"
          :class="paymentCardClass('gift-card')" :disabled="isFullyPaid"
          @click="onGiftCardCardClick">
          <div class="flex flex-col items-center justify-center w-full text-center lg:hidden">
            <i class="bx bx-gift text-2xl mb-2"></i>
            <span class="text-sm font-semibold block">Gift Card</span>
          </div>
          <div class="hidden lg:flex lg:items-center lg:gap-3">
            <i class="bx bx-gift text-2xl"></i>
            <span class="text-lg sm:text-xl lg:text-lg font-semibold">Gift card</span>
          </div>
        </button>

        <!-- Split payment -->
        <button type="button"
          class="flex min-h-[130px] flex-col items-start rounded-3xl border px-4 py-4 text-left text-sm sm:text-lg justify-center disabled:opacity-40 disabled:cursor-not-allowed"
          :class="paymentCardClass('split')" :disabled="isFullyPaid"
          @click="onSplitCardClick">
          <div class="flex flex-col items-center justify-center w-full text-center lg:hidden">
            <i class="bx bx-transfer text-2xl mb-2"></i>
            <span class="text-sm font-semibold block">Split payment</span>
          </div>
          <div class="hidden lg:flex lg:items-center lg:gap-3">
            <i class="bx bx-transfer text-2xl"></i>
            <span class="text-lg sm:text-xl lg:text-lg font-semibold">Split payment</span>
          </div>
        </button>
      </div>

      <div v-if="paymentMethod" class="mt-8 space-y-1 text-sm text-neutral-500">
        <p>
          Selected:
          <span class="font-medium text-neutral-900">{{ selectedPaymentLabel }}</span>
        </p>
        <p class="text-[12px] text-neutral-400">
          When you save, this method will be used for the payment record.
        </p>
      </div>
    </template>

  </div>
</div>

                        </transition>
                    </section>

                    <!-- RIGHT SIDE: summary sidebar -->
                    <aside class="flex w-full max-w-md flex-col bg-neutral-50 lg:max-w-lg">
                        <!-- Desktop sidebar content -->
                        <div class="hidden md:flex flex-col flex-1 min-h-0">
                            <div class="flex-1 space-y-4 overflow-y-auto px-6 py-5">
                                <!-- Client section -->
                                <div v-if="clientName" class="rounded-2xl bg-white p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="grid size-10 place-items-center rounded-full bg-neutral-800 text-sm font-semibold text-white">
                                                {{ initials(clientName) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="truncate text-base font-semibold text-neutral-900">
                                                    {{ clientName }}
                                                </div>
                                                <div v-if="clientEmail" class="truncate text-[12px] text-neutral-500">
                                                    {{ clientEmail }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button v-else-if="!isPaymentPending" type="button"
                                    class="flex w-full flex-col items-start justify-between rounded-2xl border-2 border-dashed border-neutral-300 bg-white px-4 py-4 text-left text-base shadow-sm hover:border-neutral-500 hover:bg-neutral-50"
                                    @click="onAddClient">
                                    <span class="text-base font-semibold text-[#111827]">Add client</span>
                                    <span class="mt-1 text-[12px] text-neutral-500">
                                        Leave empty for walk-ins
                                    </span>
                                </button>


                                <!-- Services + totals -->
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <h2 class="text-base font-semibold tracking-wide text-neutral-500 uppercase">
                                        Services
                                    </h2>

                                    <div class="mt-3 space-y-2 text-sm">
                                        <button v-for="(item, i) in items" :key="item.instanceUid || i" type="button"
                                            class="group flex w-full items-center justify-between rounded-xl border px-3 py-2.5 text-sm hover:border-neutral-400 hover:bg-neutral-50 sm:text-base"
                                            :style="{
                                                borderLeft: '4px solid ' + (item.color || item.color_code || defaultServiceColor),
                                            }" @click="openEditItem(item, i)">
                                            <div class="flex flex-col items-start">
                                                <span class="truncate font-medium text-neutral-900">
                                                    {{ item.label }}
                                                </span>
                                                <span v-if="item.staff_name || item.staffName"
                                                    class="mt-0.5 text-[12px] text-neutral-500">
                                                    {{ item.staff_name || item.staffName }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-right">
                                                <span class="text-sm font-semibold tabular-nums sm:text-base">
                                                    {{ currencySymbol }}
                                                    {{ formatNumber((item.price ?? item.final_price ?? 0) *
                                                        (item.quantity ?? 1)) }}
                                                </span>

                                                <!-- Delete button -->
                                                <button v-if="isCartStep" type="button"
                                                    class="ml-1 hidden rounded-full p-1 text-neutral-400 group-hover:inline-flex"
                                                    :class="items.length === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:text-red-500 cursor-pointer'"
                                                    :title="items.length === 1 ? 'At least one service is required' : 'Remove service'"
                                                    @click.stop="onRemoveItemClick(i)" aria-label="Remove service">
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            </div>
                                        </button>
                                    </div>

                                    <div class="mt-4 space-y-1.5 border-t pt-3 text-sm">
                                        <div class="flex justify-between text-neutral-500">
                                            <span>Services total</span>
                                            <span class="tabular-nums">
                                                {{ currencySymbol }}
                                                {{ discountBaseTotalFormatted }}
                                            </span>
                                        </div>

                                        <!-- total discount -->
                                        <div v-if="totalDiscount > 0" class="flex justify-between text-neutral-500">
                                            <span>Discount</span>
                                            <span class="tabular-nums">
                                                - {{ currencySymbol }}
                                                {{ totalDiscountFormatted }}
                                            </span>
                                        </div>

                                        <!-- subtotal AFTER discount -->
                                        <div class="flex justify-between text-neutral-500">
                                            <span>Subtotal</span>
                                            <span class="tabular-nums">
                                                {{ currencySymbol }}
                                                {{ baseAmountFormatted }}
                                            </span>
                                        </div>

                                        <div v-if="taxAmount > 0" class="flex justify-between text-neutral-500">
                                            <span>Tax</span>
                                            <span class="tabular-nums">
                                                {{ currencySymbol }}
                                                {{ taxAmountFormatted }}
                                            </span>
                                        </div>

                                        <div v-if="tipAmount > 0" class="flex justify-between text-neutral-500">
                                            <span>Tip</span>
                                            <span class="tabular-nums">
                                                {{ currencySymbol }}
                                                {{ tipAmountFormatted }}
                                            </span>
                                        </div>
                                        <!-- <div v-if="tipAmount > 0 && selectedTipRecipientName"
                                            class="text-[12px] text-neutral-400 mt-1">
                                            Tip goes to: {{ selectedTipRecipientName }}
                                        </div> -->

                                        <div class="mt-2 flex justify-between text-lg font-semibold text-neutral-900">
                                            <span>Total</span>
                                            <span class="tabular-nums">
                                                {{ currencySymbol }}
                                                {{ grandTotalFormatted }}
                                            </span>
                                        </div>
<!-- Payments summary-->
<div v-if="isPaymentStep && payments.length" class="mt-3 border-t pt-3 space-y-2">
  <div
    v-for="(p, idx) in payments"
    :key="'pay-sum-' + idx"
    class="flex items-center justify-between"
  >
    <div class="flex items-center gap-2 min-w-0">
      <span class="truncate text-sm font-medium text-neutral-700">
        {{ paymentLabelFor(p.method) }}
      </span>

      <button
        type="button"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 hover:bg-neutral-100 hover:text-red-500"
        @click.stop.prevent="removePaymentAt(idx)"
        aria-label="Remove payment"
        title="Remove"
      >
        <i class="bx bx-trash text-lg"></i>
      </button>
    </div>

    <span class="text-sm font-semibold tabular-nums text-neutral-900">
      {{ currencySymbol }} {{ formatNumber(p.amount) }}
    </span>
  </div>
</div>

<div v-if="isPaymentStep && payments.length" class="pt-2 text-sm font-semibold text-neutral-900">
  {{ isFullyPaid ? 'Full payment added' : '' }}
</div>

                                        <!-- Add to cart button -->
                                        <div v-if="(isTipStep || isPaymentStep) && !isPaymentPending" class="mt-4">

                                            <button type="button"
                                                class="inline-flex items-center gap-2 rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-900 hover:border-neutral-900 hover:bg-neutral-900 hover:text-white"
                                                @click="goToCartFromSummary">
                                                <i class="bx bx-cart-add text-base"></i>
                                                <span>Add to cart</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FOOTER  -->
                        <footer class="border-t bg-white px-4 py-3 sm:px-6 sm:py-4">
                            <!-- row 1: label + amount -->
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-neutral-700">
                                    {{ footerLabel }}
                                </div>

                                <div class="text-sm font-semibold text-neutral-900 tabular-nums">
                                    {{ currencySymbol }} {{ footerAmountFormatted }}
                                </div>
                            </div>

                            <!-- row 2: kebab + primary -->
                            <div class="mt-3 flex items-center gap-3">

                                <button type="button"
                                    class="h-11 flex-1 rounded-full bg-neutral-900 px-6 text-sm font-semibold text-white hover:bg-black disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer inline-flex items-center justify-center"
                                    :disabled="!canSave" @click="handlePrimaryAction">
                                    <span v-if="isSaving" class="spinner mr-2"></span>
                                    <span>
                                        <template v-if="step === 'cart'">Continue to tip</template>
                                        <template v-else-if="step === 'tip'">Continue to payment</template>
                                        <template v-else>{{ isSaving ? 'Saving…' : (leftToPay > 0 && payments.length > 0 ? 'Save part paid' : 'Save') }}</template>
                                    </span>
                                </button>

                            </div>
                        </footer>

                    </aside>
                </main>


                <!-- CUSTOM TIP MODAL (keypad) -->
                <transition name="tip-modal">
                    <div v-if="customModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[130] flex items-center justify-center bg-neutral-900/40">
                        <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl md:max-w-md"
                            @click.stop>
                            <!-- modal header -->
                            <div class="mb-5 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    Add a tip
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeCustomModal">
                                    ✕
                                </button>
                            </div>

                            <!-- big value + toggle -->
                            <div class="text-center">
                                <div class="text-xs font-medium tracking-[0.14em] text-neutral-400 uppercase">
                                    {{
                                        customIsPercent
                                            ? 'TIP PERCENT'
                                            : currencySymbol
                                    }}
                                </div>
                                <div class="mt-2 text-5xl font-semibold tracking-tight text-neutral-900 tabular-nums">
                                    {{ customMainDisplay }}
                                </div>

                                <div class="mt-4 inline-flex rounded-xl border bg-neutral-50 p-1">
                                    <button type="button"
                                        class="flex items-center gap-1 rounded-lg px-4 py-1.5 text-xs font-medium"
                                        :class="!customIsPercent
                                            ? 'bg-white text-neutral-900 shadow-sm'
                                            : 'text-neutral-500'
                                            " @click="setCustomKind('amount')">
                                        <span>{{ currencySymbol }}</span>
                                    </button>
                                    <button type="button"
                                        class="flex items-center gap-1 rounded-lg px-4 py-1.5 text-xs font-medium"
                                        :class="customIsPercent
                                            ? 'bg-white text-neutral-900 shadow-sm'
                                            : 'text-neutral-500'
                                            " @click="setCustomKind('percent')">
                                        <span>%</span>
                                    </button>
                                </div>
                            </div>

                            <!-- keypad -->
                            <div class="mt-7 grid grid-cols-3 gap-3">
                                <button v-for="key in keypadKeys" :key="key" type="button"
                                    class="flex h-14 items-center justify-center rounded-full border border-neutral-300 bg-white text-xl font-semibold text-neutral-900 shadow-sm hover:bg-neutral-50"
                                    @click="onKeypadPress(key)">
                                    <span v-if="key !== 'back'">{{ key }}</span>
                                    <span v-else>⌫</span>
                                </button>
                            </div>

                            <!-- bottom row -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="text-sm text-neutral-500">
                                    {{ currentTipPercentLabel }}
                                </div>
                                <button type="button"
                                    class="rounded-full bg-black px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600"
                                    @click="confirmCustomTip">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- CASH AMOUNT MODAL (POS keypad) -->
                <transition name="cash-modal">
                    <div v-if="cashModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[132] flex items-center justify-center bg-neutral-900/40">
                        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-2 sm:mx-0"
                            @click.stop>
                            <div class="mb-5 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    Add cash amount
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeCashModal">
                                    ✕
                                </button>
                            </div>

                            <!-- main amount -->
                            <div class="text-center">
                                <div class="text-xs font-medium tracking-[0.18em] text-neutral-400 uppercase">
                                    {{ currencySymbol }}
                                </div>
                                <div class="mt-2 text-5xl font-semibold tracking-tight text-neutral-900 tabular-nums">
                                    {{ cashInputDisplay }}
                                </div>

                                <!-- quick amounts -->
                                <div class="mt-4 flex items-center gap-2 overflow-x-auto pb-1">
                                    <button v-for="amt in cashQuickAmounts" :key="amt" type="button"
                                        class="rounded-full border border-neutral-300 px-3 py-1.5 text-xs font-medium whitespace-nowrap text-neutral-800 hover:bg-neutral-100"
                                        @click="setCashFromQuick(amt)">
                                        {{ currencySymbol }}
                                        {{ formatNumberNoCents(amt) }}
                                    </button>
                                </div>
                            </div>

                            <!-- keypad -->
                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <button v-for="key in keypadKeys" :key="'cash-' + key" type="button"
                                    class="flex h-14 items-center justify-center rounded-full border border-neutral-300 bg-white text-xl font-semibold text-neutral-900 shadow-sm hover:bg-neutral-50"
                                    @click="onCashKeypadPress(key)">
                                    <span v-if="key !== 'back'">{{ key }}</span>
                                    <span v-else>⌫</span>
                                </button>
                            </div>

                            <!-- footer info -->
                            <div class="mt-6 flex items-center justify-between text-[13px]">
                                <div class="space-y-1">
                                    <div class="text-neutral-500">
                                        Cash received by ·
                                        <span class="font-medium text-neutral-900">
                                            {{
                                                staffName ||
                                                businessLabel ||
                                                'Front desk'
                                            }}
                                        </span>
                                    </div>
                                    <div class="text-neutral-500">
                                        Left to pay ·
                                        <span class="font-semibold text-neutral-900 tabular-nums">
                                            {{ currencySymbol }}
                                            {{ cashLeftAfterFormatted }}
                                        </span>
                                    </div>
                                </div>

                                <button type="button"
                                    class="rounded-full bg-black px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 disabled:opacity-40"
                                    :disabled="cashAmountNum <= 0" @click="confirmCashAmount">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- CARD AMOUNT MODAL -->
                <transition name="card-modal">
                    <div v-if="cardModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[132] flex items-center justify-center bg-neutral-900/40">
                        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-2 sm:mx-0"
                            @click.stop>
                            <div class="mb-5 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    Add card amount
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeCardModal">
                                    ✕
                                </button>
                            </div>

                            <!-- main amount -->
                            <div class="text-center">
                                <div class="text-xs font-medium tracking-[0.18em] text-neutral-400 uppercase">
                                    {{ currencySymbol }}
                                </div>
                                <div class="mt-2 text-5xl font-semibold tracking-tight text-neutral-900 tabular-nums">
                                    {{ cardInputDisplay }}
                                </div>

                                <!-- quick amounts -->
                                <div class="mt-4 flex items-center gap-2 overflow-x-auto pb-1">
                                    <button v-for="amt in cardQuickAmounts" :key="amt" type="button"
                                        class="rounded-full border border-neutral-300 px-3 py-1.5 text-xs font-medium whitespace-nowrap text-neutral-800 hover:bg-neutral-100"
                                        @click="setCardFromQuick(amt)">
                                        {{ currencySymbol }}
                                        {{ formatNumberNoCents(amt) }}
                                    </button>
                                </div>
                            </div>

                            <!-- keypad -->
                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <button v-for="key in keypadKeys" :key="'card-' + key" type="button"
                                    class="flex h-14 items-center justify-center rounded-full border border-neutral-300 bg-white text-xl font-semibold text-neutral-900 shadow-sm hover:bg-neutral-50"
                                    @click="onCardKeypadPress(key)">
                                    <span v-if="key !== 'back'">{{ key }}</span>
                                    <span v-else>⌫</span>
                                </button>
                            </div>

                            <!-- footer info -->
                            <div class="mt-6 flex items-center justify-between text-[13px]">
                                <div class="space-y-1">
                                    <div class="text-neutral-500">
                                        Card processed by ·
                                        <span class="font-medium text-neutral-900">
                                            {{
                                                staffName ||
                                                businessLabel ||
                                                'Front desk'
                                            }}
                                        </span>
                                    </div>
                                    <div class="text-neutral-500">
                                        Left to pay ·
                                        <span class="font-semibold text-neutral-900 tabular-nums">
                                            {{ currencySymbol }}
                                            {{ cardLeftAfterFormatted }}
                                        </span>
                                    </div>
                                </div>

                                <button type="button"
                                    class="rounded-full bg-black px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 disabled:opacity-40"
                                    :disabled="cardAmountNum <= 0" @click="confirmCardAmount">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- GIFT CARD MODAL -->
                <transition name="gift-modal">
                    <div v-if="giftCardModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[133] flex items-center justify-center bg-neutral-900/40">
                        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-2 sm:mx-0"
                            @click.stop>
                            <div class="mb-5 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    Redeem gift card
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeGiftCardModal">
                                    ✕
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-medium text-neutral-600">
                                        Find gift card
                                    </label>
                                    <input v-model="giftCardCode" type="text"
                                        class="w-full rounded-full border border-neutral-300 px-4 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 focus:outline-none"
                                        placeholder="Enter gift card code" @keyup.enter="confirmGiftCard" />
                                </div>

                                <p class="text-xs text-neutral-500">
                                    For now this will simply apply the remaining
                                    balance as a payment towards this bill. Hook
                                    this up to your real gift-card lookup when
                                    ready.
                                </p>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-2">
                                <button type="button"
                                    class="rounded-full border border-neutral-300 px-4 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
                                    @click="closeGiftCardModal">
                                    Cancel
                                </button>
                                <button type="button"
                                    class="rounded-full bg-black px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 disabled:opacity-40"
                                    :disabled="!giftCardCode" @click="confirmGiftCard">
                                    Find
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- SPLIT PAYMENT: SELECT PAYMENT MODAL -->
                <transition name="split-modal">
                    <div v-if="splitSelectModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[134] flex items-center justify-center bg-neutral-900/40">
                        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-2 sm:mx-0"
                            @click.stop>
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-neutral-900">
                                    Select payment
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeSplitSelectModal">
                                    ✕
                                </button>
                            </div>

                            <div class="mb-5 grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                                <button type="button"
                                    class="flex flex-col items-center justify-center rounded-2xl border border-neutral-200 px-3 py-4 text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                    :disabled="isFullyPaid" @click="splitSelect('cash')">
                                    <i class="bx bx-money text-2xl"></i>
                                    <span class="font-medium">Cash</span>
                                </button>

                                <button type="button"
                                    class="flex flex-col items-center justify-center rounded-2xl border border-neutral-200 px-3 py-4 text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                    :disabled="isFullyPaid" @click="splitSelect('card')">
                                    <i class="bx bx-credit-card text-2xl"></i>
                                    <span class="font-medium">Card</span>
                                </button>

                                <button type="button"
                                    class="flex flex-col items-center justify-center rounded-2xl border border-neutral-200 px-3 py-4 text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                    :disabled="isFullyPaid" @click="splitSelect('gift-card')">
                                    <i class="bx bx-gift text-2xl"></i>
                                    <span class="font-medium">Gift card</span>
                                </button>

                                <!-- <button type="button"
                                    class="flex flex-col items-center justify-center rounded-2xl border border-neutral-200 px-3 py-4 text-sm hover:border-neutral-400 hover:bg-neutral-50"
                                     :disabled="isFullyPaid"
                                    @click="splitSelect('other')">
                                    <i class="bx bx-dots-horizontal-rounded text-2xl"></i>
                                    <span class="font-medium">Other</span>
                                </button> -->
                            </div>

                            <div class="flex justify-end">
                                <button type="button"
                                    class="rounded-full border border-neutral-300 px-4 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
                                    @click="closeSplitSelectModal">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>

                <!-- EDIT SERVICE MODAL -->
                <transition name="edit-modal">
                    <div v-if="editModalOpen"
                        class="pointer-events-auto fixed inset-0 z-[135] flex items-center justify-center bg-neutral-900/35">
                        <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl" @click.stop>
                            <!-- header -->
                            <div class="mb-6 flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-neutral-900">
                                    Edit {{ editItemDraft?.label || 'service' }}
                                </h2>
                                <button type="button"
                                    class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
                                    @click="closeEditModal">
                                    ✕
                                </button>
                            </div>

                            <!-- body -->
                            <div class="space-y-6">
                                <!-- Price and quantity -->
                                <div class="grid gap-5 md:grid-cols-[2fr,1fr]">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-neutral-600">
                                            Price
                                        </label>
                                        <div
                                            class="flex items-center rounded-xl border border-neutral-300 bg-neutral-50 px-3 py-2.5">
                                            <span
                                                class="mr-2 text-xs font-semibold tracking-wide text-neutral-500 uppercase">
                                                {{ currencySymbol }}
                                            </span>
                                            <input v-model="editPriceInput" type="number" min="0" step="0.01"
                                                class="w-full bg-transparent text-base font-medium text-neutral-900 outline-none" />
                                        </div>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-neutral-600">
                                            Quantity
                                        </label>
                                        <div
                                            class="flex items-center rounded-xl border border-neutral-300 bg-neutral-50 px-2 py-1.5">
                                            <button type="button"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-lg hover:bg-neutral-100"
                                                @click="adjustEditQty(-1)">
                                                −
                                            </button>
                                            <input v-model.number="editQty" type="number" min="1"
                                                class="mx-2 w-14 bg-transparent text-center text-base font-medium text-neutral-900 outline-none" />
                                            <button type="button"
                                                class="flex h-8 w-8 items-center justify-center rounded-full text-lg hover:bg-neutral-100"
                                                @click="adjustEditQty(1)">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Discounts (stub) -->
                                <!-- <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-neutral-600">
                                        Discounts
                                    </label>
                                   <SelectInputComponent
  v-model="editDiscountId"
  :options="discountSelectOptions"
  placeholder="None selected"
/>

                                </div> -->

                                <!-- Team member (display only for now) -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-neutral-600">
                                        Team member
                                    </label>
                                    <SelectInputComponent v-model="editStaffId" :options="teamMemberSelectOptions"
                                        placeholder="Select team member" />

                                </div>

                                <!-- item total -->
                                <div class="pt-2 text-base">
                                    <div class="text-xs text-neutral-500">
                                        Item total
                                    </div>
                                    <div class="mt-1 text-xl font-semibold text-neutral-900">
                                        {{ currencySymbol }}
                                        {{ formatNumber(editItemTotal) }}
                                    </div>
                                </div>
                            </div>

                            <!-- footer -->
                            <div class="mt-6 flex justify-end">
                                <button type="button" class="btn-primary" @click="applyEdit">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>



            </div>
        </div>
    </transition>
    <!-- TIP + PART-PAY BLOCK MODAL -->
<transition name="split-modal">
  <div
    v-if="tipPartPayBlockOpen"
    class="pointer-events-auto fixed inset-0 z-[140] flex items-center justify-center bg-neutral-900/40"
  >
    <div class="relative w-full max-w-lg rounded-3xl bg-white p-7 shadow-2xl mx-2 sm:mx-0" @click.stop>
      <!-- close X -->
      <button
        type="button"
        class="absolute right-5 top-5 flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
        @click="closeTipPartPayBlockModal"
        aria-label="Close"
      >
        ✕
      </button>

      <h2 class="text-2xl font-semibold text-neutral-900">Unable to save a sale</h2>

      <div class="mt-4 rounded-2xl bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-neutral-800">
        <div class="flex items-start gap-3">
          <span class="mt-0.5 inline-flex size-6 items-center justify-center rounded-full bg-yellow-100 text-yellow-700">
            !
          </span>
          <div class="font-medium">
            The sale must be fully paid to apply tips
          </div>
        </div>
      </div>

      <p class="mt-4 text-sm text-neutral-600">
        Sales with tips cannot be saved as part-paid. Would you like to remove tips now?
      </p>

      <div class="mt-6 flex items-center justify-end gap-3">
        <button
          type="button"
          class="rounded-full border border-neutral-300 px-6 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
          @click="closeTipPartPayBlockModal"
        >
          Cancel
        </button>

        <button
          type="button"
          class="rounded-full bg-neutral-900 px-6 py-2 text-sm font-semibold text-white hover:bg-black"
          @click="removeTipFromBlockModal"
        >
          Remove
        </button>
      </div>
    </div>
  </div>
</transition>


<transition name="split-modal">
  <div
    v-if="splitTipModalOpen"
    class="pointer-events-auto fixed inset-0 z-[134] flex items-center justify-center bg-neutral-900/40"
  >
    <div class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl mx-2 sm:mx-0" @click.stop>
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-neutral-900">Edit or split tip</h2>
        <button
          type="button"
          class="flex size-9 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800"
          @click="cancelSplitTipEditor"
        >
          ✕
        </button>
      </div>

      <div class="mb-4 text-sm text-neutral-600">
        Total tip:
        <span class="font-semibold text-neutral-900 tabular-nums">
          {{ currencySymbol }} {{ tipAmountFormatted }}
        </span>
      </div>

      <div class="space-y-3">
        <div
          v-for="(s, i) in tipSplitsDraft"
          :key="String(s.staff_id) + '-' + i"
          class="flex items-center gap-2"
        >
          <!-- recipient dropdown -->
          <select
            class="min-w-0 flex-1 rounded-xl border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-neutral-900"
            v-model="tipSplitsDraft[i].staff_id"
            @change="onTipSplitDraftRecipientChange(i)"
          >
            <option
              v-for="opt in tipRecipientOptions"
              :key="String(opt.id)"
              :value="opt.id"
              :disabled="isRecipientSelectedElsewhere(opt.id, i)"
            >
              {{ opt.name }}
            </option>
          </select>

          <!-- amount input -->
          <div class="relative w-32 min-w-[120px]">
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-sm text-neutral-500 pointer-events-none">
              {{ currencySymbol }}
            </div>
            <input
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-xl border border-neutral-300 pl-8 pr-3 py-2 text-sm text-right tabular-nums focus:outline-none focus:ring-1 focus:ring-neutral-900"
              :value="s.amount"
              @input="onTipSplitDraftAmountInput(i, $event)"
            />
          </div>

          <!-- delete row -->
          <button
            type="button"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-neutral-500 hover:bg-neutral-50 hover:text-red-600 disabled:opacity-40"
            :disabled="tipSplitsDraft.length <= 1"
            @click="removeTipSplitDraftRow(i)"
            aria-label="Remove recipient"
            title="Remove"
          >
            <i class="bx bx-trash text-xl"></i>
          </button>
        </div>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            class="rounded-full border border-neutral-300 px-4 py-1.5 font-medium text-neutral-700 hover:bg-neutral-100 disabled:opacity-40"
            :disabled="!canAddTipSplitDraftRow"
            @click="addTipSplitDraftRow"
          >
            + Add recipient
          </button>

          <button
            type="button"
            class="rounded-full border border-neutral-300 px-4 py-1.5 font-medium text-neutral-700 hover:bg-neutral-100"
            @click="rebalanceTipSplitsDraftEqual"
          >
            Equal split
          </button>
        </div>

        <div class="text-neutral-500 mt-2 sm:mt-0">
          Remaining:
          <span class="font-semibold text-neutral-900 tabular-nums">
            {{ currencySymbol }} {{ formatNumber(tipSplitDraftRemaining) }}
          </span>
        </div>
      </div>

      <div class="mt-5 flex flex-col sm:flex-row justify-end gap-2">
        <button
          type="button"
          class="rounded-full border border-neutral-300 px-4 py-1.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100 order-2 sm:order-1"
          @click="cancelSplitTipEditor"
        >
          Cancel
        </button>
        <button
          type="button"
          class="rounded-full bg-black px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-neutral-900 disabled:opacity-40 order-1 sm:order-2"
          :disabled="Math.abs(tipSplitDraftRemaining) > 0.01"
          @click="applySplitTipEditor"
        >
          Apply
        </button>
      </div>
    </div>
  </div>
</transition>

    <!-- Mobile Summary Offcanvas -->
    <transition name="summary-offcanvas">
        <div v-if="isMobile && showMobileSummary" class="fixed inset-0 z-[125] flex flex-col bg-white">
            <div class="flex items-center justify-between border-b px-4 py-3 bg-white">
                <h2 class="text-lg font-semibold text-neutral-900">Order Details</h2>
                <button type="button" @click="closeMobileSummary"
                    class="flex size-8 items-center justify-center rounded-full hover:bg-neutral-100">
                    ✕
                </button>
            </div>


            <div class="flex-1 overflow-y-auto px-4 py-4">
                <!-- Client section -->
                <div v-if="clientName" class="rounded-2xl bg-white p-4 shadow-sm mb-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="grid size-10 place-items-center rounded-full bg-neutral-800 text-sm font-semibold text-white">
                                {{ initials(clientName) }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-base font-semibold text-neutral-900">
                                    {{ clientName }}
                                </div>
                                <div v-if="clientEmail" class="truncate text-[12px] text-neutral-500">
                                    {{ clientEmail }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button v-else-if="!isPaymentPending" type="button"
                    class="flex w-full flex-col items-start justify-between rounded-2xl border-2 border-dashed border-neutral-300 bg-white px-4 py-4 text-left text-base shadow-sm hover:border-neutral-500 hover:bg-neutral-50 mb-4"
                    @click="onAddClient">
                    <span class="text-base font-semibold text-[#111827]">Add client</span>
                    <span class="mt-1 text-[12px] text-neutral-500">
                        Leave empty for walk-ins
                    </span>
                </button>


                <!-- Services section  -->
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <h2 class="text-base font-semibold tracking-wide text-neutral-500 uppercase mb-3">
                        Services
                    </h2>

                    <div class="space-y-2 text-sm max-h-[200px] overflow-y-auto">
                        <div v-for="(item, i) in items" :key="item.instanceUid || i"
                            class="flex w-full items-center justify-between rounded-xl border px-3 py-2.5" :style="{
                                borderLeft: '4px solid ' + (item.color || item.color_code || defaultServiceColor),
                                'min-height': '70px'
                            }">
                            <div class="flex flex-col items-start flex-1 min-w-0">
                                <span class="truncate font-medium text-neutral-900">
                                    {{ item.label }}
                                </span>
                                <span v-if="item.staff_name || item.staffName"
                                    class="mt-0.5 text-[12px] text-neutral-500">
                                    {{ item.staff_name || item.staffName }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 text-right ml-2">
                                <span class="text-sm font-semibold tabular-nums whitespace-nowrap">
                                    {{ currencySymbol }}
                                    {{ formatNumber((item.price ?? item.final_price ?? 0) * (item.quantity ?? 1)) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Totals section -->
                    <div class="mt-4 space-y-1.5 border-t pt-3 text-sm">
                        <div class="flex justify-between text-neutral-500">
                            <span>Services total</span>
                            <span class="tabular-nums">
                                {{ currencySymbol }}
                                {{ discountBaseTotalFormatted }}
                            </span>
                        </div>

                        <!-- Total discount -->
                        <div v-if="totalDiscount > 0" class="flex justify-between text-neutral-500">
                            <span>Discount</span>
                            <span class="tabular-nums">
                                - {{ currencySymbol }}
                                {{ totalDiscountFormatted }}
                            </span>
                        </div>

                        <!-- Subtotal AFTER discount -->
                        <div class="flex justify-between text-neutral-500">
                            <span>Subtotal</span>
                            <span class="tabular-nums">
                                {{ currencySymbol }}
                                {{ baseAmountFormatted }}
                            </span>
                        </div>

                        <div v-if="taxAmount > 0" class="flex justify-between text-neutral-500">
                            <span>Tax</span>
                            <span class="tabular-nums">
                                {{ currencySymbol }}
                                {{ taxAmountFormatted }}
                            </span>
                        </div>

                        <div v-if="tipAmount > 0" class="flex justify-between text-neutral-500">
                            <span>Tip</span>
                            <span class="tabular-nums">
                                {{ currencySymbol }}
                                {{ tipAmountFormatted }}
                            </span>
                        </div>

                        <div class="mt-2 flex justify-between text-lg font-semibold text-neutral-900">
                            <span>Total</span>
                            <span class="tabular-nums">
                                {{ currencySymbol }}
                                {{ grandTotalFormatted }}
                            </span>
                        </div>

                        <!-- Payments summary (under Total) -->
<!-- Payments summary (under Total) -->
<div v-if="isPaymentStep && payments.length" class="mt-3 border-t pt-3 space-y-2">
  <div
    v-for="(p, idx) in payments"
    :key="'pay-sum-m-' + idx"
    class="flex items-center justify-between"
  >
    <div class="flex items-center gap-2 min-w-0">
      <span class="truncate text-sm font-medium text-neutral-700">
        {{ paymentLabelFor(p.method) }}
      </span>

      <button
        type="button"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-neutral-400 hover:bg-neutral-100 hover:text-red-500"
        @click.stop.prevent="removePaymentAt(idx)"
        aria-label="Remove payment"
        title="Remove"
      >
        <i class="bx bx-trash text-lg"></i>
      </button>
    </div>

    <span class="text-sm font-semibold tabular-nums text-neutral-900 whitespace-nowrap">
      {{ currencySymbol }} {{ formatNumber(p.amount) }}
    </span>
  </div>
</div>


                    </div>


                    <!-- Add cart-->
                    <div v-if="(isTipStep || isPaymentStep) && !isPaymentPending" class="mt-4">

                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-full border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-900 hover:border-neutral-900 hover:bg-neutral-900 hover:text-white"
                            @click="goToCartFromSummary">
                            <i class="bx bx-cart-add text-base"></i>
                            <span>Add to cart</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <ClientPickerOffcanvas :show="showClientPicker" :clients="clients" :selected-client-id="meta.client?.id || null"
        :is-walk-in="!meta.client" @close="showClientPicker = false" @select-client="handleClientSelected"
        @set-walk-in="handleWalkInSelected" @add-new-client="handleAddNewClientFromPicker" />
    <AddClientModal :show="showAddClientModal" :countries="countries" @close="showAddClientModal = false"
        @saved="handleClientSaved" />

        <!-- Confirm Part Paid Modal -->
<transition name="confirm-modal">
<div v-if="confirmPartPaidModalOpen && !tipPartPayBlockOpen"

       class="pointer-events-auto fixed inset-0 z-[140] flex items-center justify-center bg-neutral-900/40">
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-4" @click.stop>
      <div class="text-center">
        <!-- <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
          <i class="bx bx-info-circle text-2xl text-orange-600"></i>
        </div> -->
        
        <h3 class="text-xl font-semibold text-neutral-900 m-4">
          Save Part Paid
        </h3>
        
        <div class="mb-6 text-sm text-neutral-600">
          <p class="mb-2">
            The total amount is {{ currencySymbol }} {{ totalWithTipFormatted }}, 
            but only {{ currencySymbol }} {{ totalPaidFormatted }} has been added.
          </p>
          <p>
            <span class="font-semibold text-neutral-900">{{ currencySymbol }} {{ leftToPayFormatted }}</span> 
            will remain as balance.
          </p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button type="button"
                  class="order-2 sm:order-1 rounded-full border border-neutral-300 px-8 py-2.5 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
                  @click="cancelPartPaidConfirm">
            Cancel
          </button>
          <button type="button"
                  class="order-1 sm:order-2 rounded-full bg-black px-8 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600"
                  @click="confirmPartPaidSave">
            Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</transition>

</template>

<script lang="ts">
import { router } from '@inertiajs/vue3';
import { defineComponent } from 'vue';
import AddClientModal from './AddClientModal.vue';
import ClientPickerOffcanvas from './ClientPickerOffcanvas.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import axios from 'axios';

type PaymentMethod = 'cash' | 'card' | 'gift-card' | 'split' | 'other';
type PaymentLineMethod = 'cash' | 'card' | 'gift-card' | 'other';

export default defineComponent({
    name: 'SelectTip',

    components: {
        AddClientModal,
        ClientPickerOffcanvas,
        SelectInputComponent,
    },


    data() {
        return {
            tipSplits: [] as { staff_id: number | string; name: string; amount: number }[],
            tipSplitsDraft: [] as { staff_id: number | string; name: string; amount: number }[],
            tipSplitsDirty: false as boolean,
            splitTipModalOpen: false,
            tipPartPayBlockOpen: false as boolean,
tipPartPayBlockSource: null as null | 'save' | 'cash' | 'card',
tipPartPayBlockPendingAmount: 0 as number,

 servicesLocal: [] as any[],
        servicesLoading: false,
        serviceSearch: '',

            discounts: [] as any[],
            teamMembers: [] as any[],

            editDiscountId: null as number | null,
            editStaffId: null as number | null,

            tipRecipientStaffId: null as number | string | null,

            step: 'tip' as 'cart' | 'tip' | 'payment',
            paymentMethod: null as PaymentMethod | null,

            // tip state
            mode: 'none' as 'none' | 'percent' | 'custom',
            selectedPercent: 0,

            customInput: '0',
            customIsPercent: false,
            customModalOpen: false,

            // cart items (local copy)
            localItems: [] as any[],

            selectedCategoryId: null as number | null,


            // payments
            payments: [] as {
                method: PaymentLineMethod;
                amount: number;
                meta?: any;
            }[],
            cashModalOpen: false,
            cashInput: '0',
            cashFromSplit: false,
            cashJustOpened: false,
            cardModalOpen: false,
            cardInput: '0',
            cardFromSplit: false,
            cardJustOpened: false,
            giftCardModalOpen: false,
            giftCardCode: '',
            splitSelectModalOpen: false,
            customJustOpened: false,

            // edit service modal
            editModalOpen: false,
            editIndex: -1,
            editItemDraft: null as any | null,
            editPriceInput: '',
            editQty: 1,
            editDiscountLabel: 'None selected',
            editStaffLabel: '',

            percentOptions: [10, 18, 25, 35, 45],
            keypadKeys: [
                '1',
                '2',
                '3',
                '4',
                '5',
                '6',
                '7',
                '8',
                '9',
                '.',
                '0',
                'back',
            ] as string[],
            showMobileSummary: false as boolean,
            isMobile: window.innerWidth < 640 as boolean,

            showAddClientModal: false,

            isSaving: false as boolean,
            showClientPicker: false,
            clientsLocal: [] as any[],

            selectedClient: null as any | null,

            keyHandler: null as ((e: KeyboardEvent) => void) | null,
            resizeHandler: null as (() => void) | null,
          

                    confirmPartPaidModalOpen: false as boolean,
        pendingSaveCallback: null as (() => void) | null,

        };

    },

    mounted() {
        this.keyHandler = (e: KeyboardEvent) => this.handlePosKeydown(e);
        this.resizeHandler = () => this.handleResize();

        window.addEventListener('keydown', this.keyHandler);
        window.addEventListener('resize', this.resizeHandler);

        this.isMobile = window.innerWidth < 640;
        if (this.show && this.isPreviewPendingPayment) {
            this.step = 'payment';
        }
        if (!this.$page.props.services?.length && !this.$page.props.allServices?.length) {
        this.fetchServices();
    }

    },


    beforeUnmount() {
        if (this.keyHandler) {
            window.removeEventListener('keydown', this.keyHandler);
        }
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }
    },

    computed: {
        serviceCategories(): any[] {
  return this.filteredServiceCategories;
},
        allServices(): any[] {
        if (this.servicesLocal && this.servicesLocal.length) {
            return this.servicesLocal;
        }
        return (this.$page.props.services || this.$page.props.allServices || []);
    },
    
    // Group services by category
    groupedServiceCategories(): any[] {
        const byKey: Record<string, any> = {};
        
        this.allServices.forEach((svc: any) => {
            const cat = svc.category || {};
            const id = cat.id ?? svc.service_category_id ?? null;
            const name = cat.name ?? svc.category_name ?? 'Uncategorised';
            
            if (!byKey[name]) {
                byKey[name] = {
                    id,
                    name,
                    color_code: cat.color_code || svc.color_code || this.defaultServiceColor,
                    count: 0,
                    services: [],
                };
            }
            byKey[name].count += 1;
            byKey[name].services.push(svc);
        });
        
        return Object.values(byKey).sort((a: any, b: any) => 
            a.name.localeCompare(b.name)
        );
    },
    
    // Filter services by search term
    filteredServiceCategories(): any[] {
        const q = this.serviceSearch.trim().toLowerCase();
        if (!q) return this.groupedServiceCategories;
        
        return this.groupedServiceCategories
            .map((cat: any) => {
                const filteredServices = cat.services.filter((svc: any) => {
                    const name = (svc.name || '').toLowerCase();
                    const desc = (svc.description || '').toLowerCase();
                    return name.includes(q) || desc.includes(q);
                });
                return { ...cat, services: filteredServices, count: filteredServices.length };
            })
            .filter((cat: any) => cat.services.length > 0);
    },
        selectedTeamMemberName(): string {
    const names = (this.items || [])
      .map((it: any) => it.staff_name ?? it.staffName ?? it.staff?.name ?? null)
      .filter(Boolean);

    const uniq = Array.from(new Set(names.map(String)));

    if (uniq.length === 1) return uniq[0];

    if (uniq.length > 1) return `${uniq.length} team members`;

    return this.staffName || '';
  },
 splitLineSubtitle(): string {
    return this.selectedTeamMemberName;
  },
        isSplitPaymentView(): boolean {
  return this.step === 'payment' && this.paymentMethod === 'split';
},

headerTitle(): string {
  if (this.step === 'cart') return 'Cart';
  if (this.step === 'tip') return 'Select tip';
  if (this.step === 'payment' && this.paymentMethod === 'split') return 'Split payment';
  return 'Select payment';
},

        canAddTipSplitDraftRow(): boolean {
  const used = new Set(this.tipSplitsDraft.map(s => String(s.staff_id)));
  return this.tipRecipientOptions.some(o => !used.has(String(o.id)));
},

        tipRecipientsLabel(): string {
            const recips = this.tipRecipientOptions;
            if (!recips.length) return '';
            if (recips.length === 1) return recips[0].name;
            if (recips.length === 2) return `${recips[0].name} and ${recips[1].name}`;
            // keep readable; adjust if you prefer listing all
            return `${recips[0].name}, ${recips[1].name} and ${recips.length - 2} others`;
        },

        tipSplitsTotal(): number {
            return this.tipSplits.reduce((a, s) => a + (Number(s.amount) || 0), 0);
        },

        splitSummaryLabel(): string {
            const total = Number(this.tipAmount) || 0;
            if (total <= 0 || this.tipRecipientOptions.length <= 1) return '';

            return this.tipSplits
                .map(s => {
                    const pct = total > 0 ? Math.round((Number(s.amount || 0) / total) * 100) : 0;
                    return `${pct}% ${s.name}`;
                })
                .join(' and ');
        },

        tipSplitsDraftTotal(): number {
            return this.tipSplitsDraft.reduce((a, s) => a + (Number(s.amount) || 0), 0);
        },

        tipSplitDraftRemaining(): number {
            return Number((((Number(this.tipAmount) || 0) - this.tipSplitsDraftTotal)).toFixed(2));
        },


       

        tipsJsonForSave(): { staff_id: number | string; amount: number }[] {
            const total = Number(this.tipAmount) || 0;
            if (total <= 0) return [];

            const recips = this.tipRecipientOptions;

            if (recips.length === 1) {
                this.tipSplits = [{ staff_id: recips[0].id, name: recips[0].name, amount: 0 }];
            } else {
                this.tipSplits = recips.map(r => ({ staff_id: r.id, name: r.name, amount: 0 }));
            }

            const each = total / recips.length;
            const out = recips.map(r => ({ staff_id: r.id, amount: Number(each.toFixed(2)) }));

            // Fix rounding on last row to ensure exact total
            const sum = out.reduce((s, r) => s + r.amount, 0);
            const diff = Number((total - sum).toFixed(2));
            out[out.length - 1].amount = Number((out[out.length - 1].amount + diff).toFixed(2));

            return out;
        },

        discountSelectOptions(): { label: string; value: any }[] {
            return [
                { label: 'None selected', value: null },
                ...this.discounts.map((d: any) => ({
                    label: d.type === 'percent'
                        ? `${d.name} (${d.value}%)`
                        : `${d.name} (${this.currencySymbol} ${this.formatNumber(d.value)})`,
                    value: d.id,
                })),
            ];
        },

        teamMemberSelectOptions(): { label: string; value: any }[] {
            return [
                { label: 'Select team member', value: null },
                ...this.teamMembers.map((u: any) => ({ label: u.name, value: u.id })),
            ];
        },

        editItemTotal(): number {
            const base = Math.max(0, Number(this.editPriceInput || 0));
            const qty = Math.max(1, Number(this.editQty || 1));

            const disc = this.discounts.find((d: any) => d.id === this.editDiscountId) || null;

            let finalUnit = base;
            if (disc) {
                if (disc.type === 'percent') finalUnit = Math.max(0, base - (base * Number(disc.value)) / 100);
                if (disc.type === 'amount') finalUnit = Math.max(0, base - Number(disc.value));
            }

            return finalUnit * qty;
        },
        tipRecipientSelectOptions(): { label: string; value: number | string }[] {
            return this.tipRecipientOptions.map(o => ({
                label: o.name,
                value: o.id,
            }));
        },

       tipRecipientOptions(): { id: number | string; name: string }[] {
  const map = new Map<string, { id: any; name: string }>();

  this.items.forEach((it: any) => {
    const id = it.staff_id ?? it.staffId ?? it.staff?.id ?? null;
    const name = it.staff_name ?? it.staffName ?? it.staff?.name ?? null;

    if (id !== null && id !== undefined && name) {
      map.set(String(id), { id, name });
    }
  });

  if (!map.size && this.meta?.staff?.id != null && this.meta?.staff?.name) {
    map.set(String(this.meta.staff.id), { id: this.meta.staff.id, name: this.meta.staff.name });
  }

  return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name));
},


        hasMultipleTipRecipients(): boolean {
            return this.tipRecipientOptions.length > 1;
        },

        selectedTipRecipientId(): number | string | null {
            return this.tipRecipientStaffId ?? this.tipRecipientOptions[0]?.id ?? null;
        },
        isLockedAmount(): boolean {
            return !!(this.meta?.lock_amount ?? this.meta?.lockAmount);
        },

        paymentStatus(): string {
            const b = this.meta?.booking || {};
            const raw =
                (this.meta?.payment_status ??
                    b.payment_status ??
                    b.paymentStatus ??
                    this.meta?.paymentStatus ??
                    '') as string;

            return String(raw).toLowerCase();
        },

        isPaymentPending(): boolean {
            // accept both spellings to be safe
            if (['pending_payment', 'payment_pending'].includes(this.paymentStatus)) return true;

            // fallback: your current “pending” flow already sets lock_amount
            return this.isLockedAmount;
        },
        hideBreadcrumbs(): boolean {
            return this.isPreviewPendingPayment; // single switch for UI
        },


        amountDue(): number {
            const raw =
                this.meta?.amount_due ??
                this.meta?.amountDue ??
                this.meta?.remaining ??
                null;

            const n = Number(raw);
            return Number.isFinite(n) ? Math.max(0, n) : 0;
        },

        selectedTipRecipientName(): string | null {
            const id = this.selectedTipRecipientId;
            if (!id) return null;
            return this.tipRecipientOptions.find(o => String(o.id) === String(id))?.name ?? null;
        },
        discountBaseTotal(): number {
            if (this.isLockedAmount) {
                // ✅ prevents fake “discount” when due < services sum
                return this.baseAmount;
            }

            return this.items.reduce((total: number, item: any) => {
                const basePrice =
                    item.rawPrice != null
                        ? Number(item.rawPrice)
                        : item.price_before_discount != null
                            ? Number(item.price_before_discount)
                            : Number(item.price ?? item.final_price ?? 0);

                const qty = Number(item.quantity ?? 1);

                const safeBase = isNaN(basePrice) ? 0 : basePrice;
                const safeQty = isNaN(qty) || qty <= 0 ? 1 : qty;

                return total + safeBase * safeQty;
            }, 0);
        },

        canSave(): boolean {
            if (this.isSaving) return false;

            if (this.step !== 'payment') return true;

            if (this.totalWithTip <= 0) return true;

            if (!this.paymentMethod) return false;

            if (this.paymentMethod !== 'other' && !this.payments.length) return false;

            return true;
        },


        totalDiscount(): number {
            if (this.isLockedAmount) return 0;
            const diff = this.discountBaseTotal - this.baseAmount;
            return diff > 0 ? diff : 0;
        },

        countries(): any[] {
            return (this.$page.props.countries || []) as any[];
        },

        discountBaseTotalFormatted(): string {
            return this.formatNumber(this.discountBaseTotal);
        },
        totalDiscountFormatted(): string {
            return this.formatNumber(this.totalDiscount);
        },

        show(): boolean {
            return this.$store.getters.tipShow;
        },
        meta(): any {
            return this.$store.getters.tipMeta || {};
        },

        defaultServiceColor(): string {
            return 'var(--brand, var(--brand-fallback))';
        },

        isCartStep(): boolean {
            return this.step === 'cart';
        },

        isTipStep(): boolean {
            return this.step === 'tip';
        },
        isPaymentStep(): boolean {
            return this.step === 'payment';
        },

   

        items(): any[] {
            const items =
                Array.isArray(this.localItems) && this.localItems.length
                    ? this.localItems
                    : Array.isArray(this.meta.services)
                        ? this.meta.services
                        : [];
            return items;
        },

        baseAmount(): number {
            if (this.isLockedAmount) {
                // ✅ this is the key: show/pay ONLY remaining when payment_pending
                return this.amountDue;
            }

            return this.items.reduce((total: number, item: any) => {
                const price = Number(item.price ?? item.final_price ?? 0);
                const qty = Number(item.quantity ?? 1);
                const safePrice = isNaN(price) ? 0 : price;
                const safeQty = isNaN(qty) || qty <= 0 ? 1 : qty;
                return total + safePrice * safeQty;
            }, 0);
        },
        taxAmount(): number {
            const v = Number(this.meta.taxAmount ?? 0);
            return isNaN(v) ? 0 : v;
        },

        clientName(): string | null {
            return (
                this.selectedClient?.name ||
                this.meta.client?.name ||
                null
            );
        },
        clientEmail(): string | null {
            return (
                this.selectedClient?.email ||
                this.meta.client?.email ||
                null
            );
        },

        staffName(): string | null {
            return this.meta.staff?.name || null;
        },
        businessLabel(): string | null {
            return (
                this.meta.propertyName ||
                this.meta.property?.name ||
                this.meta.businessName ||
                null
            );
        },
        bookingId(): number | null {
            return this.meta.booking?.id ?? this.meta.bookingId ?? null;
        },
        currencySymbol(): string {
            return this.meta.currencySymbol || 'LKR';
        },

        tipAmount(): number {
            if (this.mode === 'none') return 0;

            if (this.mode === 'percent') {
                const amt = (this.baseAmount * (Number(this.selectedPercent) || 0)) / 100;
                return Number(amt.toFixed(2));
            }

            // custom
            const raw = Number(parseFloat(this.customInput || '0') || 0);
            const amt = this.customIsPercent
                ? (this.baseAmount * raw) / 100
                : raw;

            return Number(Math.max(0, amt).toFixed(2));
        },


        grandTotal(): number {
            return this.baseAmount + this.taxAmount + this.tipAmount;
        },

        totalWithTip(): number {
            return this.grandTotal;
        },

        // payments
        totalPaid(): number {
            return this.payments.reduce(
                (s, p) => s + (Number(p.amount) || 0),
                0,
            );
        },
        leftToPay(): number {
            const remaining = this.totalWithTip - this.totalPaid;
            return remaining > 0 ? remaining : 0;
        },
        changeAmount(): number {
            const diff = this.totalPaid - this.totalWithTip;
            return diff > 0 ? diff : 0;
        },

        baseAmountFormatted(): string {
            return this.formatNumber(this.baseAmount);
        },
        taxAmountFormatted(): string {
            return this.formatNumber(this.taxAmount);
        },
        tipAmountFormatted(): string {
            return this.formatNumber(this.tipAmount);
        },
        grandTotalFormatted(): string {
            return this.formatNumber(this.grandTotal);
        },
        totalWithTipFormatted(): string {
            return this.formatNumber(this.totalWithTip);
        },
        totalPaidFormatted(): string {
            return this.formatNumber(this.totalPaid);
        },
        leftToPayFormatted(): string {
            return this.formatNumber(this.leftToPay);
        },
        changeAmountFormatted(): string {
            return this.formatNumber(this.changeAmount);
        },

        customSummaryLabel(): string {
            if (this.mode !== 'custom' || this.tipAmount <= 0) {
                return 'Enter a custom amount or percentage';
            }

            const pct =
                this.baseAmount > 0
                    ? (this.tipAmount / this.baseAmount) * 100
                    : 0;
            const pctStr = `${pct.toFixed(0)}%`;
            const amtStr = this.formatNumber(this.tipAmount);

            return this.customIsPercent
                ? `${pctStr} · ${this.currencySymbol} ${amtStr}`
                : `${this.currencySymbol} ${amtStr} · ${pctStr}`;
        },

        customMainDisplay(): string {
            const v = this.customInput || '0';
            return this.customIsPercent ? `${v}%` : `${v}`;
        },

        currentTipPercentLabel(): string {
            const pct =
                this.baseAmount > 0
                    ? (this.tipAmount / this.baseAmount) * 100
                    : 0;
            return `${pct.toFixed(0)}% tip`;
        },

        // payment label
        selectedPaymentLabel(): string {
            return this.paymentMethod
                ? this.paymentLabelFor(this.paymentMethod)
                : '';
        },

        // cash modal helpers
        cashAmountNum(): number {
            const n = parseFloat(this.cashInput || '0');
            return isNaN(n) ? 0 : Math.max(0, n);
        },
        cashInputDisplay(): string {
            return this.formatNumber(this.cashAmountNum);
        },
        cashQuickAmounts(): number[] {
            const base = Math.max(this.leftToPay || this.totalWithTip, 0);
            if (base <= 0) return [0];
            const b = Math.round(base);
            const list = [b, b + 3, b + 8, b + 18];
            const out: number[] = [];
            list.forEach((v) => {
                if (v > 0 && !out.includes(v)) out.push(v);
            });
            return out;
        },
        cashLeftAfter(): number {
            const remaining =
                this.totalWithTip - (this.totalPaid + this.cashAmountNum);
            return remaining > 0 ? remaining : 0;
        },
        cashLeftAfterFormatted(): string {
            return this.formatNumber(this.cashLeftAfter);
        },

        cardAmountNum(): number {
            const n = parseFloat(this.cardInput || '0');
            return isNaN(n) ? 0 : Math.max(0, n);
        },
        cardInputDisplay(): string {
            return this.formatNumber(this.cardAmountNum);
        },
        cardQuickAmounts(): number[] {
            const base = Math.max(this.leftToPay || this.totalWithTip, 0);
            if (base <= 0) return [0];
            const b = Math.round(base);
            const list = [b, b + 3, b + 8, b + 18];
            const out: number[] = [];
            list.forEach((v) => {
                if (v > 0 && !out.includes(v)) out.push(v);
            });
            return out;
        },
        cardLeftAfter(): number {
            const remaining =
                this.totalWithTip - (this.totalPaid + this.cardAmountNum);
            return remaining > 0 ? remaining : 0;
        },
        cardLeftAfterFormatted(): string {
            return this.formatNumber(this.cardLeftAfter);
        },


        footerLabel(): string {
            return this.changeAmount > 0 ? 'Change' : 'To pay';
        },
        footerAmount(): number {
            if (this.changeAmount > 0) {
                return this.changeAmount;
            }
            return this.leftToPay > 0 ? this.leftToPay : this.totalWithTip;
        },
        footerAmountFormatted(): string {
            return this.formatNumber(this.footerAmount);
        },
services(): any[] {
    return this.servicesLocal && this.servicesLocal.length
        ? this.servicesLocal
        : (this.$page.props.services || []);
},
       
        // serviceCategories(): any[] {
        //     const byKey: Record<string, any> = {};

        //     this.allServices.forEach((svc: any) => {
        //         const cat = svc.category || {};
        //         const id = cat.id ?? svc.service_category_id ?? null;
        //         const name = cat.name ?? svc.category_name ?? null;
        //         if (!id && !name) return;

        //         const color =
        //             cat.color_code ??
        //             svc.category?.color_code ??
        //             svc.color_code ??
        //             null;

        //         const key = String(id ?? name);

        //         if (!byKey[key]) {
        //             byKey[key] = {
        //                 id,
        //                 name: name || 'Uncategorised',
        //                 color_code: color,
        //                 count: 0,
        //             };
        //         }
        //         byKey[key].count += 1;
        //     });

        //     const list = Object.values(byKey);
        //     list.sort((a: any, b: any) =>
        //         String(a.name).localeCompare(String(b.name)),
        //     );
        //     return list;
        // },

        servicesForSelectedCategory(): any[] {
            if (!this.selectedCategoryId) return [];
            return this.allServices.filter((svc: any) => {
                const cat = svc.category || {};
                const id = cat.id ?? svc.service_category_id ?? null;
                return id === this.selectedCategoryId;
            });
        },

        isPreviewPendingPayment(): boolean {
            // ✅ hard override: when you want payment screen ONLY
            const force =
                !!(this.meta?.forcePaymentStep ?? this.meta?.force_payment_step);

            // ✅ “pending payment” should be based on YOUR robust logic
            const pending = this.isPaymentPending;

            // ✅ optional origin check (keep if you want)
            const originRaw =
                this.meta?.origin ??
                this.meta?.from ??
                (this as any).$route?.query?.origin ??
                (this as any).$route?.query?.from ??
                '';

            const origin = String(originRaw).toLowerCase();

            return force || (pending && origin === 'preview_take_payment');
        },

        selectedCategory(): any | null {
            if (!this.selectedCategoryId) return null;
            return (
                this.serviceCategories.find(
                    (c: any) => c.id === this.selectedCategoryId,
                ) || null
            );
        },
        selectedCategoryName(): string {
            return this.selectedCategory?.name ?? '';
        },
        selectedCategoryColor(): string {
            return (
                this.selectedCategory?.color_code || this.defaultServiceColor
            );
        },


        isFullyPaid(): boolean {
            return this.leftToPay <= 0;
        },
        clients(): any[] {
            if (this.clientsLocal && this.clientsLocal.length) {
                return this.clientsLocal;
            }
            return this.$page.props.clients || [];
        },



    },

    watch: {
        tipRecipientOptions: {
            deep: true,
            handler() {
                if (!this.show) return;
                this.initTipSplitsIfNeeded(true);
            },
        },

        tipAmount(newTotal: number, oldTotal: number) {
            if (!this.show) return;
            this.syncTipSplitsWithTipTotal(newTotal, oldTotal);
        },
        show(val: boolean) {
            if (val) {

                if (!this.servicesLocal.length) {
  this.fetchServices();
}

                const services = Array.isArray(this.meta.services)
                    ? this.meta.services
                    : [];
                this.localItems = services.map((s: any) => ({ ...s }));

                const options = (() => {
                    const map = new Map<string, { id: any; name: string }>();
                    this.localItems.forEach((it: any) => {
                        const id = it.staff_id ?? it.staffId ?? null;
                        const name = it.staff_name ?? it.staffName ?? null;
                        if (id && name) map.set(String(id), { id, name });
                    });
                    if (!map.size && this.meta?.staff?.id && this.meta?.staff?.name) {
                        map.set(String(this.meta.staff.id), { id: this.meta.staff.id, name: this.meta.staff.name });
                    }
                    return Array.from(map.values());
                })();


                this.tipRecipientStaffId =
                    this.meta?.tip_staff_id ??
                    options[0]?.id ??
                    this.meta?.staff?.id ??
                    null;
                this.step = this.isPreviewPendingPayment ? 'payment' : 'tip';
                this.paymentMethod = this.meta.paymentMethod || null;
                if (this.isPreviewPendingPayment) {
                    // idempotent: every time this panel opens in this mode, it lands on Payment
                    this.initTipSplitsIfNeeded(true);


                }

                if (
                    typeof this.meta.tipAmount === 'number' &&
                    this.meta.tipAmount > 0
                ) {
                    this.mode = this.meta.tipMode || 'custom';
                } else {
                    this.mode = 'none';
                }
                this.payments = [];
                this.cashModalOpen = false;
                this.cardModalOpen = false;
                this.giftCardModalOpen = false;
                this.splitSelectModalOpen = false;
                this.selectedCategoryId = null;
                this.clientsLocal = (this.$page.props.clients || []).slice();
                this.selectedClient = this.meta.client || null;
            } else {
                this.tipRecipientStaffId = null;

                this.step = 'tip';
                this.paymentMethod = null;
                this.mode = 'none';
                this.selectedPercent = 0;
                this.customInput = '0';
                this.customIsPercent = false;
                this.customModalOpen = false;
                this.editModalOpen = false;
                this.editIndex = -1;
                this.editItemDraft = null;
                this.localItems = [];
                this.payments = [];
                this.cashModalOpen = false;
                this.cardModalOpen = false;
                this.giftCardModalOpen = false;
                this.splitSelectModalOpen = false;
                this.selectedCategoryId = null;
                this.selectedClient = null;
            }
        },
        meta: {
            deep: true,
            handler() {
                if (!this.show) return;

                if (this.isPreviewPendingPayment) {
                    this.$nextTick(() => this.applyInitialStep());
                }
            },
        },
    },

    methods: {
         buildDefaultTipsJson(): { staff_id: number | string; amount: number }[] {
    const total = Number(this.tipAmount) || 0;
    const recips = this.tipRecipientOptions;
    if (total <= 0 || !recips.length) return [];

    const each = Number((total / recips.length).toFixed(2));
    const out = recips.map(r => ({ staff_id: r.id, amount: each }));

    const sum = out.reduce((s, r) => s + r.amount, 0);
    const diff = Number((total - sum).toFixed(2));
    out[out.length - 1].amount = Number((out[out.length - 1].amount + diff).toFixed(2));

    return out;
  },
 async fetchServices() {
  this.servicesLoading = true;

  try {
    const routeFn = (window as any).route;
    const url = routeFn ? routeFn('calendar.servicesdata') : '/calendar/servicesdata';

    const res = await axios.get(url, {
      headers: { Accept: 'application/json' },
      params: {
        branch_id: this.meta?.branch_id ?? null, // remove if your backend doesn't need it
      },
    });

    // accept multiple backend shapes
    const list =
      res.data?.services ??
      res.data?.data ??
      res.data?.data?.services ??
      [];

    this.servicesLocal = Array.isArray(list) ? list : [];
    console.log('services loaded:', this.servicesLocal.length, res.data);
  } catch (e) {
    console.error('Failed to load services:', e);
    this.servicesLocal = [];
  } finally {
    this.servicesLoading = false;
  }
},

iconForPayment(method: string): string {
  switch (method) {
    case 'cash': return 'bx bx-money';
    case 'card': return 'bx bx-credit-card';
    case 'gift-card': return 'bx bx-gift';
    default: return 'bx bx-dots-horizontal-rounded';
  }
},

        isRecipientSelectedElsewhere(id: number | string, currentIndex: number) {
  const sid = String(id);
  return this.tipSplitsDraft.some((s, idx) => idx !== currentIndex && String(s.staff_id) === sid);
},

onTipSplitDraftRecipientChange(i: number) {
  const id = this.tipSplitsDraft[i]?.staff_id;
  const opt = this.tipRecipientOptions.find(o => String(o.id) === String(id));
  if (opt) this.tipSplitsDraft[i].name = opt.name;

  const ids = this.tipSplitsDraft.map(s => String(s.staff_id));
  const dup = ids.filter((x, idx) => ids.indexOf(x) !== idx);
  if (dup.length) {
    const used = new Set(ids);
    const fallback = this.tipRecipientOptions.find(o => !used.has(String(o.id)));
    if (fallback) {
      this.tipSplitsDraft[i].staff_id = fallback.id;
      this.tipSplitsDraft[i].name = fallback.name;
    }
  }
},

addTipSplitDraftRow() {
  const used = new Set(this.tipSplitsDraft.map(s => String(s.staff_id)));
  const next = this.tipRecipientOptions.find(o => !used.has(String(o.id)));
  if (!next) return;

  this.tipSplitsDraft.push({
    staff_id: next.id,
    name: next.name,
    amount: 0,
  });

  const total = Number(this.tipAmount) || 0;
  const normalized = this.normalizeSplitsToTotal(total, this.tipSplitsDraft) || this.tipSplitsDraft;
  this.tipSplitsDraft = normalized.map((s: any) => ({ ...s }));
},

removeTipSplitDraftRow(i: number) {
  if (this.tipSplitsDraft.length <= 1) return;
  this.tipSplitsDraft.splice(i, 1);

  const total = Number(this.tipAmount) || 0;
  const normalized = this.normalizeSplitsToTotal(total, this.tipSplitsDraft) || this.tipSplitsDraft;
  this.tipSplitsDraft = normalized.map((s: any) => ({ ...s }));
},
openSplitTipEditor() {
  this.initTipSplitsIfNeeded(false);
  this.tipSplitsDraft = (this.tipSplits || []).map(s => ({ ...s }));
  this.splitTipModalOpen = true;
},

        cancelSplitTipEditor() {
            this.tipSplitsDraft = [];
            this.splitTipModalOpen = false;
        },

        applySplitTipEditor() {
            const total = Number(this.tipAmount) || 0;
            const normalized = this.normalizeSplitsToTotal(total, this.tipSplitsDraft) || this.tipSplitsDraft;

            // block apply if still off (tolerance)
            const sum = normalized.reduce((a: number, s: any) => a + (Number(s.amount) || 0), 0);
            if (Math.abs(total - sum) > 0.01) return;

            this.tipSplits = normalized.map((s: any) => ({ ...s }));
            this.tipSplitsDirty = true;
            this.tipSplitsDraft = [];
            this.splitTipModalOpen = false;
        },

        onTipSplitDraftAmountInput(i: number, e: Event) {
            const input = e.target as HTMLInputElement | null;
            const raw = input?.value ?? '0';
            const v = Math.max(0, Number(parseFloat(raw || '0') || 0));

            if (!this.tipSplitsDraft[i]) return;
            this.tipSplitsDraft[i].amount = Number(v.toFixed(2));

            // auto-balance last row while editing (matches your current behaviour)
            const total = Number(this.tipAmount) || 0;
            const n = this.tipSplitsDraft.length;
            if (n <= 1) return;

            const last = n - 1;
            if (i === last) return;

            const sumExceptLast = this.tipSplitsDraft
                .slice(0, last)
                .reduce((a, s) => a + (Number(s.amount) || 0), 0);

            this.tipSplitsDraft[last].amount = Number(Math.max(0, total - sumExceptLast).toFixed(2));
        },

        rebalanceTipSplitsDraftEqual() {
            const total = Number(this.tipAmount) || 0;
            const n = this.tipSplitsDraft.length;
            if (n <= 0) return;

            const each = Number((total / n).toFixed(2));
            const out = this.tipSplitsDraft.map((s, i) => ({
                ...s,
                amount: i === n - 1 ? 0 : each,
            }));

            const sumFirst = out.slice(0, n - 1).reduce((a, s) => a + (Number(s.amount) || 0), 0);
            out[n - 1].amount = Number((total - sumFirst).toFixed(2));

            this.tipSplitsDraft = out;
        },

     openTipPartPayBlockModal(source: 'save' | 'cash' | 'card', amount = 0) {
  // ✅ hard-close the confirm modal if it is open
  this.confirmPartPaidModalOpen = false;
  this.pendingSaveCallback = null;

  this.tipPartPayBlockSource = source;
  this.tipPartPayBlockPendingAmount = Number(amount) || 0;
  this.tipPartPayBlockOpen = true;
},


closeTipPartPayBlockModal() {
  this.tipPartPayBlockOpen = false;
  this.tipPartPayBlockSource = null;
  this.tipPartPayBlockPendingAmount = 0;
},

removeTipFromBlockModal() {
  // 1) remove tip (UI + store)
  this.mode = 'none';
  this.selectedPercent = 0;
  this.customInput = '0';
  this.customIsPercent = false;
  this.customModalOpen = false;

  // also reset split tip editor state safely
  this.tipSplitsDirty = false;
  this.tipSplits = [];
  this.tipSplitsDraft = [];
  this.splitTipModalOpen = false;

  // update store tip value so nothing stale survives
  this.$store.commit('SET_TIP_VALUE', {
    amount: 0,
    mode: 'none',
    percent: null,
  });

  // 2) clear recorded payments + selected method (your request)
  this.payments = [];
  this.paymentMethod = null;
  this.$store.commit('SET_TIP_PAYMENT_METHOD', null);

  // 3) IMPORTANT: keep the typed keypad amount if we were inside POS
  // (we do NOT change cashInput/cardInput here)

  // close the block modal
  this.closeTipPartPayBlockModal();
},


        // ---------- payload for backend ----------
        buildTipsJson(): { staff_id: number | string; amount: number }[] | null {
            const total = Number(this.tipAmount) || 0;
            if (total <= 0) return null;

            const recips = this.tipRecipientOptions;

            // single
            if (recips.length <= 1) {
                const staffId = this.selectedTipRecipientId;
                if (!staffId) return null;
                return [{ staff_id: staffId, amount: Number(total.toFixed(2)) }];
            }

            // multi: ensure normalized
            const normalized = this.normalizeSplitsToTotal(total, this.tipSplits) || this.tipSplits;
            return normalized.map((s: any) => ({
                staff_id: s.staff_id,
                amount: Number((Number(s.amount) || 0).toFixed(2)),
            }));
        },
        syncTipSplitsWithTipTotal(newTotal: number, oldTotal: number) {
            const recips = this.tipRecipientOptions;

            // nothing to do
            if (recips.length <= 0) return;

            // single recipient: just pin full amount
            if (recips.length === 1) {
                this.initTipSplitsIfNeeded(true);
                if (this.tipSplits[0]) this.tipSplits[0].amount = Number(newTotal || 0);
                return;
            }

            // multi recipient
            this.initTipSplitsIfNeeded(false);

            if (newTotal <= 0) {
                this.tipSplits = this.tipSplits.map(s => ({ ...s, amount: 0 }));
                this.tipSplitsDirty = false;
                return;
            }

            // default: equal split unless user edited
            if (!this.tipSplitsDirty || !oldTotal || oldTotal <= 0) {
                this.setEqualTipSplit(newTotal);
                return;
            }

            // user edited: scale proportions to new total
            const factor = newTotal / oldTotal;
            const scaled = this.tipSplits.map(s => ({
                ...s,
                amount: Number(((Number(s.amount) || 0) * factor).toFixed(2)),
            }));

            const normalized = this.normalizeSplitsToTotal(newTotal, scaled) || scaled;
            this.tipSplits = normalized;
            this.tipSplitsDirty = true;
        },
        onTipSplitAmountInput(i: number, raw: string) {
            const v = Math.max(0, Number(parseFloat(raw || '0') || 0));
            if (!this.tipSplits[i]) return;

            this.tipSplits[i].amount = Number(v.toFixed(2));

            // optional: auto-balance last row to keep totals correct
            const total = Number(this.tipAmount) || 0;
            const n = this.tipSplits.length;
            if (n <= 1) return;

            const last = n - 1;
            if (i === last) return;

            const sumExceptLast = this.tipSplits
                .slice(0, last)
                .reduce((a, s) => a + (Number(s.amount) || 0), 0);

            this.tipSplits[last].amount = Number(Math.max(0, total - sumExceptLast).toFixed(2));
        },

        initTipSplitsIfNeeded(forceEqual = false) {
            const recips = this.tipRecipientOptions;

            if (recips.length <= 0) {
                this.tipSplits = [];
                this.tipSplitsDirty = false;
                return;
            }

            // Single recipient: keep a single row
            if (recips.length === 1) {
                this.tipSplits = [{
                    staff_id: recips[0].id,
                    name: recips[0].name,
                    amount: Number(this.tipAmount || 0),
                }];
                this.tipSplitsDirty = false;
                return;
            }

            // Multi: ensure rows match recipients
            const existingIds = new Set(this.tipSplits.map(s => String(s.staff_id)));
            const recipIds = new Set(recips.map(r => String(r.id)));
            const same =
                existingIds.size === recipIds.size &&
                [...recipIds].every(id => existingIds.has(id));

            if (!same) {
                this.tipSplits = recips.map(r => ({
                    staff_id: r.id,
                    name: r.name,
                    amount: 0,
                }));
                this.tipSplitsDirty = false;
                forceEqual = true;
            }

            if ((Number(this.tipAmount) || 0) <= 0) {
                this.tipSplits = this.tipSplits.map(s => ({ ...s, amount: 0 }));
                this.tipSplitsDirty = false;
                return;
            }

            if (forceEqual || !this.tipSplitsDirty) {
                this.setEqualTipSplit(Number(this.tipAmount) || 0);
            } else {
                this.normalizeSplitsToTotal(Number(this.tipAmount) || 0, this.tipSplits);
            }
        },
        setEqualTipSplit(total: number) {
            const n = this.tipSplits.length;
            if (n <= 0) return;

            const each = Number((total / n).toFixed(2));
            const out = this.tipSplits.map((s, i) => ({
                ...s,
                amount: i === n - 1 ? 0 : each,
            }));

            const sumFirst = out.slice(0, n - 1).reduce((a, s) => a + (Number(s.amount) || 0), 0);
            out[n - 1].amount = Number((total - sumFirst).toFixed(2));

            this.tipSplits = out;
            this.tipSplitsDirty = false;
        },

        normalizeSplitsToTotal(total: number, splits: any[]) {
            if (!splits?.length) return;
            const out = splits.map(s => ({ ...s, amount: Number((Number(s.amount) || 0).toFixed(2)) }));
            const sum = out.reduce((a, s) => a + s.amount, 0);
            const diff = Number((total - sum).toFixed(2));
            out[out.length - 1].amount = Number((out[out.length - 1].amount + diff).toFixed(2));
            return out;
        },
        rebalanceTipSplits() {
            const total = Number(this.tipAmount) || 0;
            const n = this.tipSplits.length;
            if (n <= 0) return;

            const each = Number((total / n).toFixed(2));
            const out = this.tipSplits.map((s, i) => ({
                ...s,
                amount: i === n - 1 ? 0 : each,
            }));

            const sumFirst = out
                .slice(0, n - 1)
                .reduce((a, s) => a + (Number(s.amount) || 0), 0);

            out[n - 1].amount = Number(Math.max(0, total - sumFirst).toFixed(2));
            this.tipSplits = out;
        },
      

        openCategory(cat: any) {
            this.selectedCategoryId = cat.id ?? null;
        },


        close() {
            this.$store.commit('CLOSE_TIP_PANEL');
        },

        formatNumber(v: number): string {
            const n = Number(v) || 0;
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
        formatNumberNoCents(v: number): string {
            const n = Number(v) || 0;
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
        },
        handlePosKeydown(e: KeyboardEvent) {
            // Only react when one of the modals is open
            if (
                !this.cashModalOpen &&
                !this.cardModalOpen &&
                !this.customModalOpen
            ) {
                return;
            }

            const target = e.target as HTMLElement | null;
            if (target) {
                const tag = target.tagName;
                if (
                    tag === 'INPUT' ||
                    tag === 'TEXTAREA' ||
                    (target as any).isContentEditable
                ) {
                    return;
                }
            }

            const key = e.key;

            const isDigit = /^[0-9]$/.test(key);

            const inCash = this.cashModalOpen;
            const inCard = this.cardModalOpen;
            const inCustom = this.customModalOpen;

            // Digits 0–9
            if (isDigit) {
                if (inCash) this.onCashKeypadPress(key);
                else if (inCard) this.onCardKeypadPress(key);
                else if (inCustom) this.onKeypadPress(key);

                e.preventDefault();
                return;
            }

            if (key === '.' || key === ',') {
                if (inCash) this.onCashKeypadPress('.');
                else if (inCard) this.onCardKeypadPress('.');
                else if (inCustom) this.onKeypadPress('.');

                e.preventDefault();
                return;
            }

            // Backspace → your "back" key
            if (key === 'Backspace') {
                if (inCash) this.onCashKeypadPress('back');
                else if (inCard) this.onCardKeypadPress('back');
                else if (inCustom) this.onKeypadPress('back');

                e.preventDefault();
                return;
            }

            // Enter → confirm
            if (key === 'Enter') {
                if (inCash) this.confirmCashAmount();
                else if (inCard) this.confirmCardAmount();
                else if (inCustom) this.confirmCustomTip();

                e.preventDefault();
                return;
            }

            // Escape → close modal
            if (key === 'Escape') {
                if (inCash) this.closeCashModal();
                else if (inCard) this.closeCardModal();
                else if (inCustom) this.closeCustomModal();

                e.preventDefault();
                return;
            }
        },

        initials(name: string): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 2);
        },

        onAddClient() {
            if (this.isPaymentPending) return;
            this.showClientPicker = true;
            this.showAddClientModal = false;
        },


        handleClientSaved(newClient: any) {
            if (!newClient || !newClient.id) {
                this.showAddClientModal = false;
                return;
            }

            // 1) Update tip meta
            if (this.$store) {
                this.$store.commit('TIP_SET_CLIENT', newClient);
            }

            // 2) Keep local list in sync
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

            // 🔹 3) Show this new client immediately in the card
            this.selectedClient = newClient;

            // 4) Close both modal and picker
            this.showAddClientModal = false;
            this.showClientPicker = false;
        },




        // ===== tip cards =====
        cardClass(type: 'none' | 'percent' | 'custom', percent?: number) {
            const active =
                (type === 'none' && this.mode === 'none') ||
                (type === 'custom' && this.mode === 'custom') ||
                (type === 'percent' &&
                    this.mode === 'percent' &&
                    this.selectedPercent === percent);

            return active
                ? 'border-[1.5px] border-orange-500 ring-2 ring-orange-200 bg-orange-50'
                : 'border border-neutral-200 hover:border-neutral-400 hover:bg-neutral-50';
        },

        selectNone() {
            this.mode = 'none';
            this.selectedPercent = 0;
            this.customModalOpen = false;
        },

        selectPercent(p: number) {
            this.mode = 'percent';
            this.selectedPercent = p;
            this.customModalOpen = false;
        },

        percentAmount(p: number): string {
            const amount = (this.baseAmount * p) / 100;
            return this.formatNumber(amount);
        },

        paymentCardClass(method: PaymentMethod) {
            const active = this.paymentMethod === method;
            return active
                ? 'border-[1.5px] border-neutral-900 ring-2 ring-neutral-200 bg-neutral-50'
                : 'border border-neutral-200 hover:border-neutral-400 hover:bg-neutral-50';
        },

        paymentLabelFor(method: PaymentMethod | PaymentLineMethod): string {
            switch (method) {
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
                    return '';
            }
        },

        selectPayment(method: PaymentMethod) {
            this.paymentMethod = method;
            this.$store.commit('SET_TIP_PAYMENT_METHOD', method);
        },

        onCashCardClick() {
            if (this.isFullyPaid) return;
            this.selectPayment('cash');
            this.openCashModal(false);
        },
        onCardCardClick() {
            if (this.isFullyPaid) return;
            this.selectPayment('card');
            this.openCardModal(false);
        },
        onGiftCardCardClick() {
            if (this.isFullyPaid) return;
            this.selectPayment('gift-card');
            this.openGiftCardModal(false);
        },
       onSplitCardClick() {
  if (this.isFullyPaid) return;
  this.selectPayment('split');

  if (!this.payments.length) {
    this.splitSelectModalOpen = true;
  }
},


        openCustomModal() {
            this.mode = 'custom';
            if (!this.customInput) {
                this.customInput = '0';
            }
            this.customModalOpen = true;
            this.customJustOpened = true;
        },

        closeCustomModal() {
            this.customModalOpen = false;
            this.customJustOpened = false;
        },

        setCustomKind(kind: 'amount' | 'percent') {
            if (kind === 'percent' && !this.customIsPercent) {
                const amt = parseFloat(this.customInput || '0');
                if (this.baseAmount > 0 && !isNaN(amt)) {
                    const pct = (amt / this.baseAmount) * 100;
                    this.customInput = pct.toFixed(2);
                } else {
                    this.customInput = '0';
                }
                this.customIsPercent = true;
            } else if (kind === 'amount' && this.customIsPercent) {
                const pct = parseFloat(this.customInput || '0');
                if (!isNaN(pct)) {
                    const amt = (this.baseAmount * pct) / 100;
                    this.customInput = amt.toFixed(2);
                } else {
                    this.customInput = '0';
                }
                this.customIsPercent = false;
            }
        },


        onKeypadPress(key: string) {
            // FIRST key after opening → replace existing value
            if (this.customJustOpened) {
                this.customJustOpened = false;

                if (key === 'back') {
                    this.customInput = '0';
                    return;
                }
                if (key === '.') {
                    this.customInput = '0.';
                    return;
                }
                // digit
                this.customInput = key;
                return;
            }

            // Normal behaviour afterwards
            if (key === 'back') {
                if (!this.customInput || this.customInput === '0') {
                    this.customInput = '0';
                } else {
                    const next = this.customInput.slice(0, -1);
                    this.customInput = next.length ? next : '0';
                }
                return;
            }

            if (key === '.') {
                if (this.customInput.includes('.')) return;
                this.customInput += '.';
                return;
            }

            // digit
            if (this.customInput === '0') {
                this.customInput = key;
            } else {
                this.customInput += key;
            }
        },
        applyInitialStep() {
            if (this.isPreviewPendingPayment) {
                this.step = 'payment';
            }

        },

        confirmCustomTip() {
            this.customModalOpen = false;
        },

        // ===== cash modal =====
        openCashModal(fromSplit: boolean) {
            if (this.isFullyPaid) return;
            this.cashFromSplit = fromSplit;
            const base = this.leftToPay || this.totalWithTip;
            this.cashInput = base > 0 ? base.toFixed(2) : '0';
            this.cashModalOpen = true;
            this.cashJustOpened = true;
        },
        closeCashModal() {
            this.cashModalOpen = false;
            this.cashJustOpened = false
        },
        onCashKeypadPress(key: string) {
            // FIRST key after opening → replace prefilled amount
            if (this.cashJustOpened) {
                this.cashJustOpened = false;

                if (key === 'back') {
                    this.cashInput = '0';
                    return;
                }
                if (key === '.') {
                    this.cashInput = '0.';
                    return;
                }
                // digit
                this.cashInput = key;
                return;
            }

            // Normal behaviour afterwards
            if (key === 'back') {
                if (!this.cashInput || this.cashInput === '0') {
                    this.cashInput = '0';
                } else {
                    const next = this.cashInput.slice(0, -1);
                    this.cashInput = next.length ? next : '0';
                }
                return;
            }
            if (key === '.') {
                if (this.cashInput.includes('.')) return;
                this.cashInput += '.';
                return;
            }
            if (this.cashInput === '0') {
                this.cashInput = key;
            } else {
                this.cashInput += key;
            }
        },


        setCashFromQuick(amt: number) {
            this.cashInput = amt.toFixed(2);
        },
      confirmCashAmount() {
  if (this.cashAmountNum <= 0) return;

  // ✅ Allow adding partial cash. Do NOT open the modal here.
  this.addPayment('cash', this.cashAmountNum);
  this.closeCashModal();
},


        // ===== card modal =====
        openCardModal(fromSplit: boolean) {
            if (this.isFullyPaid) return;
            this.cardFromSplit = fromSplit;
            const base = this.leftToPay || this.totalWithTip;
            this.cardInput = base > 0 ? base.toFixed(2) : '0';
            this.cardModalOpen = true;
            this.cardJustOpened = true;
        },
        closeCardModal() {
            this.cardModalOpen = false;
            this.cardJustOpened = false;
        },
        onCardKeypadPress(key: string) {
            // FIRST key after opening → replace prefilled amount
            if (this.cardJustOpened) {
                this.cardJustOpened = false;

                if (key === 'back') {
                    this.cardInput = '0';
                    return;
                }
                if (key === '.') {
                    this.cardInput = '0.';
                    return;
                }
                // digit
                this.cardInput = key;
                return;
            }

            // Normal behaviour afterwards
            if (key === 'back') {
                if (!this.cardInput || this.cardInput === '0') {
                    this.cardInput = '0';
                } else {
                    const next = this.cardInput.slice(0, -1);
                    this.cardInput = next.length ? next : '0';
                }
                return;
            }
            if (key === '.') {
                if (this.cardInput.includes('.')) return;
                this.cardInput += '.';
                return;
            }
            if (this.cardInput === '0') {
                this.cardInput = key;
            } else {
                this.cardInput += key;
            }
        },

        setCardFromQuick(amt: number) {
            this.cardInput = amt.toFixed(2);
        },
       confirmCardAmount() {
  if (this.cardAmountNum <= 0) return;

  // ✅ Allow adding partial card. Do NOT open the modal here.
  this.addPayment('card', this.cardAmountNum);
  this.closeCardModal();
},


        openGiftCardModal(fromSplit: boolean) {
            if (this.isFullyPaid) return;
            this.cashFromSplit = fromSplit;
            this.giftCardCode = '';
            this.giftCardModalOpen = true;
        },
        closeGiftCardModal() {
            this.giftCardModalOpen = false;
        },
        confirmGiftCard() {
            if (!this.giftCardCode) return;

            const amount = this.leftToPay || this.totalWithTip;
            if (amount > 0) {
                this.addPayment('gift-card', amount);
            }

            this.giftCardModalOpen = false;
        },


        closeSplitSelectModal() {
            this.splitSelectModalOpen = false;
        },
        splitSelect(method: PaymentLineMethod) {

            if (this.isFullyPaid) {
                this.splitSelectModalOpen = false;
                return;

            }
            this.splitSelectModalOpen = false;

            if (method === 'cash') {
                this.openCashModal(true);
            } else if (method === 'card') {
                this.openCardModal(true);
            } else if (method === 'gift-card') {
                this.openGiftCardModal(true);
            } else {
                const amount = this.leftToPay || this.totalWithTip;
                if (amount > 0) {
                    this.addPayment('other', amount);
                }
            }
        },

        addPayment(method: PaymentLineMethod, amount: number) {
            const safeAmount = Number(amount) || 0;
            if (safeAmount <= 0) return;

            const methodsSet = new Set<PaymentLineMethod>(
                this.payments.map((p: any) => p.method as PaymentLineMethod),
            );
            methodsSet.add(method);
            const distinctMethods = Array.from(methodsSet);
            const distinctCount = distinctMethods.length;

            if (!this.paymentMethod) {
                if (distinctCount === 1) {
                    this.selectPayment(distinctMethods[0] as PaymentMethod);
                } else {
                    this.selectPayment('split');
                }
            } else if (distinctCount > 1 && this.paymentMethod !== 'split') {

                this.selectPayment('split');
            }

            this.payments.push({
                method,
                amount: safeAmount,
            });
        },


        removePaymentAt(index: number) {
            if (index < 0 || index >= this.payments.length) return;

            this.payments.splice(index, 1);

            const methodsSet = new Set<PaymentLineMethod>(
                this.payments.map((p: any) => p.method as PaymentLineMethod),
            );
            const distinctMethods = Array.from(methodsSet);

            if (this.payments.length === 0) {
                this.paymentMethod = null;
                this.$store.commit('SET_TIP_PAYMENT_METHOD', null);
                return;
            }


            if (!distinctMethods.includes(this.paymentMethod as PaymentLineMethod)) {
                if (distinctMethods.length === 1) {
                    this.selectPayment(distinctMethods[0] as PaymentMethod);
                } else {
                    this.selectPayment('split');
                }
            } else if (distinctMethods.length === 1 && this.paymentMethod === 'split') {
                this.selectPayment(distinctMethods[0] as PaymentMethod);
            }
        },


        async openEditItem(item: any, index: number) {
            this.editIndex = index;
            this.editItemDraft = { ...item };

            const basePrice =
                item.rawPrice != null ? Number(item.rawPrice) :
                    item.price_before_discount != null ? Number(item.price_before_discount) :
                        Number(item.price ?? item.final_price ?? 0);

            const qty = Number(item.quantity ?? 1);

            this.editPriceInput = isNaN(basePrice) ? '0' : basePrice.toFixed(2);
            this.editQty = isNaN(qty) || qty <= 0 ? 1 : qty;

            this.editStaffId =
                item.staff_id ??
                item.staff?.id ??
                item.staffId ??
                null;

            this.editDiscountId = item.discount_id ?? null;

            await Promise.all([this.loadTeamMembers(), this.loadDiscountsForEdit(item)]);

            this.editModalOpen = true;
        },
        async loadTeamMembers() {
            const fromPage = (this.$page?.props?.employees || this.$page?.props?.staff || []) as any[];
            if (Array.isArray(fromPage) && fromPage.length) {
                this.teamMembers = fromPage.map((u: any) => ({ id: u.id, name: u.name }));
                return;
            }

            const routeFn = (window as any).route;
            const url = routeFn ? routeFn('employee.select') : '/employee/select';

            const res = await axios.get(url, {
                params: { branch_id: this.meta.branch_id ?? null },
            });

            this.teamMembers = (res.data?.data || []).map((u: any) => ({ id: u.id, name: u.name }));
        },

        async loadDiscountsForEdit(item: any) {
            try {
                const { start, end } = this.getEditDateRange();

                const routeFn = (window as any).route;
                const url = routeFn ? routeFn('discounts.select') : '/discounts/select';

                const res = await axios.get(url, {
                    params: {
                        branch_id: this.meta.branch_id ?? null,
                        service_id: item.service_id ?? item.serviceId ?? null,
                        start: start ? String(start) : null,
                        end: end ? String(end) : null,
                    },
                });

                this.discounts = res.data?.data || [];
            } catch (err: any) {
                console.error('loadDiscountsForEdit failed:', err?.response?.data || err);
                this.discounts = [];
            }
        },

        getEditDateRange(): { start: string | null; end: string | null } {
            const b = this.meta?.booking || {};

            const start =
                b.slot_start || b.slotStart || b.starts_at || b.startsAt ||
                this.items?.[0]?.starts_at ||
                null;

            const end =
                b.slot_end || b.slotEnd || b.ends_at || b.endsAt ||
                this.items?.[this.items.length - 1]?.ends_at ||
                null;

            return { start, end };
        },



        closeEditModal() {
            this.editModalOpen = false;
            this.editIndex = -1;
            this.editItemDraft = null;
        },

        adjustEditQty(delta: number) {
            const next = (this.editQty || 1) + delta;
            this.editQty = next <= 1 ? 1 : next;
        },

        applyEdit() {
            if (this.editIndex < 0 || !this.editItemDraft) {
                this.closeEditModal();
                return;
            }

            const base = Math.max(0, Number(parseFloat(this.editPriceInput || '0') || 0));
            const qty = Math.max(1, Number(this.editQty || 1));

            const staff = this.teamMembers.find((u: any) => u.id === this.editStaffId) || null;
            const disc = this.discounts.find((d: any) => d.id === this.editDiscountId) || null;

            let discount_type = 'none';
            let discount_value = 0;

            if (disc) {
                discount_type = disc.type;       // 'percent' | 'amount'
                discount_value = Number(disc.value) || 0;
            }

            let finalUnit = base;
            if (discount_type === 'percent') finalUnit = Math.max(0, base - (base * discount_value) / 100);
            if (discount_type === 'amount') finalUnit = Math.max(0, base - discount_value);

            const updated = {
                ...this.editItemDraft,

                // pricing
                rawPrice: base,
                price_before_discount: base,
                discount_id: this.editDiscountId,
                discount_type,
                discount_value,

                // IMPORTANT: this drives your subtotal/baseAmount calculations
                price: finalUnit,
                final_price: finalUnit,

                quantity: qty,

                // staff
                staff_id: this.editStaffId,
                staff_name: staff?.name ?? this.editItemDraft.staff_name ?? null,
            };

            const items = [...this.items];
            items.splice(this.editIndex, 1, updated);

            this.localItems = items;
            this.$store.commit('TIP_UPDATE_SERVICES', items);

            this.closeEditModal();
        },

        goToPaymentStep() {
            const percentFromCustom =
                this.mode === 'custom' && this.customIsPercent
                    ? parseFloat(this.customInput || '0')
                    : null;

            this.$store.commit('SET_TIP_VALUE', {
                amount: this.tipAmount,
                mode: this.mode,
                percent:
                    this.mode === 'percent'
                        ? this.selectedPercent
                        : percentFromCustom,
            });
            this.$store.commit('UPDATE_TIP_BASE_AMOUNT', this.baseAmount);

            this.step = 'payment';
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


        guessDurationFromService(serviceId: number | null): number {
            if (!serviceId) return 0;
            const svc = this.allServices.find(
                (s: any) => s.id === serviceId || s.service_id === serviceId,
            );
            if (!svc) return 0;
            const raw = Number(svc.duration_minutes ?? svc.duration ?? 0);
            return isNaN(raw) ? 0 : raw;
        },


        buildServicesPayloadFromCart(): any[] {
            const services: any[] = [];


            const booking = this.meta.booking || {};
            const baseIso =
                booking.slot_start ||
                booking.slotStart ||
                booking.starts_at ||
                booking.startsAt ||
                null;

            const baseDate = baseIso ? new Date(baseIso) : new Date();
            baseDate.setSeconds(0, 0, 0);

            let prevEnd: Date | null = null;

            this.items.forEach((item: any, index: number) => {

                let start: Date;
                if (item.starts_at) {
                    start = new Date(item.starts_at);
                } else if (index === 0) {

                    start = new Date(baseDate);
                } else if (prevEnd) {

                    start = new Date(prevEnd);
                } else {
                    start = new Date(baseDate);
                }


                const rawDuration =
                    Number(item.duration_minutes ?? item.duration ?? 0) ||
                    this.guessDurationFromService(item.service_id ?? null) ||
                    0;
                const extraMinutes = Number(
                    item.extra_minutes ?? item.extraMinutes ?? 0,
                );
                const totalMinutes = Math.max(
                    0,
                    Number(rawDuration) + (isNaN(extraMinutes) ? 0 : extraMinutes),
                );

                const end = new Date(start);
                if (totalMinutes > 0) {
                    end.setMinutes(end.getMinutes() + totalMinutes);
                }
                prevEnd = end;


                const basePrice =
                    item.rawPrice != null
                        ? Number(item.rawPrice)
                        : item.price_before_discount != null
                            ? Number(item.price_before_discount)
                            : Number(item.price ?? item.final_price ?? 0);

                const discountType =
                    item.discount_type || item.discountType || 'none';
                const discountValue = Number(
                    item.discount_value ?? item.discountValue ?? 0,
                );

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

                    id: item.id ?? null,

                    service_id: item.service_id ?? item.serviceId ?? null,
                    service_variant_id:
                        item.service_variant_id ?? item.variantId ?? null,
                    staff_id:
                        item.staff_id ??
                        item.staff?.id ??
                        item.staffId ??
                        this.meta.staff?.id ??
                        null,


                    label: item.label,

                    duration_minutes: Number(rawDuration),
                    extra_minutes: Number(extraMinutes) || 0,

                    starts_at: this.formatLocalDateTime(start),
                    ends_at: this.formatLocalDateTime(end),

                    price: basePrice,
                    discount_type: discountType,
                    discount_value: discountValue,
                    final_price: finalPrice,

                    color_code: item.color_code ?? item.color ?? null,
                    quantity: Number(item.quantity ?? 1),
                });
            });

            return services;
        },

        saveUnpaid() {

            // 🚫 If tip is applied, do not allow part-paid saves
if (this.tipAmount > 0 && this.leftToPay > 0.01) {
  this.openTipPartPayBlockModal('save');
  return;
}


            if (this.isSaving) return;

            const servicesPayload = this.buildServicesPayloadFromCart();

            const slotStart =
                servicesPayload.length > 0
                    ? servicesPayload[0].starts_at
                    : this.meta.booking?.slot_start ?? null;
            const slotEnd =
                servicesPayload.length > 0
                    ? servicesPayload[servicesPayload.length - 1].ends_at
                    : this.meta.booking?.slot_end ?? null;

            const syncUrl = (window as any).route
                ? (window as any).route('booking.services.sync', {
                    booking: this.bookingId,
                })
                : `/bookings/${this.bookingId}/sync-services`;


            const tipsJson = this.buildTipsJson();
            const tipStaffId = this.tipRecipientOptions.length <= 1 ? this.selectedTipRecipientId : null;


            this.isSaving = true;

            router.post(
                syncUrl,
                {
                    services: servicesPayload,
                    slot_start: slotStart,
                    slot_end: slotEnd,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        const payload = {
                            booking_id: this.bookingId,
                            branch_id: this.meta.branch_id ?? null,
                            client_id:
                                this.selectedClient?.id ??
                                this.meta.client?.id ??
                                null,
                            tip_staff_id: tipStaffId,
                            tip_amount: this.tipAmount,
                            tips_json: tipsJson,

                            base_amount: this.baseAmount,
                            tax_amount: this.taxAmount,
                            total_with_tip: this.totalWithTip,

                            payment_method: this.paymentMethod,
                            payments: this.payments,

                            discount_base_amount: this.discountBaseTotal,
                            discount_total: this.totalDiscount,

                            total_paid: this.totalPaid,
                            remaining: this.leftToPay,

                            services: servicesPayload,
                            slot_start: slotStart,
                            slot_end: slotEnd,
                        };

                        const saleUrl = (window as any).route
                            ? (window as any).route('booking.sales.store')
                            : '/booking-sales';

                        router.post(saleUrl, payload, {
                            preserveScroll: true,
                            preserveState: true,
                            onSuccess: (page: any) => {
                                this.$store.commit(
                                    'SET_TIP_PAYMENT_METHOD',
                                    this.paymentMethod,
                                );
                                this.$store.commit('CLOSE_TIP_PANEL');

                                const saleMeta = {
                                    tip_staff_id: this.selectedTipRecipientId,

                                    bookingId: this.bookingId,
                                    booking: this.meta.booking ?? null,
                                    client: this.meta.client,
                                    staff: this.meta.staff,
                                    branch_id: this.meta.branch_id ?? null,

                                    currencySymbol: this.currencySymbol,

                                    base_amount: this.baseAmount,
                                    tax_amount: this.taxAmount,
                                    tip_amount: this.tipAmount,
                                    total_with_tip: this.totalWithTip,

                                    discount_base_amount: this.discountBaseTotal,
                                    discount_total: this.totalDiscount,

                                    payment_method: this.paymentMethod,
                                    payments: this.payments,
                                    total_paid: this.totalPaid,
                                    remaining: this.leftToPay,

                                    services: servicesPayload,
                                };

                                this.$store.commit(
                                    'OPEN_SUCCESS_PAYMENT',
                                    saleMeta,
                                );


                                this.isSaving = false;
                            },
                            onError: (errors) => {
                                console.error('Failed to save booking sale', errors);

                                this.isSaving = false;
                            },
                        });
                    },
                    onError: (errors) => {
                        console.error(
                            'Failed to sync services from tip panel',
                            errors,
                        );
                        // ✅ stop loader if first request fails
                        this.isSaving = false;
                    },
                },
            );
        },



handlePrimaryAction() {
    if (this.step === 'cart') {
        this.step = 'tip';
        return;
    }

    if (this.step === 'tip') {
        this.goToPaymentStep();
        return;
    }

    if (this.step === 'payment') {
        // Check if this is a part-paid booking
        if (this.totalWithTip > 0 && this.payments.length > 0 && this.leftToPay > 0) {
            // Show confirmation modal for part-paid
            this.showPartPaidConfirmation();
        } else {
            // Full payment or no payments yet - proceed directly
            this.processPaymentSave();
        }
    }
},

showPartPaidConfirmation() {
  // ✅ If tip exists + not fully paid, show tip-block modal instead of confirm part-paid
  if (this.tipAmount > 0 && this.leftToPay > 0.01) {
    this.openTipPartPayBlockModal('save');
    return;
  }

  this.confirmPartPaidModalOpen = true;
},


cancelPartPaidConfirm() {
    this.confirmPartPaidModalOpen = false;
    this.pendingSaveCallback = null;
},

confirmPartPaidSave() {
    this.confirmPartPaidModalOpen = false;
    this.processPaymentSave();
},

processPaymentSave() {
    // Move your existing save logic here
    if (this.paymentMethod === 'other' && this.totalWithTip > 0 && this.payments.length === 0) {
        this.addPayment('other', this.totalWithTip);
    }
    
    this.saveUnpaid();
},


        addServiceToCart(service: any) {
            const priceNum = Number(
                service.price ?? service.final_price ?? service.amount ?? 0,
            );
            const safePrice = isNaN(priceNum) ? 0 : Math.max(0, priceNum);

            const durationMinutes = Number(
                service.duration_minutes ?? service.duration ?? 0,
            );
            const safeDuration =
                isNaN(durationMinutes) || durationMinutes <= 0
                    ? 30
                    : durationMinutes;

            // 🔹 pick up the real service/category color
            const colorFromService =
                service.color_code ||
                service.color ||
                (service.category && service.category.color_code) ||
                this.defaultServiceColor;

            const newItem = {
                instanceUid:
                    service.instanceUid ||
                    `svc-${service.id || service.service_id || Date.now()}-${Math.random()
                        .toString(36)
                        .slice(2)}`,

                service_id: service.id ?? service.service_id ?? null,
                label: service.name ?? service.label ?? 'Service',

                price: safePrice,
                rawPrice: safePrice,
                price_before_discount: safePrice,
                discount_type: 'none',
                discount_value: 0,

                quantity: 1,

                // 🔹 store color in both fields so old code keeps working
                color: colorFromService,
                color_code: colorFromService,

                duration_minutes: safeDuration,
                extra_minutes: 0,
                starts_at: service.starts_at ?? null,
                ends_at: service.ends_at ?? null,

                staff_name:
                    service.staff_name ||
                    service.staffName ||
                    this.staffName ||
                    null,
                staff_id:
                    service.staff_id ??
                    service.staffId ??
                    this.meta.staff?.id ??
                    null,
            };

            const items = [...this.items, newItem];

            this.localItems = items;
            this.$store.commit('TIP_UPDATE_SERVICES', items);

                if (this.isMobile) {
        this.step = 'tip';
        this.selectedCategoryId = null; 
        this.showMobileSummary = false; 
    }
        },

        removeItemAt(index: number) {
            if (index < 0 || index >= this.items.length) return;

            // clone current items (items = localItems || meta.services)
            const items = [...this.items];

            // remove the one at index
            items.splice(index, 1);

            // update local state and Vuex
            this.localItems = items;
            this.$store.commit('TIP_UPDATE_SERVICES', items);
        },

        // optional: remove by instanceUid if you prefer
        removeItemByUid(uid: string) {
            const items = this.items.filter(
                (item: any) => item.instanceUid !== uid,
            );
            this.localItems = items;
            this.$store.commit('TIP_UPDATE_SERVICES', items);
        },

        onRemoveItemClick(index: number) {
            // prevent deleting the last remaining service
            if (this.items.length <= 1) {
                return;
            }
            this.removeItemAt(index);
        },

        goToCartFromSummary() {
            this.step = 'cart';
            this.showMobileSummary = false;
        },
        toggleMobileSummary() {
            this.showMobileSummary = !this.showMobileSummary;
        },
        handleResize() {
            this.isMobile = window.innerWidth < 640;
            if (!this.isMobile) {
                this.showMobileSummary = true;
            }
        },

        handleClientSelected(client: any) {
            if (this.isPaymentPending) return;
            // 1) Attach client to tip meta / Vuex
            if (this.$store) {
                this.$store.commit('TIP_SET_CLIENT', client);
            }

            // 2) Ensure the client is in our local list
            if (!Array.isArray(this.clientsLocal)) {
                this.clientsLocal = [];
            }
            const idx = this.clientsLocal.findIndex(
                (c: any) => c.id === client.id,
            );
            if (idx === -1) {
                this.clientsLocal.push(client);
            } else {
                this.clientsLocal.splice(idx, 1, client);
            }

            // 🔹 3) reflect in this panel immediately
            this.selectedClient = client;

            // 4) Close the picker
            this.showClientPicker = false;
        },


        handleWalkInSelected() {
            if (this.isPaymentPending) return;
            if (this.$store) {
                this.$store.commit('TIP_SET_CLIENT', null);
            }
            this.selectedClient = null;   // 🔹 clear from UI too
            this.showClientPicker = false;
        },

        handleAddNewClientFromPicker() {
            if (this.isPaymentPending) return;
            // Open the existing AddClientModal from inside the picker
            this.showAddClientModal = true;
        },
    

        closeMobileSummary() {
            this.showMobileSummary = false;
        },

        openMobileSummary() {
            this.showMobileSummary = true;
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

/* step switch animation */
.step-slide-enter-active,
.step-slide-leave-active {
    transition:
        opacity 0.2s ease-out,
        transform 0.2s ease-out;
}

.step-slide-enter-from,
.step-slide-leave-to {
    opacity: 0;
    transform: translateX(12px);
}

.tip-modal-enter-active,
.tip-modal-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}

.tip-modal-enter-from,
.tip-modal-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
}

.cash-modal-enter-active,
.cash-modal-leave-active,
.card-modal-enter-active,
.card-modal-leave-active,
.gift-modal-enter-active,
.gift-modal-leave-active,
.split-modal-enter-active,
.split-modal-leave-active,
.edit-modal-enter-active,
.edit-modal-leave-active {
    transition:
        opacity 0.28s cubic-bezier(0.24, 0.8, 0.25, 1),
        transform 0.28s cubic-bezier(0.24, 0.8, 0.25, 1);
}

.cash-modal-enter-from,
.cash-modal-leave-to,
.card-modal-enter-from,
.card-modal-leave-to,
.gift-modal-enter-from,
.gift-modal-leave-to,
.split-modal-enter-from,
.split-modal-leave-to,
.edit-modal-enter-from,
.edit-modal-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}

button {
    cursor: pointer;
}

.client-offcanvas-enter-active,
.client-offcanvas-leave-active {
    transition:
        opacity 0.24s ease-out,
        transform 0.24s ease-out;
}

.client-offcanvas-enter-from,
.client-offcanvas-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

.spinner {
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    border: 2px solid rgba(255, 255, 255, 0.28);
    border-top-color: rgba(255, 255, 255, 0.95);
    border-right-color: rgba(255, 255, 255, 0.55);
    display: inline-block;
    animation: spin 0.75s linear infinite;
    filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.25));
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}





.summary-offcanvas-enter-active,
.summary-offcanvas-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.summary-offcanvas-enter-from,
.summary-offcanvas-leave-to {
    transform: translateX(100%);
}

.confirm-modal-enter-active,
.confirm-modal-leave-active {
    transition:
        opacity 0.2s ease-out,
        transform 0.2s ease-out;
}

.confirm-modal-enter-from,
.confirm-modal-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(8px);
}
</style>
