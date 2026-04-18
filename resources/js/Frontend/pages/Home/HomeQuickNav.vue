<script setup lang="ts">
import { ref } from 'vue';

type NavTarget = {
    id: string;
    label: string;
    accentClass: string;
    iconClass: string;
    iconAnimClass: string;
};

const isOpen = ref(true);

const targets: NavTarget[] = [
    {
        id: 'mobile-essentials-section',
        label: 'Mobile Essentials',
        accentClass: 'from-sky-50 to-indigo-100 ring-1 ring-sky-200/60',
        iconClass: 'text-sky-700',
        iconAnimClass: 'icon-ring',
    },
    {
        id: 'shoe-featured-products-section',
        label: 'Featured Shoes',
        accentClass: 'from-emerald-50 to-lime-100 ring-1 ring-emerald-200/60',
        iconClass: 'text-emerald-700',
        iconAnimClass: 'icon-hop',
    },
    {
        id: 'cosmetics-section',
        label: 'Cosmetics',
        accentClass: 'from-rose-50 to-pink-100 ring-1 ring-rose-200/60',
        iconClass: 'text-rose-700',
        iconAnimClass: 'icon-float',
    },
];

function scrollToTarget(targetId: string) {
    if (typeof window === 'undefined') return;

    const element = document.getElementById(targetId);
    if (!element) return;

    const headerOffset = 96;
    const y = Math.max(
        0,
        element.getBoundingClientRect().top + window.scrollY - headerOffset,
    );

    window.scrollTo({ top: y, behavior: 'smooth' });

    const url = new URL(window.location.href);
    url.hash = targetId;
    window.history.replaceState({}, '', url.toString());
}
</script>

<template>
    <div
        class="fixed right-3 bottom-24 z-[95] md:top-1/2 md:right-6 md:bottom-auto md:-translate-y-1/2"
        aria-label="Quick section navigation"
    >
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-3 scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-3 scale-95"
        >
            <div v-if="isOpen" class="relative">
                <div
                    class="rounded-2xl border border-white/70 bg-white/90 shadow-[0_18px_50px_rgba(15,23,42,0.14)] backdrop-blur-md"
                >
                    <div class="flex flex-col items-center gap-2 p-2.5">
                        <button
                            v-for="target in targets"
                            :key="target.id"
                            type="button"
                            class="group relative inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 focus:outline-none sm:h-11 sm:w-11"
                            :class="target.accentClass"
                            :aria-label="`Go to ${target.label}`"
                            :title="target.label"
                            @click="scrollToTarget(target.id)"
                        >
                            <span
                                class="pointer-events-none absolute inset-0 rounded-xl bg-white/40 opacity-0 transition group-hover:opacity-100"
                            />

                            <span
                                :class="[
                                    'relative inline-flex',
                                    target.iconAnimClass,
                                    target.iconClass,
                                ]"
                            >
                                <svg
                                    v-if="
                                        target.id ===
                                        'mobile-essentials-section'
                                    "
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 sm:h-6 sm:w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect
                                        x="7"
                                        y="2.5"
                                        width="10"
                                        height="19"
                                        rx="2.2"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        d="M11 18.2h2"
                                    />
                                </svg>

                                <svg
                                    v-else-if="
                                        target.id ===
                                        'shoe-featured-products-section'
                                    "
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 sm:h-6 sm:w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M3 16c2.2 0 4.3-.6 6.3-2l5.1 3.1c2.1 1.3 4.4 1.3 6.6 1.3V21H3v-5Z"
                                    />
                                    <path d="M9 14 8 8.5" />
                                    <path d="M14 17h1" />
                                    <path d="M17 17h1" />
                                </svg>

                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 sm:h-6 sm:w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M10 2h4" />
                                    <path
                                        d="M9 2v4l-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V8l-2-2V2"
                                    />
                                    <path d="M9 10h6" />
                                    <path
                                        d="M12 13c1 1 2 2 2 3a2 2 0 0 1-4 0c0-1 1-2 2-3Z"
                                    />
                                </svg>
                            </span>
                        </button>

                        <div class="my-1 h-px w-8 bg-neutral-200/80 sm:w-10" />

                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-neutral-200 bg-white text-neutral-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-50 hover:shadow-md focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 focus:outline-none sm:h-10 sm:w-10"
                            aria-label="Hide quick navigation"
                            title="Hide"
                            @click="isOpen = false"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-5 w-5"
                                fill="none"
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
            </div>
        </Transition>

        <Transition
            enter-active-class="transition duration-250 ease-out"
            enter-from-class="opacity-0 translate-x-3 scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-3 scale-95"
        >
            <button
                v-if="!isOpen"
                type="button"
                class="group relative inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/70 bg-white/90 text-neutral-800 shadow-[0_18px_50px_rgba(15,23,42,0.14)] backdrop-blur-md transition hover:-translate-y-0.5 hover:shadow-[0_24px_70px_rgba(15,23,42,0.18)] focus:ring-2 focus:ring-red-500/30 focus:ring-offset-2 focus:outline-none"
                aria-label="Open quick navigation"
                title="Quick navigation"
                @click="isOpen = true"
            >
                <span
                    class="absolute inset-0 rounded-2xl bg-gradient-to-br from-rose-100/30 via-white/10 to-sky-100/30 opacity-0 transition group-hover:opacity-100"
                />
                <span class="relative inline-flex items-center justify-center">
                    <span
                        class="cta-pulse absolute inline-flex h-9 w-9 rounded-xl bg-red-500/10 blur-[1px]"
                    />
                    <svg
                        viewBox="0 0 24 24"
                        class="relative h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </span>
            </button>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes ring {
    0%,
    82%,
    100% {
        transform: rotate(0deg);
    }
    86% {
        transform: rotate(12deg);
    }
    90% {
        transform: rotate(-12deg);
    }
    94% {
        transform: rotate(8deg);
    }
    98% {
        transform: rotate(-8deg);
    }
}

@keyframes hop {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-2px);
    }
}

@keyframes floaty {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-2px);
    }
}

@keyframes ctaPulse {
    0%,
    100% {
        transform: scale(0.92);
        opacity: 0.55;
    }
    50% {
        transform: scale(1.08);
        opacity: 0.9;
    }
}

.icon-ring {
    animation: ring 2.8s ease-in-out infinite;
    transform-origin: 50% 90%;
}

.icon-hop {
    animation: hop 2.4s ease-in-out infinite;
}

.icon-float {
    animation: floaty 2.7s ease-in-out infinite;
}

.cta-pulse {
    animation: ctaPulse 2.2s ease-in-out infinite;
}
</style>
