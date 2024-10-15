<script setup>
import { ref, watch, onMounted } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import axios from 'axios';

const props = defineProps( {
    show: Boolean,
    selectedEtiqueta: Object,
} );

const emit = defineEmits( [ 'update:show', 'tag-updated' ] );

const nombreEtiqueta = ref( '' );
const descripcionEtiqueta = ref( '' );
const etiquetaFormError = ref( '' );

watch( () => props.selectedEtiqueta, ( newVal ) => {
    if ( newVal ) {
        nombreEtiqueta.value = newVal.nombre_etiqueta;
        descripcionEtiqueta.value = newVal.descripcion_etiqueta;
    } else {
        clearForm();
    }
} );

const submitForm = async () => {
    if ( !nombreEtiqueta.value || !descripcionEtiqueta.value ) {
        etiquetaFormError.value = 'Todos los campos son obligatorios.';
        return;
    }

    try {
        if ( props.selectedEtiqueta ) {
            // Update existing tag logic
            await axios.put( `/tags/${ props.selectedEtiqueta.id }`, {
                nombre_etiqueta: nombreEtiqueta.value,
                descripcion_etiqueta: descripcionEtiqueta.value
            } );
        } else {
            // Create new tag logic
            await axios.post( '/tags', {
                nombre_etiqueta: nombreEtiqueta.value,
                descripcion_etiqueta: descripcionEtiqueta.value,
                estado: true,
                usuarios_id: 1,
            } );
        }

        emit( 'tag-updated' );
        closeModal();
    } catch ( error ) {
        etiquetaFormError.value = 'Error al guardar la etiqueta. Inténtalo de nuevo.';
    }
};

const clearForm = () => {
    nombreEtiqueta.value = '';
    descripcionEtiqueta.value = '';
    etiquetaFormError.value = '';
};

const closeModal = () => {
    emit( 'update:show', false );
    clearForm();
};
</script>

<!-- TagModal.vue -->
<template>
    <DialogModal :show=" show " @close=" closeModal ">
        <template #title>{{ selectedEtiqueta ? 'Actualizar Etiqueta' : 'Crear Etiqueta' }}</template>
        <template #content>
            <form @submit.prevent=" submitForm " class="mb-4 p-4 bg-gray-100 border border-gray-300 rounded">
                <div class="mb-4">
                    <label for="nombre_etiqueta" class="block text-gray-700">Nombre de la Etiqueta</label>
                    <input type="text" id="nombre_etiqueta" v-model=" nombreEtiqueta "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div class="mb-4">
                    <label for="descripcion_etiqueta" class="block text-gray-700">Descripción</label>
                    <textarea id="descripcion_etiqueta" v-model=" descripcionEtiqueta "
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
                </div>
                <div v-if=" etiquetaFormError " class="text-red-500 text-center mb-4">
                    {{ etiquetaFormError }}
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click=" clearForm "
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-500 hover:bg-gray-600">
                        Limpiar
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        {{ selectedEtiqueta ? 'Actualizar Etiqueta' : 'Crear Etiqueta' }}
                    </button>
                </div>
            </form>
        </template>
    </DialogModal>
</template>
