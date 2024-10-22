<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-extrabold text-gray-900 text-center mb-10">Gestión de Usuarios, Roles y Permisos</h1>
      
      <!-- Tabla de Usuarios -->
      <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-xl font-semibold text-gray-800">Todos los Usuarios</h2>
          <button 
            @click="openCreateUserModal"
            class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Agregar Usuario
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Género</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ user.gender }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ user.roles[0]?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                  <button 
                    @click="editUser(user.id)"
                    class="text-yellow-600 hover:text-yellow-900 transition duration-200"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button 
                    @click="deleteUser(user.id)"
                    class="text-red-600 hover:text-red-900 transition duration-200"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Roles y Permisos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Roles -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Roles</h2>
            <button 
              @click="openCreateRoleModal"
              class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Crear Rol
            </button>
          </div>
          <div class="p-6">
            <div v-for="role in roles" :key="role.id" class="mb-4 p-4 bg-gray-50 rounded-lg">
              <h3 class="text-lg font-medium text-gray-900">{{ role.name }}</h3>
              <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="permission in role.permissions" :key="permission.id" 
                      class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                  {{ permission.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Permisos -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Permisos</h2>
            <button 
              @click="openCreatePermissionModal"
              class="bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-200 flex items-center"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Crear Permiso
            </button>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="permission in permissions" :key="permission.id" 
                   class="p-2 bg-gray-50 rounded-lg text-sm text-gray-700">
                {{ permission.name }}
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Modal para Crear Usuario -->
      <div v-if="showCreateUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl">
          <h2 class="text-2xl font-bold mb-6">Crear Nuevo Usuario</h2>
          <form @submit.prevent="createUser" class="grid grid-cols-2 gap-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
              <input type="text" id="name" v-model="newUser.name" required
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" id="email" v-model="newUser.email" required
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </div>
            <div>
              <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Género</label>
              <select id="gender" v-model="newUser.gender" required
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div>
              <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
              <select id="role" v-model="newUser.roleId" required
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
              </select>
            </div>
            <div class="col-span-2">
              <button type="submit"
                      class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">Crear Usuario</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modales adicionales de Crear Roles y Permisos -->
      <!-- ... -->
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      showCreateUserModal: false,
      newUser: {
        name: '',
        email: '',
        gender: 'Masculino',
        roleId: ''
      },
      users: [
        { id: 1, name: 'Juan Pérez', email: 'juan.perez@example.com', gender: 'Masculino', roles: [{ name: 'Administrador' }] },
        { id: 2, name: 'Maria Gómez', email: 'maria.gomez@example.com', gender: 'Femenino', roles: [{ name: 'Colaborador' }] },
        // Otros usuarios de ejemplo...
      ],
      roles: [
        { id: 1, name: 'Administrador', permissions: [{ id: 1, name: 'Crear Usuarios' }, { id: 2, name: 'Eliminar Usuarios' }] },
        { id: 2, name: 'Colaborador', permissions: [{ id: 3, name: 'Ver Reportes' }] }
      ],
      permissions: [
        { id: 1, name: 'Crear Usuarios' },
        { id: 2, name: 'Eliminar Usuarios' },
        { id: 3, name: 'Ver Reportes' }
      ]
    };
  },
  methods: {
    openCreateUserModal() {
      this.showCreateUserModal = true;
    },
    createUser() {
      // Agregar lógica para crear el usuario...
      this.showCreateUserModal = false;
    },
    editUser(userId) {
      // Lógica para editar usuario...
    },
    deleteUser(userId) {
      // Lógica para eliminar usuario...
    }
  }
};
</script>