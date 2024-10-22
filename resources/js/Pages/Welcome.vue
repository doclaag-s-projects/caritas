<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { Link, usePage } from "@inertiajs/vue3";
import { ChevronLeft, Search, ChevronDown, ChevronUp, FileText, LogIn } from 'lucide-vue-next';

const principales = ref([]);
const searchQuery = ref("");
const searchResult = ref([]);
const loading = ref(true);
const error = ref(null);
const sidebarOpen = ref(true);
const results = ref(null);  // Resultados de la búsqueda

// Función para obtener categorías y archivos
const fetchCategoriesWithFiles = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get("/categories-with-files");
        principales.value = response.data.principales || [];
        searchResult.value = principales.value;
    } catch (err) {
        error.value = err.response?.data?.message || "Error al cargar categorías y archivos";
    } finally {
        loading.value = false;
    }
};

onMounted(fetchCategoriesWithFiles);

// Funciones para expandir categorías y subcategorías
const toggleCategory = (category) => {
    category.expanded = !category.expanded;
};
const toggleSubcategory = (subcategory) => {
    subcategory.expanded = !subcategory.expanded;
};

// Función de búsqueda
const searchFiles = async () => {
    loading.value = true;
    error.value = null;
    results.value = null;

    try {
        const response = await axios.get(`search?nombre=${searchQuery.value}`);
        results.value = response.data;  // Asignar los resultados obtenidos
    } catch (err) {
        error.value = "Error al realizar la búsqueda.";
    } finally {
        loading.value = false;
    }
};

// Función para verificar si el archivo es un PDF
const isPdf = (file) => {
    return file.nombre_archivo.toLowerCase().endsWith('.pdf');
};

// Función para previsualizar archivos PDF
const previewPdf = async (id) => {
    try {
        const response = await axios.get(`/files/${id}/preview`, {
            headers: {
                'Accept': 'application/json'
            },
            withCredentials: true
        });
        const fileUrl = response.data.url;

        // Abre el PDF en una nueva pestaña del navegador
        window.open(fileUrl, '_blank');
    } catch (err) {
        alert("Error al previsualizar el archivo.");
    }
};

// Función para alternar el sidebar
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Filtrar los archivos planos
const flattenedFiles = computed(() => {
    return searchResult.value.flatMap(category => 
        category.subcategorias.flatMap(subcategory => 
            subcategory.files
        )
    );
});
</script>


<template>
    <div class="flex flex-col h-screen overflow-hidden font-sans bg-gray-100">
        <header class="sticky top-0 flex justify-between items-center p-4 bg-white shadow-md z-50">
            <div class="flex items-center">
                <img src="/img/logo-letters.png" alt="Logo" class="w-10 h-10 mr-4">
                <h1 class="text-2xl font-bold text-gray-800">Biblioteca Pública</h1>
            </div>
            <Link :href="route('login')" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <LogIn class="w-5 h-5 mr-2" />
                Iniciar Sesión
            </Link>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <aside :class="['w-92 bg-white shadow-lg overflow-y-auto transition-all duration-300 ease-in-out', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
                <div class="p-4">
                    <h2 class="text-lg font-bold mb-4 text-gray-800">Documentos públicos</h2>
                    <div v-for="(category, categoryIndex) in searchResult" :key="categoryIndex" class="mb-2">
                        <button @click="toggleCategory(category)" class="flex justify-between items-center w-full p-2 text-left hover:bg-gray-100 rounded transition-colors">
                            <span class="text-gray-700">{{ category.nombre_categoria }}</span>
                            <ChevronDown v-if="!category.expanded" class="w-5 h-5 text-gray-500" />
                            <ChevronUp v-else class="w-5 h-5 text-gray-500" />
                        </button>

                        <div v-if="category.expanded" class="ml-4">
                            <div v-for="(subcategory, subIndex) in category.subcategorias" :key="subIndex" class="mb-2">
                                <button @click="toggleSubcategory(subcategory)" class="flex justify-between items-center w-full p-2 text-left hover:bg-gray-100 rounded transition-colors">
                                    <span class="text-gray-600">{{ subcategory.nombre_categoria }}</span>
                                    <ChevronDown v-if="!subcategory.expanded" class="w-4 h-4 text-gray-400" />
                                    <ChevronUp v-else class="w-4 h-4 text-gray-400" />
                                </button>
                                <ul v-if="subcategory.expanded" class="ml-4 space-y-1">
                                    <li v-for="file in subcategory.files" :key="file.id" class="py-1">
                                        <a :href="file.ubicacion_archivo" class="flex items-center text-blue-600 hover:text-blue-800 hover:underline">
                                            <FileText class="w-4 h-4 mr-2" />
                                            {{ file.nombre_archivo }}
                                        </a>
                                        <button v-if="isPdf(file)" @click="previewPdf(file.id)" class="ml-2 text-sm text-blue-500 hover:underline">Previsualizar PDF</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-4xl mx-auto">
                    <div class="flex mb-6 bg-white rounded-lg shadow-md">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Buscar archivos por nombre"
                            class="flex-1 p-3 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button 
                            @click="searchFiles" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <Search class="w-5 h-5" />
                        </button>
                    </div>

                    <div v-if="loading" class="text-center py-10">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-4 text-gray-600">Cargando documentos...</p>
                    </div>

                    <div v-else-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ error }}</p>
                    </div>

                    <div v-else-if="flattenedFiles.length === 0" class="text-center py-10">
                        <FileText class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                        <p class="text-xl font-semibold text-gray-700">No se encontraron documentos</p>
                        <p class="text-gray-500 mt-2">Intenta con una búsqueda diferente</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Resultados de Categorías (mostrando solo los archivos PDF, ignorando subcategorías, en un diseño de grilla) -->
                        <div v-for="file in results?.categorias.flatMap(categoria => categoria.subcategorias.flatMap(subcategoria => subcategoria.files)).filter(file => isPdf(file))" :key="file.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="p-4">
                                <FileText class="w-8 h-8 text-blue-600 mb-2" />
                                <h3 class="font-semibold text-gray-800 mb-2">{{ file.nombre_archivo }}</h3>
                                <a href="#" @click.prevent="previewPdf(file.id)" class="text-blue-600 hover:underline">Previsualizar PDF</a>
                            </div>
                        </div>

                        <!-- Resultados de Archivos (cada archivo en su propia tarjeta) -->
                        <div v-for="archivo in results?.archivos.filter(archivo => isPdf(archivo))" :key="archivo.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="p-4">
                                <FileText class="w-8 h-8 text-blue-600 mb-2" />
                                <h3 class="font-semibold text-gray-800 mb-2">{{ archivo.nombre_archivo }}</h3>
                                <a href="#" @click.prevent="previewPdf(archivo.id)" class="text-blue-600 hover:underline">Previsualizar PDF</a>
                            </div>
                        </div>

                        <!-- Archivos por Etiquetas (cada archivo por etiqueta en su propia tarjeta) -->
                        <div v-for="archivo in results?.archivosPorEtiquetas.filter(archivo => isPdf(archivo))" :key="archivo.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="p-4">
                                <FileText class="w-8 h-8 text-blue-600 mb-2" />
                                <h3 class="font-semibold text-gray-800 mb-2">{{ archivo.nombre_archivo }}</h3>
                                <a href="#" @click.prevent="previewPdf(archivo.id)" class="text-blue-600 hover:underline">Previsualizar PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
