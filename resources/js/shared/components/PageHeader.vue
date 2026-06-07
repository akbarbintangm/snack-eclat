<template>
    <section class="page-heading compact-heading">
        <div>
            <p class="eyebrow">{{ eyebrow }}</p>
            <h1>{{ title }}</h1>
            <p v-if="description" class="lead-text">{{ description }}</p>
        </div>
        <slot />
    </section>
</template>

<script setup lang="ts">
defineProps<{
    eyebrow: string;
    title: string;
    description?: string;
}>();
</script>
