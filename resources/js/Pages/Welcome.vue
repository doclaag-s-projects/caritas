<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { Link, usePage } from "@inertiajs/vue3";

// Variables reactivas
const principales = ref([]);
const searchQuery = ref("");
const searchResult = ref([]);
const loading = ref(true);
const error = ref(null);

// Función para obtener categorías con archivos
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

// Llamar a la función al montar el componente
onMounted(fetchCategoriesWithFiles);

// Función para expandir categorías y subcategorías
const toggleCategory = (category) => {
    category.expanded = !category.expanded;
};
const toggleSubcategory = (subcategory) => {
    subcategory.expanded = !subcategory.expanded;
};

// Función de búsqueda
const searchFiles = () => {
    if (!searchQuery.value) {
        searchResult.value = principales.value;
    } else {
        searchResult.value = principales.value.filter(category =>
            category.files.some(file =>
                file.nombre_archivo.toLowerCase().includes(searchQuery.value.toLowerCase())
            )
        );
    }
};
</script>

<template>
    <div class="biblioteca-publica">
        <header>
            <div class="header-left">
                <button class="back-button">&lt;</button>
                <img src="/img/logo-letters.png" alt="Logo" class="logo">
                <h1>Biblioteca Pública</h1>
            </div>
            <Link :href="route('login')" class="login-button">Iniciar Sesión</Link>
        </header>

        <div class="main-content">
            <aside class="sidebar">
                <h2>Documentos públicos</h2>

                <div v-for="(category, categoryIndex) in searchResult" :key="categoryIndex" class="area">
                    <button @click="toggleCategory(category)" class="area-toggle">
                        <span class="area-icon">{{ category.icon }}</span>
                        <span class="area-text">{{ category.nombre_categoria }}</span>
                        <span class="toggle-icon">{{ category.expanded ? '▲' : '▼' }}</span>
                    </button>

                    <div v-if="category.expanded">
                        <div v-for="(subcategory, subIndex) in category.subcategorias" :key="subIndex" class="sub-area">
                            <button @click="toggleSubcategory(subcategory)" class="sub-area-toggle">
                                {{ subcategory.nombre_categoria }}
                                <span class="toggle-icon">{{ subcategory.expanded ? '▲' : '▼' }}</span>
                            </button>
                            <ul v-if="subcategory.expanded">
                                <li v-for="file in subcategory.files" :key="file.id">
                                    <a :href="file.ubicacion_archivo" class="text-blue-500 hover:underline">{{
                                        file.nombre_archivo }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>

            <main>
                <div class="search-bar">
                    <input type="text" v-model="searchQuery" placeholder="Buscar archivos" class="search-input">
                    <button @click="searchFiles" class="search-button">Buscar</button>
                </div>

                <div class="document-grid">
                    <div v-for="file in searchResult.flatMap(category => category.files)" :key="file.id"
                        class="document-item">
                        {{ file.nombre_archivo }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Estilos conservados del primer código */
.biblioteca-publica {
    font-family: Arial, sans-serif;
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

header {
    position: sticky;
    top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #ccc;
    background-color: white;
    z-index: 1000;
}

.header-left {
    display: flex;
    align-items: center;
}

.back-button,
.login-button,
.search-button {
    padding: 5px 10px;
    margin: 0 10px;
}

.login-button {
    background-color: #d9534f;
    color: white;
    border: none;
    border-radius: 15px;
}

.search-button {
    background-color: #d9534f;
    color: white;
    border: none;
    border-radius: 15px;
}

.logo {
    width: 50px;
    height: 50px;
    margin-right: 10px;
}

.main-content {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.search-bar {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.search-input {
    padding: 10px;
    flex-grow: 1;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.sidebar {
    width: 350px;
    padding: 25px;
    background-color: #f8f9fa;
    overflow-y: auto;
    height: calc(100vh - 60px);
}

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.document-item {
    aspect-ratio: 1;
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    border-radius: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 16px;
    color: #495057;
}
</style>