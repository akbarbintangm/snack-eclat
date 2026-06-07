<template>
    <div v-if="message" class="error-banner" role="alert">
        <div>
            <strong>{{ message }}</strong>
            <span v-if="statusCode">HTTP {{ statusCode }}</span>
        </div>
        <button v-if="retry" class="btn btn-sm btn-outline-success" type="button" @click="retry">
            {{ t('retry') }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { t } from '../i18n';

defineProps<{
    message?: string;
    statusCode?: number;
    retry?: () => void;
}>();
</script>
