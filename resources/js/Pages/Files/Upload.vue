<script setup>
import { ref, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CustomTag from '@/Components/CustomTag.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import NavLinkFiles from '@/Components/NavLinkFiles.vue';
import TagModal from '@/Components/TagModal.vue';
import CategoryModal from '@/Components/CategoryModal.vue';
import SubcategoryModal from '@/Components/SubcategoryModal.vue';
import axios from 'axios';

const categoriasPrincipales = ref( [] );
const subcategorias = ref( [] );
const etiquetas = ref( [] );
const selectedTags = ref( [] );
const showFileModal = ref( false );
const showTagModal = ref( false );
const showCategoryModal = ref( false ); // Estado para mostrar el modal de categoría
const showSubcategoryModal = ref( false ); // Estado para mostrar el modal de subcategoría
const modalMessage = ref( '' );
const selectedFile = ref( null );
const filePreviewUrl = ref( null );
const form = ref( {
    estado: 0,
    publico: 0,
    categoria: '',
    subcategoria: '',
    tag: '',
} );
const notifications = ref( [] );
const resetTags = ref( false );
const selectedEtiqueta = ref( null ); // Mueve selectedEtiqueta aquí
const selectedCategoria = ref( null ); // Estado para la categoría seleccionada
const selectedSubcategoria = ref( null ); // Estado para la subcategoría seleccionada

// Función para actualizar las etiquetas seleccionadas
const updateSelectedTags = ( newTags ) => {
    selectedTags.value = newTags;
    form.value.tag = selectedTags.value.join( ',' );
};

onMounted( async () => {
    const response = await axios.get( '/categories' );
    categoriasPrincipales.value = response.data.principales;
    await loadTags();
} );

watch( () => form.value.categoria, async ( newCategoriaId ) => {
    if ( newCategoriaId ) {
        const response = await axios.get( `/categories/${ newCategoriaId }/subcategories` );
        subcategorias.value = response.data;
    } else {
        subcategorias.value = [];
    }
} );

const handleFileUpload = ( event ) => {
    const file = event.target.files[ 0 ];
    if ( file ) {
        const validTypes = [ 'application/pdf' ];
        if ( !validTypes.includes( file.type ) ) {
            showNotification( 'error', 'Tipo de archivo no permitido. Solo se permiten archivos PDF.' );
            return;
        }
        selectedFile.value = file;
        filePreviewUrl.value = URL.createObjectURL( file ); // Crear URL para la previsualización
    }
};

const validateAndUploadFile = async () => {
    if ( !selectedFile.value ) {
        showNotification( 'error', 'Por favor, selecciona un archivo primero.' );
        return;
    }
    if ( !form.value.categoria ) {
        showNotification( 'error', 'Por favor, selecciona una categoría.' );
        return;
    }

    try {
        await uploadFile();
    } catch ( error ) {
        if ( error.response && error.response.status === 409 ) {
            showFileModal.value = true;
            modalMessage.value = error.response.data.message;
        } else {
            showNotification( 'error', 'Error al subir el archivo' );
        }
    }
};

const uploadFile = async ( action = '' ) => {
    const formData = new FormData();
    formData.append( 'file', selectedFile.value );
    formData.append( 'estado', form.value.estado );
    formData.append( 'publico', form.value.publico );
    formData.append( 'categoria', form.value.categoria );
    formData.append( 'subcategoria', form.value.subcategoria );
    formData.append( 'tag', form.value.tag );

    if ( action ) {
        formData.append( 'action', action );
    }

    const response = await axios.post( '/files/upload', formData );
    if ( response.status === 200 ) {
        showNotification( 'success', 'Archivo subido exitosamente.' );
        selectedFile.value = null;
        filePreviewUrl.value = null; // Limpiar la URL de previsualización
        form.value.categoria = '';
        form.value.subcategoria = '';
        form.value.tag = '';
        resetTags.value = true;
        setTimeout( () => resetTags.value = false, 0 );
    }
};

const handleModalAction = async ( action ) => {
    showFileModal.value = false;
    await uploadFile( action );
};

// Función para mostrar notificaciones
const showNotification = ( type, message ) => {
    const id = Date.now();
    notifications.value.push( { id, type, message } );
    setTimeout( () => {
        notifications.value = notifications.value.filter( notification => notification.id !== id );
    }, 5000 );
};

// Modal form functions
const openModal = () => {
    showTagModal.value = true;
};

const loadTags = async () => {
    try {
        const response = await axios.get( '/tags', { withCredentials: true } );
        etiquetas.value = response.data;
    } catch ( err ) {
        showNotification( 'error', err.response?.data?.message || 'Error desconocido al cargar las etiquetas' );
    }
};

const selectEtiqueta = ( etiqueta ) => {
    selectedEtiqueta.value = etiqueta;
    showTagModal.value = true;
};

const openCategoryModal = () => {
    selectedCategoria.value = null;
    showCategoryModal.value = true;
};

const openSubcategoryModal = () => {
    selectedSubcategoria.value = null;
    showSubcategoryModal.value = true;
};

const handleCategoryUpdated = async () => {
    showCategoryModal.value = false;
    const response = await axios.get( '/categories' );
    categoriasPrincipales.value = response.data.principales;
};

const handleSubcategoryUpdated = async () => {
    showSubcategoryModal.value = false;
    if ( form.value.categoria ) {
        const response = await axios.get( `/categories/${ form.value.categoria }/subcategories` );
        subcategorias.value = response.data;
    }
};
</script>

<template>
    <AppLayout title="File-Upload">
        <template #header>
            <NavLinkFiles />
        </template>

        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-6 flex flex-col lg:flex-row">
                        <!-- Previsualización del archivo PDF -->
                        <div v-if=" filePreviewUrl " class="w-full lg:w-1/2 lg:pr-6 mb-6 lg:mb-0">
                            <h2 class="text-lg font-semibold text-gray-700 mb-2">Previsualización del archivo</h2>
                            <iframe :src=" filePreviewUrl "
                                class="w-full h-96 border border-gray-200 rounded-md"></iframe>
                        </div>

                        <div :class=" [ 'w-full', { 'lg:w-1/2 lg:pl-6': filePreviewUrl } ] ">
                            <div class="space-y-6">
                                <!-- File Upload -->
                                <div class="upload">
                                    <div v-if=" selectedFile "
                                        class="flex items-center mb-4 p-4 bg-gray-100 border border-gray-200 rounded-md">
                                        <img src="/img/clip.svg" class="w-6 h-6 text-gray-600" />
                                        <span class="ml-2 text-lg text-gray-700">{{ selectedFile.name }}</span>
                                    </div>
                                    <input type="file" class="hidden" id="upload" name="file" accept=".pdf"
                                        ref="fileInput" @change=" handleFileUpload " />
                                    <label for="upload"
                                        class="flex items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition duration-300">
                                        <div class="mr-2">
                                            <img src="/img/file.svg" class="w-8 h-8 text-gray-500" />
                                        </div>
                                        <span class="text-lg text-gray-600">Arrastra o selecciona tu archivo</span>
                                    </label>
                                </div>

                                <!-- Categorías -->
                                <div>
                                    <label for="categoria"
                                        class="block text-sm font-medium text-gray-700 mb-1">Categorías
                                        disponibles</label>
                                    <div class="flex items-center space-x-2">
                                        <select id="categoria" v-model=" form.categoria "
                                            class="block w-full bg-white border border-gray-300 text-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Seleccione una categoría</option>
                                            <option v-for=" categoria in categoriasPrincipales " :key=" categoria.id "
                                                :value=" categoria.id ">
                                                {{ categoria.nombre_categoria }}
                                            </option>
                                        </select>
                                        <button @click=" openCategoryModal "
                                            class="p-2 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-300">
                                            <img src="/img/library-plus.svg" class="w-6 h-6 text-gray-600" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Subcategorías -->
                                <div>
                                    <label for="subcategoria"
                                        class="block text-sm font-medium text-gray-700 mb-1">Subcategorías
                                        disponibles</label>
                                    <div class="flex items-center space-x-2">
                                        <select id="subcategoria" v-model=" form.subcategoria "
                                            class="block w-full bg-white border border-gray-300 text-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Seleccione una subcategoría</option>
                                            <option v-for=" subcategoria in subcategorias " :key=" subcategoria.id "
                                                :value=" subcategoria.id ">
                                                {{ subcategoria.nombre_categoria }}
                                            </option>
                                        </select>
                                        <button @click=" openSubcategoryModal "
                                            class="p-2 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-300">
                                            <img src="/img/library-plus.svg" class="w-6 h-6 text-gray-600" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Etiquetas -->
                                <div>
                                    <label for="etiquetas"
                                        class="block text-sm font-medium text-gray-700 mb-1">Etiquetas
                                        disponibles</label>
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="flex-1 max-h-32 overflow-y-auto bg-white border border-gray-200 rounded-md p-2">
                                            <CustomTag :label=" 'Etiquetas' " :items=" etiquetas "
                                                :selected-items=" selectedTags "
                                                @update-selected-items=" updateSelectedTags " :reset=" resetTags " />
                                        </div>
                                        <button @click=" openModal "
                                            class="p-2 bg-gray-100 rounded-md hover:bg-gray-200 transition duration-300">
                                            <img src="/img/library-plus.svg" class="w-6 h-6 text-gray-600" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Switch para "publico" -->
                                <label class="inline-flex items-center cursor-pointer">
                                    <span class="mr-2 text-gray-700">Público</span>
                                    <div class="relative">
                                        <input type="checkbox" v-model=" form.publico " true-value="1" false-value="0"
                                            class="sr-only">
                                        <div class="block bg-gray-300 w-14 h-8 rounded-full transition duration-300">
                                        </div>
                                        <div
                                            :class=" [ 'dot absolute top-1 bg-white w-6 h-6 rounded-full transition transform duration-300', form.publico ? 'translate-x-6' : 'translate-x-0' ] ">
                                        </div>
                                    </div>
                                </label>

                                <!-- Botón para cargar archivo -->
                                <PrimaryButton @click=" validateAndUploadFile "
                                    class="w-full justify-center py-3 text-lg focus:ring-offset-2">
                                    Cargar Archivo
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para renombrar o reemplazar archivo -->
        <DialogModal :show=" showFileModal " @close="showFileModal = false">
            <template #title>Archivo Existente</template>
            <template #content>{{ modalMessage }}</template>
            <template #footer>
                <PrimaryButton @click=" () => handleModalAction( 'rename' ) "
                    class="mr-2 bg-indigo-600 hover:bg-indigo-700">Renombrar</PrimaryButton>
                <PrimaryButton @click=" () => handleModalAction( 'replace' ) " class="bg-indigo-600 hover:bg-indigo-700">
                    Reemplazar</PrimaryButton>
            </template>
        </DialogModal>

        <!-- Modal para crear/actualizar etiquetas -->
        <TagModal :show=" showTagModal " :selectedEtiqueta=" selectedEtiqueta " @update:show="showTagModal = $event"
            @tag-updated=" loadTags " />

        <!-- Modal para crear/actualizar categorías -->
        <CategoryModal :show=" showCategoryModal " :selectedCategoria=" selectedCategoria "
            @update:show="showCategoryModal = $event" @categoria-updated=" handleCategoryUpdated " />

        <!-- Modal para crear/actualizar subcategorías -->
        <SubcategoryModal :show=" showSubcategoryModal " :selectedSubcategoria=" selectedSubcategoria "
            :categoriaId=" form.categoria " @update:show="showSubcategoryModal = $event"
            @subcategoria-updated=" handleSubcategoryUpdated " />

        <!-- Renderiza las notificaciones -->
        <div class="fixed bottom-4 right-4 space-y-2">
            <ToastNotification v-for=" notification in notifications " :key=" notification.id " :type=" notification.type "
                :message=" notification.message "
                @close="notifications = notifications.filter( n => n.id !== notification.id )" />
        </div>
    </AppLayout>
</template>

<style scoped>
input:checked+.block {
    background-color: #4f46e5;
}

.block {
    transition: background-color 0.3s ease;
}

.dot {
    transition: transform 0.3s ease;
}
</style>
