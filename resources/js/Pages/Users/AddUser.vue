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
        <div class="mb-4">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
          <input v-model="form.password_confirmation" type="password" id="password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
        </div>

        <!-- Selección de Roles -->
        <div class="mb-4">
          <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
          <select v-model="form.role" id="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-opacity-50" required>
            <option value="" disabled selected>Seleccione un rol</option>
            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
          </select>
        </div>

        <!-- Selección de Permisos -->
        <div class="mb-4">
          <label for="permissions" class="block text-sm font-medium text-gray-700">Permisos</label>
          <div class="mt-1">
            <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
              <input type="checkbox" :value="permission.name" v-model="form.permissions" class="mr-2">
              <label>{{ permission.name }}</label>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Agregar Usuario</button>
        </div>
      </form>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';

export default {
  setup() {
    const form = useForm({
      name: '',
      email: '',
      gender: '',
      password: '',
      password_confirmation: '',
      role: '',
      permissions: []
    });

    const roles = ref([]);
    const permissions = ref([]);

    const fetchRolesAndPermissions = async () => {
      try {
        const response = await axios.get('/admin/users/create');
        roles.value = response.data.roles;
        permissions.value = response.data.permissions;
      } catch (error) {
        console.error('Error fetching roles and permissions:', error);
      }
    };

    const submit = () => {
      form.post('/admin/users', {
        onSuccess: () => {
          form.reset();
        },
        onError: (errors) => {
          console.error('Error submitting form:', errors);
        }
      });
    };

    fetchRolesAndPermissions();

    return {
      form,
      roles,
      permissions,
      submit
    };
  }
};
</script>

<style scoped>
/* Añade tus estilos aquí */
</style>
