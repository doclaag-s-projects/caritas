<template>
    <label class="inline-flex items-center cursor-pointer">
      <span class="mr-2 ">{{ label }}</span>
      <div class="relative">
        <input type="checkbox" :checked="modelValue" @change="updateValue($event.target.checked)" class="sr-only" />
        <div class="block bg-gray-300 dark:bg-gray-600 w-14 h-8 rounded-full transition duration-300"></div>
        <div
          :class="[
            'dot absolute top-1 bg-white w-6 h-6 rounded-full transition transform duration-300',
            modelValue ? 'translate-x-6' : 'translate-x-0'
          ]"
        ></div>
      </div>
    </label>
  </template>

  <script setup>
  import { defineProps, defineEmits } from 'vue'

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false,
    },
    label: {
      type: String,
      default: '',
    },
  })

  const emit = defineEmits(['update:modelValue'])

  const updateValue = (value) => {
    emit('update:modelValue', value)
  }
  </script>

  <style scoped>
  input:checked + .block {
    background-color: #4f46e5;
  }

  .block {
    transition: background-color 0.3s ease;
  }

  .dot {
    transition: transform 0.3s ease;
  }
  </style>
