<template>
    <div v-if="meta && meta.last_page > 1" class="pagination-bar">
        <span>{{ t('page') }} {{ meta.current_page }} / {{ meta.last_page }}</span>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-success" type="button" :disabled="meta.current_page <= 1" @click="$emit('change', meta.current_page - 1)">
                {{ t('previous') }}
            </button>
            <button class="btn btn-sm btn-outline-success" type="button" :disabled="meta.current_page >= meta.last_page" @click="$emit('change', meta.current_page + 1)">
                {{ t('next') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { PageMeta } from '../api/types';
import { t } from '../i18n';

defineProps<{
    meta?: PageMeta | null;
}>();

defineEmits<{
    change: [page: number];
}>();
</script>
