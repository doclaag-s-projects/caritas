<script setup>
import { ref, onMounted, computed } from "vue";
import axios from 'axios';
import { Link } from "@inertiajs/vue3";
import {  Search, ChevronDown, ChevronUp, FileText, LogIn, XCircle } from 'lucide-vue-next';

const principales = ref( [] );
const searchQuery = ref( "" );
const searchResult = ref( [] );
const loading = ref( true );
const error = ref( null );
const sidebarOpen = ref( true );
const results = ref( null );
const pdfUrl = ref( null ); // URL del PDF a visualizar
const pdfVisible = ref( false ); // Controla si la vista del PDF está activa

// Variables de paginación
const currentPage = ref( 1 );
const itemsPerPage = ref( 9 ); // Número de archivos por página

// Función para obtener categorías y archivos
const fetchCategoriesWithFiles = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get( "/categories-with-files" );
        principales.value = response.data.principales || [];
        searchResult.value = principales.value;
    } catch ( err ) {
        error.value = err.response?.data?.message || "Error al cargar categorías y archivos";
    } finally {
        loading.value = false;
    }
};

onMounted( fetchCategoriesWithFiles );

// Funciones para expandir categorías y subcategorías
const toggleCategory = ( category ) => {
    category.expanded = !category.expanded;
};
const toggleSubcategory = ( subcategory ) => {
    subcategory.expanded = !subcategory.expanded;
};

// Función de búsqueda
const searchFiles = async () => {
    loading.value = true;
    error.value = null;
    results.value = null;

    try {
        const response = await axios.get( `search?nombre=${ searchQuery.value }` );
        results.value = response.data;
    } catch ( err ) {
        error.value = "Error al realizar la búsqueda.";
    } finally {
        loading.value = false;
    }
};

// Función para verificar si el archivo es un PDF
const isPdf = ( file ) => {
    return file.nombre_archivo.toLowerCase().endsWith( '.pdf' );
};

/*/////////////////////////////////////////////////////////////// */
// Función para previsualizar archivos PDF
const previewPdf = async ( id ) => {
    try {
        const response = await axios.get( `/files/${ id }/preview`, {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        } );
        pdfUrl.value = response.data.url;
        pdfVisible.value = true; // Activar vista del PDF
    } catch ( err ) {
        console.error( "Error al previsualizar el archivo:", err );
    }
};

// Función para cerrar la vista del PDF
const closePdfView = () => {
    pdfVisible.value = false;
    pdfUrl.value = null; // Limpiar la URL del PDF
};

/*/////////////////////////////////////////////////////////////// */

// // Función para alternar el sidebar
// const toggleSidebar = () => {
//     sidebarOpen.value = !sidebarOpen.value;
// };

// Computed property para obtener archivos aplanados
const flattenedFiles = computed( () => {
    const files = [];
    if ( results.value ) {
        // Archivos de las categorías
        files.push( ...results.value.archivos.filter( archivo => isPdf( archivo ) ) );
        // Archivos de las subcategorías
        files.push(
            ...results.value.categorias.flatMap( categoria =>
                categoria.subcategorias.flatMap( subcategoria =>
                    subcategoria.files.filter( file => isPdf( file ) )
                )
            )
        );
    } else {
        files.push( ...searchResult.value.flatMap( category =>
            category.files.filter( file => isPdf( file ) ) // Archivos en la categoría
        ) );
        files.push( ...searchResult.value.flatMap( category =>
            category.subcategorias.flatMap( subcategory =>
                subcategory.files.filter( file => isPdf( file ) )
            )
        ) );
    }
    return files;
} );

// Computed property para archivos paginados
const paginatedFiles = computed( () => {
    const start = ( currentPage.value - 1 ) * itemsPerPage.value;
    return flattenedFiles.value.slice( start, start + itemsPerPage.value );
} );

// Funciones para paginación
const totalPages = computed( () => Math.ceil( flattenedFiles.value.length / itemsPerPage.value ) );

const goToPage = ( page ) => {
    if ( page > 0 && page <= totalPages.value ) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if ( currentPage.value < totalPages.value ) {
        currentPage.value++;
    }
};

const previousPage = () => {
    if ( currentPage.value > 1 ) {
        currentPage.value--;
    }
};


// Función para alternar el sidebar
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};


</script>

<template>
    <div class="flex flex-col h-screen overflow-hidden font-sans bg-gray-100">
    <header class="sticky top-0 flex justify-between items-center p-4 bg-white shadow-md z-50">
        <div class="flex items-center space-x-4">
        <!-- Botón hamburguesa -->
            <button @click="toggleSidebar"
                class="md:hidden rounded-full bg-blue-600 text-white p-2 rounded focus:outline-none">
            <svg v-if="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            </button>

        <!-- Logo e ícono -->
        <a href="/">
            <img src="/img/logo-letters.png" alt="Logo" class="w-10 h-10">
        </a>
        <h1>Biblioteca Publica</h1>
    </div>

    <!-- Botón de inicio de sesión -->
    <Link :href="route('login')"
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        <LogIn class="w-5 h-5 mr-2" />
        Iniciar Sesión
    </Link>
