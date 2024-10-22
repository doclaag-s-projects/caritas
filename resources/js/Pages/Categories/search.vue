<template>
    <div class="categoria-container">
        <div class="buscador">
            <input type="text" placeholder="Escriba aqui para buscar una categoría..." v-model="searchQuery" />
            <select v-model="searchOption">
                <option value="nombre">Nombre</option>
                <option value="descripcion">Descripción</option>
            </select>
            <button @click="buscarCategoria">
                <img src="/img/SearchCategories.svg" alt="SearchCategories" class="w-8 h-8" />
            </button>
        </div>

        <div class="add-buttons">
            <button @click="agregarCategoria" class="btn-agregar">
                <img src="/img/Plus.svg" alt="Plus" class="w-8 h-8" />
                Añadir Categoría
            </button>
            <button @click="agregarSubcategoria" class="btn-agregar">
                <img src="/img/Plus.svg" alt="Plus" class="w-8 h-8" />
                Añadir Subcategoría
            </button>
        </div>

        <table class="tabla-categorias">
            <thead>
                <tr>
                    <th class="hidden-column">Id</th>
                    <th>Categoría</th>
                    <th class="hidden-column">Principal</th>
                    <th class="hidden-column">Padre</th>
                    <th>Ruta Completa</th>
                    <th class="hidden-column">Nivel</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="categoria in filteredCategorias" :key="categoria.id" :class="getNivelClass(categoria.nivel)">
                    <td class="hidden-column">{{ categoria.id }}</td>
                    <td>
                        <strong>{{ categoria.nombre_categoria }}</strong><br>
                        <span>{{ categoria.descripcion_categoria }}</span>
                    </td>
                    <td class="hidden-column">{{ categoria.categoria_principal }}</td>
                    <td class="hidden-column">{{ categoria.categoria_padre }}</td>
                    <td>{{ categoria.full_path }}</td>
                    <td class="hidden-column">{{ categoria.nivel }}</td>
                    <td>
                        <button @click="editarModal(categoria.id, categoria.categoria_principal)" class="btn-accion editar">
                            <img src="/img/edit.svg" alt="Edit" class="w-8 h-8" />
                        </button>
                        <button @click="confirmarEliminacion(categoria.id)" class="btn-accion eliminar">
                            <img src="/img/trash.svg" alt="Trash" class="w-8 h-8" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal Crear Categoria -->
    <div v-if="showModalCategoria" class="modal">
        <div class="modal-content">
            <span @click="showModalCategoria = false" class="close">&times;</span>
            <h2>Crear Nueva Categoría</h2>
            <form @submit.prevent="guardarCategoria">
                <label for="nombreCategoria">Nombre Categoría:</label>
                <input type="text" id="nombreCategoria" v-model="nombreCategoria" required>

                <label for="descripcionCategoria">Descripción Categoría:</label>
                <textarea id="descripcionCategoria" v-model="descripcionCategoria" required></textarea>

                <div class="modal-buttons">
                    <button type="submit" class="btn-aceptar">Aceptar</button>
                    <button type="button" @click="showModalCategoria = false" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Crear Subcategoria -->
    <div v-if="showModalSubcategoria" class="modal">
        <div class="modal-content">
            <span @click="showModalSubcategoria = false" class="close">&times;</span>
            <h2>Crear Nueva Subcategoría</h2>
            <form @submit.prevent="guardarSubcategoria">
                <label for="nombreSubcategoria">Nombre Subcategoría:</label>
                <input type="text" id="nombreSubcategoria" v-model="nombreSubcategoria" required>

                <label for="descripcionSubcategoria">Descripción Subcategoría:</label>
                <textarea id="descripcionSubcategoria" v-model="descripcionSubcategoria" required></textarea>

                <label for="categoriaPrincipal">Categoría Principal:</label>
                <select id="categoriaPrincipal" v-model="categoriaPrincipal" required>
                    <option v-for="categoria in categoriasPrincipales" :key="categoria.id" :value="categoria.id">
                        {{ categoria.nombre_categoria }}
                    </option>
                </select>

                <div class="modal-buttons">
                    <button type="submit" class="btn-aceptar">Aceptar</button>
                    <button type="button" @click="showModalSubcategoria = false" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Categoria -->
    <div v-if="showModalEditarCategoria" class="modal">
        <div class="modal-content">
            <span @click="showModalEditarCategoria = false" class="close">&times;</span>
            <h2>Editar Categoría</h2>
            <form @submit.prevent="guardarCategoriaEditada">
                <label for="nombreCategoriaEditada">Nombre Categoría:</label>
                <input type="text" id="nombreCategoriaEditada" v-model="categoriaEditada.nombre_categoria" required>

                <label for="descripcionCategoriaEditada">Descripción Categoría:</label>
                <textarea id="descripcionCategoriaEditada" v-model="categoriaEditada.descripcion_categoria" required></textarea>

                <div class="modal-buttons">
                    <button type="submit" class="btn-aceptar">Aceptar</button>
                    <button type="button" @click="showModalEditarCategoria = false" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Subcategoria -->
    <div v-if="showModalEditarSubcategoria" class="modal">
        <div class="modal-content">
            <span @click="showModalEditarSubcategoria = false" class="close">&times;</span>
            <h2>Editar Subcategoría</h2>
            <form @submit.prevent="guardarSubcategoriaEditada">
                <label for="nombreSubcategoriaEditada">Nombre Subcategoría:</label>
                <input type="text" id="nombreSubcategoriaEditada" v-model="subcategoriaEditada.nombre_categoria" required>

                <label for="descripcionSubcategoriaEditada">Descripción Subcategoría:</label>
                <textarea id="descripcionSubcategoriaEditada" v-model="subcategoriaEditada.descripcion_categoria" required></textarea>

                <label for="categoriaPrincipalEditada">Categoría Principal:</label>
                <select id="categoriaPrincipalEditada" v-model="subcategoriaEditada.categoria_padre" required>
                    <option v-for="categoria in categoriasPrincipales" :key="categoria.id" :value="categoria.id">
                        {{ categoria.nombre_categoria }}
                    </option>
                </select>

                <div class="modal-buttons">
                    <button type="submit" class="btn-aceptar">Aceptar</button>
                    <button type="button" @click="showModalEditarSubcategoria = false" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Confirmación Eliminación -->
    <div v-if="showConfirmacionEliminacion" class="modal">
        <div class="modal-content">
            <span @click="showConfirmacionEliminacion = false" class="close">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <div class="icono-container">
                <img src="/img/warning.svg" alt="Warning" class="icono-advertencia">
            </div>
            <p>¿Estás seguro de que deseas eliminar esta categoría?</p>
            <div class="modal-buttons">
                <button @click="eliminarCategoria(confirmarEliminacionId)" class="btn-aceptar">Eliminar</button>
                <button @click="showConfirmacionEliminacion = false" class="btn-cancelar">Cancelar</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            searchQuery: "",
            searchOption: "nombre",
            selectedName: "",
            selectedPrincipal: "",
            showModalCategoria: false,
            showModalSubcategoria: false,
            showModalEditarCategoria: false,
            showModalEditarSubcategoria: false,
            showConfirmacionEliminacion: false,
            confirmarEliminacionId: null,
            nombreCategoria: '',
            descripcionCategoria: '',
            nombreSubcategoria: '',
            descripcionSubcategoria: '',
            categoriaPrincipal: '',
            categoriaEditada: {
                id: null,
                nombre_categoria: '',
                descripcion_categoria: ''
            },
            subcategoriaEditada: {
                id: null,
                nombre_categoria: '',
                descripcion_categoria: '',
                categoria_padre: null
            },
            categorias: [],
            categoriasPrincipales: [] // Nuevo estado para categorías principales
        };
    },
    computed: {
        filteredCategorias() {
            let filtered = this.categorias;
            if (this.searchQuery) {
                if (this.searchOption === "nombre") {
                    filtered = filtered.filter((categoria) => categoria.nombre_categoria.toLowerCase().includes(this.searchQuery.toLowerCase()));
                } else if (this.searchOption === "descripcion") {
                    filtered = filtered.filter((categoria) => categoria.descripcion_categoria.toLowerCase().includes(this.searchQuery.toLowerCase()));
                }
            }
            return filtered;
        }
    },
    methods: {
        async obtenerCategoriasRecursivas() {
            try {
                const response = await axios.get('/categorias/recursivas');
                this.categorias = response.data;
            } catch (error) {
                console.error('Error al obtener las categorías recursivas:', error);
            }
        },
        async obtenerCategoriasPrincipales() {
            try {
                const response = await axios.get('/categorias/principales');
                this.categoriasPrincipales = response.data;
                console.log(this.categoriasPrincipales);
            } catch (error) {
                console.error('Error al obtener las categorías principales:', error);
            }
        },
        buscarCategoria() {
            console.log("Buscando categoría: ", this.searchQuery);
            this.obtenerCategoriasRecursivas();
        },
        agregarCategoria() {
            this.showModalCategoria = true;
        },
        agregarSubcategoria() {
            this.obtenerCategoriasPrincipales();
            this.showModalSubcategoria = true;
        },
        async editarModal(id, categoriaPrincipal) {
            try {
                if (categoriaPrincipal === 1) {
                    const response = await axios.get(`/categorias/${id}`);
                    this.categoriaEditada = response.data;
                    this.showModalEditarCategoria = true;
                } else {
                    const response = await axios.get(`/subcategorias/${id}`);
                    this.subcategoriaEditada = response.data;
                    this.obtenerCategoriasPrincipales();
                    this.showModalEditarSubcategoria = true;
                }
            } catch (error) {
                console.error('Error al obtener la categoría o subcategoría:', error);
            }
        },
        async guardarCategoriaEditada() {
            try {
                console.log(this.categoriaEditada);
                const response = await axios.put(`/categorias/${this.categoriaEditada.id}`, {
                    nombre_categoria: this.categoriaEditada.nombre_categoria,
                    descripcion_categoria: this.categoriaEditada.descripcion_categoria
                });
                console.log(response.data);
                this.showModalEditarCategoria = false;
                this.obtenerCategoriasRecursivas();
            } catch (error) {
                console.error('Error al actualizar la categoría:', error);
            }
        },
        async guardarSubcategoriaEditada() {
            try {
                await axios.put(`/subcategorias/${this.subcategoriaEditada.id}`, {
                    nombre_categoria: this.subcategoriaEditada.nombre_categoria,
                    descripcion_categoria: this.subcategoriaEditada.descripcion_categoria,
                    categoria_padre: this.subcategoriaEditada.categoria_padre
                });
                this.showModalEditarSubcategoria = false;
                this.obtenerCategoriasPrincipales();
            } catch (error) {
                console.error('Error al actualizar la subcategoría:', error);
            }
        },
        async guardarCategoria() {
            try {
                const response = await axios.post('/categorias/crear', {
                    nombre_categoria: this.nombreCategoria,
                    descripcion_categoria: this.descripcionCategoria,
                });
                console.log(response.data);
                this.categorias.push(response.data.categoria);
                this.showModalCategoria = false;
                this.nombreCategoria = '';
                this.descripcionCategoria = '';
                this.obtenerCategoriasRecursivas(); // Actualizar la lista de categorías
            } catch (error) {
                console.error('Error al guardar la categoría:', error);
            }
        },
        async guardarSubcategoria() {
            try {
                const response = await axios.post('/subcategorias/crear', {
                    nombre_categoria: this.nombreSubcategoria,
                    descripcion_categoria: this.descripcionSubcategoria,
                    categoria_padre: this.categoriaPrincipal,
                });
                console.log(response.data);
                this.categorias.push(response.data.subcategoria);
                this.showModalSubcategoria = false;
                this.nombreSubcategoria = '';
                this.descripcionSubcategoria = '';
                this.categoriaPrincipal = '';
                this.obtenerCategoriasRecursivas();
            } catch (error) {
                console.error('Error al guardar la subcategoría:', error);
            }
        },
       confirmarEliminacion(id) {
            this.confirmarEliminacionId = id;
            this.showConfirmacionEliminacion = true;
        },
        async eliminarCategoria(id) {
            try {
                const response = await axios.delete(`/categorias/${id}`);
                console.log(response.data);
                this.obtenerCategoriasRecursivas();
                this.showConfirmacionEliminacion = false;
            } catch (error) {
                console.error('Error al eliminar la categoría:', error);
                if (error.response && error.response.data && error.response.data.error) {
                    alert(error.response.data.error);
                }
            }
        },
        getNivelClass(nivel) {
            return `nivel-${nivel}`;
        }
    },
    mounted() {
        this.obtenerCategoriasRecursivas();
    }
};
</script>

