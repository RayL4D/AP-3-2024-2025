<template>
  <div>
    <!-- Navbar incluse dans l'interface -->
    <Navbar />

    <div class="produits-app">
      <h1>Gestion des Produits</h1>

      <!-- Formulaire pour ajouter ou modifier un produit -->
      <form @submit.prevent="saveProduct">
        <!-- Champ pour le nom du produit -->
        <input v-model="currentProduct.nom" placeholder="Nom du produit" required>

        <!-- Champ pour le prix du produit -->
        <input v-model.number="currentProduct.prix" placeholder="Prix" required>

        <!-- Champ pour la quantité en stock -->
        <input v-model.number="currentProduct.quantiteStock" placeholder="Quantité en stock" required>

        <!-- Sélecteur de catégorie -->
        <select v-model="currentProduct.categorieId" required>
          <option disabled value="">Sélectionnez une catégorie</option>
          <!-- Liste des catégories disponibles -->
          <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
            {{ categorie.nom }}
          </option>
        </select>

        <!-- Groupe de boutons pour soumettre ou annuler -->
        <div class="button-group">
          <!-- Bouton pour soumettre le formulaire (ajouter ou mettre à jour selon le mode) -->
          <button type="submit" class="btn btn-success">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          
          <!-- Bouton pour annuler l'édition d'un produit -->
          <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">Annuler</button>
        </div>
      </form>

      <!-- Tableau des produits -->
      <div class="table-container">
        <div class="table-header">
          <!-- Entêtes de colonnes du tableau -->
          <div>ID</div>
          <div>Nom</div>
          <div>Prix</div>
          <div>Quantité en stock</div>
          <div>Emplacement X</div>
          <div>Emplacement Y</div>
          <div>Catégorie</div>
          <div>Actions</div>
        </div>

        <!-- Liste des produits affichée dans des lignes -->
        <div class="table-row" v-for="produit in produits" :key="produit.id">
          <!-- Affichage des données de chaque produit -->
          <div>{{ produit.id }}</div>
          <div>{{ produit.nom }}</div>
          <div>{{ produit.prix }}</div>
          <div>{{ produit.quantiteStock }}</div>
          <div>{{ produit.x }}</div>
          <div>{{ produit.y }}</div>
          <div>{{ getCategorieName(produit.categorie_id) }}</div>
          <div>
            <!-- Boutons pour modifier et supprimer un produit -->
            <button @click="editProduct(produit)" class="btn btn-primary">Modifier</button>
            <button @click="deleteProduct(produit.id)" class="btn btn-danger">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { ref, onMounted } from 'vue';
import Navbar from './NavbarAdmin.vue'; // Importation de la barre de navigation

export default {
  name: 'ProduitsApp',  // Nom du composant
  components: {
    Navbar,  // Composant Navbar utilisé dans l'interface
  },
  setup() {
    // Déclaration des variables réactives avec `ref` de Vue Composition API
    const produits = ref([]);  // Liste des produits
    const categories = ref([]);  // Liste des catégories
    const currentProduct = ref({
      id: null,
      nom: '',
      prix: 0,
      quantiteStock: 0,
      x: 0,  // Champ géré automatiquement, par défaut à 0
      y: 0,  // Champ géré automatiquement, par défaut à 0
      categorieId: null,
    });
    const isEditing = ref(false);  // Booléen pour savoir si un produit est en mode édition
    const errorMessage = ref('');  // Message d'erreur, si applicable

    // Fonction pour récupérer les produits depuis l'API
    const fetchProduits = async () => {
      try {
        const response = await fetch('/api/produits');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des produits');
        }
        produits.value = await response.json();  // Stockage des produits dans la variable réactive
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;  // Affichage de l'erreur
      }
    };

    // Fonction pour récupérer les catégories depuis l'API
    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/categories');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des catégories');
        }
        categories.value = await response.json();  // Stockage des catégories dans la variable réactive
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;  // Affichage de l'erreur
      }
    };

    // Fonction pour sauvegarder un produit (ajouter ou mettre à jour)
    const saveProduct = async () => {
      try {
        let response;
        // Si un produit a un ID, on effectue une mise à jour
        if (currentProduct.value.id) {
          response = await fetch(`/api/produits/update/${currentProduct.value.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nom: currentProduct.value.nom,
              prix: currentProduct.value.prix,
              quantiteStock: currentProduct.value.quantiteStock,
              categorie_id: currentProduct.value.categorieId,
            }),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de la mise à jour du produit');
          }
        } else {
          // Si aucun ID, c'est un ajout de produit
          response = await fetch('/api/produits/add', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nom: currentProduct.value.nom,
              prix: currentProduct.value.prix,
              quantiteStock: currentProduct.value.quantiteStock,
              categorie_id: currentProduct.value.categorieId,
            }),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de l\'ajout du produit');
          }
        }

        // Rafraîchir la liste des produits après ajout ou mise à jour
        await fetchProduits();
        resetForm();  // Réinitialiser le formulaire
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;  // Affichage de l'erreur
      }
    };

    // Fonction pour supprimer un produit
    const deleteProduct = async (id) => {
      try {
        const response = await fetch(`/api/produits/delete/${id}`, {
          method: 'DELETE',
        });

        if (!response.ok) {
          throw new Error('Erreur lors de la suppression du produit');
        }

        await fetchProduits();  // Rafraîchir la liste des produits après suppression
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;  // Affichage de l'erreur
      }
    };

    // Fonction pour éditer un produit, charger ses données dans le formulaire
    const editProduct = (produit) => {
      currentProduct.value = {
        id: produit.id,
        nom: produit.nom,
        prix: produit.prix,
        quantiteStock: produit.quantiteStock,
        x: produit.x,  // Géré automatiquement
        y: produit.y,  // Géré automatiquement
        categorieId: produit.categorie_id,
      };
      isEditing.value = true;  // Mettre l'état d'édition à vrai
    };

    // Fonction pour réinitialiser le formulaire après ajout ou modification
    const resetForm = () => {
      currentProduct.value = {
        id: null,
        nom: '',
        prix: 0,
        quantiteStock: 0,
        x: 0,  // Réinitialisé à 0
        y: 0,  // Réinitialisé à 0
        categorieId: null,
      };
      isEditing.value = false;  // Réinitialiser l'état d'édition
    };

    // Fonction pour annuler l'édition et réinitialiser le formulaire
    const cancelEdit = () => {
      resetForm();
    };

    // Fonction pour obtenir le nom d'une catégorie à partir de son ID
    const getCategorieName = (categorieId) => {
      const categorie = categories.value.find(cat => cat.id === categorieId);
      return categorie ? categorie.nom : 'Catégorie non trouvée';
    };

    // Appel des fonctions de récupération des produits et catégories lorsque le composant est monté
    onMounted(() => {
      fetchProduits();
      fetchCategories();
    });

    // Retourne les données et méthodes pour qu'elles soient accessibles dans le template
    return {
      produits,
      categories,
      currentProduct,
      isEditing,
      errorMessage,
      saveProduct,
      deleteProduct,
      editProduct,
      cancelEdit,
      getCategorieName,
    };
  },
};
</script>

<style scoped>
.produits-app {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 1rem;
  background: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}

.produits-app h1 {
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
  grid-template-columns: repeat(8, 1fr);
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
