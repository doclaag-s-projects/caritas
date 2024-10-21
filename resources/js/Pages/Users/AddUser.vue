<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
    <h1 class="text-2xl font-bold text-center mb-6">Agregar Usuario</h1>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
        <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
      </div>
      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input v-model="form.email" type="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
      </div>
      <div class="mb-4">
        <label for="gender" class="block text-sm font-medium text-gray-700">Género</label>
        <select v-model="form.gender" id="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
          <option value="" disabled selected>Seleccione un género</option>
          <option value="male">Masculino</option>
          <option value="female">Femenino</option>
          <option value="other">Otro</option>
        </select>
      </div>
      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input v-model="form.password" type="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md hover:bg-blue-700 transition duration-200">Guardar</button>
      <router-link to="/users" class="block text-center mt-4 text-blue-600 hover:underline">Volver a la lista de usuarios</router-link>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({
      name: '',
      email: '',
      gender: '', // Agregar el campo gender
      password: '',
    });

    function submit() {
      form.post(route('store.user'));
    }

    return { form, submit };
  },
};
</script>
