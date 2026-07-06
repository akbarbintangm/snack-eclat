<template>
    <div v-if="meta && (always || meta.last_page > 1)" class="pagination-bar">
        <span>{{ t('page') }} {{ meta.current_page }} / {{ meta.last_page }}</span>
        <div class="pagination-pages" aria-label="Pagination">
            <button class="btn btn-sm btn-outline-success" type="button" :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">
                &lt;
            </button>
            <template v-for="(page, index) in visiblePages" :key="`${page}-${index}`">
                <span v-if="page === 'ellipsis'" class="pagination-ellipsis">...</span>
                <button
                    v-else
                    class="btn btn-sm btn-outline-success"
                    :class="{ active: page === meta.current_page }"
                    type="button"
                    :aria-current="page === meta.current_page ? 'page' : undefined"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>
            </template>
            <button class="btn btn-sm btn-outline-success" type="button" :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">
                &gt;
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { PageMeta } from '../api/types';
import { t } from '../i18n';

const props = defineProps<{
    meta?: PageMeta | null;
    always?: boolean;
}>();

const emit = defineEmits<{
    change: [page: number];
}>();

const visiblePages = computed<Array<number | 'ellipsis'>>(() => {
    const meta = props.meta;

    if (!meta) {
        return [];
    }

    if (meta.last_page <= 7) {
        return pageRange(1, meta.last_page);
    }

    const pages = new Set<number>([1, meta.last_page, meta.current_page, meta.current_page - 1, meta.current_page + 1]);

    if (meta.current_page <= 4) {
        pageRange(2, 5).forEach((page) => pages.add(page));
    }

    if (meta.current_page >= meta.last_page - 3) {
        pageRange(meta.last_page - 4, meta.last_page - 1).forEach((page) => pages.add(page));
    }

    const sortedPages = [...pages]
        .filter((page) => page >= 1 && page <= meta.last_page)
        .sort((left, right) => left - right);

    return sortedPages.reduce<Array<number | 'ellipsis'>>((items, page, index) => {
        const previous = sortedPages[index - 1];

        if (index > 0 && previous && page - previous > 1) {
            items.push('ellipsis');
        }

        items.push(page);

        return items;
    }, []);
});

function pageRange(start: number, end: number): number[] {
    return Array.from({ length: Math.max(end - start + 1, 0) }, (_, index) => start + index);
}

function goToPage(page: number): void {
    if (!props.meta || page < 1 || page > props.meta.last_page || page === props.meta.current_page) {
        return;
    }

    emit('change', page);
}
</script>
