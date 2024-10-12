<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const principales = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchCategoriesWithFiles = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get("/categories-with-files");
        principales.value = response.data.principales || [];
    } catch (err) {
        error.value =
            err.response?.data?.message ||
            "Error desconocido al cargar las categorías y archivos";
    } finally {
        loading.value = false;
    }
};

onMounted(fetchCategoriesWithFiles);

const toggleCategory = (category) => {
    category.expanded = !category.expanded;
};

const toggleSubcategory = (subcategory) => {
    subcategory.expanded = !subcategory.expanded;
};
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-custom text-custom/50">
        <div
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[var(--color-red)] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-end gap-2 py-10 lg:grid-cols-3 lg:justify-end"
                >
                    <nav v-if="props.canLogin" class="flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 link-custom"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 link-custom"
                                >Iniciar Sesión</Link
                            >
                            <Link
                                v-if="props.canRegister"
                                :href="route('register')"
                                class="rounded-md px-3 py-2 link-custom"
                                >Registrarse</Link
                            >
                        </template>
                    </nav>
                </header>

                <main class="mt-6 flex justify-center">
                    <div
                        class="grid gap-6 lg:grid-cols-1 lg:gap-8 justify-items-center"
                    >
                        <div class="py-5">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                <div
                                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                                >
                                    <div
                                        v-if="error"
                                        class="text-center text-red-500"
                                    >
                                        {{ error }}
                                    </div>
                                    <div
                                        v-if="loading"
                                        class="text-center text-gray-500"
                                    >
                                        Cargando categorías y archivos...
                                    </div>
                                    <div v-else>
                                        <ul>
                                            <li v-for="category in principales" :key="category.id" class="mb-4 p-4 bg-gray-100 border border-gray-300 rounded">
                                                <h3 @click="toggleCategory(category)" class="font-semibold text-lg text-gray-800 cursor-pointer">
                                                    {{ category.nombre_categoria }}
                                                </h3>
                                                <div v-if="category.expanded">
                                                    <!-- Mostrar archivos de la categoría principal -->
                                                    <ul>
                                                        <li v-for="file in category.files" :key="file.id" class="ml-4 mb-2 p-2 bg-gray-200 border border-gray-300 rounded">
                                                            <h4 class="font-semibold text-md text-gray-700">{{ file.nombre_archivo }}</h4>
                                                            <a :href="file.ubicacion_archivo" class="text-blue-500 hover:underline">Ver PDF</a>
                                                        </li>
                                                    </ul>
                                                    <!-- Mostrar subcategorías -->
                                                    <ul>
                                                        <li v-for="subcategory in category.subcategorias" :key="subcategory.id" class="ml-4 mb-2 p-2 bg-gray-200 border border-gray-300 rounded">
                                                            <h4 @click="toggleSubcategory(subcategory)" class="font-semibold text-md text-gray-700 cursor-pointer">
                                                                {{ subcategory.nombre_categoria }}
                                                            </h4>
                                                            <div v-if="subcategory.expanded">
                                                                <!-- Mostrar archivos de la subcategoría -->
                                                                <ul>
                                                                    <li v-for="file in subcategory.files" :key="file.id" class="ml-4 mb-2 p-2 bg-gray-300 border border-gray-400 rounded">
                                                                        <h5 class="font-semibold text-sm text-gray-600">{{ file.nombre_archivo }}</h5>
                                                                        <a :href="file.ubicacion_archivo" class="text-blue-500 hover:underline">Ver PDF</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-16 text-center text-sm text-custom">
                    Todos los derechos reservados &copy; 2024 -
                    {{ new Date().getFullYear() }} Caritas de Guatemala
                </footer>
            </div>
        </div>
    </div>
</template>
