<template>
    <label class="inline-flex items-center cursor-pointer">
      <span class="mr-3 text-sm font-medium text-gray-700">{{ label }}</span>
      <div class="relative">
        <input
          type="checkbox"
          :checked="modelValue"
          @change="updateValue($event.target.checked)"
          class="sr-only peer"
        />
        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
      </div>
    </label>
  </template>

  <script setup>
  import { defineProps, defineEmits, watch } from 'vue';

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false,
    },
    label: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['update:modelValue']);

  const updateValue = (value) => {
    emit('update:modelValue', value);
  };

  watch(() => props.modelValue, (newValue) => {
    emit('update:modelValue', newValue);
  });
  </script>