</header>


        <div class="flex flex-1 overflow-hidden">

        

    <!-- Aside para menú -->
    <aside :class="['bg-white shadow-lg transition-all duration-300 ease-in-out', 
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full', 
                    'w-80 md:w-64 lg:w-80 fixed md:relative z-50 md:z-auto flex-shrink-0']">
        <div class="h-full overflow-y-auto overflow-x-hidden">
        <!-- Contenido del menú -->
        <div class="p-4">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Documentos públicos</h2>
            <div v-for="(category, index) in searchResult" :key="index" class="mb-2">
            <button @click="toggleCategory(category)"
                    class="flex justify-between items-center w-full p-2 text-left rounded transition-colors duration-200 hover:bg-gray-100">
                <div class="flex items-center">
                <img src="/img/folder.svg" alt="folder" class="w-5 h-5 mr-2" />
                <span class="text-gray-700 truncate">{{ category.nombre_categoria }}</span>
                </div>
                <ChevronDown v-if="!category.expanded" class="w-5 h-5 text-gray-500" />
                <ChevronUp v-else class="w-5 h-5 text-gray-500" />
            </button>
            <ul v-if="category.expanded" class="ml-4 mt-2">
                <!-- Archivos en la categoría -->
                <li v-for="file in category.files" :key="file.id">
                <button @click.prevent="previewPdf(file.id)"
                        class="text-blue-500 flex items-center w-full" :title="file.nombre_archivo">
                    <FileText class="w-4 h-4 mr-2" />
                    <span class="truncate">{{ file.nombre_archivo }}</span>
            </button>
                </li>
            </ul>
            <div v-if="category.expanded" class="ml-4 mt-2">
                <div v-for="(subcategory, subIndex) in category.subcategorias" :key="subIndex" class="mb-2">
                <button @click="toggleSubcategory(subcategory)"
                        class="flex justify-between items-center w-full p-2 text-left rounded transition-colors duration-200 hover:bg-gray-100">
                    <div class="flex items-center">
                    <img src="/img/corner-down-right.svg" alt="corner-down-right" class="w-4 h-4 mr-2" />
                    <span class="text-gray-600 truncate">{{ subcategory.nombre_categoria }}</span>
                    </div>
                    <ChevronDown v-if="!subcategory.expanded" class="w-4 h-4 text-gray-400" />
                    <ChevronUp v-else class="w-4 h-4 text-gray-400" />
                </button>
                <ul v-if="subcategory.expanded" class="ml-4 mt-2">
                    <li v-for="file in subcategory.files" :key="file.id">
                    <button @click.prevent="previewPdf(file.id)"
                            class="text-blue-500 flex items-center w-full" :title="file.nombre_archivo">
                        <FileText class="w-4 h-4 mr-2" />
                        <span class="truncate">{{ file.nombre_archivo }}</span>
                    </button>
                    </li>
                </ul>
                </div>
            </div>
            </div>
        </div>
        </div>
    </aside>


            <main class="flex-1 p-6 overflow-y-auto bg-gradient-to-br from-gray-50 to-gray-100">
                <div class="max-w-4xl mx-auto">
                    <div class="relative max-w-xl mx-auto mb-8">
                        <div
                            class="flex  bg-white rounded-full shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl">
                            <input type="text" v-model=" searchQuery " placeholder="Buscar archivos por nombre"
                                class="flex-1 pl-12 pr-4 py-4 text-gray-700 border-none placeholder-gray-400 bg-transparent focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50" />
                            <button @click=" searchFiles "
                                class="px-4 py-2 text-white bg-blue-600 rounded-r-full hover:bg-blue-700">
                                <Search />
                            </button>
                        </div>
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <Search class="w-5 h-5 text-gray-400" />
                        </div>
                    </div>
                    <div v-if=" loading " class="text-center py-10">
                        <div class="animate-spin rounded-full h-16 w-16  border-t-4 border-b-4 border-blue-500 mx-auto">
                        </div>
                        <p class="mt-6 text-lg text-gray-600 font-medium">Cargando documentos...</p>
                    </div>

                    <div v-else-if=" error "
                        class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md" role="alert">
                        <p class="font-bold text-lg mb-2">Error</p>
                        <p class="text-base">{{ error }}</p>
                    </div>

                    <div v-else-if=" flattenedFiles.length === 0 " class="text-center py-16">
                        <FileText class="w-24 h-24 text-gray-300 mx-auto mb-6" />
                        <p class="text-2xl font-semibold text-gray-700 mb-2">No se encontraron documentos</p>
                        <p class="text-gray-500 text-lg">Intenta con una búsqueda diferente</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="  file in paginatedFiles  " :key=" file.id "
                            class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-6">
                                <FileText class="w-10 h-10 text-blue-600 mb-4" />
                                <h3 class="font-semibold text-gray-800 text-lg mb-3 truncate":title="file.nombre_archivo" >{{ file.nombre_archivo }}
                                </h3>
                                <a href="#" @click.prevent="previewPdf( file.id )"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                                    Previsualizar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Paginación -->
                    <div class="flex justify-between items-center mt-4">
                        <button @click=" previousPage " :disabled=" currentPage === 1 "
                            class="px-4 py-2 bg-blue-600  hover:bg-blue-700 text-white rounded-full">
                            Anterior
                        </button>
                        <span class="text-blue-700">Página {{ currentPage }} de {{ totalPages }}</span>
                        <button @click=" nextPage " :disabled=" currentPage === totalPages "
                            class="px-4 py-2 bg-blue-600  hover:bg-blue-700 text-white rounded-full">
                            Siguiente
                        </button>
                    </div>

                </div>
                <div v-if="pdfVisible" class="fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
    <div class="relative w-11/12 h-5/6 bg-white rounded-lg overflow-hidden shadow-2xl">
    <button
        @click="closePdfView"
        class="absolute top-4 right-6 text-red-400 hover:text-red-600 transition-colors duration-200 focus:outline-none"
        aria-label="Close PDF viewer"
    >
        <XCircle class="w-11 h-11" />
    </button>
    <iframe
        :src="pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0'"
        class="w-full h-full"
        title="PDF Viewer"
    >
    </iframe>
    </div>
  </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

iframe {
    border: none;
}
</style>
