<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps<{
    message: string
}>()
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <div class="p-6">
            <h1 class="text-2xl font-bold">{{ message }}</h1>
        </div>
    </AppLayout>
</template>
