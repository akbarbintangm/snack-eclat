<template>
    <div class="date-picker-control">
        <input
            :id="id"
            ref="nativeInput"
            class="date-picker-native"
            type="date"
            :value="modelValue"
            :required="required"
            :aria-label="ariaLabel"
            :title="title"
            @input="updateValue"
            @change="updateValue"
            @focus="openPicker"
        >
        <input
            class="form-control date-control date-picker-display"
            type="text"
            :value="displayValue"
            :placeholder="placeholder"
            readonly
            tabindex="-1"
            aria-hidden="true"
        >
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { formatDateInput } from '../dateFormatter';

const props = withDefaults(defineProps<{
    modelValue?: string;
    id?: string;
    ariaLabel?: string;
    title?: string;
    required?: boolean;
    placeholder?: string;
}>(), {
    modelValue: '',
    placeholder: 'dd/mm/yyyy',
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const nativeInput = ref<(HTMLInputElement & { showPicker?: () => void }) | null>(null);
const displayValue = computed(() => formatDateInput(props.modelValue) || '');

function updateValue(event: Event): void {
    emit('update:modelValue', (event.currentTarget as HTMLInputElement).value);
}

function openPicker(): void {
    const input = nativeInput.value;

    if (!input) {
        return;
    }

    try {
        input.showPicker?.();
    } catch {
        input.focus();
    }
}
</script>
