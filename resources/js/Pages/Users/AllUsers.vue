<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-extrabold text-gray-900 text-center mb-10">Gestión de Usuarios, Roles y Permisos</h1>
      
      <!-- Tabla de Usuarios -->
      <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div class="flex-1 max-w-sm">
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Buscar usuarios..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <button 
            @click="openUserModal()"
            class="ml-4 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center"
          >
            <span class="mr-2">+</span>
            Agregar Usuario
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th v-for="header in tableHeaders" :key="header" 
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img class="h-10 w-10 rounded-full" 
                           :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`" 
                           :alt="user.name">
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.gender }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ user.roles[0]?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button @click="editUser(user)" class="text-blue-600 hover:text-blue-900">
                    Editar
                  </button>
                  <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación -->
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
            >
              Anterior
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage >= totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }"
            >
              Siguiente
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando
                <span class="font-medium">{{ startIndex + 1 }}</span>
                a
                <span class="font-medium">{{ Math.min(endIndex, filteredUsers.length) }}</span>
                de
                <span class="font-medium">{{ filteredUsers.length }}</span>
                resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="currentPage = page"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                  :class="[
                    currentPage === page
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'text-gray-500 hover:bg-gray-50',
                    page === 1 ? 'rounded-l-md' : '',
                    page === totalPages ? 'rounded-r-md' : ''
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de Roles y Vistas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Roles -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">Roles</h2>
            <button 
              @click="openCreateRoleModal"
              class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
            >
              Crear Rol
            </button>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="role in roles" :key="role.id" class="p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                  <h3 class="text-lg font-medium text-gray-900">{{ role.name }}</h3>
                  <button @click="editRole(role)" class="text-blue-600 hover:text-blue-900">
                    Editar
                  </button>
                </div>
                <div class="flex flex-wrap gap-2">
                  <span v-for="permission in role.permissions" :key="permission.id"
                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                    {{ permission.name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Vistas -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Vistas</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div v-for="view in views" :key="view.id"
                   class="p-4 bg-gray-50 rounded-lg flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">{{ view.name }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="view.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ view.active ? 'Activo' : 'Inactivo' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Usuario (Crear/Editar) -->
      <div v-if="showUserModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold text-gray-900">
                {{ isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
              </h3>
              <button @click="closeUserModal" class="text-gray-400 hover:text-gray-600">
                <span class="sr-only">Cerrar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <form @submit.prevent="saveUser" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                  <input
                    id="name"
                    v-model="userForm.name"
                    type="text"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
                
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input
                    id="email"
                    v-model="userForm.email"
                    type="email"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>

                <template v-if="!isEditing">
                  <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input
                      id="password"
                      v-model="userForm.password"
                      type="password"
                      required
                      @input="validatePassword"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="passwordError" class="mt-1 text-xs text-red-600">{{ passwordError }}</p>
                  </div>

                  <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                    <input
                      id="confirmPassword"
                      v-model="userForm.confirmPassword"
                      type="password"
                      required
                      @input="validatePasswordMatch"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="passwordMatchError" class="mt-1 text-xs text-red-600">{{ passwordMatchError }}</p>
                  </div>
                </template>

                <div>
                  <label for="gender" class="block text-sm font-medium text-gray-700">Género</label>
                  <select
                    id="gender"
                    v-model="userForm.gender"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Seleccionar género</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700">Rol</label>
                  <select
                    id="role"
                    v-model="userForm.roleId"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Seleccionar rol</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                      {{ role.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Permisos en Modal de Usuario -->
              <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-4">Permisos del Usuario</h4>
                <div class="space-y-4">
                  <div v-for="permission in basicPermissions" :key="permission.id" 
                       class="flex items-center  justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">{{ permission.name }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        v-model="userForm.permissions"
                        :value="permission.id"
                        class="sr-only peer"
                      />
                      <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeUserModal"
                  class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="!isFormValid"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                  {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal de Editar Rol -->
      <div v-if="showEditRoleModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Editar Rol</h3>
              <button @click="closeEditRoleModal" class="text-gray-400 hover:text-gray-600">
                <span class="sr-only">Cerrar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveRole" class="space-y-4">
              <div>
                <label for="roleName" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
                <input
                  id="roleName"
                  v-model="roleForm.name"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-4">Vistas Accesibles</h4>
                <div class="space-y-4">
                  <div v-for="view in views" :key="view.id" 
                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">{{ view.name }}</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        v-model="roleForm.views"
                        :value="view.id"
                        class="sr-only peer"
                      />
                      <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="closeEditRoleModal"
                  class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  Guardar Cambios
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Mock Data
const users = ref([
  { 
    id: 1, 
    name: 'Juan Pérez', 
    email: 'juan@example.com', 
    gender: 'Masculino', 
    roles: [{ name: 'Admin' }],
    permissions: [1, 2, 3]
  },
  { 
    id: 2, 
    name: 'María García', 
    email: 'maria@example.com', 
    gender: 'Femenino', 
    roles: [{ name: 'Editor' }],
    permissions: [1, 2]
  },
  { 
    id: 3, 
    name: 'Carlos López', 
    email: 'carlos@example.com', 
    gender: 'Masculino', 
    roles: [{ name: 'Usuario' }],
    permissions: [1]
  }
])

const roles = ref([
  { 
    id: 1, 
    name: 'Admin', 
    permissions: [{ id: 1, name: 'CREAR' }, { id: 2, name: 'EDITAR' }, { id: 3, name: 'ELIMINAR' }],
    views: [1, 2, 3, 4]
  },
  { 
    id: 2, 
    name: 'Editor', 
    permissions: [{ id: 1, name: 'CREAR' }, { id: 2, name: 'EDITAR' }],
    views: [1, 2]
  },
  { 
    id: 3, 
    name: 'Usuario', 
    permissions: [{ id: 1, name: 'CREAR' }],
    views: [1]
  }
])

const views = ref([
  { id: 1, name: 'Dashboard', active: true },
  { id: 2, name: 'Usuarios', active: true },
  { id: 3, name: 'Reportes', active: true },
  { id: 4, name: 'Configuración', active: true }
])

const basicPermissions = [
  { id: 1, name: 'CREAR' },
  { id: 2, name: 'EDITAR' },
  { id: 3, name: 'ELIMINAR' }
]

// Table State
const tableHeaders = ['Usuario', 'Email', 'Género', 'Rol', 'Acciones']
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 10

// Form State
const userForm = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  gender: '',
  roleId: '',
  permissions: []
})

const roleForm = ref({
  id: null,
  name: '',
  views: []
})

// Modal State
const showUserModal = ref(false)
const showPermissionsModal = ref(false)
const showEditRoleModal = ref(false)
const isEditing = ref(false)
const selectedPermissions = ref([])
const currentUserId = ref(null)

// Validation State
const passwordError = ref('')
const passwordMatchError = ref('')

// Computed Properties
const filteredUsers = computed(() => {
  return users.value.filter(user =>
    user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => startIndex.value + itemsPerPage)
const paginatedUsers = computed(() => filteredUsers.value.slice(startIndex.value, endIndex.value))

const isFormValid = computed(() => {
  if (isEditing.value) {
    return (
      userForm.value.name &&
      userForm.value.email &&
      userForm.value.gender &&
      userForm.value.roleId
    )
  }
  
  return (
    userForm.value.name &&
    userForm.value.email &&
    userForm.value.password &&
    userForm.value.confirmPassword &&
    userForm.value.gender &&
    userForm.value.roleId &&
    !passwordError.value &&
    !passwordMatchError.value
  )
})

// Methods
const validatePassword = () => {
  if (userForm.value.password.length < 8) {
    passwordError.value = 'La contraseña debe tener al menos 8 caracteres'
    return false
  }
  passwordError.value = ''
  return true
}

const validatePasswordMatch = () => {
  if (userForm.value.password !== userForm.value.confirmPassword) {
    passwordMatchError.value = 'Las contraseñas no coinciden'
    return false
  }
  passwordMatchError.value = ''
  return true
}

// Navigation Methods
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

// Modal Methods
const openUserModal = (user = null) => {
  isEditing.value = !!user
  if (user) {
    userForm.value = {
      ...user,
      roleId: roles.value.find(r => r.name === user.roles[0].name)?.id || '',
      permissions: [...user.permissions]
    }
  } else {
    userForm.value = {
      name: '',
      email: '',
      password: '',
      confirmPassword: '',
      gender: '',
      roleId: '',
      permissions: []
    }
  }
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  isEditing.value = false
  userForm.value = {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    gender: '',
    roleId: '',
    permissions: []
  }
}

const openPermissionsModal = (userId) => {
  currentUserId.value = userId
  const user = users.value.find(u => u.id === userId)
  selectedPermissions.value = [...user.permissions]
  showPermissionsModal.value = true
}

const closePermissionsModal = () => {
  showPermissionsModal.value = false
  currentUserId.value = null
  selectedPermissions.value = []
}

const openEditRoleModal = (role) => {
  roleForm.value = {
    id: role.id,
    name: role.name,
    views: [...role.views]
  }
  showEditRoleModal.value = true
}

const closeEditRoleModal = () => {
  showEditRoleModal.value = false
  roleForm.value = {
    id: null,
    name: '',
    views: []
  }
}

// Action Methods
const saveUser = () => {
  if (!isFormValid.value) return

  const userData = {
    ...userForm.value,
    roles: [{ name: roles.value.find(r => r.id === userForm.value.roleId).name }]
  }

  if (isEditing.value) {
    const index = users.value.findIndex(u => u.id === userData.id)
    if (index !== -1) {
      users.value[index] = userData
    }
  } else {
    userData.id = users.value.length + 1
    users.value.push(userData)
  }

  closeUserModal()
}

const savePermissions = () => {
  const userIndex = users.value.findIndex(u => u.id === currentUserId.value)
  if (userIndex !== -1) {
    users.value[userIndex].permissions = [...selectedPermissions.value]
  }
  closePermissionsModal()
}

const saveRole = () => {
  const roleIndex = roles.value.findIndex(r => r.id === roleForm.value.id)
  if (roleIndex !== -1) {
    roles.value[roleIndex] = {
      ...roles.value[roleIndex],
      name: roleForm.value.name,
      views: roleForm.value.views
    }
  }
  closeEditRoleModal()
}

const editUser = (user) => {
  openUserModal(user)
}

const editRole = (role) => {
  openEditRoleModal(role)
}

const deleteUser = (userId) => {
  if (confirm('¿Está seguro de eliminar este usuario?')) {
    users.value = users.value.filter(user => user.id !== userId)
  }
}

const openCreateRoleModal = () => {
  roleForm.value = {
    id: roles.value.length + 1,
    name: '',
    views: []
  }
  showEditRoleModal.value = true
}
</script>