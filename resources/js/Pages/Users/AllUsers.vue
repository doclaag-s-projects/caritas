<template>
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
      <h1 class="text-2xl font-bold text-center mb-6">Todos los Usuarios</h1>
      <div class="mb-4 text-right">
        <button 
          @click="goToAddUser"
          class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
        >
          Agregar Usuario
        </button>
      </div>
      <table class="min-w-full bg-white border border-gray-300">
        <thead>
          <tr class="bg-gray-200 text-gray-700">
            <th class="py-3 px-4 text-left">Nombre</th>
            <th class="py-3 px-4 text-left">Email</th>
            <th class="py-3 px-4 text-left">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-100 transition duration-200">
            <td class="py-3 px-4 border-b border-gray-300">{{ user.name }}</td>
            <td class="py-3 px-4 border-b border-gray-300">{{ user.email }}</td>
            <td class="py-3 px-4 border-b border-gray-300 text-right">
              <button 
                @click="goToEditUser(user.id)"
                class="bg-yellow-500 text-white py-1 px-3 rounded-lg hover:bg-yellow-600 transition duration-200"
              >
                Editar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      users: Array,
    },
    methods: {
      goToAddUser() {
        window.location.href = route('add.user');
      },
      goToEditUser(userId) {
        window.location.href = route('edit.user', userId);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Puedes agregar estilos adicionales aquí si es necesario */
  </style>
  