<style scoped>
.categoria-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.buscador {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    margin-bottom: 20px;
}

.buscador input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.buscador select {
    margin-left: 10px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.buscador button {
    color: white;
    border: none;
    padding: 10px;
    margin-left: 10px;
    border-radius: 4px;
    cursor: pointer;
}

.add-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.btn-agregar {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 5px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.btn-agregar img {
    margin-right: 0.5rem;
}

.tabla-categorias {
    width: 100%;
    border-collapse: collapse;
}

.tabla-categorias th,
.tabla-categorias td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.tabla-categorias th {
    background-color: #1f2937;
    color: white;
}

.hidden-column {
    display: none;
}

.btn-accion {
    background: none;
    border: none;
    cursor: pointer;
}

.modal {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    text-align: start;
}

.modal-content h2 {
    display: flex;
    align-items: start;
    justify-content: start;
    font-size: 24px;
    margin-bottom: 20px;
}

.modal-content p {
    font-size: 18px;
    margin-bottom: 20px;
}

.modal-content label {
  display: block;
  margin-bottom: 5px;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.icono-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.icono-advertencia {
    width: 20%;
    height: 20%;
}

.btn-cancelar {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-aceptar {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

/* Estilos para los diferentes niveles */
.nivel-1 {
    background-color: #f5f5f5; /* Gris muy claro */
    color: black;
}

.nivel-2 {
    background-color: #bdbdbd; /* Gris medio */
    color: black;
}

</style>