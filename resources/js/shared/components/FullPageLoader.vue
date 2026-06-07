<template>
    <div class="full-page-loader" role="status" aria-live="polite">
        <div class="loader-box">
            <span class="spinner-border text-success" aria-hidden="true"></span>
            <span>{{ label }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
}>();
</script>
