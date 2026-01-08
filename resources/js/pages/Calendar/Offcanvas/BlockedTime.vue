<template>
    <Transition name="offcanvas">
        <div v-if="show" class="fixed inset-0 z-[300] overflow-hidden">
            <!-- Backdrop -->
            <Transition name="backdrop">
                <div
                    v-if="show"
                    class="absolute inset-0 bg-gradient-to-br from-black/60 to-black/40 backdrop-blur-sm"
                    @click="$emit('close')"
                ></div>
            </Transition>

            <!-- Panel -->
            <Transition name="slide">
                <div
                    v-if="show"
                    class="absolute top-0 right-0 flex h-full w-full max-w-lg flex-col bg-white shadow-2xl"
                >
                    <!-- Header -->
                    <div
                        class="relative overflow-hidden border-b border-neutral-100 bg-gradient-to-br from-neutral-50 to-white px-8 py-6"
                    >
                        <div
                            class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-[var(--brand)]/5"
                        ></div>
                        <div
                            class="absolute -right-8 -bottom-8 h-32 w-32 rounded-full bg-[var(--brand)]/3"
                        ></div>

                        <div class="relative flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 shadow-lg shadow-rose-500/30"
                                >
                                    <svg
                                        class="h-6 w-6 text-white"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                        />
                                    </svg>
                                </div>
                                <div>
                                   <h2 class="text-xl font-bold text-neutral-900">
    {{ form.id ? 'Edit blocked time' : 'Add blocked time' }}
