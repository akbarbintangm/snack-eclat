<template>
    <div class="skeleton-stack" aria-hidden="true">
        <span v-for="line in lines" :key="line" class="skeleton-line"></span>
    </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    lines?: number;
}>(), {
    lines: 3,
});
</script>
