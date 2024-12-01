<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import EditModal from '@/Components/EditModal.vue';
import DeleteModal from '@/Components/DeleteModal.vue';

const notifications = ref( [] ); // Para mostrar notificaciones
const views = ref( [] );
const roles = ref( [] );
const users = ref( [] );
const notification = ref( '' ); // Para mostrar notificaciones
const roleError = ref( null );
const assignedUsersCount = ref( 0 );

// Obtener datos
const loadRoles = async () => {
    try {
        const response = await axios.get( '/roles', {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        roles.value = response.data
            .filter( role => role.estado !== 0 ) // Filtrar roles con estado 0
            .map( role => ( {
                id: role.id,
                name: role.nombre,
                estado: role.estado,
                views: [ 1, 2, 3, 4 ]
            } ) );

        // Esto forzará la reactividad en la vista
        roles.value = [ ...roles.value ];
    } catch ( error ) {
        console.error( 'Error fetching roles:', error );
    }
};

const loadData = async () => {
    try {
        await loadRoles();
        const userResponse = await axios.get( '/usersend', {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        const viewsResponse = await axios.get( '/views', {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        users.value = userResponse.data.map( user => ( {
            id: user.id,
            name: user.name,
            email: user.email,
            gender: user.gender,
            roles: user.usuarios_roles.map( usuarioRol => ( {
                name: usuarioRol.role.nombre
            } ) ),
            permissions: [
                { crear: 'CREAR', estado: user.Crear },
                { editar: 'EDITAR', estado: user.Editar },
                { eliminar: 'ELIMINAR', estado: user.Eliminar }
            ],
        } ) );

        views.value = viewsResponse.data.map( view => ( {
            id: view.id,
            name: view.nombre,
            active: 'activo',
        } ) );

    } catch ( error ) {
        console.error( 'Error fetching data:', error );
    }
};

onMounted( loadData );

let basicPermissions = [
    { id: 1, name: 'Crear', estado: 0 },
    { id: 2, name: 'Editar', estado: 0 },
    { id: 3, name: 'Eliminar', estado: 0 }
];

// Table State
const tableHeaders = [ 'Usuario', 'Email', 'Género', 'Rol', 'Acciones' ];
const searchQuery = ref( '' );
const currentPage = ref( 1 );
const itemsPerPage = 10;

// Form State
const userForm = ref( {
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    gender: '',
    roleId: null,
    Crear: false,
    Eliminar: false,
    Editar: false,
} );

const roleForm = ref( {
    id: null,
    name: '',
    views: [],
    estado: true // Asegúrate de incluir el estado en el formulario
} );

// Modal State
const showUserModal = ref( false );
const showPermissionsModal = ref( false );
const showEditRoleModal = ref( false );
const isEditing = ref( false );
const selectedPermissions = ref( [] );
const currentUserId = ref( null );

// Validation State
const passwordError = ref( '' );
const passwordMatchError = ref( '' );

// Computed Properties
const filteredUsers = computed( () => {
    return users.value.filter( user =>
        user.name.toLowerCase().includes( searchQuery.value.toLowerCase() ) ||
        user.email.toLowerCase().includes( searchQuery.value.toLowerCase() )
    );
} );

const totalPages = computed( () => Math.ceil( filteredUsers.value.length / itemsPerPage ) );
const startIndex = computed( () => ( currentPage.value - 1 ) * itemsPerPage );
const endIndex = computed( () => startIndex.value + itemsPerPage );
const paginatedUsers = computed( () => filteredUsers.value.slice( startIndex.value, endIndex.value ) );

const isFormValid = computed( () => {
    if ( isEditing.value ) {
        return (
            userForm.value.name &&
            userForm.value.email &&
            userForm.value.gender &&
            userForm.value.roleId
        );
    }

    userForm.value.crear === true;
    return (
        userForm.value.name &&
        userForm.value.email &&
        userForm.value.password &&
        userForm.value.confirmPassword &&
        userForm.value.gender &&
        userForm.value.roleId &&
        !passwordError.value &&
        !passwordMatchError.value
    );
} );

const validatePassword = () => {
    if ( userForm.value.password.length < 8 ) {
        passwordError.value = 'La contraseña debe tener al menos 8 caracteres';
        return false;
    }
    passwordError.value = '';
    return true;
};

const validatePasswordMatch = () => {
    if ( userForm.value.password !== userForm.value.confirmPassword ) {
        passwordMatchError.value = 'Las contraseñas no coinciden';
        return false;
    }
    passwordMatchError.value = '';
    return true;
};

// Navigation Methods
const nextPage = () => {
    if ( currentPage.value < totalPages.value ) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if ( currentPage.value > 1 ) {
        currentPage.value--;
    }
};

const updatePermissionState = ( permission ) => {

    permission.estado = permission.estado ? 1 : 0;
    userForm.value.permissions[ permission.name ] = permission.estado;
    switch ( permission.name.toLowerCase() ) {
        case 'crear':
            userForm.value.Crear = permission.estado;
            break;
        case 'editar':
            userForm.value.Editar = permission.estado;
            break;
        case 'eliminar':
            userForm.value.Eliminar = permission.estado;
            break;

        default:
            break;
    }
};

// Modal Methods
const openUserModal = ( user = null ) => {

    basicPermissions = [
        { id: 1, name: 'Crear', estado: user?.permissions[ 0 ].estado },
        { id: 2, name: 'Editar', estado: user?.permissions[ 1 ].estado },
        { id: 3, name: 'Eliminar', estado: user?.permissions[ 2 ].estado }
    ];

    isEditing.value = !!user;
    if ( user ) {
        userForm.value = {
            ...user,
            roleId: roles.value.find( r => r.name === user.roles[ 0 ].name )?.id || '',
            Crear: user.permissions.find( p => p.name === 'Crear' )?.estado === 1,
            Editar: user.permissions.find( p => p.name === 'Editar' )?.estado === 1,
            Eliminar: user.permissions.find( p => p.name === 'Eliminar' )?.estado === 1,
        };


    } else {
        userForm.value = {
            id: null,
            name: '',
            email: '',
            password: '',
            confirmPassword: '',
            gender: '',
            roleId: null,
            Crear: false,
            Editar: false,
            Eliminar: false,
        };
    }
    showUserModal.value = true;
};

const closeUserModal = () => {
    showUserModal.value = false;
    isEditing.value = false;
    userForm.value = {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        gender: '',
        roleId: '',
        permissions: []
    };
};

const openPermissionsModal = ( userId ) => {
    currentUserId.value = userId;
    const user = users.value.find( u => u.id === userId );
    selectedPermissions.value = [ ...user.permissions ];
    showPermissionsModal.value = true;
};

const closePermissionsModal = () => {
    showPermissionsModal.value = false;
    currentUserId.value = null;
    selectedPermissions.value = [];
};

const openEditRoleModal = ( role ) => {
    roleForm.value = {
        id: role.id,
        name: role.name,
        views: [ ...role.views ],
        estado: role.estado // Asegúrate de incluir el estado en el formulario
    };
    showEditRoleModal.value = true;
};

const closeEditRoleModal = () => {
    showEditRoleModal.value = false;
    roleForm.value = {
        id: null,
        name: '',
        views: [],
        estado: true // Asegúrate de incluir el estado en el formulario
    };
};

const savePermissions = () => {
    const userIndex = users.value.findIndex( u => u.id === currentUserId.value );
    if ( userIndex !== -1 ) {
        users.value[ userIndex ].permissions = [ ...selectedPermissions.value ];
    }
    closePermissionsModal();
};


const saveRole = async () => {
    const roleName = roleForm.value.name.trim(); // Elimina espacios en blanco al principio y al final

    // Validar que el nombre no contenga espacios, números o símbolos
    const namePattern = /^[A-Za-zñÑáéíóúÁÉÍÓÚ]+( [A-Za-zñÑáéíóúÁÉÍÓÚ]+)*$/; // Permite letras, incluyendo ñ y tildes

    if ( !namePattern.test( roleName ) ) {
        notifications.value.push( { type: 'error', message: 'El nombre del rol solo debe contener letras.' } );
        return; // Detiene la ejecución si la validación falla
    }

    const roleData = {
        nombre: roleName,
        estado: roleForm.value.estado
    };

    try {
        if ( roleForm.value.id ) {
            // Actualizar rol existente
            await axios.put( `/roles/${ roleForm.value.id }`, roleData, {
                headers: { 'Accept': 'application/json' },
                withCredentials: true
            } );
            notifications.value.push( { type: 'success', message: 'Rol actualizado correctamente' } );
        } else {
            // Crear nuevo rol
            await axios.post( '/roles', roleData, {
                headers: { 'Accept': 'application/json' },
                withCredentials: true
            } );
            notifications.value.push( { type: 'success', message: 'Rol creado correctamente' } );
        }
    } catch ( error ) {
        console.error( roleForm.value.id ? 'Error updating role:' : 'Error creating role:', error );
        notifications.value.push( { type: 'error', message: roleForm.value.id ? 'Error al actualizar el rol' : 'Error al crear el rol' } );
    }

    await loadRoles();
    closeEditRoleModal();
};

const saveUser = async () => {
    if ( !isFormValid.value ) return;

    const userData = {
        name: userForm.value.name,
        email: userForm.value.email,
        gender: userForm.value.gender,
        rol_id: userForm.value.roleId,
        Crear: userForm.value.Crear ? '1' : '0',
        Editar: userForm.value.Editar ? '1' : '0',
        Eliminar: userForm.value.Eliminar ? '1' : '0',
    };

    if ( !isEditing.value ) {
        userData.password = userForm.value.password;
        userData.password_confirmation = userForm.value.confirmPassword;
    }

    try {
        let response;
        if ( isEditing.value ) {
            response = await axios.put( `/user/controller/${ userForm.value.id }`, userData );
        } else {
            response = await axios.post( '/users/create', userData );
        }

        notifications.value.push( { type: 'success', message: isEditing.value ? 'User updated successfully' : 'User created successfully' } );
        closeUserModal();
        await loadData();
    } catch ( error ) {
        console.error( 'Error saving user:', error.response?.data );
        notifications.value.push( { type: 'error', message: `Error ${ isEditing.value ? 'updating' : 'creating' } user` } );
    }
};

const showDeleteModal = ref( false );
const roleIdToDelete = ref( null );


const openDeleteModal = ( roleId ) => {
    roleIdToDelete.value = roleId;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    roleIdToDelete.value = null;
    assignedUsersCount.value = 0;
};

const deleteRole = async ( roleId ) => {
    try {
        const response = await axios.delete( `/roles/${ roleId }`, {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        if ( response.status === 400 ) {
            assignedUsersCount.value = response.data.assignedUsersCount;
            openDeleteModal( roleId );
        } else {
            notifications.value.push( { type: 'success', message: 'Rol eliminado correctamente' } );
            await loadRoles();
        }
    } catch ( error ) {
        if ( error.response && error.response.status === 400 ) {
            assignedUsersCount.value = error.response.data.assignedUsersCount;
            openDeleteModal( roleId );
        } else {
            console.error( 'Error deleting role:', error );
            notifications.value.push( { type: 'error', message: 'Error al eliminar el rol' } );
        }
    }
};

const confirmDeleteRole = async () => {
    try {
        await axios.delete( `/roles/${ roleIdToDelete.value }?force=true`, {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        notifications.value.push( { type: 'success', message: 'Rol y relaciones eliminados correctamente' } );
        await loadRoles();
    } catch ( error ) {
        console.error( 'Error deleting role:', error );
        notifications.value.push( { type: 'error', message: 'Error al eliminar el rol y sus relaciones' } );
    }

    closeDeleteModal();
};


const editRole = ( role ) => {
    openEditRoleModal( role );
};

const handleUpdate = ( user ) => {
    openUserModal( user );
};


const handleDelete = async () => {

    try {
        const userResponse = await axios.get( '/usersend', {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );

        users.value = userResponse.data.map( user => ( {
            id: user.id,
            name: user.name,
            email: user.email,
            gender: user.gender,
            roles: user.usuarios_roles.map( usuarioRol => ( {
                name: usuarioRol.role.nombre
            } ) ),
            permissions: [
                { crear: 'CREAR', estado: user.Crear },
                { editar: 'EDITAR', estado: user.Editar },
                { eliminar: 'ELIMINAR', estado: user.Eliminar }
            ],
        } ) );
    } catch ( err ) {
        error.value = err.message || 'Error desconocido al cargar los archivos';
    }

};

const openCreateRoleModal = () => {
    roleForm.value = {
        id: null,
        name: '',
        views: [],
        estado: true // Asegúrate de incluir el estado en el formulario
    };
    showEditRoleModal.value = true;
};

// Notificación de éxito
const showSuccessMessage = ( message ) => {
    notification.value = message;
    setTimeout( () => {
        notification.value = '';
    }, 3000 );
};
</script>

<template>
    <AppLayout title="Usuarios">
        <div class="notification-container">
            <ToastNotification v-for="(notification, index) in notifications" :key="index"
                :type="notification.type" :message="notification.message" />
        </div>


        <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-extrabold text-gray-900 text-center mb-10">Gestión de Usuarios, Roles y
                    Permisos</h1>

                <!-- Tabla de Usuarios -->
                <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex-1 max-w-sm">
                            <input v-model="searchQuery" type="text" placeholder="Buscar usuarios..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <button @click="openUserModal()"
                            class="ml-4 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
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
                                <tr v-for="user in paginatedUsers" :key="user.id"
                                    class="hover:bg-gray-50">
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
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ user.roles[0]?.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button @click="handleUpdate(user)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <img src="/img/edit.svg" alt="Delete" class="w-6 h-6">
                                            </button>
                                            <DeleteModal :id="user.id" :name="user.name" :option="'user'"
                                                @delete="handleDelete" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button @click="prevPage" :disabled="currentPage === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                            Anterior
                        </button>
                        <button @click="nextPage" :disabled="currentPage >= totalPages"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage >= totalPages }">
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
                                <button v-for="page in totalPages" :key="page"
                                    @click="currentPage = page"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                                    :class="[
                                        currentPage === page
                                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                            : 'text-gray-500 hover:bg-gray-50',
                                        page === 1 ? 'rounded-l-md' : '',
                                        page === totalPages ? 'rounded-r-md' : ''
                                    ]">
                                    {{ page }}
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Grid de Roles y Vistas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Roles -->
                    <div class="bg-white shadow-xl rounded-lg overflow-hidden ">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center modal">
                            <h2 class="text-xl font-semibold text-gray-800">Roles</h2>
                            <button @click="openCreateRoleModal"
                                class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                                Crear Rol
                            </button>
                        </div>
                        <div class="p-6">
                            <!-- Mensaje de error de validación -->
                            <div v-if="roleError" class="bg-red-100 text-red-700 p-4 rounded-lg mb-4">
                                {{ roleError }}
                            </div>
                            <div class="space-y-4">
                                <div v-for="role in roles" :key="role.id"
                                    class="p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="text-lg font-medium text-gray-900">{{ role.name }}</h3>
                                        <div>
                                            <button @click="editRole(role)"
                                                class="text-blue-600 hover:text-blue-900 mr-2">
                                                Editar
                                            </button>
                                            <button @click="deleteRole(role.id)"
                                                class="text-red-600 hover:text-red-900">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="permission in role.permissions"
                                            :key="permission.id"
                                            class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                            {{ permission.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmación de eliminación -->
                    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div
                                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11V7a1 1 0 10-2 0v2a1 1 0 002 0zm0 4a1 1 0 10-2 0v2a1 1 0 002 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                                Confirmar eliminación
                                            </h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    El rol está asignado a {{ assignedUsersCount }} usuarios. ¿Desea
                                                    continuar y eliminar el rol?
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button @click="confirmDeleteRole" type="button"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        Eliminar
                                    </button>
                                    <button @click="closeDeleteModal" type="button"
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                        Cancelar
                                    </button>
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
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Usuario (Crear/Editar) -->
                    <div v-if="showUserModal"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
                        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
                                    </h3>
                                    <button @click="closeUserModal" class="text-gray-400 hover:text-gray-600">
                                        <span class="sr-only">Cerrar</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <form @submit.prevent="saveUser" class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">

                                        <div>
                                            <label for="name"
                                                class="block text-sm font-medium text-gray-700">Nombre</label>
                                            <input id="name" v-model="userForm.name" type="text" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                        </div>

                                        <div>
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-700">Email</label>
                                            <input id="email" v-model="userForm.email" type="email" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                        </div>

                                        <template v-if="!isEditing">
                                            <div>
                                                <label for="password"
                                                    class="block text-sm font-medium text-gray-700">Contraseña</label>
                                                <input id="password" v-model="userForm.password" type="password"
                                                    required @input="validatePassword"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                <p v-if="passwordError" class="mt-1 text-xs text-red-600">{{ passwordError }}</p>
                                            </div>

                                            <div>
                                                <label for="confirmPassword"
                                                    class="block text-sm font-medium text-gray-700">Confirmar
                                                    Contraseña</label>
                                                <input id="confirmPassword" v-model="userForm.confirmPassword"
                                                    type="password" required @input="validatePasswordMatch"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                                <p v-if="passwordMatchError" class="mt-1 text-xs text-red-600">{{
                                                    passwordMatchError }}</p>
                                            </div>
                                        </template>

                                        <div>
                                            <label for="gender"
                                                class="block text-sm font-medium text-gray-700">Género</label>
                                            <select id="gender" v-model="userForm.gender" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Seleccionar género</option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="role"
                                                class="block text-sm font-medium text-gray-700">Rol</label>
                                            <select id="role" v-model="userForm.roleId" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">Seleccionar rol</option>
                                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                                    {{ role.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Permisos en Modal de Usuario (actualizado) -->
                                    <div class="mt-6">
                                        <h4 class="text-sm font-medium text-gray-700 mb-4">Permisos del Usuario</h4>
                                        <div class="space-y-4">
                                            <div v-for="permission in basicPermissions" :key="permission.id"
                                                class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                                                <input type="checkbox" :id="'permission-' + permission.id"
                                                    v-model="permission.estado"
                                                    @change="updatePermissionState(permission)"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                                <label :for="'permission-' + permission.id"
                                                    class="text-sm font-medium text-gray-700">
                                                    {{ permission.name }} -
                                                    <span :class="permission.estado ? 'text-green-500' : 'text-red-500'">
                                                        {{ permission.estado ? 'Activo' : 'Desactivado' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3 mt-6">
                                        <button type="button" @click="closeUserModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                            Cancelar
                                        </button>
                                        <button type="submit" :disabled="!isFormValid"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                                            {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
                                        </button>
                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>

                    <!-- Modal de Editar Rol -->
                    <div v-if="showEditRoleModal"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ roleForm.id ? 'Editar Rol' :
                                        'Crear Rol' }}</h3>
                                    <button @click="closeEditRoleModal" class="text-gray-400 hover:text-gray-600">
                                        <span class="sr-only">Cerrar</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <form @submit.prevent="saveRole" class="space-y-4">
                                    <div>
                                        <label for="roleName" class="block text-sm font-medium text-gray-700">Nombre del
                                            Rol</label>
                                        <input id="roleName" v-model="roleForm.name" type="text" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                    </div>

                                    <div class="mt-6">

                                        <div class="space-y-4">
                                            <div v-for="view in views" :key="view.id"
                                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <span class="text-sm font-medium text-gray-700">{{ view.name }}</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="roleForm.views" :value="view.id"
                                                        class="sr-only peer" />
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3">
                                        <button type="button" @click="closeEditRoleModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
