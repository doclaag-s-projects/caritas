<script setup>
import { defineProps, watch } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    items: {
        type: Array,
        required: true
    },
    selectedItems: {
        type: Array,
        required: true
    },
    reset: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update-selected-items']);

const toggleItem = (itemId) => {
    const newSelectedItems = [itemId]; // Solo permite una selección
    emit('update-selected-items', newSelectedItems);
};

// Watch for changes in the reset prop to clear selected items
watch(() => props.reset, (newVal) => {
    if (newVal) {
        emit('update-selected-items', []);
    }
});

// Capitalize filter
const capitalize = (value) => {
    if (!value) return '';
    value = value.toString();
    return value.charAt(0).toUpperCase() + value.slice(1);
};
</script>

<template>
    <div class="space-y-8 p-4">
        <div class="space-y-2">
            <label class="text-sm font-medium">{{ label }}</label>
            <div class="flex flex-wrap gap-2">
                <div v-for="item in items" :key="item.id" class="px-3 py-1 rounded-full text-sm cursor-pointer bg-green-500 text-white"
                    :class="selectedItems.includes(item.id) ? 'ring-2 ring-offset-2 ring-green-700' : ''"
                    @click="toggleItem(item.id)">
                    {{ capitalize(item.nombre_etiqueta) }}
                </div>
            </div>
        </div>
    </div>
</template>