<template>
    <div>
      <Navbar />
      <div class="categorie-app">
        <h1>Gestion des Catégories</h1>
        <form @submit.prevent="saveCategorie">
          <input v-model="currentCategorie.nom" placeholder="Nom de la catégorie" required>
          <div class="button-group">
            <button type="submit" class="btn btn-success">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
            <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">Annuler</button>
          </div>
        </form>
  
        <div class="table-container">
          <div class="table-header">
            <div>ID</div>
            <div>Nom</div>
            <div>Actions</div>
          </div>
          <div class="table-row" v-for="categorie in categories" :key="categorie.id">
            <div>{{ categorie.id }}</div>
            <div>{{ categorie.nom }}</div>
            <div>
              <button @click="editCategorie(categorie)" class="btn btn-primary">Modifier</button>
              <button @click="deleteCategorie(categorie.id)" class="btn btn-danger">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import Navbar from './NavbarAdmin.vue';
  
  export default {
    name: 'CategorieApp',
    components: {
      Navbar,
    },
    setup() {
      const categories = ref([]);
      const currentCategorie = ref({
        id: null,
        nom: '',
      });
  
      const isEditing = ref(false);
  
      const fetchCategories = async () => {
        try {
          const response = await fetch('/api/categories');
          if (!response.ok) {
            throw new Error('Erreur lors du chargement des catégories');
          }
          categories.value = await response.json();
        } catch (error) {
          console.error(error);
        }
      };
  
      const saveCategorie = async () => {
        try {
          let response;
          if (currentCategorie.value.id) {
            response = await fetch(`/api/categories/update/${currentCategorie.value.id}`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify(currentCategorie.value),
            });
  
            if (!response.ok) {
              throw new Error('Erreur lors de la mise à jour de la catégorie');
            }
          } else {
            response = await fetch('/api/categories/add', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify(currentCategorie.value),
            });
  
            if (!response.ok) {
              throw new Error('Erreur lors de l\'ajout de la catégorie');
            }
          }
  
          await fetchCategories();
          resetForm();
        } catch (error) {
          console.error(error);
        }
      };
  
      const deleteCategorie = async (id) => {
        try {
          const response = await fetch(`/api/categories/delete/${id}`, {
            method: 'DELETE',
          });
  
          if (!response.ok) {
            throw new Error('Erreur lors de la suppression de la catégorie');
          }
  
          await fetchCategories();
        } catch (error) {
          console.error(error);
        }
      };
  
      const editCategorie = (categorie) => {
        currentCategorie.value = { ...categorie };
        isEditing.value = true;
      };
  
      const resetForm = () => {
        currentCategorie.value = {
          id: null,
          nom: '',
        };
        isEditing.value = false;
      };
  
      const cancelEdit = () => {
        resetForm();
      };
  
      onMounted(() => {
        fetchCategories();
      });
  
      return {
        categories,
        currentCategorie,
        isEditing,
        saveCategorie,
        deleteCategorie,
        editCategorie,
        cancelEdit,
      };
    },
  };
  </script>
  
  <style scoped>
  .categorie-app {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 1rem;
    background: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
  }
  
  .categorie-app h1 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #34495e;
  }
  
  form {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
  }
  
  input {
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }
  
  .button-group {
    display: flex;
    gap: 10px;
  }
  
  button {
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
  }
  
  .btn-primary {
    background-color: #007bff;
    color: white;
  }
  
  .btn-primary:hover {
    background-color: #0056b3;
  }
  
  .btn-success {
    background-color: #28a745;
    color: white;
  }
  
  .btn-success:hover {
    background-color: #218838;
  }
  
  .btn-secondary {
    background-color: #6c757d;
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: #5a6268;
  }
  
  .btn-danger {
    background-color: #dc3545;
    color: white;
  }
  
  .btn-danger:hover {
    background-color: #c82333;
  }
  
  .table-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  
  .table-header,
  .table-row {
    background-color: #ecf0f1;
    border-radius: 5px;
    display: contents;
    font-weight: bold;
    text-align: center;
  }
  
  .table-header {
    background-color: #bdc3c7;
    font-weight: bold;
  }
  
  .table-row div {
    padding: 0.75rem;
    transition: background-color 0.3s ease;
  }
  
  .table-row:nth-child(even) div {
    background-color: #f4f4f4;
  }
  
  .table-row div:hover {
    background-color: #dfe6e9;
  }
  
  .table-row div {
    border-right: 1px solid #ddd;
  }
  
  .table-row div:last-child {
    border-right: none;
  }
  </style>
  