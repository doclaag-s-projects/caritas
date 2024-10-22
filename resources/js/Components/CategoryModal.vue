<script setup>
import { ref, watch } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import axios from 'axios';

const props = defineProps( {
    show: Boolean,
    selectedCategoria: Object,
} );

const emit = defineEmits( [ 'update:show', 'categoria-updated' ] );

const nombreCategoria = ref( '' );
const descripcionCategoria = ref( '' );
const categoriaFormError = ref( '' );
const categorias = ref( [] );

watch( () => props.selectedCategoria, ( newVal ) => {
    if ( newVal ) {
        nombreCategoria.value = newVal.nombre_categoria;
        descripcionCategoria.value = newVal.descripcion_categoria;
    } else {
        clearForm();
    }
} );

const submitForm = async () => {
    if ( !nombreCategoria.value || !descripcionCategoria.value ) {
        categoriaFormError.value = 'Todos los campos son obligatorios.';
        return;
    }

    try {
        if ( props.selectedCategoria ) {
            // Update existing category logic
            await axios.put( `/categories/${ props.selectedCategoria.id }`, {
                nombre_categoria: nombreCategoria.value,
                descripcion_categoria: descripcionCategoria.value
            } );
        } else {
            // Create new category logic
            const response = await axios.post( '/categorias/crear', {
                nombre_categoria: nombreCategoria.value,
                descripcion_categoria: descripcionCategoria.value,
            } );
            console.log( response.data );
            categorias.value.push( response.data.categoria );
        }

        emit( 'categoria-updated' );
        closeModal();
        obtenerCategoriasRecursivas(); // Actualizar la lista de categorías
    } catch ( error ) {
        console.error( 'Error al guardar la categoría:', error );
        categoriaFormError.value = 'Error al guardar la categoría. Inténtalo de nuevo.';
    }
};

const clearForm = () => {
    nombreCategoria.value = '';
    descripcionCategoria.value = '';
    categoriaFormError.value = '';
};

const closeModal = () => {
    emit( 'update:show', false );
    clearForm();
};

const obtenerCategoriasRecursivas = async () => {
    try {
        const response = await axios.get( '/categorias' );
        categorias.value = response.data;
    } catch ( error ) {
        console.error( 'Error al obtener las categorías:', error );
    }
};
</script>

<template>
    <DialogModal :show=" show " @close=" closeModal ">
        <template #title>{{ selectedCategoria ? 'Actualizar Categoría' : 'Crear Categoría' }}</template>
        <template #content>
            <form @submit.prevent=" submitForm " class="mb-4 p-4 bg-gray-100 border border-gray-300 rounded">
                <div class="mb-4">
                    <label for="nombre_categoria" class="block text-gray-700">Nombre de la Categoría</label>
                    <input type="text" id="nombre_categoria" v-model=" nombreCategoria "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="descripcion_categoria" class="block text-gray-700">Descripción</label>
                    <textarea id="descripcion_categoria" v-model=" descripcionCategoria "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                </div>
                <div v-if=" categoriaFormError " class="text-red-500 text-center mb-4">
                    {{ categoriaFormError }}
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click=" clearForm "
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-500 hover:bg-gray-600">
                        Limpiar
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        {{ selectedCategoria ? 'Actualizar Categoría' : 'Crear Categoría' }}
                    </button>
                </div>
            </form>
        </template>
    </DialogModal>
</template>