</h2>

                                    <p class="mt-0.5 text-sm text-neutral-500">
                                        Mark time as unavailable
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="group relative flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl text-neutral-400 transition-all hover:bg-white hover:text-neutral-700 hover:shadow-md"
                                @click="$emit('close')"
                            >
                                <svg
                                    class="h-5 w-5 transition-transform group-hover:rotate-90"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto">
                        <div class="space-y-8 px-8 py-6">
                            <!-- Block Type Selection with Swiper -->
                            <div class="space-y-3">
                                <label
                                    class="flex items-center text-sm font-semibold text-neutral-900"
                                >
                                    Block time type
                                </label>

                                <div class="relative">
                                    <!-- Navigation Buttons -->
                                    <button
                                        v-if="canScrollLeft"
                                        type="button"
                                        class="absolute top-1/2 left-0 z-10 flex h-10 w-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-lg transition-all hover:shadow-xl"
                                        @click="scrollLeft"
                                    >
                                        <svg
                                            class="h-5 w-5 text-neutral-700"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 19l-7-7 7-7"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Swiper Container -->
                                    <div
                                        ref="swiperContainer"
                                        class="no-scrollbar flex gap-4 overflow-x-auto scroll-smooth px-1 py-2"
                                        @scroll="updateScrollButtons"
                                    >
                                        <!-- Lunch Block -->
                                        <div
                                            class="group min-w-[180px] cursor-pointer rounded-2xl border-2 p-4 transition-all"
                                            :class="
                                                form.block_type === 'lunch'
                                                    ? 'border-[var(--brand)] bg-[var(--brand)]/5 shadow-lg'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:shadow-md'
                                            "
                                            @click="selectBlockType('lunch')"
                                        >
                                            <div
                                                class="mb-3 flex justify-center"
                                            >
                                                <div
                                                    class="flex h-16 w-16 items-center justify-center rounded-2xl text-4xl transition-transform group-hover:scale-110"
                                                >
                                                    🥪
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div
                                                    class="font-semibold text-neutral-900"
                                                >
                                                    Lunch
                                                </div>
                                                <div
                                                    class="mt-1 text-xs text-neutral-500"
                                                >
                                                    30min · Unpaid
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Training Block -->
                                        <div
                                            class="group min-w-[180px] cursor-pointer rounded-2xl border-2 p-4 transition-all"
                                            :class="
                                                form.block_type === 'training'
                                                    ? 'border-[var(--brand)] bg-[var(--brand)]/5 shadow-lg'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:shadow-md'
                                            "
                                            @click="selectBlockType('training')"
                                        >
                                            <div
                                                class="mb-3 flex justify-center"
                                            >
                                                <div
                                                    class="flex h-16 w-16 items-center justify-center rounded-2xl text-4xl transition-transform group-hover:scale-110"
                                                >
                                                    📚
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div
                                                    class="font-semibold text-neutral-900"
                                                >
                                                    Training
                                                </div>
                                                <div
                                                    class="mt-1 text-xs text-neutral-500"
                                                >
                                                    1h · Paid
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Meeting Block -->
                                        <div
                                            class="group min-w-[180px] cursor-pointer rounded-2xl border-2 p-4 transition-all"
                                            :class="
                                                form.block_type === 'meeting'
                                                    ? 'border-[var(--brand)] bg-[var(--brand)]/5 shadow-lg'
                                                    : 'border-neutral-200 bg-white hover:border-neutral-300 hover:shadow-md'
                                            "
                                            @click="selectBlockType('meeting')"
                                        >
                                            <div
                                                class="mb-3 flex justify-center"
                                            >
                                                <div
                                                    class="flex h-16 w-16 items-center justify-center rounded-2xl text-4xl transition-transform group-hover:scale-110"
                                                >
                                                    📅
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div
                                                    class="font-semibold text-neutral-900"
                                                >
                                                    Meeting
                                                </div>
                                                <div
                                                    class="mt-1 text-xs text-neutral-500"
                                                >
                                                    1h · Paid
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Custom Block -->
                                        <div
                                            class="group min-w-[180px] cursor-pointer rounded-2xl border-2 border-dashed p-4 transition-all"
                                            :class="
                                                form.block_type === 'custom'
                                                    ? 'border-[var(--brand)] bg-[var(--brand)]/5 shadow-lg'
                                                    : 'border-neutral-300 bg-white hover:border-[var(--brand)] hover:shadow-md'
                                            "
                                            @click="selectBlockType('custom')"
                                        >
                                            <div
                                                class="mb-3 flex justify-center"
                                            >
                                                <div
                                                    class="flex h-16 w-16 items-center justify-center rounded-2xl transition-transform group-hover:scale-110"
                                                    :class="
                                                        form.block_type ===
                                                        'custom'
                                                            ? 'bg-[var(--brand)] text-white'
                                                            : 'bg-[var(--brand)]/10 text-[var(--brand)]'
                                                    "
                                                >
                                                    <svg
                                                        class="h-8 w-8"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M12 4v16m8-8H4"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div
                                                    class="font-semibold"
                                                    :class="
                                                        form.block_type ===
                                                        'custom'
                                                            ? 'text-[var(--brand)]'
                                                            : 'text-neutral-900'
                                                    "
                                                >
                                                    New type
                                                </div>
                                                <div
                                                    class="mt-1 text-xs text-neutral-500"
                                                >
                                                    Custom block
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        v-if="canScrollRight"
                                        type="button"
                                        class="absolute top-1/2 right-0 z-10 flex h-10 w-10 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white shadow-lg transition-all hover:shadow-xl"
                                        @click="scrollRight"
                                    >
                                        <svg
                                            class="h-5 w-5 text-neutral-700"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Date Display -->
                            <div class="space-y-2">
                                <label
                                    class="flex items-center text-sm font-semibold text-neutral-900"
                                >
                                    Date
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border-2 border-neutral-200 bg-neutral-50 px-4 py-3.5"
                                >
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-sm font-bold text-[var(--brand)]"
                                    >
                                        {{ dateDay }}
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-semibold text-neutral-900"
                                        >
                                            {{ formattedDate }}
                                        </div>
                                        <div class="text-xs text-neutral-500">
                                            {{ dateYear }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Selection with SelectInputComponent -->
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label
                                        class="flex items-center text-sm font-semibold text-neutral-900"
                                    >
                                        Start time
                                    </label>
                                    <SelectInputComponent
                                        v-model="form.starts_at_time"
                                        :options="timeOptions"
                                        placeholder="Select start time"
                                        @update:modelValue="updateEndTime"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="flex items-center text-sm font-semibold text-neutral-900"
                                    >
                                        End time
                                    </label>
                                    <SelectInputComponent
                                        v-model="form.ends_at_time"
                                        :options="timeOptions"
                                        placeholder="Select end time"
                                    />
                                </div>
                            </div>

                            <!-- Duration Display -->
                            <div
                                class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[var(--brand)]/10 to-[var(--brand)]/5 px-4 py-3"
                            >
                                <svg
                                    class="h-5 w-5 text-[var(--brand)]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"
                                    />
                                </svg>
                                <span
                                    class="text-sm font-semibold text-neutral-700"
                                >
                                    Duration:
                                </span>
                                <span
                                    class="text-sm font-bold text-[var(--brand)]"
                                >
                                    {{ duration }}
                                </span>
                            </div>

                            <!-- Staff Member -->
                            <div class="space-y-2">
                                <label
                                    class="flex items-center text-sm font-semibold text-neutral-900"
                                >
                                    Team members
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border-2 border-neutral-200 bg-white px-4 py-3.5"
                                >
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[var(--brand)] to-[var(--brand)]/80 text-sm font-bold text-white shadow-[var(--brand)]/30 shadow-lg"
                                    >
                                        {{ initials(staff.name) }}
                                    </div>
                                    <span
                                        class="font-semibold text-neutral-900"
                                    >
                                        {{ staff.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Frequency -->
                            <!-- <div class="space-y-2">
                                <label
                                    class="flex items-center text-sm font-semibold text-neutral-900"
                                >
                                    Frequency
                                </label>
                                <SelectInputComponent
                                    v-model="form.frequency"
                                    :options="frequencyOptions"
                                    placeholder="Select frequency"
                                />
                            </div> -->

                            <!-- Description -->
                            <div class="space-y-2">
                                <label
                                    class="flex items-center text-sm font-semibold text-neutral-900"
                                >
                                    Description
                                    <span class="ml-1 text-xs text-neutral-400">
                                        (Optional)
                                    </span>
                                </label>
                                <div class="relative">
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full resize-none rounded-xl border-2 border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 transition-all placeholder:text-neutral-400 focus:border-[var(--brand)] focus:ring-4 focus:ring-[var(--brand)]/10 focus:outline-none"
                                        placeholder="Add any notes or details about this blocked time..."
                                        maxlength="255"
                                    ></textarea>
                                    <div
                                        class="absolute right-3 bottom-3 text-xs font-medium"
                                        :class="
                                            form.description.length > 240
                                                ? 'text-amber-600'
                                                : 'text-neutral-400'
                                        "
                                    >
                                        {{ form.description.length }}/255
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="border-t border-neutral-100 bg-gradient-to-br from-neutral-50 to-white px-8 py-6"
                    >
                        <div class="flex gap-3">
                            <button
                                type="button"
                                class="btn-secondary w-full"
                                @click="$emit('close')"
                                :disabled="form.processing"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary w-full"
                                @click="saveBlockedTime"
                                :disabled="form.processing"
                            >
                                <span
                                    class="relative z-10 flex items-center justify-center gap-2"
                                >
                                    <svg
                                        v-if="!form.processing"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-4 w-4 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    <span v-if="form.processing"
                                        >Saving...</span
                                    >
                                    <span v-else>Save</span>
                                </span>

                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script lang="ts">
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, defineComponent, nextTick, ref, watch } from 'vue';

interface Staff {
    id: number;
    name: string;
}

interface BlockedTimeData {
    id?: number | null;
    staff: Staff;
    date: string;
    startIso: string;
    endIso: string;
    block_type?: string | null;
    description?: string | null;
}

interface TimeOption {
    value: string;
    label: string;
}

interface FrequencyOption {
    value: string;
    label: string;
}

export default defineComponent({
    name: 'BlockedTime',
    components: {
        SelectInputComponent,
    },
    props: {
        show: {
            type: Boolean,
            required: true,
        },
        data: {
            type: Object as () => BlockedTimeData | null,
            default: null,
        },
    },

    emits: ['close', 'saved'],

    setup(props, { emit }) {
        const staff = ref<Staff>({ id: 0, name: '' });
        const swiperContainer = ref<HTMLElement | null>(null);
        const canScrollLeft = ref(false);
        const canScrollRight = ref(false);

        const form = useForm({
            id: null as number | null,
            staff_id: 0,
            date: '',
            starts_at: '',
            ends_at: '',
            starts_at_time: '',
            ends_at_time: '',
            block_type: 'lunch',
            frequency: 'does_not_repeat',
            description: '',
            branch_id: null as number | null,
        });

        const timeOptions = computed<TimeOption[]>(() => {
            const options: TimeOption[] = [];
            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 15) {
                    const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                    const displayTime = new Date(
                        `2000-01-01T${timeString}`,
                    ).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    });
                    options.push({
                        value: timeString,
                        label: displayTime,
                    });
                }
            }
            return options;
        });

        const frequencyOptions = computed<FrequencyOption[]>(() => {
            const dayName = dayOfWeek.value;
            return [
                { value: 'does_not_repeat', label: "Doesn't repeat" },
                { value: 'daily', label: 'Daily' },
                {
                    value: 'weekly',
                    label: dayName ? `Weekly (every ${dayName})` : 'Weekly',
                },
                { value: 'monthly', label: 'Monthly' },
            ];
        });

        const formattedDate = computed(() => {
            if (!props.data?.date) return '';
            const date = new Date(props.data.date + 'T00:00:00');
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
            });
        });

        const dateDay = computed(() => {
            if (!props.data?.date) return '';
            const date = new Date(props.data.date + 'T00:00:00');
            return date.getDate().toString();
        });

        const dateYear = computed(() => {
            if (!props.data?.date) return '';
            const date = new Date(props.data.date + 'T00:00:00');
            return date.getFullYear().toString();
        });

        const dayOfWeek = computed(() => {
            if (!props.data?.date) return '';
            const date = new Date(props.data.date + 'T00:00:00');
            return date.toLocaleDateString('en-US', { weekday: 'long' });
        });

        const duration = computed(() => {
            if (!form.starts_at_time || !form.ends_at_time) return '0min';

            const start = new Date(`2000-01-01T${form.starts_at_time}`);
            const end = new Date(`2000-01-01T${form.ends_at_time}`);
            const diff = (end.getTime() - start.getTime()) / (1000 * 60);

            if (diff <= 0) return '0min';

            if (diff < 60) {
                return `${diff}min`;
            } else {
                const hours = Math.floor(diff / 60);
                const minutes = diff % 60;
                return minutes > 0 ? `${hours}h ${minutes}min` : `${hours}h`;
            }
        });

        const selectBlockType = (type: string) => {
            form.block_type = type;
            updateEndTime();
        };

        const updateEndTime = () => {
            if (!form.starts_at_time) return;

            const start = new Date(`2000-01-01T${form.starts_at_time}`);
            let durationMinutes = 60;

            switch (form.block_type) {
                case 'lunch':
                    durationMinutes = 30;
                    break;
                case 'training':
                case 'meeting':
                    durationMinutes = 60;
                    break;
                case 'custom':
                    if (form.ends_at_time) return;
                    durationMinutes = 60;
                    break;
            }

            const end = new Date(start.getTime() + durationMinutes * 60000);
            form.ends_at_time = end.toTimeString().slice(0, 5);
        };

        const updateScrollButtons = () => {
            if (!swiperContainer.value) return;

            const container = swiperContainer.value;
            canScrollLeft.value = container.scrollLeft > 0;
            canScrollRight.value =
                container.scrollLeft <
                container.scrollWidth - container.clientWidth - 1;
        };

        const scrollLeft = () => {
            if (!swiperContainer.value) return;
            swiperContainer.value.scrollBy({
                left: -200,
                behavior: 'smooth',
            });
        };

        const scrollRight = () => {
            if (!swiperContainer.value) return;
            swiperContainer.value.scrollBy({
                left: 200,
                behavior: 'smooth',
            });
        };

        const saveBlockedTime = () => {
            if (!props.data) return;

            form.starts_at = `${props.data.date}T${form.starts_at_time}:00`;
            form.ends_at = `${props.data.date}T${form.ends_at_time}:00`;
            form.staff_id = staff.value.id;
            form.date = props.data.date;

            const pageProps = (window as any).page?.props;
            if (pageProps?.auth?.user?.branch_id) {
                form.branch_id = pageProps.auth.user.branch_id;
            }

            const hasId = !!form.id;

            const options = {
                preserveScroll: true,
                onSuccess: () => {
                    emit('saved');
                    emit('close');
                },
                onError: (errors: any) => {
                    console.error('Failed to save blocked time:', errors);
                },
            };

            if (hasId) {
                form.put(
                    route('bookings.blocked-time.update', form.id as number),
                    options,
                );
            } else {
                form.post(route('bookings.blocked-time.store'), options);
            }
        };

        const initials = (name: string): string => {
            return name
                .split(' ')
                .map((part) => part[0]?.toUpperCase() || '')
                .join('')
                .slice(0, 2);
        };

        watch(
            () => form.block_type,
            () => {
                updateEndTime();
            },
        );

        watch(
            () => props.data,
            (newData) => {
                if (newData) {
                    staff.value = newData.staff;

                    const startDate = new Date(newData.startIso);
                    const endDate = new Date(newData.endIso);

                    form.starts_at_time = startDate.toTimeString().slice(0, 5);
                    form.ends_at_time = endDate.toTimeString().slice(0, 5);
                    form.staff_id = newData.staff.id;
                    form.date = newData.date;

                    form.id = (newData as any).id ?? null;
                    form.block_type = (newData as any).block_type || 'lunch';
                    form.description = (newData as any).description || '';
                }
            },
            { immediate: true },
        );

        watch(
            () => props.show,
            async (newVal) => {
                if (newVal) {
                    await nextTick();
                    updateScrollButtons();
                }
            },
        );

        return {
            form,
            staff,
            timeOptions,
            frequencyOptions,
            formattedDate,
            dateDay,
            dateYear,
            dayOfWeek,
            duration,
            selectBlockType,
            updateEndTime,
            saveBlockedTime,
            initials,
            swiperContainer,
            canScrollLeft,
            canScrollRight,
            updateScrollButtons,
            scrollLeft,
            scrollRight,
        };
    },
});
</script>

<style scoped>
/* Hide scrollbar but keep functionality */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Offcanvas transitions */
.offcanvas-enter-active,
.offcanvas-leave-active {
    transition: opacity 0.3s ease;
}

.offcanvas-enter-from,
.offcanvas-leave-to {
    opacity: 0;
}

/* Backdrop transitions */
.backdrop-enter-active,
.backdrop-leave-active {
    transition: opacity 0.3s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
    opacity: 0;
}

/* Slide transitions */
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}
</style>
