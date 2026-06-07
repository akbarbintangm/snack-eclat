<template>
    <PageHeader eyebrow="Swagger" :title="t('docs')" description="Dokumentasi API Snack Eclat dengan skema Bearer Auth JWT." />
    <section class="content-panel swagger-panel">
        <div ref="swaggerElement"></div>
    </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import SwaggerUI from 'swagger-ui-dist/swagger-ui-es-bundle.js';
import 'swagger-ui-dist/swagger-ui.css';
import PageHeader from '../../../shared/components/PageHeader.vue';
import { t } from '../../../shared/i18n';
import { openApiUrl } from '../api';

const swaggerElement = ref<HTMLElement | null>(null);

onMounted(() => {
    if (!swaggerElement.value) {
        return;
    }

    SwaggerUI({
        domNode: swaggerElement.value,
        url: openApiUrl,
        deepLinking: true,
        persistAuthorization: true,
    });
});
</script>
