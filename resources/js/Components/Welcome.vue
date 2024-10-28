<script setup>
// import { ref, onMounted } from 'vue';
// import ApplicationLogo from '@/Components/ApplicationLogo.vue';

import { ref, onMounted, computed } from "vue";
import axios from 'axios';
import { Link, usePage } from "@inertiajs/vue3";
import { ChevronLeft, Search, ChevronDown, ChevronUp, FileText, LogIn,XCircle} from 'lucide-vue-next';

///Vista Privada

const principales = ref([]);
const searchQuery = ref("");
const searchResult = ref([]);
const loading = ref(true);
const error = ref(null);
const sidebarOpen = ref(true);
const results = ref(null);
const pdfUrl = ref(null); // URL del PDF a visualizar
const pdfVisible = ref(false); // Controla si la vista del PDF está activa

// Variables de paginación
const currentPage = ref(1);
const itemsPerPage = ref(9); // Número de archivos por página

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
const previewPdf = async (id) => {
    try {
        const response = await axios.get(`/files/${id}/preview`, {
            headers: { 'Accept': 'application/json' },
            withCredentials: true
        });
        pdfUrl.value = response.data.url;
        pdfVisible.value = true; // Activar vista del PDF
    } catch (err) {
        console.error("Error al previsualizar el archivo:", err);
    }
};

// Función para cerrar la vista del PDF
const closePdfView = () => {
    pdfVisible.value = false;
    pdfUrl.value = null; // Limpiar la URL del PDF
};

/*/////////////////////////////////////////////////////////////// */ 

// Función para alternar el sidebar
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};
 
// Computed property para obtener archivos aplanados
const flattenedFiles = computed(() => {
    const files = [];
    if (results.value) {
        // Archivos de las categorías
        files.push(...results.value.archivos.filter(archivo => isPdf(archivo)));
        // Archivos de las subcategorías
        files.push(
            ...results.value.categorias.flatMap(categoria =>
                categoria.subcategorias.flatMap(subcategoria =>
                    subcategoria.files.filter(file => isPdf(file))
                )
            )
        );
    } else {
        files.push(...searchResult.value.flatMap(category =>
            category.files.filter(file => isPdf(file)) // Archivos en la categoría
        ));
        files.push(...searchResult.value.flatMap(category =>
            category.subcategorias.flatMap(subcategory =>
                subcategory.files.filter(file => isPdf(file))
            )
        ));
    }
    return files;
});

// Computed property para archivos paginados
const paginatedFiles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return flattenedFiles.value.slice(start, start + itemsPerPage.value);
});

// Funciones para paginación
const totalPages = computed(() => Math.ceil(flattenedFiles.value.length / itemsPerPage.value));

