<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import NavLinkFiles from '@/Components/NavLinkFiles.vue';
import EditModal from '@/Components/EditModal.vue';
import DeleteModal from '@/Components/DeleteModal.vue';
import { SearchIcon, ChevronLeftIcon, ChevronRightIcon, FolderIcon, FileTextIcon, EyeIcon } from 'lucide-vue-next';

const files = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const loading = ref(true);
const error = ref(null);
const searchTerm = ref('');
const displayingToken = ref(false);
const previewUrl = ref('');

const fetchFiles = async (page = 1) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/files/list?page=${page}`, {
            headers: {
                'Accept': 'application/json'
            },
            withCredentials: true
        });
        files.value = response.data.data || response.data;
        currentPage.value = response.data.current_page || 1;
        totalPages.value = response.data.last_page || 1;
    } catch (err) {
        error.value = err.message || 'Error desconocido al cargar los archivos';
    } finally {
        loading.value = false;
    }
};

const fetchPreviewUrl = async (fileId) => {
    console.log("File ID:", fileId);
    try {
        const response = await axios.get(`/files/${fileId}/preview`, {
            headers: {
                'Accept': 'application/json'
            },
            withCredentials: true
        });
        previewUrl.value = response.data.url;
        window.open(previewUrl.value, '_blank');
    } catch (err) {
        error.value = err.message || 'Error desconocido al obtener la vista previa';
    }
};

onMounted(() => {
    fetchFiles();
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchFiles(page);
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        goToPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        goToPage(currentPage.value - 1);
    }
};

const filteredFiles = computed(() => {
    if (!searchTerm.value) {
        return files.value;
    }
    return files.value.filter(file => file.name.toLowerCase().includes(searchTerm.value.toLowerCase()));
});

const formatFileUrl = (url) => {
    const startIndex = url.indexOf('CARITASPRUEBASDOCS');
    if (startIndex === -1) {
        return [];
    }
    const formattedUrl = url.substring(startIndex + 'CARITASPRUEBASDOCS'.length + 1);
    return formattedUrl.split('/').map((part, index) => ({
        text: part,
        isLast: index === formattedUrl.split('/').length - 1
    }));
};

const handleUpdate = () => {
    fetchFiles(currentPage.value);
};

const handleDelete = () => {
    fetchFiles(currentPage.value);
};
</script>

<template>
    <AppLayout title="File List">
        <template #header>
            <NavLinkFiles />
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <!-- Search Bar -->
                <div class="mb-8">
                    <div class="relative">
                        <input type="text" placeholder="Buscar archivos..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out"
                            v-model="searchTerm" />
                        <SearchIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                    </div>
                </div>

                <!-- Mensaje de error -->
                <div v-if="error" class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                    <p class="font-bold">Error</p>
                    <p>{{ error }}</p>
                </div>

                <!-- Cargando -->
                <div v-if="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                </div>

                <!-- Lista de archivos -->
                <div v-else>
                    <div v-if="filteredFiles.length === 0" class="text-center text-gray-500 py-8">
                        No hay archivos disponibles.
                    </div>
                    <div v-else class="space-y-6">
                        <transition-group name="list" tag="ul">
                            <li v-for="file in filteredFiles" :key="file.id"
                                class="relative bg-white shadow-md rounded-lg overflow-hidden transition-transform duration-300 ease-in-out hover:scale-105 hover:shadow-lg mt-2">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ file.name }}</h3>
                                    <p class="text-sm text-gray-600 flex items-center flex-wrap">
                                        <template v-for="(part, index) in formatFileUrl(file.url)" :key="index">
                                            <FolderIcon v-if="!part.isLast"
                                                class="inline-block w-4 h-4 mx-1 text-gray-400" />
                                            <FileTextIcon v-if="part.isLast"
                                                class="inline-block w-4 h-4 mx-1 text-gray-400" />
                                            <span class="mx-1"> {{ part.text }} </span>
                                        </template>
                                    </p>
                                    <div class="absolute top-2 right-2 flex space-x-2 items-center">
                                        <EditModal :id="file.id" :name="file.name" @update="handleUpdate" />
                                        <DeleteModal :id="file.id" :name="file.name" @delete="handleDelete" />
                                    </div>
                                    <button @click="fetchPreviewUrl(file.id)"
                                        class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <EyeIcon class="h-5 w-5 mr-2" />
                                        Ver PDF
                                    </button>
                                </div>
                            </li>
                        </transition-group>

                        <!-- Paginación -->
                        <div class="flex justify-between items-center mt-8">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                class="flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                                <ChevronLeftIcon class="h-5 w-5 mr-2" />
                                Anterior
                            </button>
                            <span class="text-sm text-gray-700">
                                Página {{ currentPage }} de {{ totalPages }}
                            </span>
                            <button @click="nextPage" :disabled="currentPage === totalPages"
                                class="flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                                Siguiente
                                <ChevronRightIcon class="h-5 w-5 ml-2" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DialogModal -->
        <DialogModal :show="displayingToken" @close="displayingToken = false">
            <template #title>
                Éxito
            </template>

            <template #content>
                <div>
                    Su archivo ha sido agregado con éxito.
                </div>
            </template>

            <template #footer>
                <PrimaryButton @click="displayingToken = false">
                    Cerrar
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
