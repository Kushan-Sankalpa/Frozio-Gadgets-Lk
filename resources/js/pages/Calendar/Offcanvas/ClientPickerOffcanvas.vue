<template>
    <transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-[140] flex pointer-events-auto"
        >
            <!-- Dim background -->
            <div
                class="hidden flex-1 bg-neutral-900/30 md:block"
                @click="$emit('close')"
            ></div>

            <!-- Right panel -->
            <div
                class="pointer-events-auto relative flex h-full w-full max-w-md flex-col bg-white shadow-2xl md:ml-auto md:rounded-l-2xl"
            >
                <!-- HEADER -->
                <header
                    class="flex items-center justify-between border-b px-4 py-3 sm:px-5 sm:py-4"
                >
                    <div>
                        <h2
                            class="text-base font-semibold text-neutral-900 sm:text-lg"
                        >
                            Select a client
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-neutral-500 sm:text-sm"
                        >
                            Or keep it as a walk-in.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer text-xs font-medium text-orange-600 hover:text-orange-700 sm:text-sm"
                        @click="$emit('close')"
                    >
                        Done
                    </button>
                </header>

                <!-- SEARCH + ADD / WALK-IN -->
                <div class="border-b px-4 pt-4 pb-3 sm:px-5">
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-full border border-orange-200 bg-white px-3 py-2 text-xs placeholder:text-neutral-400 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:outline-none sm:text-sm"
                            placeholder="Search client or leave empty"
                        />
                    </div>

                    <div class="mt-3 space-y-3 text-xs sm:text-sm">
                        <!-- Add new client -->
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-2 rounded-lg bg-white px-3 py-4 text-left text-orange-700 shadow-sm hover:bg-orange-50"
                            @click="$emit('add-new-client')"
                        >
                            <span
                                class="inline-flex size-6 items-center justify-center rounded-full bg-orange-100 text-orange-600"
                            >
                                +
                            </span>
                            <span class="font-medium">
                                Add new client
                            </span>
                        </button>

                        <!-- Walk-in -->
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-4 text-left hover:bg-neutral-100"
                            :class="{
                                'bg-neutral-900 text-white hover:bg-neutral-900':
                                    isWalkIn && !selectedClientId,
                            }"
                            @click="$emit('set-walk-in')"
                        >
                            <span
                                class="inline-flex size-6 items-center justify-center rounded-full bg-neutral-900 text-xs font-semibold text-white"
                            >
                                W
                            </span>
                            <div>
                                <div
                                    class="text-xs font-medium sm:text-sm"
                                >
                                    Walk-in
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- CLIENT LIST -->
                <div class="flex-1 overflow-y-auto px-2 py-4">
                    <div
                        v-for="c in filtered"
                        :key="c.id"
                        class="mb-1"
                    >
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-4 text-left text-xs hover:bg-neutral-100 sm:text-sm"
                            :class="{
                                'bg-neutral-900 text-white hover:bg-neutral-900':
                                    selectedClientId === c.id,
                            }"
                            @click="$emit('select-client', c)"
                        >
                            <div
                                class="flex size-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-semibold text-neutral-700 sm:text-sm"
                            >
                                {{ initials(c.name || '') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-xs font-medium sm:text-sm"
                                >
                                    {{ c.name }}
                                </div>
                                <div
                                    class="truncate text-[11px] text-neutral-500 sm:text-xs"
                                >
                                    <span v-if="c.email">{{ c.email }}</span>
                                    <span
                                        v-if="c.email && c.phone"
                                    >
                                        ·
                                    </span>
                                    <span v-if="c.phone">{{ c.phone }}</span>
                                </div>
                            </div>
                        </button>
                    </div>

                    <p
                        v-if="!filtered.length"
                        class="px-3 py-4 text-center text-xs text-neutral-400 sm:text-sm"
                    >
                        No clients found.
                    </p>
                </div>
            </div>
        </div>
    </transition>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

export default defineComponent({
    name: 'ClientPickerOffcanvas',

    props: {
        show: {
            type: Boolean,
            default: false,
        },
        clients: {
            type: Array as PropType<any[]>,
            default: () => [],
        },
        selectedClientId: {
            type: [Number, String, null] as PropType<
                number | string | null
            >,
            default: null,
        },
        isWalkIn: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            search: '',
        };
    },

    computed: {
        filtered(): any[] {
            const q = this.search.trim().toLowerCase();
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
    },

    watch: {
        show(val: boolean) {
            if (!val) {
                this.search = '';
            }
        },
    },

    methods: {
        initials(name: string): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 2);
        },
    },
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(16px);
}
</style>
