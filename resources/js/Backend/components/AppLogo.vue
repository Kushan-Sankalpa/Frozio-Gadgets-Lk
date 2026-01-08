<script setup lang="ts">
import AppLogoIcon from '@/Backend/components/AppLogoIcon.vue'
</script>

<template>
    <div class="flex items-center gap-2 min-w-0">
        <div class="flex aspect-square size-8 items-center justify-center rounded-md
             bg-sidebar-primary text-sidebar-primary-foreground
             ring-1 ring-white/10 shadow-[0_0_12px_rgba(255,32,0,.35)]">
            <AppLogoIcon class="fill-current text-white dark:text-black" />
        </div>

       <h1
    class="brand-neon font-extrabold uppercase tracking-wider leading-none
           font-['Orbitron',sans-serif] whitespace-nowrap truncate">
    D<span>r</span>if<span>t</span>
    Ba<span>r</span>b<span>e</span>r
</h1>

    </div>
</template>

<style scoped>


/* flickers */
@keyframes flickerBlink {

    0%,
    18%,
    22%,
    25%,
    53%,
    57%,
    100% {
        opacity: 1
    }

    20%,
    24%,
    55% {
        opacity: .25
    }
}

.flicker {
    animation: flickerBlink 2.5s infinite
}

.flicker-2 {
    animation: flickerBlink 3.0s infinite
}

.flicker-3 {
    animation: flickerBlink 2.8s infinite
}

.flicker-4 {
    animation: flickerBlink 3.3s infinite
}
.brand-neon {
    color: transparent;
    -webkit-text-stroke: 1.2px #ffffff;
    text-shadow: 0 0 0 #ffffff;
    letter-spacing: .06em;
    font-size: 1rem;        
}

@media (max-width: 639px) {
    .brand-neon {
        font-size: 1.4rem;
    }
}

</style>