const goToPage = (page) => {
    if (page > 0 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};


///Vista Privada




// Variables reactivas para las categorías
const categoriasPrincipales = ref([]);
const categorias = ref([]);

// Función para obtener las categorías principales
const fetchCategoriasPrincipales = async () => {
    try {
        const response = await fetch('/get-categorias-principales');
        const data = await response.json();
        categoriasPrincipales.value = data;
        console.log(data);
    } catch (error) {
        console.error('Error al obtener categorías principales:', error);
    }
};

// Función para obtener las categorías
const fetchCategorias = async () => {
    try {
        const response = await fetch('/get-categorias');
        const data = await response.json();
        categorias.value = data;
        console.log(data);
    } catch (error) {
        console.error('Error al obtener categorías:', error);
    }
};

// Ejecutar las funciones cuando el componente se monte
onMounted(() => {
    fetchCategoriasPrincipales();
    fetchCategorias();
});
</script>

<template>
    <div class="flex h-screen  overflow-hidden font-sans bg-gray-100">
        <!-- flex-col -->
        <!-- <header class="sticky top-0 flex justify-between items-center p-4 bg-white shadow-md z-50">
            <div class="flex items-center">
                <a href="/" > <img src="/img/logo-letters.png" alt="Logo" class="w-10 h-10 mr-4"></a>
                <h1>Biblioteca Privada</h1>
            </div>
            <Link :href=" route( 'login' ) "
                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <LogIn class="w-5 h-5 mr-2" />
            Iniciar Sesión
            </Link>
        </header> -->

        <div class="flex flex-1 overflow-hidden">
            <aside :class="['bg-white shadow-lg transition-all duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'w-80 md:w-64 lg:w-80 fixed md:relative z-50 md:z-auto']"
       class="md:block hidden md:w-auto w-full">
    <div class="h-full overflow-y-auto overflow-x-hidden">
        <div class="p-4">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Documentos públicos</h2>
            <div v-for="(category, index) in searchResult" :key="index" class="mb-2">
                <button @click="toggleCategory(category)" class="flex justify-between items-center w-full p-2 text-left rounded transition-colors duration-200 hover:bg-gray-100">
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
                        <button @click.prevent="previewPdf(file.id)" class="text-blue-500 flex items-center w-full">
                            <FileText class="w-4 h-4 mr-2" />
                            <span class="truncate">{{ file.nombre_archivo }}</span>
                        </button>
                    </li>
                </ul>

                <div v-if="category.expanded" class="ml-4 mt-2">
                    <div v-for="(subcategory, subIndex) in category.subcategorias" :key="subIndex" class="mb-2">
                        <button @click="toggleSubcategory(subcategory)" class="flex justify-between items-center w-full p-2 text-left rounded transition-colors duration-200 hover:bg-gray-100">
                            <div class="flex items-center">
                                <img src="/img/corner-down-right.svg" alt="corner-down-right" class="w-4 h-4 mr-2" />
                                <span class="text-gray-600 truncate">{{ subcategory.nombre_categoria }}</span>
                            </div>
                            <ChevronDown v-if="!subcategory.expanded" class="w-4 h-4 text-gray-400" />
                            <ChevronUp v-else class="w-4 h-4 text-gray-400" />
                        </button>
                        <ul v-if="subcategory.expanded" class="ml-4 mt-2">
                            <li v-for="file in subcategory.files" :key="file.id">
                                <button @click.prevent="previewPdf(file.id)" class="text-blue-500 flex items-center w-full">
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
                                class="flex-1 pl-12 pr-4 py-4 text-gray-700 border-transparent placeholder-gray-400 bg-transparent focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50" />
                                <button @click="searchFiles" class="px-4 py-2 text-white bg-blue-600 rounded-r-full hover:bg-blue-700">
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
                        <div v-for=" file in paginatedFiles " :key=" file.id "
                            class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-6">
                                <FileText class="w-10 h-10 text-blue-600 mb-4" />
                                <h3 class="font-semibold text-gray-800 text-lg mb-3 truncate">{{ file.nombre_archivo }}
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
                        <button @click="previousPage" :disabled="currentPage === 1" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 text-white rounded-full" >
                            Anterior
                        </button>
                        <span class="text-blue-700">Página {{ currentPage }} de {{ totalPages }}</span>
                        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 text-white rounded-full">
                            Siguiente
                        </button>
                    </div>

                </div>
                <div v-if="pdfVisible" class="fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center">
                        <div class="relative w-3/4 h-3/4 bg-white rounded-lg overflow-hidden">
                            <button @click="closePdfView" class="absolute top-2 right-2 text-red-500">
                                <XCircle class="w-8 h-8" />
                            </button>
                            <iframe
                                :src="pdfUrl + '#toolbar=0&navpanes=0&scrollbar=0'"
                                class="w-full h-full">
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















<!-- <template>
    <div>
        <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
            <h1 class="mt-8 text-2xl font-medium text-gray-900">
                ¡Bienvenido!
            </h1>

            <p class="mt-6 text-gray-500 leading-relaxed">
                Bienvenido a la plataforma de documentación para los proyectos de la Pastoral Social-Caritas de la Arquidiócesis de Los Altos.
                Aquí encontrarás toda la información y recursos necesarios para apoyar y gestionar los proyectos de nuestra comunidad. ¡Gracias por formar parte de esta noble causa!
            </p>
        </div>

        <!-- <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900">
                        <a href="https://laravel.com/docs">Documentation</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                    Laravel has wonderful documentation covering every aspect of the framework. Whether you're new to the framework or have previous experience, we recommend reading all of the documentation from beginning to end.
                </p>

                <p class="mt-4 text-sm">
                    <a href="https://laravel.com/docs" class="inline-flex items-center font-semibold text-indigo-700">
                        Explore the documentation

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500">
                            <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900">
                        <a href="https://laracasts.com">Laracasts</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                    Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript development. Check them out, see for yourself, and massively level up your development skills in the process.
                </p>

                <p class="mt-4 text-sm">
                    <a href="https://laracasts.com" class="inline-flex items-center font-semibold text-indigo-700">
                        Start watching Laracasts

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 w-5 h-5 fill-indigo-500">
                            <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900">
                        <a href="https://tailwindcss.com/">Tailwind</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                    Laravel Jetstream is built with Tailwind, an amazing utility first CSS framework that doesn't get in your way. You'll be amazed how easily you can build and maintain fresh, modern designs with this wonderful framework at your fingertips.
                </p>
            </div>

            <div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900">
                        Authentication
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                    Authentication and registration views are included with Laravel Jetstream, as well as support for user email verification and resetting forgotten passwords. So, you're free to get started with what matters most: building your application.
                </p>
            </div>
        </div> -->
    <!-- </div>
</template> --> 