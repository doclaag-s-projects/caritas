<script setup>
import { ref, watch, onMounted } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import axios from 'axios';

const props = defineProps( {
    show: Boolean,
    selectedSubcategoria: Object,
    categoriaId: Number,
} );

const emit = defineEmits( [ 'update:show', 'subcategoria-updated' ] );

const nombreSubcategoria = ref( '' );
const descripcionSubcategoria = ref( '' );
const subcategoriaFormError = ref( '' );
const categoriasPrincipales = ref( [] );
const categoriaPrincipal = ref( '' );

watch( () => props.selectedSubcategoria, ( newVal ) => {
    if ( newVal ) {
        nombreSubcategoria.value = newVal.nombre_categoria;
        descripcionSubcategoria.value = newVal.descripcion_categoria;
        categoriaPrincipal.value = newVal.categoria_id;
    } else {
        clearForm();
    }
} );

const submitForm = async () => {
    if ( !nombreSubcategoria.value || !descripcionSubcategoria.value || !categoriaPrincipal.value ) {
        subcategoriaFormError.value = 'Todos los campos son obligatorios.';
        return;
    }

    try {
        if ( props.selectedSubcategoria ) {
            // Update existing subcategory logic
            await axios.put( `/subcategories/${ props.selectedSubcategoria.id }`, {
                nombre_categoria: nombreSubcategoria.value,
                descripcion_categoria: descripcionSubcategoria.value,
                categoria_id: categoriaPrincipal.value,
            } );
        } else {
            // Create new subcategory logic
            const response = await axios.post( '/subcategorias/crear', {
                nombre_categoria: nombreSubcategoria.value,
                descripcion_categoria: descripcionSubcategoria.value,
                categoria_padre: categoriaPrincipal.value,
            } );
            console.log( response.data );
        }

        emit( 'subcategoria-updated' );
        closeModal();
    } catch ( error ) {
        console.error( 'Error al guardar la subcategoría:', error );
        subcategoriaFormError.value = 'Error al guardar la subcategoría. Inténtalo de nuevo.';
    }
};

const clearForm = () => {
    nombreSubcategoria.value = '';
    descripcionSubcategoria.value = '';
    subcategoriaFormError.value = '';
    categoriaPrincipal.value = '';
};

const closeModal = () => {
    emit( 'update:show', false );
    clearForm();
};

const handleCategoryUpdated = async () => {
    await loadCategoriasPrincipales();
};

const loadCategoriasPrincipales = async () => {
    try {
        const response = await axios.get( '/categories' );
        categoriasPrincipales.value = response.data.principales;
    } catch ( error ) {
        console.error( 'Error al cargar las categorías principales:', error );
    }
};

onMounted( async () => {
    await loadCategoriasPrincipales();
} );
</script>

<template>
    <DialogModal :show=" show " @close=" closeModal ">
        <template #title>{{ selectedSubcategoria ? 'Actualizar Subcategoría' : 'Crear Subcategoría' }}</template>
        <template #content>
            <form @submit.prevent=" submitForm " class="mb-4 p-4 bg-gray-100 border border-gray-300 rounded">
                <div class="mb-4">
                    <label for="nombre_subcategoria" class="block text-gray-700">Nombre de la Subcategoría</label>
                    <input type="text" id="nombre_subcategoria" v-model=" nombreSubcategoria "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="descripcion_subcategoria" class="block text-gray-700">Descripción</label>
                    <textarea id="descripcion_subcategoria" v-model=" descripcionSubcategoria "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="categoria" class="block text-gray-700">Categoría Principal</label>
                    <select id="categoria" v-model=" categoriaPrincipal "
                        class="block w-full bg-white border border-gray-300 text-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Seleccione una categoría</option>
                        <option v-for=" categoria in categoriasPrincipales " :key=" categoria.id " :value=" categoria.id ">
                            {{ categoria.nombre_categoria }}
                        </option>
                    </select>
                </div>
                <div v-if=" subcategoriaFormError " class="text-red-500 text-center mb-4">
                    {{ subcategoriaFormError }}
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click=" clearForm "
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-500 hover:bg-gray-600">
                        Limpiar
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        {{ selectedSubcategoria ? 'Actualizar Subcategoría' : 'Crear Subcategoría' }}
                    </button>
                </div>
            </form>
        </template>
    </DialogModal>
</template>
