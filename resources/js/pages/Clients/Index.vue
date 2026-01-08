<template>
    <div>

        <Head title="Clients" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto p-4">
                <div
                    class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                    <div
                        class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Clients</h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Your clients list will appear here.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('clients.create')" class="btn-primary text-base cursor-pointer">
                            Add
                            </Link>
                        </div>
                    </div>
                    <div class="p-5">
                        <DataTable id="clientsTable" :url="route('clients.getdata')" :columns="columns"
                            :columnDefs="columnDefs"
                            >
                            <template #header>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Country</th>
                                    <th>Created at</th>
                                    <th>Actions</th>
                                </tr>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>

            <div v-if="showCanvas" class="fixed inset-0 bg-black/40 z-[9999] flex justify-end" @click.self="closeCanvas"
                :class="animating === 'out' ? 'animate-fadeOut' : 'animate-fadeIn'">

                <!-- DESKTOP SKELETON LOADING -->
                <div v-if="loadingCanvas"
                    class="hidden lg:flex w-full max-w-[1000px] h-full bg-white dark:bg-zinc-900 p-6 gap-6">

                    <!-- LEFT -->
                    <div class="w-[260px] space-y-6">

                        <div class="flex justify-center mt-10">
                            <div class="skeleton h-20 w-20 rounded-full"></div>
                        </div>

                        <div class="space-y-3 px-6">
                            <div class="skeleton h-4 w-32 mx-auto"></div>
                            <div class="skeleton h-3 w-24 mx-auto"></div>
                        </div>

                        <div class="skeleton h-10 w-full mt-4"></div>

                        <div class="space-y-4 mt-8">
                            <div class="skeleton h-4 w-40"></div>
                            <div class="skeleton h-4 w-32"></div>
                        </div>

                        <hr class="border-zinc-200 dark:border-zinc-700 my-6">

                        <!-- Tabs skeleton -->
                        <div class="space-y-3">
                            <div class="skeleton h-8 w-full"></div>
                            <div class="skeleton h-8 w-full"></div>
                            <div class="skeleton h-8 w-full"></div>
                        </div>

                    </div>

                    <!-- RIGHT -->
                    <div class="flex-1 space-y-6 overflow-y-auto p-4">

                        <div class="skeleton h-6 w-40"></div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="skeleton h-24 w-full"></div>
                            <div class="skeleton h-24 w-full"></div>
                        </div>

                        <div class="skeleton h-20 w-full"></div>
                        <div class="skeleton h-20 w-full"></div>
                        <div class="skeleton h-20 w-full"></div>

                    </div>

                </div>


                <!-- =============== DESKTOP VERSION (lg ONLY) =============== -->
                <div v-else class="hidden lg:flex w-full max-w-[1000px] h-full bg-white dark:bg-zinc-900
                shadow-2xl flex-row relative" :class="animating === 'in' ? 'animate-slideIn'
                    : animating === 'out' ? 'animate-slideOut' : ''">

                    <button @click="closeCanvas" class="absolute top-4 left-4 h-10 w-10 flex items-center justify-center
                   rounded-full bg-white text-zinc-600 hover:text-black hover:bg-zinc-100
                   border border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700
                   dark:text-zinc-300 dark:hover:bg-zinc-700 z-[99999] cursor-pointer">
                        <i class="bx bx-x text-2xl"></i>
                    </button>

                    <div class="w-[260px] border-r border-zinc-200 dark:border-zinc-700 p-6 overflow-y-auto">

                        <div class="flex flex-col items-center mt-12">

                            <div
                                class="h-20 w-20 rounded-full overflow-hidden bg-[#ff2000]/10 flex items-center justify-center">

                                <!-- If avatar exists -->
                                <img v-if="selectedClient?.media?.length" :src="selectedClient.media[0].original_url"
                                    alt="avatar" class="h-full w-full object-cover" />

                                <!-- Fallback initials -->
                                <span v-else class="text-[#ff2000] text-4xl font-semibold">
                                    {{ selectedClient.initial }}
                                </span>

                            </div>


                            <h2 class="mt-4 text-lg font-semibold text-center">
                                {{ selectedClient.first_name }} {{ selectedClient.last_name }}
                            </h2>

                            <p class="text-zinc-500 text-sm text-center">{{ selectedClient.phone }}</p>

                            <div class="w-full mt-4 space-y-2" v-if="selectedClient?.id">
                                    <a :href="`tel:${selectedClient.phone_e164 || selectedClient.phone}`" 
                                    class="btn-secondary w-full text-center block">
                                        Call
                                    </a>
                                <Link :href="route('clients.edit', selectedClient.id)"
                                    class="btn-primary w-full text-center">Edit
                                </Link>
                            </div>

                        </div>

                        <div class="mt-8 space-y-5 text-sm text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-center gap-3">
                                <i class="bx bx-cake text-xl"></i>
                                {{ selectedClient.birthday_year }}-{{ selectedClient.birthday_daymonth }}
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="bx bx-time text-xl"></i>
                                Created {{ formatDateTime(selectedClient.created_at) }}

                            </div>
                        </div>
                        <hr class="my-5">
                        <div class="py-2 pt-2
                    overflow-y-auto sticky top-0 bg-white dark:bg-zinc-900">

                            <div class="space-y-2">
                                <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                                    class="block w-full text-left py-2 px-3 rounded-lg transition cursor-pointer"
                                    :class="activeTab === tab
                                        ? 'bg-[#ff2000]/10 text-[#ff2000]  font-semibold'
                                        : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800'">
                                    {{ tab }}
                                </button>
                            </div>

                        </div>

                    </div>

                    <div class="flex-1 pb-6 overflow-y-auto bg-zinc-50 dark:bg-zinc-800">

                        <div class="flex items-center justify-between sticky top-0 pb-3 mb-6 pt-6 px-6
                        bg-zinc-50 dark:bg-zinc-800 z-[10]">

                            <h3 class="text-xl font-semibold">{{ activeTab }}</h3>


                        </div>

                        <div>
                            <section v-show="activeTab === 'Appointments'" class="px-6">
                                <div class="flex gap-2 mb-4 overflow-x-auto whitespace-nowrap pb-2">
                                    <button v-for="status in statusTabs" :key="status.value"
                                        @click="activeBookingStatus = status.value"
                                        class="px-3 py-1.5 rounded-full text-sm transition cursor-pointer" :class="activeBookingStatus === status.value
                                            ? 'bg-[#ff2000]/10 text-[#ff2000]  font-semibold'
                                            : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600'">

                                        {{ status.label }}
                                    </button>
                                </div>

                                <div v-if="filteredBookings.length === 0"
                                    class="text-sm text-zinc-500 py-6 text-center">
                                    No appointments found.
                                </div>

                                <div v-else class="space-y-4">

                                    <div v-for="booking in filteredBookings" :key="booking.id" class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer">

                                        <div class="flex items-start gap-4">


                                            <div class="h-12 w-12 rounded-full bg-[#ff2000]/10 text-[#ff2000] flex flex-col
                            items-center justify-center text-sm font-semibold leading-tight">
                                                <span class="text-base">
                                                    {{ new Date(booking.starts_at).getDate() }}
                                                </span>
                                                <span class="text-[10px] uppercase tracking-wide">
                                                    {{ new Date(booking.starts_at).toLocaleString('en', {
                                                        month: 'short'
                                                    }) }}
                                                </span>
                                            </div>


                                            <div class="flex-1">

                                                <div class="space-y-1">
                                                    <div v-for="service in booking.services" :key="service.id">

                                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                            {{ service.label }}
                                                        </p>

                                                        <p class="text-xs text-zinc-500">
                                                            {{ formatTime(service.starts_at) }} – {{
                                                                formatTime(service.ends_at) }}
                                                        </p>

                                                        <p class="text-xs text-zinc-500">
                                                            With {{ service.staff?.name || 'Unknown Staff' }}
                                                        </p>

                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                                        'bg-green-100 text-green-700': booking.status === 'completed',
                                                        'bg-blue-100 text-blue-700': booking.status === 'scheduled',
                                                        'bg-yellow-100 text-yellow-700': booking.status === 'pending',
                                                        'bg-red-100 text-red-700': booking.status === 'cancelled',
                                                        'bg-orange-100 text-orange-700': booking.status === 'no_show',
                                                    }">
                                                        {{ booking.status.replace('_', ' ') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                class="text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                                LKR {{Number( booking.total_price).toLocaleString() }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </section>
                            <section v-show="activeTab === 'Client details'" class="px-6">
                                <h4 class="text-sm font-semibold mb-3">Profile</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-xs text-zinc-500">Full name</p>
                                        <p class="text-sm">{{ selectedClient.first_name }} {{ selectedClient.last_name
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500">Email</p>
                                        <p class="text-sm">{{ selectedClient.email || '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500">Phone</p>
                                        <p class="text-sm">{{ selectedClient.phone }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500">Birthday</p>
                                        <p class="text-sm">
                                            {{ selectedClient.birthday_year }}-{{ selectedClient.birthday_daymonth }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">Gender</p>
                                        <p class="text-sm">
                                            {{ selectedClient.gender }}
                                        </p>
                                    </div>

                                </div>


                                <hr class="my-6">

                                <h4 class="text-sm font-semibold mb-3">Addresses</h4>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-xs text-zinc-500">Address Type</p>
                                        <p class="text-sm">
                                            {{ selectedClient.address_type }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">Address</p>
                                        <p class="text-sm">
                                            {{ selectedClient.address }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">District</p>
                                        <p class="text-sm">
                                            {{ selectedClient.district }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">City</p>
                                        <p class="text-sm">
                                            {{ selectedClient.city }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">Postcode</p>
                                        <p class="text-sm">
                                            {{ selectedClient.postcode }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500">Address Country</p>
                                        <p class="text-sm">
                                            {{ selectedClient.address_country }}
                                        </p>
                                    </div>
                                </div>


                            </section>

                            <section v-show="activeTab === 'Loyalty'" class="px-6">
                                <div class="mb-6">

                                    <div class=" inline-flex items-center gap-2 px-4 py-1.5 rounded-full
                    border border-zinc-200 dark:border-zinc-700 shadow-sm
                    bg-white dark:bg-zinc-800">

                                        <span class="w-2.5 h-2.5 rounded-full" :class="{
                                            'bg-yellow-500': selectedClient.loyalty_tier?.name === 'Gold',
                                            'bg-gray-400': selectedClient.loyalty_tier?.name === 'Silver',
                                            'bg-orange-500': selectedClient.loyalty_tier?.name === 'Bronze',
                                        }">
                                        </span>

                                        <span class="text-sm font-semibold">
                                            {{ selectedClient.loyalty_tier?.name || "No Tier" }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-zinc-500 mt-2">
                                        {{ selectedClient.loyalty_tier?.description || "Tier description unavailable."
                                        }}
                                    </p>

                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                        <p class="text-xs text-zinc-500">Current Points</p>
                                        <p class="text-2xl font-bold mt-1">{{ selectedClient.current_points }}</p>
                                    </div>


                                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                        <p class="text-xs text-zinc-500">Lifetime Points</p>
                                        <p class="text-2xl font-bold mt-1">{{ selectedClient.lifetime_points }}</p>
                                    </div>


                                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                        <p class="text-xs text-zinc-500">Tier Range</p>
                                        <p class="text-sm mt-1 font-semibold">
                                            {{ selectedClient.loyalty_tier?.start_points }} – {{
                                                selectedClient.loyalty_tier?.end_points }}
                                        </p>
                                    </div>

                                </div>



                                <div class="mb-8">

                                    <p class="text-sm text-zinc-500 mb-2">
                                        Progress toward next tier
                                    </p>


                                    <div class="w-full h-3 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all" :style="{
                                            width: progressToNextTier + '%',
                                            backgroundColor: '#ff2000'
                                        }">
                                        </div>
                                    </div>


                                    <p class="text-sm text-zinc-700 dark:text-zinc-300 mt-2">
                                        {{ nextTierMessage }}
                                    </p>

                                    <hr class="my-5">
                                    <!-- INLINE ADD POINTS FIELD (DESKTOP) -->

                                    <div v-if="hasPermission('clients.edit')" class="mt-4 bg-white dark:bg-zinc-900 border border-zinc-200
            dark:border-zinc-700 rounded-xl p-4 shadow-sm space-y-4">

                                        <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                            Add Points Manually
                                        </p>

                                        <!-- Points input + Add button -->
                                        <div class="flex items-center gap-3">
                                            <InputField type="number" v-model="addPointsValue" placeholder="Add points"
                                                class="w-28 px-3 py-2 border rounded-lg dark:bg-zinc-800
                   dark:border-zinc-700 text-sm" />


                                        </div>

                                        <!-- Note text area -->
                                        <div>
                                            <label class="text-xs text-zinc-500">Note (optional)</label>
                                            <textarea v-model="addPointsNote"
                                                placeholder="Write a note such as reason, manual adjustment, etc."
                                                class="w-full mt-1 px-3 py-2 border rounded-lg text-sm
                   dark:bg-zinc-800 dark:border-zinc-700 focus:outline-none
                   focus:ring-2 focus:ring-[#ff2000]/40 resize-none" rows="3"></textarea>
                                        </div>

                                        <button @click="submitAddPoints" class="btn-primary"
                                            :disabled="loadingAddPoints">


                                            <span class="inline-flex items-center">

                                                <!-- Normal Text -->
                                                <span v-if="!loadingAddPoints">Add</span>

                                                <!-- Loading Dots -->
                                                <span v-else class="loading-dots inline-flex items-center gap-1">
                                                    <span class="dot"></span>
                                                    <span class="dot"></span>
                                                    <span class="dot"></span>
                                                </span>

                                            </span>
                                        </button>



                                    </div>




                                </div>

                            </section>

                            <section v-show="activeTab === 'Loyalty History'" class="px-6">

                                <div v-if="selectedClientLogs.length === 0"
                                    class="text-sm text-zinc-500 py-6 text-center">
                                    No loyalty activity yet.
                                </div>

                                <div v-else class="space-y-4">

                                    <div v-for="log in selectedClientLogs" :key="log.id" class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                   rounded-xl shadow-sm">

                                        <div class="flex items-center justify-between">

                                            <!-- Left: Points and note -->
                                            <div>
                                                <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                    +{{ log.points_added }} points
                                                </p>

                                                <p v-if="log.note" class="text-xs text-zinc-500 mt-1">
                                                    {{ log.note }}
                                                </p>
                                            </div>

                                            <!-- Right: Date + Admin -->
                                            <div class="text-right text-xs text-zinc-500">
                                                <p>{{ new Date(log.created_at).toLocaleDateString() }}</p>
                                                <p class="mt-0.5">
                                                    By: <span class="font-medium">{{ log.admin?.name || "Unknown"
                                                    }}</span>
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </section>


                        </div>

                    </div>

                </div>


                <!-- MOBILE SKELETON -->
                <div v-if="loadingCanvas"
                    class="flex lg:hidden w-full h-full bg-white dark:bg-zinc-900 flex-col p-6 space-y-6">

                    <div class="flex justify-center mt-14">
                        <div class="skeleton h-20 w-20 rounded-full"></div>
                    </div>

                    <div class="space-y-3 text-center">
                        <div class="skeleton h-4 w-32 mx-auto"></div>
                        <div class="skeleton h-3 w-20 mx-auto"></div>
                    </div>

                    <div class="flex gap-3">
                        <div class="skeleton h-10 flex-1"></div>
                        <div class="skeleton h-10 flex-1"></div>
                    </div>

                    <!-- Tabs -->
                    <div class="flex gap-2 overflow-x-auto py-2">
                        <div class="skeleton h-8 w-24"></div>
                        <div class="skeleton h-8 w-24"></div>
                        <div class="skeleton h-8 w-24"></div>
                    </div>

                    <!-- Content -->
                    <div class="space-y-4">
                        <div class="skeleton h-24 w-full"></div>
                        <div class="skeleton h-24 w-full"></div>
                    </div>

                </div>

                <!-- ======== MOBILE + TABLET VERSION (xs, sm, md ONLY) ======= -->
                <div v-else class="flex lg:hidden w-full h-full bg-white dark:bg-zinc-900 flex-col relative" :class="animating === 'in' ? 'animate-slideIn'
                    : animating === 'out' ? 'animate-slideOut' : ''">

                    <button @click="closeCanvas" class="absolute top-4 right-4 h-10 w-10 flex items-center justify-center
                   rounded-full bg-white border dark:bg-zinc-800 z-[99999]">
                        <i class="bx bx-x text-2xl"></i>
                    </button>
                    <div class="p-6 pt-20 flex flex-col items-center text-center">

                        <div
                            class="h-20 w-20 rounded-full overflow-hidden bg-[#ff2000]/10 flex items-center justify-center">

                            <!-- If avatar exists -->
                            <img v-if="selectedClient?.media?.length" :src="selectedClient.media[0].original_url"
                                alt="avatar" class="h-full w-full object-cover" />

                            <!-- Fallback initials -->
                            <span v-else class="text-[#ff2000] text-4xl font-semibold">
                                {{ selectedClient.initial }}
                            </span>

                        </div>

                        <h2 class="mt-4 text-lg font-semibold">
                            {{ selectedClient.first_name }} {{ selectedClient.last_name }}
                        </h2>
                        <p class="text-zinc-500 text-sm">{{ selectedClient.phone }}</p>


                        <div class="flex gap-3 w-full mt-6 justify-center">


                            <a :href="`tel:${selectedClient.phone_e164 || selectedClient.phone}`" class="flex-1 btn-secondary
                  py-2  text-center">
                                Call
                            </a>


                            <Link v-if="selectedClient?.id" :href="route('clients.edit', selectedClient.id)" class="flex-1 btn-primary
                     py-2  text-center">
                            Edit
                            </Link>

                        </div>

                    </div>

                    <div class="px-4 overflow-x-auto whitespace-nowrap border-b border-zinc-200
                    dark:border-zinc-700 py-3 bg-white dark:bg-zinc-900">

                        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                            class="inline-block px-4 py-2 rounded-full mr-2 text-sm transition" :class="activeTab === tab
                                ? 'bg-[#ff2000]/10 text-[#ff2000] font-semibold'
                                : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600'">
                            {{ tab }}
                        </button>

                    </div>

                    <div class="flex-1 overflow-y-auto p-6 bg-zinc-50 dark:bg-zinc-800">



                        <section v-show="activeTab === 'Appointments'">

                            <div class="flex gap-2 mb-4 overflow-x-auto whitespace-nowrap pb-2">
                                <button v-for="status in statusTabs" :key="status.value"
                                    @click="activeBookingStatus = status.value"
                                    class="px-3 py-1.5 rounded-full text-sm transition" :class="activeBookingStatus === status.value
                                        ? 'bg-[#ff2000]/10 text-[#ff2000] font-semibold'
                                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600'">

                                    {{ status.label }}
                                </button>
                            </div>

                            <div v-if="filteredBookings.length === 0" class="text-sm text-zinc-500 py-6 text-center">
                                No appointments found.
                            </div>

                            <div v-else class="space-y-4">

                                <div v-for="booking in filteredBookings" :key="booking.id" class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl shadow-sm hover:shadow-md transition-all cursor-pointer">

                                    <div class="flex items-start gap-4">


                                        <div class="h-12 w-12 rounded-full bg-[#ff2000]/10 text-[#ff2000] flex flex-col
                            items-center justify-center text-sm font-semibold leading-tight">
                                            <span class="text-base">
                                                {{ new Date(booking.starts_at).getDate() }}
                                            </span>
                                            <span class="text-[10px] uppercase tracking-wide">
                                                {{ new Date(booking.starts_at).toLocaleString('en', {
                                                    month: 'short'
                                                }) }}
                                            </span>
                                        </div>


                                        <div class="flex-1">


                                            <div class="space-y-1">
                                                <div v-for="service in booking.services" :key="service.id">

                                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                        {{ service.label }}
                                                    </p>

                                                    <p class="text-xs text-zinc-500">
                                                        {{ formatTime(service.starts_at) }} – {{
                                                            formatTime(service.ends_at) }}
                                                    </p>

                                                    <p class="text-xs text-zinc-500">
                                                        With {{ service.staff?.name || 'Unknown Staff' }}
                                                    </p>

                                                </div>
                                            </div>


                                            <div class="mt-3">
                                                <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                                    'bg-green-100 text-green-700': booking.status === 'completed',
                                                    'bg-blue-100 text-blue-700': booking.status === 'scheduled',
                                                    'bg-yellow-100 text-yellow-700': booking.status === 'pending',
                                                    'bg-red-100 text-red-700': booking.status === 'cancelled',
                                                    'bg-orange-100 text-orange-700': booking.status === 'no_show',
                                                }">
                                                    {{ booking.status.replace('_', ' ') }}
                                                </span>
                                            </div>
                                        </div>


                                        <div class="text-right text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                            LKR {{ booking.total_price }}
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </section>

                        <section v-show="activeTab === 'Client details'">

                            <h4 class="text-sm font-semibold mb-3">Profile</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                                <div>
                                    <p class="text-xs text-zinc-500">Full name</p>
                                    <p class="text-sm">{{ selectedClient.first_name }} {{ selectedClient.last_name }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500">Email</p>
                                    <p class="text-sm">{{ selectedClient.email || '-' }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500">Phone</p>
                                    <p class="text-sm">{{ selectedClient.phone }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500">Birthday</p>
                                    <p class="text-sm">
                                        {{ selectedClient.birthday_year }}-{{ selectedClient.birthday_daymonth }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">Gender</p>
                                    <p class="text-sm">
                                        {{ selectedClient.gender }}
                                    </p>
                                </div>

                            </div>


                            <hr class="my-6">

                            <h4 class="text-sm font-semibold mb-3">Addresses</h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs text-zinc-500">Address Type</p>
                                    <p class="text-sm">
                                        {{ selectedClient.address_type }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">Address</p>
                                    <p class="text-sm">
                                        {{ selectedClient.address }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">District</p>
                                    <p class="text-sm">
                                        {{ selectedClient.district }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">City</p>
                                    <p class="text-sm">
                                        {{ selectedClient.city }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">Postcode</p>
                                    <p class="text-sm">
                                        {{ selectedClient.postcode }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">Address Country</p>
                                    <p class="text-sm">
                                        {{ selectedClient.address_country }}
                                    </p>
                                </div>
                            </div>


                        </section>

                        <section v-show="activeTab === 'Loyalty'">
                            <div class="mb-6">
                                <div class=" inline-flex items-center gap-2 px-4 py-1.5 rounded-full
                    border border-zinc-200 dark:border-zinc-700 shadow-sm
                    bg-white dark:bg-zinc-800">

                                    <span class="w-2.5 h-2.5 rounded-full" :class="{
                                        'bg-yellow-500': selectedClient.loyalty_tier?.name === 'Gold',
                                        'bg-gray-400': selectedClient.loyalty_tier?.name === 'Silver',
                                        'bg-orange-500': selectedClient.loyalty_tier?.name === 'Bronze',
                                    }">
                                    </span>

                                    <span class="text-sm font-semibold">
                                        {{ selectedClient.loyalty_tier?.name || "No Tier" }}
                                    </span>
                                </div>

                                <p class="text-sm text-zinc-500 mt-2">
                                    {{ selectedClient.loyalty_tier?.description || "Tier description unavailable."
                                    }}
                                </p>

                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                    <p class="text-xs text-zinc-500">Current Points</p>
                                    <p class="text-2xl font-bold mt-1">{{ selectedClient.current_points }}</p>
                                </div>


                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                    <p class="text-xs text-zinc-500">Lifetime Points</p>
                                    <p class="text-2xl font-bold mt-1">{{ selectedClient.lifetime_points }}</p>
                                </div>


                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                    rounded-xl p-4 shadow-sm">
                                    <p class="text-xs text-zinc-500">Tier Range</p>
                                    <p class="text-sm mt-1 font-semibold">
                                        {{ selectedClient.loyalty_tier?.start_points }} – {{
                                            selectedClient.loyalty_tier?.end_points }}
                                    </p>
                                </div>

                            </div>



                            <div class="mb-8">

                                <p class="text-sm text-zinc-500 mb-2">
                                    Progress toward next tier
                                </p>


                                <div class="w-full h-3 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all" :style="{
                                        width: progressToNextTier + '%',
                                        backgroundColor: '#ff2000'
                                    }">
                                    </div>
                                </div>


                                <p class="text-sm text-zinc-700 dark:text-zinc-300 mt-2">
                                    {{ nextTierMessage }}
                                </p>

                                <hr class="my-5">
                                <p class="text-sm text-zinc-500 mb-2">
                                    Add Points Manually
                                </p>
                                <!-- INLINE ADD POINTS FIELD (MOBILE) -->

                                <div v-if="hasPermission('clients.edit')" class="mt-4 bg-white dark:bg-zinc-900 border border-zinc-200
            dark:border-zinc-700 rounded-xl p-4 shadow-sm space-y-4">

                                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                        Add Points Manually
                                    </p>

                                    <!-- Points input + Add button -->
                                    <div class="flex items-center gap-3">
                                        <InputField type="number" v-model="addPointsValue" placeholder="Add points"
                                            class="w-28 px-3 py-2 border rounded-lg dark:bg-zinc-800
                   dark:border-zinc-700 text-sm" />


                                    </div>

                                    <!-- Note text area -->
                                    <div>
                                        <label class="text-xs text-zinc-500">Note (optional)</label>
                                        <textarea v-model="addPointsNote"
                                            placeholder="Write a note such as reason, manual adjustment, etc." class="w-full mt-1 px-3 py-2 border rounded-lg text-sm
                   dark:bg-zinc-800 dark:border-zinc-700 focus:outline-none
                   focus:ring-2 focus:ring-[#ff2000]/40 resize-none" rows="3"></textarea>
                                    </div>

                                    <button @click="submitAddPoints" class="btn-primary" :disabled="loadingAddPoints">


                                        <span class="inline-flex items-center">

                                            <!-- Normal Text -->
                                            <span v-if="!loadingAddPoints">Add</span>

                                            <!-- Loading Dots -->
                                            <span v-else class="loading-dots inline-flex items-center gap-1">
                                                <span class="dot"></span>
                                                <span class="dot"></span>
                                                <span class="dot"></span>
                                            </span>

                                        </span>
                                    </button>



                                </div>


                            </div>

                        </section>

                        <section v-show="activeTab === 'Loyalty History'">



                            <div v-if="selectedClientLogs.length === 0" class="text-sm text-zinc-500 py-6 text-center">
                                No loyalty activity yet.
                            </div>

                            <div v-else class="space-y-4">

                                <div v-for="log in selectedClientLogs" :key="log.id" class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700
                   rounded-xl shadow-sm">

                                    <div class="flex items-center justify-between">

                                        <!-- Left: Points and note -->
                                        <div>
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                                +{{ log.points_added }} points
                                            </p>

                                            <p v-if="log.note" class="text-xs text-zinc-500 mt-1">
                                                {{ log.note }}
                                            </p>
                                        </div>

                                        <!-- Right: Date + Admin -->
                                        <div class="text-right text-xs text-zinc-500">
                                            <p>{{ new Date(log.created_at).toLocaleDateString() }}</p>
                                            <p class="mt-0.5">
                                                By: <span class="font-medium">{{ log.admin?.name || "Unknown"
                                                }}</span>
                                            </p>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </section>

                    </div>

                </div>

            </div>
                    <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="overlay-fade">
            <div v-if="clientDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal">
                <Transition name="modal-pop" appear>
                    <div v-show="clientDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">

                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete client
                            </h4>
                            <button class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDeleteModal">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="mb-2 text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ clientDeleteMeta.name }}
                        </p>

                        <p class="mb-6 text-base text-zinc-600 dark:text-zinc-300">
                            This client will be permanently deleted. This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button class="btn-secondary text-base cursor-pointer" @click="closeDeleteModal">
                                Cancel
                            </button>

                            <button
                                class="btn-primary bg-rose-600 hover:bg-rose-700 text-white text-base cursor-pointer"
                                :disabled="deletingClient" @click="confirmDeleteClient">
                                {{ deletingClient ? "Deleting…" : "Delete" }}
                            </button>
                        </div>

                    </div>
                </Transition>
            </div>
        </Transition>


        </AppLayout>
    </div>

</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import axios from 'axios'
import InputField from '@/components/InputField.vue'


export default {
    components: { AppLayout, DataTable, Head, Link, InputField },

    data() {
        const page = usePage()

        return {

            addPointsValue: '',
            addPointsNote: '',
            loadingAddPoints: false,
            breadcrumbs: [{ title: 'Clients', href: route('clients.index') }],
            loadingCanvas: false,
            showCanvas: false,
            loading: false,
            animating: false,
            selectedClient: {},
            selectedClientBookings: {},
            selectedClientLogs: [],
            activeTab: "Appointments",
            tabs: ["Appointments", "Client details", "Loyalty"], //, "Loyalty History"

            activeBookingStatus: 'all',
            statusTabs: [
                { label: 'All', value: 'all' },
                { label: 'Scheduled', value: 'scheduled' },
                { label: 'Completed', value: 'completed' },
                { label: 'Pending', value: 'pending' },
                { label: 'Cancelled', value: 'cancelled' },
                { label: 'No show', value: 'no_show' },
            ],

            hasPermission: (perm) => !!(page.props.permission || {})[perm],

            columns: [
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'country', name: 'country' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],

            columnDefs: [
                {
                    targets: 0,
                    className: 'whitespace-nowrap',
                    render: (data, type, row) => {
                        if (type !== 'display') return data

                        const name = data || ''
                        const fallback = row.email || ''
                        const source = (name || fallback).trim()
                        const initial = source.charAt(0).toUpperCase()
                        const safeName = name || '—'
                        const avatar = row.avatar_url || ''

                        let avatarHtml = ''

                        if (avatar) {
                            avatarHtml = `
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 overflow-hidden">
                  <img src="${avatar}" class="h-full w-full object-cover" />
                </div>`
                       } else {
    avatarHtml = `
        <div
          class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-700 text-lg font-semibold client-avatar-initial"
          data-initial="${initial}"
        >
        </div>`
}


                        return `
              <div class="flex items-center gap-3">
                ${avatarHtml}
                <span>${safeName}</span>
              </div>
            `
                    },
                },

                {
                    targets: 4,
                    className: 'whitespace-nowrap',
                    render: (d) => {
                        if (!d) return '—'
                        const dt = new Date(d)
                        return isNaN(+dt) ? d : dt.toLocaleDateString()
                    },
                },

                {
                    targets: 5,
                    className: 'whitespace-nowrap',
                    render: (_, __, r) => {
                        let buttons = ''

                        // VIEW BUTTON
                        buttons += `
              <button data-id="${r.id}" class="tw-btn tw-btn-view mr-1" title="View Client">
                <i class="bx bx-show"></i>
              </button>
            `

                        if (this.hasPermission('clients.edit')) {
                            buttons += `
                <button data-id="${r.id}" class="tw-btn tw-btn-primary mr-1" title="Edit Client">
                  <i class="bx bx-edit"></i>
                </button>`
                        }

                        if (this.hasPermission('clients.delete')) {
                            buttons += `
                <button data-id="${r.id}" class="tw-btn tw-btn-danger" title="Delete Client">
                  <i class="bx bx-trash"></i>
                </button>`
                        }

                        return buttons
                    },
                },
            ],
            clientDeleteOpen: false,
            clientDeleteMeta: { id: null, name: "" },
            deletingClient: false,
        }
    },

    mounted() {
        const $ = window.jQuery
        if (!$) return

        $('#clientsTable')
            // VIEW
            .on('click', 'button.tw-btn.tw-btn-view', (e) => {
                const id = e.currentTarget.getAttribute('data-id')
                if (id) this.openCanvas(id)
            })

            // EDIT
            .on('click', 'button.tw-btn.tw-btn-primary', (e) => {
                const id = e.currentTarget.getAttribute('data-id')
                if (id) router.visit(route('clients.edit', id))
            })

            // DELETE
         .on('click', 'button.tw-btn.tw-btn-danger', (e) => {
    const id = e.currentTarget.getAttribute('data-id')
    if (!id) return

                // Get client name from the row
            const row = $(e.currentTarget).closest('tr');
            const clientName = row.find('td:first-child span').text().trim() || 'Client';
            
            this.openDeleteModal(id, clientName);

    // if (confirm('Delete this client?')) {

    //     router.delete(route('clients.destroy', id), {
    //         onSuccess: () => {
    //             $('#clientsTable').DataTable().ajax.reload(null, false)
    //         },
    //         preserveScroll: true,
    //     })

    // }
})

    },
    computed: {
        normalizedBookings() {
            if (!this.selectedClientBookings) return [];
            if (Array.isArray(this.selectedClientBookings)) {
                return this.selectedClientBookings;
            }
            if (Array.isArray(this.selectedClientBookings.data)) {
                return this.selectedClientBookings.data;
            }
            if (typeof this.selectedClientBookings === 'object') {
                return Object.values(this.selectedClientBookings);
            }
            return [];
        },

        filteredBookings() {
            if (this.activeBookingStatus === 'all') {
                return this.normalizedBookings;
            }

            return this.normalizedBookings.filter(
                b => b.status === this.activeBookingStatus
            );
        },
        progressToNextTier() {
            if (!this.selectedClient.loyalty_tier) return 0;

            const start = this.selectedClient.loyalty_tier.start_points;
            const end = this.selectedClient.loyalty_tier.end_points;
            const current = this.selectedClient.current_points;

            if (current <= start) return 0;
            if (current >= end) return 100;

            return ((current - start) / (end - start)) * 100;
        },

        nextTierMessage() {
            if (!this.selectedClient.loyalty_tier) return '';

            const end = this.selectedClient.loyalty_tier.end_points;
            const current = this.selectedClient.current_points;

            const remaining = end - current;

            if (remaining <= 0) return "You've reached the highest tier!";
            return `${remaining} more points to reach the next tier.`;
        }
    }
    ,
    methods: {
        async submitAddPoints() {
            if (!this.addPointsValue || this.addPointsValue <= 0) {
                alert("Enter a valid point amount.");
                return;
            }

            this.loadingAddPoints = true;

            this.$inertia.post(
                route('clients.addPoints', this.selectedClient.id),
                { points: this.addPointsValue, note: this.addPointsNote, },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Update UI instantly
                        this.selectedClient.current_points += parseInt(this.addPointsValue);
                        this.selectedClient.lifetime_points += parseInt(this.addPointsValue);
                        this.addPointsValue = '';
                        this.addPointsNote = '';
                    },
                    onFinish: () => {
                        this.loadingAddPoints = false;
                    },
                }
            );
        }
        ,
        async openCanvas(id) {
            this.showCanvas = true;
            this.animating = "in";
            this.loadingCanvas = true;   // START SKELETON

            try {
                const res = await axios.get(route("clients.show", id));

                this.selectedClient = {
                    ...res.data.client,
                    initial: (res.data.client.first_name || 'U').charAt(0).toUpperCase(),
                };

                this.selectedClientBookings = res.data.bookings;
                this.selectedClientLogs = res.data.logs || [];

            } finally {
                setTimeout(() => {
                    this.loadingCanvas = false;  // STOP SKELETON
                }, 400); // smooth fade
            }
        },


        closeCanvas() {
            this.activeBookingStatus = 'all',
                this.activeTab = "Appointments",
                this.animating = "out"
            setTimeout(() => {
                this.showCanvas = false
                this.animating = false
            }, 350)
        },
        getTabComponent(tab) {
            return {
                'Overview': 'OverviewTab',
                'Client details': 'ClientDetailsTab',
                'Appointments': 'AppointmentsTab',
                'Loyalty': 'LoyaltyTab',
            }[tab];
        },
        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        formatDateTime(dateString) {
            if (!dateString) return "-";

            const d = new Date(dateString);

            return d.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
                openDeleteModal(clientId, clientName) {
            this.clientDeleteMeta = { id: clientId, name: clientName };
            this.clientDeleteOpen = true;
        },

        closeDeleteModal() {
            this.clientDeleteOpen = false;
        },

        confirmDeleteClient() {
            this.deletingClient = true;
            const id = this.clientDeleteMeta.id;

            this.$inertia.delete(route("clients.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    $('#clientsTable').DataTable().ajax.reload(null, false);
                    this.clientDeleteOpen = false;
                    this.deletingClient = false;
                    this.$root.showMessage("Client deleted successfully!");
                },
                onError: () => {
                    this.deletingClient = false;
                    this.$root.showMessage("Failed to delete client.");
                }
            })
        }

    }

}
</script>
<style scoped>
@keyframes slideIn {
    from {
        transform: translateX(100%);
    }

    to {
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(100%);
    }
}

.animate-slideIn {
    animation: slideIn .35s cubic-bezier(.25, 1, .5, 1) forwards;
}

.animate-slideOut {
    animation: slideOut .35s cubic-bezier(.25, 1, .5, 1) forwards;
}

/* optional but smooth */
@keyframes fadeOut {
    from {
        opacity: 1;
    }

    to {
        opacity: 0;
    }
}

.animate-fadeOut {
    animation: fadeOut .25s ease-out forwards;
}


.skeleton {
    background: rgba(0, 0, 0, 0.08);
    /* light gray – matches zinc-200 */
    border-radius: 6px;
    position: relative;
    overflow: hidden;
}

.dark .skeleton {
    background: rgba(255, 255, 255, 0.08);
    /* matches zinc-700 */
}

.skeleton::after {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg,
            transparent,
            rgba(255, 255, 255, 0.6),
            transparent);
    animation: shimmer 1.2s infinite;
}

@keyframes shimmer {
    100% {
        left: 100%;
    }
}
@media (max-width: 360px) {
    :deep div.dataTables_wrapper div.dataTables_filter input {
        max-width: 150px !important;
        min-width: 150px !important;
    }
}
</style>
