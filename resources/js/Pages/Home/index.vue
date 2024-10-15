<template>
  <div class="biblioteca-publica">
    <header>
      <div class="header-left">
        <button class="back-button">&lt;</button>
        <img src="/img/logo-letters.png" alt="Logo" class="logo">
        <h1>Biblioteca Publica</h1>
      </div>
      <button class="login-button" @click="goToLogin">Iniciar Sesion</button>
    </header>

    <div class="main-content">
      <aside class="sidebar">
        <h2>Documentos publico</h2>
        <div v-for="(area, areaIndex) in areas" :key="areaIndex" class="area">
          <button @click="toggleArea(areaIndex)" class="area-toggle">
            <span class="area-icon">{{ area.icon }}</span>
            <span class="area-text">{{ area.name }}</span>
            <span class="toggle-icon">{{ area.isOpen ? '▲' : '▼' }}</span>
          </button>

          <div v-if="area.isOpen">
            <div v-for="(subArea, subIndex) in area.subAreas" :key="subIndex" class="sub-area">
              <button @click="toggleSubArea(areaIndex, subIndex)" class="sub-area-toggle">
                {{ subArea.name }}
                <span class="toggle-icon">{{ subArea.isOpen ? '▲' : '▼' }}</span>
              </button>
              <ul v-if="subArea.isOpen">
                <li v-for="(item, itemIndex) in subArea.items" :key="itemIndex">
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </aside>

      <main>
        <div class="search-bar">
          <input type="text" v-model="searchQuery" placeholder="Buscar Archivos" class="search-input">
          <button @click="searchFiles" class="search-button">Buscar</button>
        </div>

        <div class="document-grid">
          <!-- posición para elementos de documento/archivo -->
          <div v-for="i in filteredDocuments.length" :key="i" class="document-item">
            {{ filteredDocuments[i] }} <!-- Esto puede reemplazarse por contenido real -->
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BibliotecaPublica',
  data() {
    return {
      areas: [
        {
          name: 'Area Social',
          icon: '👥',
          isOpen: true,
          subAreas: [
            {
              name: 'Participación ciudadana e incide',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            },
            {
              name: 'Legislación Guatemalteca',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            }
          ]
        },
        {
          name: 'Area Ambiental/Agropecuaria',
          icon: '🌱',
          isOpen: false,
          subAreas: [
            {
              name: 'Producción agrícola',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            },
            {
              name: 'Producción pecuaria',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            }
          ]
        },
        {
          name: 'Area de Salud',
          icon: '🏥',
          isOpen: false,
          subAreas: [
            {
              name: 'Seguridad Alimentaria',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            },
            {
              name: 'Medicina Alternativa',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            }
          ]
        },
        {
          name: 'Area de Educación',
          icon: '🎓',
          isOpen: false,
          subAreas: [
            {
              name: 'Educación',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            },
            {
              name: 'Alimentación Escolar',
              isOpen: false,
              items: ['Subitem 1', 'Subitem 2']
            }
          ]
        }
      ],
      searchQuery: '', // Para la búsqueda
      documents: Array.from({ length: 20 }, (_, i) => `Documento ${i + 1}`) // Lista de documentos ficticios
    };
  },
  computed: {
    filteredDocuments() {
      // Filtra los documentos según el término de búsqueda
      return this.documents.filter(doc => doc.toLowerCase().includes(this.searchQuery.toLowerCase()));
    }
  },
  methods: {
    toggleArea(areaIndex) {
      this.areas[areaIndex].isOpen = !this.areas[areaIndex].isOpen;
    },
    toggleSubArea(areaIndex, subIndex) {
      this.areas[areaIndex].subAreas[subIndex].isOpen = !this.areas[areaIndex].subAreas[subIndex].isOpen;
    },
    searchFiles() {
      // Aquí puedes manejar la lógica adicional de búsqueda si es necesario
      console.log(`Buscando archivos con la consulta: ${this.searchQuery}`);
    },
    // Método para redirigir a la pantalla de login
    goToLogin() {
      this.$router.push({ name: 'Login' });
    }
  }
}
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

.back-button, .login-button, .search-button {
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
.area-toggle {
  width: 100%;
  text-align: right;
  padding: 15px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 15px;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
  align-items: left;
  /* justify-content: flex-end; */
 
}
.area-text {
  flex-grow: 1; /* Asegura que el texto ocupe el espacio disponible */
  text-align: left;
}
.area-icon {
  margin-right: 10px;
  justify-content: flex-end;
  
}
.toggle-icon {
  font-size: 12px;
  justify-content: flex-end;

}
.sub-area {
  margin-left: 20px;
}
.sub-area-toggle {
  width: 100%;
  text-align: right;
  padding: 8px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.sub-area ul {
  list-style-type: none;
  padding: 0 0 0 15px;
  margin: 0;
}
.sub-area li {
  padding: 8px;
  font-size: 12px;
  background-color: #f1f3f5;
  margin-bottom: 2px;
}

main {
  flex-grow: 1;
  padding: 20px;
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
