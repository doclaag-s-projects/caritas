<template>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
      <h1 class="text-2xl font-bold text-center mb-6">Editar Usuario</h1>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="name" class="block text-gray-700 font-medium mb-2">Nombre</label>
          <input 
            v-model="form.name" 
            type="text" 
            id="name" 
            class="border border-gray-300 rounded-lg p-2 w-full"
          >
        </div>
        <div class="mb-4">
          <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            id="email" 
            class="border border-gray-300 rounded-lg p-2 w-full"
          >
        </div>
        <div class="mb-4">
          <label for="password" class="block text-gray-700 font-medium mb-2">Contraseña</label>
          <input 
            v-model="form.password" 
            type="password" 
            id="password" 
            class="border border-gray-300 rounded-lg p-2 w-full"
          >
        </div>
        <button 
          type="submit" 
          class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200"
        >
          Actualizar
        </button>
      </form>
      <div class="flex justify-between mt-4">
        <button 
          @click="goToUsers" 
          class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-200"
        >
          Regresar a Usuarios
        </button>
      </div>
    </div>
  </template>
  
  <script>
  import { useForm } from '@inertiajs/vue3';
  
  export default {
    props: {
      user: Object,
    },
    setup(props) {
      const form = useForm({
        name: props.user.name,
        email: props.user.email,
        password: '',
      });
  
      function submit() {
        form.put(route('update.user', props.user.id));
      }
  
      function goToUsers() {
        window.location.href = route('all.users');
      }
  
      return { form, submit, goToUsers };
    },
  };
  </script>
  