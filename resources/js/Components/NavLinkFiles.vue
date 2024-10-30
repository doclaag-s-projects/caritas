<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isExpanded = ref(true);
const navRef = ref(null);

const checkNavVisibility = () => {
  if (navRef.value) {
    const navWidth = navRef.value.scrollWidth;
    const containerWidth = navRef.value.offsetWidth;
    isExpanded.value = navWidth <= containerWidth;
  }
};

onMounted(() => {
  checkNavVisibility();
  window.addEventListener('resize', checkNavVisibility);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkNavVisibility);
});
</script>

<template>
  <div class="relative" ref="navRef">
    <!-- Expanded Navigation -->
    <div v-if="isExpanded" class="flex space-x-4 items-center">
      <Link
        :href="route('list')"
        :class="[
          route().current('list') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700',
          'px-1 pt-1 text-sm font-medium transition-colors duration-200'
        ]"
      >
        Lista de Archivos
      </Link>
      <Link
        :href="route('files')"
        :class="[
          route().current('files') ? 'text-gray-900 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700',
          'px-1 pt-1 text-sm font-medium transition-colors duration-200'
        ]"
      >
        Subir Archivos
      </Link>
    </div>

    <!-- Collapsed Navigation -->
    <div v-else class="relative">
      <button
        @click="isExpanded = !isExpanded"
        class="flex items-center px-3 py-2 border rounded text-gray-500 border-gray-600 hover:text-gray-800 hover:border-gray-800"
      >
        <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <title>Menu</title>
          <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
        </svg>
      </button>

      <div v-if="!isExpanded" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <div class="py-1">
          <Link
            :href="route('list')"
            :class="[
              route().current('list') ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
              'block px-4 py-2 text-sm'
            ]"
          >
            Lista de Archivos
          </Link>
          <Link
            :href="route('files')"
            :class="[
              route().current('files') ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
              'block px-4 py-2 text-sm'
            ]"
          >
            Subir Archivos
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
