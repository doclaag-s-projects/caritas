<template>
    <div class="biblioteca-publica">
      <header>
        <div class="header-left">
          <button class="back-button">&lt;</button>
          <img src="/img/logo-letters.png" alt="Logo" class="logo">
          <h1>Biblioteca Pública</h1>
        </div>
        <button class="login-button" @click="goToLogin">Iniciar Sesión</button>
      </header>
  
      <div class="main-content">
        <main>
          <div class="search-bar">
            <input type="text" v-model="searchTerm" placeholder="Buscar archivos o categorías" class="search-input">
            <button @click="search" class="search-button">Buscar</button>
          </div>
  
          <!-- Mostrando los resultados debajo de la barra de búsqueda -->
          <div v-if="loading" class="loading">Buscando...</div>
          <div v-if="error" class="error">{{ error }}</div>
  
          <div v-if="results && !loading" class="results-container">
            <h2>Resultados de Categorías</h2>
            <ul>
              <li v-for="categoria in results.categorias" :key="categoria.id">
                <strong>{{ categoria.nombre_categoria }}:</strong> {{ categoria.descripcion_categoria }}
                <ul v-if="categoria.subcategorias.length">
                  <li v-for="subcategoria in categoria.subcategorias" :key="subcategoria.id">
                    <strong>Subcategoría:</strong> {{ subcategoria.nombre_categoria }}
                    <ul v-if="subcategoria.files.length" class="pdf-container">
                      <!-- Filtrando solo archivos PDF y añadiendo el evento click para previsualizar -->
                      <li v-for="file in subcategoria.files.filter(file => isPdf(file))" :key="file.id" class="pdf-item">
                        <a href="#" @click.prevent="previewPdf(file.id)" class="text-blue-500 hover:underline">{{ file.nombre_archivo }}</a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>
            </ul>
  
            <h2>Resultados de Archivos</h2>
            <ul class="pdf-container">
              <!-- Filtrando solo archivos PDF y añadiendo el evento click para previsualizar -->
              <li v-for="archivo in results.archivos.filter(archivo => isPdf(archivo))" :key="archivo.id" class="pdf-item">
                <a href="#" @click.prevent="previewPdf(archivo.id)" class="text-blue-500 hover:underline">{{ archivo.nombre_archivo }}</a>
              </li>
            </ul>
  
            <h2>Archivos por Etiquetas</h2>
            <ul class="pdf-container">
              <!-- Filtrando solo archivos PDF y añadiendo el evento click para previsualizar -->
              <li v-for="archivo in results.archivosPorEtiquetas.filter(archivo => isPdf(archivo))" :key="archivo.id" class="pdf-item">
                <a href="#" @click.prevent="previewPdf(archivo.id)" class="text-blue-500 hover:underline">{{ archivo.nombre_archivo }}</a>
              </li>
            </ul>
          </div>
        </main>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from "vue";
  import axios from "axios";
  
  // Variables reactivas
  const searchTerm = ref(""); // Variable para el término de búsqueda
  const results = ref(null);  // Resultados de la búsqueda
  const loading = ref(false); // Estado de carga
  const error = ref(null);    // Estado de error
  
  // Función de búsqueda
  const search = async () => {
    loading.value = true;
    error.value = null;
    results.value = null;
  
    try {
      // Realizar la solicitud GET a la API con el término de búsqueda
      const response = await axios.get(`http://127.0.0.1:8000/search?nombre=${searchTerm.value}`);
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
  </script>
  
  <style scoped>
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
  
  .login-button,
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
  
  .results-container {
    margin-top: 20px;
  }
  
  ul {
    list-style-type: none;
    padding: 0;
  }
  
  li {
    margin: 10px 0;
  }
  
  /* Estilos personalizados para mostrar los archivos PDF en recuadros y alinearlos uno al lado del otro */
  .pdf-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .pdf-item {
    width: 200px;
    height: 300px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-align: center;
    font-size: 12px;
    padding: 10px;
  }
  
  .loading {
    margin-top: 20px;
  }
  
  .error {
    color: red;
    margin-top: 20px;
  }
  </style>