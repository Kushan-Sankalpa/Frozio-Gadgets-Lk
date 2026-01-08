<template>
    <Head title="Services" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900"
            >
                <!-- Header -->
                <div
                    class="flex flex-col gap-3 border-b border-zinc-200 px-4 py-3 sm:px-5 sm:py-4 dark:border-zinc-700/60"
                >
                    <!-- Mobile First Row  -->
                    <div class="flex items-center justify-between sm:hidden">
                        <h5
                            class="text-xl font-semibold text-zinc-900 dark:text-zinc-100"
                        >
                            Service Menu
                        </h5>
                        <div class="flex items-center gap-2">
                            <!-- Mobile Options Button  -->
                            <div class="relative" ref="mobileOptionsRef">
                                <button
                                    class="inline-flex cursor-pointer items-center rounded-full p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                    @click="toggleMobileOptions"
                                >
                                    <i
                                        class="bx bx-dots-vertical-rounded text-xl"
                                    ></i>
                                </button>
                                <Transition
                                    enter-active-class="transition ease-out duration-300"
                                    enter-from-class="opacity-0 scale-95"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-200"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div
                                        v-if="mobileOptionsOpen"
                                        class="absolute right-0 z-30 mt-2 w-52 origin-top-right overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900"
                                    >
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportPdf"
                                        >
                                            Export as PDF
                                        </button>
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportCsv"
                                        >
                                            Export as CSV
                                        </button>
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportExcel"
                                        >
                                            Export as Excel
                                        </button>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Mobile Add Button  -->
                            <div class="relative" ref="mobileAddRef">
                                <button
                                    class="btn-primary inline-flex items-center justify-center rounded-full p-2 text-base"
                                    @click="toggleMobileAdd"
                                >
                                    <i class="bx bx-plus text-xl"></i>
                                </button>
                                <Transition
                                    enter-active-class="transition ease-out duration-400"
                                    enter-from-class="opacity-0 translate-y-1 scale-95"
                                    enter-to-class="opacity-100 translate-y-0 scale-100"
                                    leave-active-class="transition ease-in duration-300"
                                    leave-from-class="opacity-100 translate-y-0 scale-100"
                                    leave-to-class="opacity-0 translate-y-1 scale-95"
                                >
                                    <div
                                        v-if="mobileAddOpen"
                                        class="absolute right-0 z-40 mt-2 w-60 origin-top-right overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900"
                                    >
                                        <button
                                            class="flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="goCreateService"
                                        >
                                            <i
                                                class="bx bx-plus-circle text-lg"
                                            ></i>
                                            Single service
                                        </button>
                                        <button
                                            class="flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="openCategoryModal()"
                                        >
                                            <i
                                                class="bx bx-folder-plus text-lg"
                                            ></i>
                                            Category
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- First row -->
                    <div
                        class="hidden flex-wrap items-center justify-between gap-2 sm:flex"
                    >
                        <div>
                            <h5
                                class="text-xl font-semibold text-zinc-900 dark:text-zinc-100"
                            >
                                Service Menu
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                View and manage the services offered by your
                                business.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <div
                                class="relative w-full sm:w-auto"
                                ref="optionsRef"
                            >
                                <button
                                    class="btn-secondary inline-flex cursor-pointer items-center text-base"
                                    @click="toggleOptions"
                                >
                                    Export
                                    <i
                                        class="bx bx-chevron-down ml-1 text-xl"
                                    ></i>
                                </button>
                                <Transition
                                    enter-active-class="transition ease-out duration-300"
                                    enter-from-class="opacity-0 scale-95"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-200"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div
                                        v-if="optionsOpen"
                                        class="absolute left-0 z-30 mt-2 w-52 origin-top-right overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900 "
                                    >
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportPdf"
                                        >
                                            Export as PDF
                                        </button>
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportCsv"
                                        >
                                            Export as CSV
                                        </button>
                                        <button
                                            class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="exportExcel"
                                        >
                                            Export as Excel
                                        </button>
                                    </div>
                                </Transition>
                            </div>

                            <div class="relative w-full sm:w-auto" ref="addRef">
                                <button
                                    class="btn-primary inline-flex w-full items-center justify-center text-base sm:w-auto"
                                    @click="toggleAdd"
                                >
                                    Add
                                    <i
                                        class="bx bx-chevron-down ml-1 text-xl"
                                    ></i>
                                </button>

                                <Transition
                                    enter-active-class="transition ease-out duration-400"
                                    enter-from-class="opacity-0 translate-y-1 scale-95"
                                    enter-to-class="opacity-100 translate-y-0 scale-100"
                                    leave-active-class="transition ease-in duration-300"
                                    leave-from-class="opacity-100 translate-y-0 scale-100"
                                    leave-to-class="opacity-0 translate-y-1 scale-95"
                                >
                                    <div
                                        v-if="addOpen"
                                        class="absolute right-0 z-40 mt-2 w-60 origin-top-right overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900"
                                    >
                                        <button
                                            class="flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="goCreateService"
                                        >
                                            <i
                                                class="bx bx-plus-circle text-lg"
                                            ></i>
                                            Single service
                                        </button>
                                        <button
                                            class="flex w-full cursor-pointer items-center gap-2 px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            @click="openCategoryModal()"
                                        >
                                            <i
                                                class="bx bx-folder-plus text-lg"
                                            ></i>
                                            Category
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Description  -->
                    <div class="sm:hidden">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            View and manage the services offered by your
                            business.
                        </p>
                    </div>

                    <!-- Second row-->
                    <div class="flex flex-1 flex-wrap items-center gap-2">
                        <div class="relative min-w-[150px] flex-1 sm:max-w-sm">
                            <input
                                v-model="q"
                                type="text"
                                placeholder="Search service name"
                                class="w-full rounded-full border border-zinc-300 px-3 py-2 text-base text-zinc-800 placeholder-zinc-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            />
                            <i
                                class="bx bx-search absolute top-2.5 right-3 text-zinc-400"
                            ></i>
                        </div>

                        <!-- Mobile Action Buttons  -->
                        <div class="flex items-center gap-2 sm:hidden">
                            <!-- <button
                class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 p-2 text-base dark:border-zinc-600"
                @click="openFilters">
                <i class="bx bx-slider-alt text-lg"></i>
              </button> -->

                            <button
                                class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 p-2 text-base dark:border-zinc-600"
                                @click="openManageOrder"
                            >
                                <i class="bx bx-sort text-lg"></i>
                            </button>
                        </div>

                        <!-- <button
                class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 px-3 py-1.5 text-base dark:border-zinc-600">
                All locations <i class="bx bx-chevron-down ml-1 text-lg"></i>
              </button> -->

                        <div class="ml-auto hidden items-center gap-2 sm:flex">
                            <!-- <button
                class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 px-3 py-1.5 text-base dark:border-zinc-600">
                <i class="bx bx-slider-alt mr-1"></i> Filters
              </button> -->

                            <button
                                class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 px-3 py-1.5 text-base dark:border-zinc-600"
                                @click="openManageOrder"
                            >
                                <i class="bx bx-sort mr-1"></i> Manage order
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="gap-6 p-5 md:grid md:grid-cols-1 lg:grid lg:grid-cols-[260px_minmax(0,3fr)]"
                >
                    <aside
                        class="mb-4 self-start rounded-xl border border-zinc-200 dark:border-zinc-700/60"
                    >
                        <div class="p-4">
                            <h6
                                class="mb-3 text-lg font-semibold text-zinc-700 dark:text-zinc-300"
                            >
                                Categories
                            </h6>

                            <!-- Horizontal scroll for iPad -->
                            <div class="block lg:hidden">
                                <div
                                    class="scrollbar-thin scrollbar-thumb-zinc-300 scrollbar-track-transparent dark:scrollbar-thumb-zinc-600 overflow-x-auto pb-2"
                                >
                                    <div class="flex min-w-max space-x-2">
                                        <!-- All Categories -->
                                        <button
                                            class="flex min-w-[140px] shrink-0 cursor-pointer items-center justify-between rounded-lg border px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            :class="{
                                                'border-zinc-300 bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800':
                                                    selectedCategory === 'all',
                                                'border-zinc-200 dark:border-zinc-700/60':
                                                    selectedCategory !== 'all',
                                            }"
                                            @click="selectCategory('all')"
                                        >
                                            <span class="truncate text-sm"
                                                >All categories</span
                                            >
                                            <span
                                                class="ml-2 shrink-0 text-sm text-zinc-500"
                                                >{{ totalCount }}</span
                                            >
                                        </button>

                                        <!-- Category Buttons -->
                                        <button
                                            v-for="c in categoriesSorted"
                                            :key="c.id"
                                            class="flex min-w-[140px] shrink-0 cursor-pointer items-center justify-between rounded-lg border px-4 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            :class="{
                                                'border-zinc-300 bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800':
                                                    selectedCategory === c.id,
                                                'border-zinc-200 dark:border-zinc-700/60':
                                                    selectedCategory !== c.id,
                                            }"
                                            @click="selectCategory(c.id)"
                                        >
                                            <span class="truncate text-sm">{{
                                                c.name
                                            }}</span>
                                            <span
                                                class="ml-2 shrink-0 text-sm text-zinc-500"
                                                >{{
                                                    c.service_count || 0
                                                }}</span
                                            >
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vertical list for desktop -->
                            <div class="hidden lg:block">
                                <ul class="space-y-1">
                                    <li>
                                        <button
                                            class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            :class="{
                                                'bg-zinc-50 dark:bg-zinc-800':
                                                    selectedCategory === 'all',
                                            }"
                                            @click="selectCategory('all')"
                                        >
                                            <span>All categories</span>
                                            <span class="text-zinc-500">{{
                                                totalCount
                                            }}</span>
                                        </button>
                                    </li>

                                    <li
                                        v-for="c in categoriesSorted"
                                        :key="c.id"
                                    >
                                        <button
                                            class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                            :class="{
                                                'bg-zinc-50 dark:bg-zinc-800':
                                                    selectedCategory === c.id,
                                            }"
                                            @click="selectCategory(c.id)"
                                        >
                                            <span class="truncate">{{
                                                c.name
                                            }}</span>
                                            <span class="text-zinc-500">{{
                                                c.service_count || 0
                                            }}</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <button
                                class="mt-3 cursor-pointer text-base text-rose-600 transition-colors hover:underline dark:text-rose-400"
                                @click="openCategoryModal()"
                            >
                                Add category
                            </button>
                        </div>
                    </aside>

                    <section
                        class="w-full space-y-6 px-1 sm:px-2 md:space-y-4 lg:px-0"
                    >
                    <div v-if="!q.trim() || visibleCategoryBlocks.length > 0">
                        <div
                            v-for="cat in visibleCategoryBlocks"
                            :key="cat.key"
                        >
                            <div class="m-2 flex items-center justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    <h5
                                        class="min-w-0 cursor-pointer truncate text-xl font-semibold text-zinc-900 hover:underline md:text-lg dark:text-zinc-100"
                                        @click="openCategoryModal(cat)"
                                    >
                                        {{ cat.title }}
                                    </h5>
                                    <span
                                        v-if="cat.status === 'inactive'"
                                        class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800"
                                    >
                                        Inactive
                                    </span>
                                </div>

                                <div
                                    class="relative shrink-0 pl-2"
                                    :ref="(el) => setActionRef(cat.key, el)"
                                >
                                    <button
                                        class="inline-flex cursor-pointer items-center rounded-full border border-zinc-300 px-3 py-1.5 text-base dark:border-zinc-600"
                                        @click="toggleActions(cat.key)"
                                    >
                                        Actions
                                        <i
                                            class="bx bx-chevron-down ml-1 text-lg"
                                        ></i>
                                    </button>
                                    <Transition
                                        enter-active-class="transition ease-out duration-300"
                                        enter-from-class="opacity-0 scale-95"
                                        enter-to-class="opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-200"
                                        leave-from-class="opacity-100 scale-100"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div
                                            v-if="actionsOpenKey === cat.key"
                                            class="absolute right-0 z-20 mt-2 w-60 origin-top-right overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900"
                                        >
                                            <button
                                                class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                @click="openCategoryModal(cat)"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                v-if="cat.status === 'active'"
                                                class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                @click="
                                                    goCreateService(cat.key)
                                                "
                                            >
                                                Add service
                                            </button>

                                            <button
                                                class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                @click="
                                                    toggleCategoryActive(cat)
                                                "
                                            >
                                                <span
                                                    v-if="
                                                        cat.status === 'active'
                                                    "
                                                    >Deactivate category</span
                                                >
                                                <span v-else
                                                    >Activate category</span
                                                >
                                            </button>

                                            <div
                                                class="mx-3 my-1 border-t border-zinc-200 dark:border-zinc-700/60"
                                            ></div>
                                            <button
                                                class="w-full cursor-pointer px-4 py-2 text-left text-base text-rose-600 hover:bg-rose-50 dark:hover:bg-zinc-800"
                                                @click="openDeleteCategory(cat)"
                                            >
                                                Permanently delete
                                            </button>
                                        </div>
                                    </Transition>
                                </div>
                            </div>

                            <div
                                v-if="cat.items.length === 0"
                                class="rounded-xl border border-dashed border-zinc-300 p-6 text-base text-zinc-500 md:p-4 md:text-sm dark:border-zinc-700/60"
                            >
                                No services
                            </div>

                            <div v-else class="space-y-3 md:space-y-2">
                                <article
                                    v-for="s in cat.items"
                                    :key="s.id"
                                    class="flex cursor-pointer items-center justify-between rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition-colors hover:shadow-md md:p-3 dark:border-zinc-700/60 dark:bg-zinc-900"
                                    :style="
                                        cat.color
                                            ? {
                                                  borderLeft: `4px solid ${cat.color}`,
                                              }
                                            : {}
                                    "
                                    @click="editService(s.id)"
                                >
                                    <div class="min-w-0 flex-1">
                                        <h6
                                            class="truncate text-lg font-semibold text-zinc-900 dark:text-zinc-100"
                                        >
                                            {{ s.name }}
                                            <span
                                                v-if="s.status === 'inactive'"
                                                class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800"
                                            >
                                                Inactive
                                            </span>
                                        </h6>

                                        <p
                                            class="truncate text-base text-zinc-500"
                                        >
                                            <span>{{
                                                minutesToLabel(
                                                    s.duration_minutes,
                                                )
                                            }}</span>
                                            <span v-if="s.description">
                                                ·
                                                {{
                                                    truncateDescription(
                                                        s.description,
                                                    )
                                                }}</span
                                            >
                                        </p>
                                    </div>
                                    <div class="ml-4 flex items-center gap-2">
                                        <span
                                            class="text-base font-medium whitespace-nowrap text-zinc-700 dark:text-zinc-300"
                                        >
                                            <template
                                                v-if="s.price_type === 'from'"
                                                >from
                                            </template>
                                            LKR {{ numberFmt(s.price) }}
                                        </span>

                                        <div
                                            class="relative"
                                            :ref="
                                                (el) => setRowMenuRef(s.id, el)
                                            "
                                        >
                                            <button
                                                class="cursor-pointer rounded-full p-1 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                                @click.stop="
                                                    toggleRowMenu(s.id)
                                                "
                                            >
                                                <i
                                                    class="bx bx-dots-vertical-rounded text-2xl md:text-xl"
                                                ></i>
                                            </button>
                                            <Transition
                                                enter-active-class="transition ease-out duration-300"
                                                enter-from-class="opacity-0 translate-y-1"
                                                enter-to-class="opacity-100 translate-y-0"
                                                leave-active-class="transition ease-in duration-200"
                                                leave-from-class="opacity-100 translate-y-0"
                                                leave-to-class="opacity-0 translate-y-1"
                                            >
                                                <div
                                                    v-if="
                                                        rowMenuOpenId === s.id
                                                    "
                                                    class="absolute right-0 z-20 mt-2 w-52 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900"
                                                >
                                                    <button
                                                        class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        @click.stop="
                                                            editService(s.id)
                                                        "
                                                    >
                                                        Edit
                                                    </button>

                                                    <button
                                                        class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        @click.stop="
                                                            toggleServiceActive(
                                                                s,
                                                            )
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                s.status ===
                                                                'active'
                                                            "
                                                            >Deactivate</span
                                                        >
                                                        <span v-else
                                                            >Activate</span
                                                        >
                                                    </button>

                                                    <div
                                                        class="border-t border-zinc-200 dark:border-zinc-700/60"
                                                    ></div>
                                                    <button
                                                        class="w-full cursor-pointer px-4 py-2 text-left text-base text-rose-600 hover:bg-rose-50 dark:hover:bg-zinc-800"
                                                        @click.stop="
                                                            openDeleteService(s)
                                                        "
                                                    >
                                                        Permanently delete
                                                    </button>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                        </div>

                            <div v-else-if="q.trim() && visibleCategoryBlocks.length === 0" class="py-8 text-center">
        <div class="rounded-xl border border-dashed border-zinc-300 p-8">
            <i class="bx bx-search text-4xl text-zinc-400 mb-3"></i>
            <h4 class="text-lg font-semibold text-zinc-700 mb-2">
                No services found
            </h4>
            <p class="text-zinc-500">
                No services match "{{ q }}"
            </p>
        </div>
    </div>
                    </section>
                </div>
            </div>
        </div>
        <Transition name="overlay-fade">
            <div
                v-if="showCategory"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="closeCategoryModal"
            >
                <Transition name="slide-down-slow">
                    <div
                        v-if="showCategory"
                        class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700/60"
                        >
                            <h5 class="text-xl font-semibold md:text-2xl">
                                {{
                                    isEditingCategory
                                        ? 'Edit category'
                                        : 'Add category'
                                }}
                            </h5>
                            <button
                                type="button"
                                class="cursor-pointer rounded-full p-1.5 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeCategoryModal"
                            >
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <form
                            @submit.prevent="submitCategory"
                            class="p-6 md:p-8"
                        >
                            <div
                                class="grid grid-cols-1 gap-6 md:grid-cols-[180px_1fr]"
                            >
                                <!-- Left: image picker -->
                                <div class="flex flex-col items-start">
                                    <label
                                        class="mb-2 block text-base font-medium"
                                        >Category image</label
                                    >
                                    <FileInputComponent
                                        :key="
                                            isEditingCategory
                                                ? `cat-${editingCategoryId}-${catImageInitial}`
                                                : 'cat-new'
                                        "
                                        id="category_image"
                                        v-model="categoryForm.image"
                                        :initial-urls="
                                            catImageInitial
                                                ? [catImageInitial]
                                                : []
                                        "
                                        :accept="'image/*'"
                                        :maxSizeMB="10"
                                        :parentCls="'mb-2'"
                                    />
                                    <p class="mt-1 text-xs text-zinc-500">
                                        JPG/PNG/WebP up to 10MB.
                                    </p>
                                </div>

                                <!-- Right: fields -->
                                <div class="space-y-4">
                                    <Input
                                        id="cat_name"
                                        label="Category name"
                                        v-model="categoryForm.name"
                                        :is-required="true"
                                        class="text-base"
                                    />

                                    <div class="relative" ref="colorRef">
                                        <label
                                            class="mb-1 block text-base font-medium"
                                            >Appointment color</label
                                        >

                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between rounded-md border border-zinc-300 bg-white px-3 py-2 text-base hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                                            @click="toggleColorMenu"
                                        >
                                            <span
                                                class="inline-flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-4 w-4 rounded-full border border-zinc-300"
                                                    :style="{
                                                        backgroundColor:
                                                            selectedColor.value,
                                                    }"
                                                ></span>
                                                <span>{{
                                                    selectedColor.label
                                                }}</span>
                                            </span>
                                            <i
                                                class="bx bx-chevron-down text-xl text-zinc-500"
                                            ></i>
                                        </button>

                                        <div
                                            v-if="colorMenuOpen"
                                            class="absolute z-[80] mt-2 max-h-80 w-full overflow-auto rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                        >
                                            <button
                                                v-for="opt in colorOptions"
                                                :key="opt.value"
                                                type="button"
                                                class="flex w-full cursor-pointer items-center justify-between px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                @click="chooseColor(opt.value)"
                                            >
                                                <span
                                                    class="inline-flex items-center gap-2"
                                                >
                                                    <span
                                                        class="h-4 w-4 rounded-full border border-zinc-300"
                                                        :style="{
                                                            backgroundColor:
                                                                opt.value,
                                                        }"
                                                    ></span>
                                                    <span>{{ opt.label }}</span>
                                                </span>
                                                <span
                                                    v-if="
                                                        categoryForm.color_code ===
                                                        opt.value
                                                    "
                                                    class="text-lg text-indigo-600"
                                                >
                                                    ✓
                                                </span>
                                            </button>
                                        </div>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            This color will be used for
                                            appointments in this category.
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            class="mb-1 block text-base font-medium"
                                            >Description</label
                                        >
                                        <textarea
                                            v-model="categoryForm.description"
                                            rows="4"
                                            maxlength="1000"
                                            class="w-full rounded-md border border-zinc-300 px-3 py-2 text-base dark:border-zinc-700 dark:bg-zinc-800"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="mt-6 flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="btn-secondary cursor-pointer text-base"
                                    @click="closeCategoryModal"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="btn-primary cursor-pointer text-base"
                                >
                                    {{ isEditingCategory ? 'Save' : 'Add' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>

        <Transition name="overlay-fade">
            <div v-if="manageOpen" class="fixed inset-0 z-50 bg-black/60">
                <div
                    class="flex min-h-full items-start justify-center overflow-y-auto"
                >
                    <div
                        class="mx-auto my-8 w-[1100px] max-w-[calc(100vw-40px)] overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
                    >
                        <div
                            class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-200 bg-white/90 p-5 backdrop-blur dark:border-zinc-700/60 dark:bg-zinc-900/90"
                        >
                            <h3 class="text-2xl font-semibold">
                                Set menu order
                            </h3>
                            <div class="flex gap-2">
                                <button
                                    class="btn-secondary cursor-pointer text-base"
                                    @click="closeManage"
                                >
                                    Close
                                </button>
                                <button
                                    class="btn-primary cursor-pointer text-base"
                                    :disabled="savingOrder"
                                    @click="saveManage"
                                >
                                    Save
                                </button>
                            </div>
                        </div>

                        <div class="max-h-[74vh] overflow-auto p-6">
                            <p class="mb-4 text-base text-zinc-600">
                                Drag categories and services to change the
                                order. You can move services between categories.
                            </p>

                            <Draggable
                                v-model="orderCats"
                                item-key="cid"
                                handle=".cat-header"
                                :animation="220"
                                :easing="'cubic-bezier(0.22, 1, 0.36, 1)'"
                                :ghost-class="'drag-ghost'"
                                :chosen-class="'drag-chosen'"
                                :drag-class="'dragging'"
                                :swap-threshold="0.65"
                                :fallback-on-body="true"
                            >
                                <template #item="{ element }">
                                    <div
                                        class="mb-6 overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700/60 dark:bg-zinc-900"
                                    >
                                        <div
                                            class="cat-header flex cursor-grab items-center gap-3 border-b border-zinc-200 px-4 py-3 active:cursor-grabbing dark:border-zinc-700/60"
                                        >
                                            <div
                                                class="h-6 w-1.5 rounded-sm"
                                                :style="{
                                                    backgroundColor:
                                                        element.color ||
                                                        '#e5e7eb',
                                                }"
                                            ></div>
                                            <i
                                                class="bx bx-dots-vertical-rounded text-2xl text-zinc-400"
                                            ></i>
                                            <div
                                                class="truncate text-lg font-semibold"
                                            >
                                                {{ element.title }}
                                            </div>
                                        </div>

                                        <Draggable
                                            v-model="element.services"
                                            group="services"
                                            item-key="id"
                                            class="space-y-2 p-3"
                                            handle=".svc-row"
                                            :animation="220"
                                            :easing="'cubic-bezier(0.22, 1, 0.36, 1)'"
                                            :ghost-class="'drag-ghost'"
                                            :chosen-class="'drag-chosen'"
                                            :drag-class="'dragging'"
                                            :swap-threshold="0.65"
                                            :fallback-on-body="true"
                                        >
                                            <template
                                                #item="{ element: s, index }"
                                            >
                                                <div
                                                    class="svc-row user-select-none flex cursor-grab items-center justify-between rounded-lg border border-zinc-200 bg-white px-3 py-2 active:cursor-grabbing dark:border-zinc-700/60 dark:bg-zinc-900"
                                                >
                                                    <div
                                                        class="flex min-w-0 items-center gap-2"
                                                    >
                                                        <i
                                                            class="bx bx-dots-vertical-rounded cursor-move text-2xl text-zinc-400"
                                                        ></i>
                                                        <div class="truncate">
                                                            <div
                                                                class="truncate font-medium"
                                                            >
                                                                {{ s.name }}
                                                            </div>
                                                            <div
                                                                class="text-sm text-zinc-500"
                                                            >
                                                                {{
                                                                    minutesToLabel(
                                                                        s.duration_minutes,
                                                                    )
                                                                }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="text-sm whitespace-nowrap text-zinc-700 dark:text-zinc-300"
                                                    >
                                                        <template
                                                            v-if="
                                                                s.price_type ===
                                                                'from'
                                                            "
                                                            >from </template
                                                        >LKR
                                                        {{ numberFmt(s.price) }}
                                                    </div>
                                                </div>
                                            </template>
                                        </Draggable>
                                    </div>
                                </template>
                            </Draggable>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
        <Transition name="overlay-fade">
            <div
                v-if="deleteOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
                @click.self="closeDelete"
            >
                <Transition name="modal-pop" appear>
                    <div
                        v-show="deleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <h4
                                class="text-xl font-semibold text-zinc-900 dark:text-zinc-100"
                            >
                                Permanently delete category
                            </h4>
                            <button
                                class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDelete"
                            >
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p
                            class="mb-6 text-base text-zinc-600 dark:text-zinc-300"
                        >
                            <strong>{{ deleteMeta.count }}</strong>
                            {{
                                deleteMeta.count === 1 ? 'service' : 'services'
                            }}
                            in this category will also be permanently
                            deleted.<br />
                            This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button
                                class="btn-secondary cursor-pointer text-base"
                                @click="closeDelete"
                            >
                                Cancel
                            </button>
                            <button
                                class="btn-primary cursor-pointer bg-rose-600 text-base text-white hover:bg-rose-700"
                                :disabled="deleting"
                                @click="confirmDeleteAll"
                            >
                                {{ deleting ? 'Deleting…' : 'Delete all' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
        <Transition name="overlay-fade">
            <div
                v-if="serviceDeleteOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
                @click.self="closeDeleteService"
            >
                <Transition name="modal-pop" appear>
                    <div
                        v-show="serviceDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <h4
                                class="text-xl font-semibold text-zinc-900 dark:text-zinc-100"
                            >
                                Permanently delete service
                            </h4>
                            <button
                                class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDeleteService"
                            >
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p
                            class="mb-2 text-base font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            {{ serviceDeleteMeta.name }}
                        </p>
                        <p
                            class="mb-6 text-base text-zinc-600 dark:text-zinc-300"
                        >
                            This service and its variants (if any) will be
                            permanently deleted. This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button
                                class="btn-secondary cursor-pointer text-base"
                                @click="closeDeleteService"
                            >
                                Cancel
                            </button>
                            <button
                                class="btn-primary cursor-pointer bg-rose-600 text-base text-white hover:bg-rose-700"
                                :disabled="deletingService"
                                @click="confirmDeleteService"
                            >
                                {{ deletingService ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </AppLayout>
</template>

<script>
import FileInputComponent from '@/components/FileInputComponent.vue';
import Input from '@/components/InputField.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Draggable from 'vuedraggable';
import { route } from 'ziggy-js';
import * as XLSX from 'xlsx';

const byOrder = (a, b) => {
    const ao = Number(a?.sort_order ?? 0);
    const bo = Number(b?.sort_order ?? 0);
    if (ao !== bo) return ao - bo;
    return String(a?.name || '').localeCompare(String(b?.name || ''));
};

export default {
    components: { AppLayout, Input, Head, Link, Draggable, FileInputComponent },
    props: {
        categories: { type: Array, default: () => [] },
        grouped: { type: Object, default: () => ({}) },
        uncategorizedCount: { type: Number, default: 0 },
        permission: { type: Object, default: () => ({}) },
    },

    data() {
        return {
            breadcrumbs: [{ title: 'Services', href: route('service.index') }],
            categoriesLocal: this.categories?.slice?.() || [],
            groupedLocal: { ...(this.grouped || {}) },
            uncatCountLocal: this.uncategorizedCount || 0,

            q: '',
            selectedCategory: 'all',
            optionsOpen: false,
            addOpen: false,
            actionsOpenKey: null,
            rowMenuOpenId: null,
            actionRefs: new Map(),
            rowMenuRefs: new Map(),

            // Mobile specific states
            mobileOptionsOpen: false,
            mobileAddOpen: false,

            manageOpen: false,
            orderCats: [],
            savingOrder: false,

            showCategory: false,
            isEditingCategory: false,
            editingCategoryId: null,
            categoryForm: {
                name: '',
                color_code: '#60a5fa',
                description: '',
                image: null,
            },
            catImageInitial: '',
            deleteOpen: false,
            deleteMeta: { id: null, title: '', count: 0 },
            deleting: false,
            serviceDeleteOpen: false,
            serviceDeleteMeta: { id: null, name: '' },
            deletingService: false,
            categoryForm: {
                name: '',
                color_code: '#60a5fa',
                description: '',
                image: null,
            },
            catImageInitial: '',
            colorMenuOpen: false,
            colorOptions: [
                { label: 'Blue', value: '#93c5fd' },
                { label: 'Dark Blue', value: '#60a5fa' },
                { label: 'Jordy Blue', value: '#7aa8ff' },
                { label: 'Indigo', value: '#818cf8' },
                { label: 'Lavender', value: '#c4b5fd' },
                { label: 'Purple', value: '#a78bfa' },
                { label: 'Wisteria', value: '#bfa6ff' },
                { label: 'Pink', value: '#f9a8d4' },
                { label: 'Coral', value: '#fda4af' },
                { label: 'Blood Orange', value: '#fb923c' },
                { label: 'Orange', value: '#fbbf24' },
                { label: 'Amber', value: '#fcd34d' },
                { label: 'Yellow', value: '#fef08a' },
                { label: 'Lime', value: '#bef264' },
                { label: 'Green', value: '#86efac' },
                { label: 'Teal', value: '#5eead4' },
                { label: 'Cyan', value: '#67e8f9' },
            ],

            deleteOpen: false,
        };
    },
    computed: {
        selectedColor() {
            const hex = String(
                this.categoryForm?.color_code || '',
            ).toLowerCase();
            const found = this.colorOptions.find(
                (c) => c.value.toLowerCase() === hex,
            );
            return found || this.colorOptions[0];
        },
        categoriesSorted() {
            return (this.categoriesLocal || []).slice().sort(byOrder);
        },
        totalCount() {
            const catSum = (this.categoriesLocal || []).reduce(
                (a, c) => a + (c.service_count || 0),
                0,
            );
            return catSum + (this.uncatCountLocal || 0);
        },
        visibleCategoryBlocks() {
            const blocks = [];

            const pushBlock = (key, title, desc, color, status, list) => {
                let items = Array.isArray(list)
                    ? list.slice().sort(byOrder)
                    : [];

                if (this.q && this.q.trim()) {
                    const s = this.q.trim().toLowerCase();
                    items = items.filter((x) =>
                        (x.name || '').toLowerCase().includes(s),
                    );
                

                // blocks.push({
                //     key,
                //     title,
                //     description: desc || null,
                //     color: color || null,
                //     status: status || 'active',
                //     items,
                // });

            if (items.length > 0 || !this.q.trim()) {
            blocks.push({
                key,
                title,
                description: desc || null,
                color: color || null,
                status: status || 'active',
                items,
            });
        }
                } else {
            // When no search, always add the block even if empty
            blocks.push({
                key,
                title,
                description: desc || null,
                color: color || null,
                status: status || 'active',
                items,
            });
        }
            };

            if (this.selectedCategory === 'all') {
                this.categoriesSorted.forEach((c) => {
                    pushBlock(
                        String(c.id),
                        c.name,
                        c.description,
                        c.color_code,
                        c.status,
                        this.servicesByCat(c.id),
                    );
                });

                        const unc = this.servicesByCat('uncategorized');
        if (unc.length || !this.q.trim()) {
                    pushBlock(
                        'uncategorized',
                        'Uncategorized',
                        null,
                        null,
                        'active',
                        unc,
                    );
                }
            } else {
                const c = this.categoriesSorted.find(
                    (x) => String(x.id) === String(this.selectedCategory),
                );

                if (c) {
                    pushBlock(
                        String(c.id),
                        c.name,
                        c.description,
                        c.color_code,
                        c.status,
                        this.servicesByCat(c.id),
                    );
                }

                if (!c && String(this.selectedCategory) === '0') {
                    pushBlock(
                        'uncategorized',
                        'Uncategorized',
                        null,
                        null,
                        'active',
                        this.servicesByCat('uncategorized'),
                    );
                }
            }

            return blocks;
        },
    },
    watch: {
        categories: {
            immediate: true,
            handler(v) {
                this.categoriesLocal = v?.slice?.() || [];
            },
        },
        grouped: {
            immediate: true,
            handler(v) {
                this.groupedLocal = { ...(v || {}) };
            },
        },
        uncategorizedCount: {
            immediate: true,
            handler(v) {
                this.uncatCountLocal = v || 0;
            },
        },
    },
    mounted() {
        window.addEventListener('click', this.onGlobalClick);
        window.addEventListener('keydown', this.onEsc);
    },
    beforeUnmount() {
        window.removeEventListener('click', this.onGlobalClick);
        window.removeEventListener('keydown', this.onEsc);
    },
    methods: {
        toggleServiceActive(s) {
            const id = Number(s.id);
            if (!Number.isFinite(id)) {
                this.$root?.showMessage?.('This service cannot be updated.');
                return;
            }

            this.rowMenuOpenId = null;

            router.put(
                route('service.status.update'),
                { id, status: s.status || 'inactive' },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.(
                            s.status === 'active'
                                ? 'Service deactivated.'
                                : 'Service activated.',
                        );
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to update service.';
                        this.$root?.showMessage?.(first);
                    },
                },
            );
        },

        toggleCategoryActive(cat) {
            if (String(cat.key) === 'uncategorized') {
                this.$root?.showMessage?.(
                    '“Uncategorized” cannot be deactivated.',
                );
                return;
            }

            const id = Number(cat.key);
            if (!Number.isFinite(id)) {
                this.$root?.showMessage?.('This category cannot be updated.');
                return;
            }

            this.actionsOpenKey = null;

            router.put(
                route('service.category.status.update'),
                { id, status: cat.status || 'inactive' },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.(
                            cat.status === 'active'
                                ? 'Category deactivated.'
                                : 'Category activated.',
                        );
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to update category.';
                        this.$root?.showMessage?.(first);
                    },
                },
            );
        },

        exportPdf() {
            const rows = this.buildExportRows();
            if (!rows.length) {
                this.$root?.showMessage?.('There is nothing to export.');
                return;
            }

            const escapeHtml = (str) =>
                String(str ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

            const tableRows = rows
                .map(
                    (r) => `
      <tr>
        <td>${escapeHtml(r.Category)}</td>
        <td>${escapeHtml(r.Name)}</td>
        <td>${escapeHtml(r.Duration)}</td>
        <td>${escapeHtml(r['Price type'])}</td>
        <td style="text-align:right;">LKR ${this.numberFmt(r.Price)}</td>
      </tr>
    `,
                )
                .join('');

            const html = `
      <!doctype html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>Service menu</title>
          <style>
            body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; padding: 24px; }
            h1 { font-size: 20px; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            th, td { border: 1px solid #ddd; padding: 6px 8px; }
            th { background: #f4f4f5; text-align: left; }
          </style>
        </head>
        <body>
          <h1>Service menu</h1>
          <table>
            <thead>
              <tr>
                <th>Category</th>
                <th>Name</th>
                <th>Duration</th>
                <th>Price type</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              ${tableRows}
            </tbody>
          </table>
        </body>
      </html>
    `;

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                this.$root?.showMessage?.(
                    'Unable to open print window (popup blocked).',
                );
                return;
            }

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
            }, 300);
        },
        buildExportRows() {
            const rows = [];

            const pushServices = (categoryLabel, services) => {
                services.forEach((s) => {
                    rows.push({
                        Category: categoryLabel,
                        Name: s.name || '',
                        Duration: this.minutesToLabel(s.duration_minutes),
                        'Price type':
                            s.price_type === 'from' ? 'From' : 'Fixed',
                        Price: Number(s.price ?? 0),
                    });
                });
            };

            // Categorised
            this.categoriesSorted.forEach((c) => {
                pushServices(c.name, this.servicesByCat(c.id));
            });

            // Uncategorized
            const unc = this.servicesByCat('uncategorized');
            if (unc.length) {
                pushServices('Uncategorized', unc);
            }

            return rows;
        },

        downloadFile({ content, mime, filename }) {
            const blob = new Blob([content], { type: mime });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
        exportCsv() {
            const rows = this.buildExportRows();
            if (!rows.length) {
                this.$root?.showMessage?.('There is nothing to export.');
                return;
            }

            const headers = Object.keys(rows[0]);

            const escapeCsv = (value) => {
                const str = String(value ?? '');
                const escaped = str.replace(/"/g, '""');
                return `"${escaped}"`;
            };

            const lines = [
                headers.join(','), // header row
                ...rows.map((row) =>
                    headers.map((h) => escapeCsv(row[h])).join(','),
                ),
            ];

            const csvContent = lines.join('\r\n');

            this.downloadFile({
                content: csvContent,
                mime: 'text/csv;charset=utf-8;',
                filename: 'services.csv',
            });
        },

exportExcel() {
    const rows = this.buildExportRows();
    if (!rows.length) {
        this.$root?.showMessage?.('There is nothing to export.');
        return;
    }

    const ws = XLSX.utils.json_to_sheet(rows);

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Services');

    const wbout = XLSX.write(wb, {
        bookType: 'xlsx',
        type: 'array',
    });

    const blob = new Blob(
        [wbout],
        {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        },
    );
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'services.xlsx';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
},


        toggleMobileOptions() {
            this.mobileOptionsOpen = !this.mobileOptionsOpen;
            if (this.mobileOptionsOpen) {
                this.mobileAddOpen = false;
            }
        },

        toggleMobileAdd() {
            this.mobileAddOpen = !this.mobileAddOpen;
            if (this.mobileAddOpen) {
                this.mobileOptionsOpen = false;
            }
        },

        openFilters() {
            console.log('Open filters on mobile');
        },

        onGlobalClick(e) {
            const t = e.target;

            // Desktop menus
            if (this.$refs.optionsRef && !this.$refs.optionsRef.contains(t))
                this.optionsOpen = false;
            if (this.$refs.addRef && !this.$refs.addRef.contains(t))
                this.addOpen = false;

            // Mobile menus
            if (
                this.$refs.mobileOptionsRef &&
                !this.$refs.mobileOptionsRef.contains(t)
            )
                this.mobileOptionsOpen = false;
            if (this.$refs.mobileAddRef && !this.$refs.mobileAddRef.contains(t))
                this.mobileAddOpen = false;

            if (this.actionsOpenKey !== null) {
                const holder = this.actionRefs.get(String(this.actionsOpenKey));
                if (holder && !holder.contains(t)) this.actionsOpenKey = null;
            }
            if (this.rowMenuOpenId !== null) {
                const holder2 = this.rowMenuRefs.get(
                    Number(this.rowMenuOpenId),
                );
                if (holder2 && !holder2.contains(t)) this.rowMenuOpenId = null;
            }
            if (this.$refs.colorRef && !this.$refs.colorRef.contains(t)) {
                this.colorMenuOpen = false;
            }
        },

        truncateDescription(text, limit = 10) {
            if (!text) return '';
            const str = String(text).trim();
            if (str.length <= limit) return str;
            return str.slice(0, limit) + '...';
        },

        minutesToLabel(m) {
            if (!m) return '—';
            const h = Math.floor(m / 60),
                r = m % 60;
            return h ? `${h}h${r ? ` ${r}min` : ''}` : `${m}min`;
        },

        onEsc(e) {
            if (e.key === 'Escape') {
                // Desktop menus
                this.optionsOpen = false;
                this.addOpen = false;

                // Mobile menus
                this.mobileOptionsOpen = false;
                this.mobileAddOpen = false;

                this.actionsOpenKey = null;
                this.rowMenuOpenId = null;
                this.manageOpen = false;
                this.deleteOpen = false;
                this.serviceDeleteOpen = false;
                this.colorMenuOpen = false;
            }
        },
        toggleColorMenu() {
            this.colorMenuOpen = !this.colorMenuOpen;
        },
        chooseColor(value) {
            this.categoryForm.color_code = value;
            this.colorMenuOpen = false;
        },
        openDeleteService(s) {
            this.rowMenuOpenId = null;
            this.serviceDeleteMeta = {
                id: Number(s.id),
                name: s.name || 'Untitled service',
            };
            if (!Number.isFinite(this.serviceDeleteMeta.id)) {
                this.$root?.showMessage?.('This service cannot be deleted.');
                return;
            }
            this.serviceDeleteOpen = true;
        },

        closeDeleteService() {
            this.serviceDeleteOpen = false;
            this.deletingService = false;
        },

        confirmDeleteService() {
            const id = this.serviceDeleteMeta.id;
            if (!Number.isFinite(id)) return;
            this.deletingService = true;

            router.post(
                route('service.delete'),
                { id },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeDeleteService();
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.(
                            'Service was permanently removed.',
                        );
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to delete service.';
                        this.$root?.showMessage?.(first);
                    },
                    onFinish: () => {
                        this.deletingService = false;
                    },
                },
            );
        },

        openDeleteCategory(cat) {
            if (String(cat.key) === 'uncategorized') {
                this.$root?.showMessage?.('“Uncategorized” cannot be deleted.');
                return;
            }
            const found = (this.categoriesLocal || []).find(
                (x) => String(x.id) === String(cat.key),
            );
            const count =
                found?.service_count ??
                (this.servicesByCat(cat.key)?.length || 0);

            this.actionsOpenKey = null;
            this.deleteMeta = {
                id: Number(cat.key),
                title: found?.name || String(cat.title || ''),
                count: Number(count || 0),
            };
            this.deleteOpen = true;
        },

        closeDelete() {
            this.deleteOpen = false;
            this.deleting = false;
        },

        confirmDeleteAll() {
            if (!Number.isFinite(this.deleteMeta.id)) return;
            this.deleting = true;

            router.post(
                route('service.category.destroy'),
                {
                    id: this.deleteMeta.id,
                    delete_all: true,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeDelete();
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.(
                            'Category and its services were removed.',
                        );
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to delete category.';
                        this.$root?.showMessage?.(first);
                    },
                    onFinish: () => {
                        this.deleting = false;
                    },
                },
            );
        },
        async deleteCategory(key) {
            const id = Number(key);
            if (!Number.isFinite(id)) {
                this.$root?.showMessage?.('This category cannot be deleted.');
                return;
            }

            if (
                !confirm(
                    'Delete this category? Services in it will be moved to “Uncategorized”.',
                )
            ) {
                return;
            }

            this.actionsOpenKey = null;

            router.post(
                route('service.category.destroy'),
                { id },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.('Category removed.');
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to delete category.';
                        this.$root?.showMessage?.(first);
                    },
                },
            );
        },
        openCategoryModal(cat = null) {
            this.isEditingCategory = !!cat;
            this.editingCategoryId = cat ? cat.key : null;

            if (cat) {
                const found = (this.categoriesLocal || []).find(
                    (x) => String(x.id) === String(cat.key),
                );
                this.categoryForm = {
                    name: found?.name || '',
                    color_code: found?.color_code || '#60a5fa',
                    description: found?.description || '',
                    image: null,
                };
                this.catImageInitial = found?.image_url || '';
            } else {
                this.categoryForm = {
                    name: '',
                    color_code: '#60a5fa',
                    description: '',
                    image: null,
                };
                this.catImageInitial = '';
            }

            // normalize color to palette
            const hex = String(
                this.categoryForm.color_code || '',
            ).toLowerCase();
            const inPalette = this.colorOptions.some(
                (c) => c.value.toLowerCase() === hex,
            );
            if (!inPalette) {
                this.categoryForm.color_code = this.colorOptions[0].value;
            }

            this.colorMenuOpen = false;
            this.showCategory = true;
        },

        closeCategoryModal() {
            this.showCategory = false;
            this.isEditingCategory = false;
            this.editingCategoryId = null;
            this.colorMenuOpen = false;
        },

        submitCategory() {
            const name = this.categoryForm?.name?.trim?.() || '';
            if (!name) {
                this.$root?.showMessage?.('Category name is required.');
                return;
            }

            const isEdit = !!(
                this.isEditingCategory && this.editingCategoryId != null
            );
            const routeName = isEdit
                ? 'service.category.update'
                : 'service.category.store';

            const fd = new FormData();
            if (isEdit) fd.append('id', this.editingCategoryId);
            fd.append('name', name);
            fd.append('color_code', this.categoryForm.color_code || '');
            fd.append('description', this.categoryForm.description || '');

            const img = this.categoryForm.image;
            if (img) {
                if (img instanceof File) {
                    fd.append('image', img);
                } else if (img?.file instanceof File) {
                    fd.append('image', img.file);
                } else if (Array.isArray(img) && img[0] instanceof File) {
                    fd.append('image', img[0]);
                }
            }

            router.post(route(routeName), fd, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.closeCategoryModal();
                    this.categoryForm = {
                        name: '',
                        color_code: '#60a5fa',
                        description: '',
                        image: null,
                    };
                    this.catImageInitial = '';
                    router.reload({
                        only: ['categories', 'grouped', 'uncategorizedCount'],
                        preserveScroll: true,
                    });
                    this.$root?.showMessage?.(
                        isEdit
                            ? 'Category updated successfully!'
                            : 'Category created successfully!',
                    );
                },
                onError: (err) => {
                    const first =
                        (err && Object.values(err)[0]) ||
                        'Something went wrong!';
                    this.$root?.showMessage?.(first);
                },
            });
        },

        servicesByCat(catKey) {
            const arr = (this.groupedLocal[String(catKey)] || []).slice();
            return arr.sort(byOrder);
        },
        numberFmt(v) {
            const n = parseFloat(v ?? 0);
            if (isNaN(n)) return '0.00';
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
        minutesToLabel(m) {
            if (!m) return '—';
            const h = Math.floor(m / 60),
                r = m % 60;
            return h ? `${h}h${r ? ` ${r}min` : ''}` : `${m}min`;
        },

        // menus
        setActionRef(key, el) {
            if (el) this.actionRefs.set(String(key), el);
        },
        setRowMenuRef(key, el) {
            if (el) this.rowMenuRefs.set(Number(key), el);
        },
        // onGlobalClick(e) {
        //     const t = e.target;
        //     if (this.$refs.optionsRef && !this.$refs.optionsRef.contains(t))
        //         this.optionsOpen = false;
        //     if (this.$refs.addRef && !this.$refs.addRef.contains(t))
        //         this.addOpen = false;
        //     if (this.actionsOpenKey !== null) {
        //         const holder = this.actionRefs.get(String(this.actionsOpenKey));
        //         if (holder && !holder.contains(t)) this.actionsOpenKey = null;
        //     }
        //     if (this.rowMenuOpenId !== null) {
        //         const holder2 = this.rowMenuRefs.get(
        //             Number(this.rowMenuOpenId),
        //         );
        //         if (holder2 && !holder2.contains(t)) this.rowMenuOpenId = null;
        //     }
        //     if (this.$refs.colorRef && !this.$refs.colorRef.contains(t)) {
        //         this.colorMenuOpen = false;
        //     }
        // },

        onEsc(e) {
            if (e.key === 'Escape') {
                this.optionsOpen = false;
                this.addOpen = false;
                this.actionsOpenKey = null;
                this.rowMenuOpenId = null;
                this.manageOpen = false;
                this.deleteOpen = false;
                this.serviceDeleteOpen = false;
                this.colorMenuOpen = false;
            }
        },

        selectCategory(id) {
            this.selectedCategory = id;
        },
        toggleOptions() {
            this.optionsOpen = !this.optionsOpen;
        },
        toggleAdd() {
            this.addOpen = !this.addOpen;
        },
        toggleActions(key) {
            this.actionsOpenKey = this.actionsOpenKey === key ? null : key;
        },
        toggleRowMenu(id) {
            this.rowMenuOpenId = this.rowMenuOpenId === id ? null : id;
        },

        goCreateService(categoryId = null) {
            const url = categoryId
                ? route('service.create', { category_id: categoryId })
                : route('service.create');
            router.visit(url);
        },
        editService(id) {
            router.visit(route('service.edit', id));
        },
        deleteService(id) {
            if (!confirm('Permanently delete this service?')) return;
            router.post(
                route('service.delete'),
                { id },
                { preserveScroll: true },
            );
        },

        openManageOrder() {
            const cats = this.categoriesSorted.map((c) => ({
                cid: String(c.id),
                id: c.id,
                title: c.name,
                color: c.color_code || null,
                services: this.servicesByCat(c.id).map((s) => ({
                    id: s.id,
                    name: s.name,
                    duration_minutes: s.duration_minutes,
                    price: s.price,
                    price_type: s.price_type,
                    sort_order: s.sort_order ?? 0,
                })),
            }));

            const unc = this.servicesByCat('uncategorized');
            if (unc.length) {
                cats.push({
                    cid: 'uncategorized',
                    id: null,
                    title: 'Uncategorized',
                    color: '#e5e7eb',
                    services: unc.map((s) => ({
                        id: s.id,
                        name: s.name,
                        duration_minutes: s.duration_minutes,
                        price: s.price,
                        price_type: s.price_type,
                        sort_order: s.sort_order ?? 0,
                    })),
                });
            }

            this.orderCats = cats;
            this.manageOpen = true;
        },
        closeManage() {
            this.manageOpen = false;
        },

        saveManage() {
            const categories = this.orderCats
                .filter((c) => c.id !== null)
                .map((c, i) => ({ id: c.id, sort_order: i }));

            const services = [];
            this.orderCats.forEach((cat) => {
                cat.services.forEach((s, i) => {
                    services.push({
                        id: s.id,
                        service_category_id: cat.id,
                        sort_order: i,
                    });
                });
            });

            this.savingOrder = true;
            router.post(
                route('service.reorder'),
                { categories, services },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.manageOpen = false;
                        router.reload({
                            only: [
                                'categories',
                                'grouped',
                                'uncategorizedCount',
                            ],
                            preserveScroll: true,
                        });
                        this.$root?.showMessage?.('Menu order updated.');
                    },
                    onError: (err) => {
                        const first =
                            (err && Object.values(err)[0]) ||
                            'Unable to save order.';
                        this.$root?.showMessage?.(first);
                    },
                    onFinish: () => {
                        this.savingOrder = false;
                    },
                },
            );
        },
    },
};
</script>

<style scoped>
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity 0.45s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0;
}

.modal-pop-enter-active,
.modal-pop-leave-active {
    transition:
        opacity 0.18s ease,
        transform 0.18s ease;
}

.modal-pop-enter-from,
.modal-pop-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

/* Nice visual feedback while sorting */
.drag-ghost {
    opacity: 0.65 !important;
    background: rgb(244 244 245 / 1);
    /* zinc-100 */
    border: 1px dashed rgb(212 212 216 / 1);
    /* zinc-300 */
}

.drag-chosen {
    transform: scale(1.01);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.dragging {
    cursor: grabbing !important;
}

.user-select-none {
    user-select: none;
}
.slide-down-slow-enter-active,
.slide-down-slow-leave-active {
    transition:
        opacity 0.28s ease,
        transform 0.28s ease;
}

.slide-down-slow-enter-from,
.slide-down-slow-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
}
</style>
