<template>
  <div>
    <!-- Inclusion de la barre de navigation -->
    <Navbar />
    
    <div class="categorie-app">
      <h1>Gestion des Catégories</h1>

      <!-- Formulaire pour ajouter ou mettre à jour une catégorie -->
      <form @submit.prevent="saveCategorie">
        <!-- Champ pour le nom de la catégorie -->
        <input v-model="currentCategorie.nom" placeholder="Nom de la catégorie" required />
        
        <div class="button-group">
          <!-- Bouton pour soumettre le formulaire : texte change en fonction du mode (ajout ou édition) -->
          <button type="submit" class="btn btn-success">
            {{ isEditing ? 'Mettre à jour' : 'Ajouter' }}
          </button>

          <!-- Bouton pour annuler l'édition (visible uniquement en mode édition) -->
          <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">
            Annuler
          </button>
        </div>
      </form>

      <!-- Tableau pour afficher la liste des catégories -->
      <div class="table-container">
        <div class="table-header">
          <div>ID</div>
          <div>Nom</div>
          <div>Emplacement X</div>
          <div>Actions</div>
        </div>
        <!-- Affiche chaque catégorie dans une ligne -->
        <div class="table-row" v-for="categorie in categories" :key="categorie.id">
          <div>{{ categorie.id }}</div>
          <div>{{ categorie.nom }}</div>
          <div>{{ categorie.x }}</div>
          <div>
            <!-- Bouton pour passer en mode édition -->
            <button @click="editCategorie(categorie)" class="btn btn-primary">Modifier</button>
            <!-- Bouton pour supprimer une catégorie -->
            <button @click="deleteCategorie(categorie.id)" class="btn btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import Navbar from './NavbarAdmin.vue'; // Composant pour la barre de navigation

export default {
  name: 'CategorieApp',
  components: {
    Navbar,
  },
  setup() {
    // Liste des catégories récupérées depuis l'API
    const categories = ref([]);
    
    // Objet représentant la catégorie actuellement manipulée (ajout ou édition)
    const currentCategorie = ref({
      id: null,
      nom: '',
      x: 0,
    });

    // Indique si l'utilisateur est en mode édition
    const isEditing = ref(false);

    // Message d'erreur à afficher en cas de problème
    const errorMessage = ref('');

    // Fonction pour récupérer les catégories depuis l'API
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/categories'); // Appelle l'API pour obtenir les catégories
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des catégories');
        }
        categories.value = await response.json(); // Stocke les catégories dans la variable `categories`
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message; // Affiche le message d'erreur
      }
    };

    // Fonction pour enregistrer une catégorie (ajout ou mise à jour)
    const saveCategorie = async () => {
      try {
        let response;

        if (currentCategorie.value.id) {
          // Requête PUT pour mettre à jour une catégorie existante
          response = await fetch(`/api/categories/update/${currentCategorie.value.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nom: currentCategorie.value.nom }), // Envoi uniquement le nom
          });

          if (!response.ok) {
            throw new Error('Erreur lors de la mise à jour de la catégorie');
          }
        } else {
          // Requête POST pour ajouter une nouvelle catégorie
          response = await fetch('/api/categories/add', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ nom: currentCategorie.value.nom }), // Envoi le nom
          });

          if (!response.ok) {
            throw new Error('Erreur lors de l\'ajout de la catégorie');
          }
        }

        await fetchCategories(); // Recharge les catégories après l'ajout ou la mise à jour
        resetForm(); // Réinitialise le formulaire
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;
      }
    };

    // Fonction pour supprimer une catégorie
    const deleteCategorie = async (id) => {
      try {
        const response = await fetch(`/api/categories/delete/${id}`, {
          method: 'DELETE', // Suppression via DELETE
        });

        if (!response.ok) {
          throw new Error('Erreur lors de la suppression de la catégorie');
        }

        await fetchCategories(); // Recharge les catégories après la suppression
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message; // Stocke un message d'erreur si la suppression échoue
      }
    };

    // Fonction pour activer l'édition d'une catégorie
    const editCategorie = (categorie) => {
      currentCategorie.value = { ...categorie }; // Copie les données de la catégorie sélectionnée
      isEditing.value = true; // Passe en mode édition
    };

    // Réinitialise le formulaire et sort du mode édition
    const resetForm = () => {
      currentCategorie.value = {
        id: null,
        nom: '',
        x: 0,
      };
      isEditing.value = false;
    };

    // Fonction pour annuler l'édition
    const cancelEdit = () => {
      resetForm(); // Réinitialise le formulaire
    };

    // Récupère les catégories à l'initialisation du composant
    onMounted(() => {
      fetchCategories(); // Charge les catégories au montage du composant
    });

    return {
      categories,
      currentCategorie,
      isEditing,
      errorMessage,
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

input, select {
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
  grid-template-columns: repeat(4, 1fr);
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

