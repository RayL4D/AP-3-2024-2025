<template>
  <div>
    <Navbar />
    <div class="produits-app">
      <h1>Gestion des Produits</h1>
      <form @submit.prevent="saveProduct">
        <input v-model="currentProduct.nom" placeholder="Nom du produit" required>
        <input v-model.number="currentProduct.prix" placeholder="Prix" required>
        <!-- Menu déroulant pour les catégories -->
        <select v-model="currentProduct.categorieId" required>
          <option disabled value="">Sélectionnez une catégorie</option>
          <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
            {{ categorie.nom }}
          </option>
        </select>
        <div class="button-group">
          <!-- Boutons pour sauvegarder ou annuler -->
          <button type="submit" class="btn btn-success">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">Annuler</button>
        </div>
      </form>

      <!-- Tableau pour afficher les produits -->
      <div class="table-container">
        <div class="table-header">
          <div>ID</div>
          <div>Nom</div>
          <div>Prix</div>
          <div>Catégorie</div>
          <div>Actions</div>
        </div>
        <div class="table-row" v-for="produit in produits" :key="produit.id">
          <div>{{ produit.id }}</div>
          <div>{{ produit.nom }}</div>
          <div>{{ produit.prix }}</div>
          <div>{{ getCategorieName(produit.categorieId) }}</div>
          <div>
            <!-- Boutons pour éditer ou supprimer un produit -->
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
import Navbar from '../Navbar.vue';

export default {
  name: 'ProduitsApp', // Nom du composant
  components: {
    Navbar, // Composant de la barre de navigation
  },
  setup() {
    // Utilisation de la composition API de Vue 3
    const produits = ref([]); // Référence pour stocker les produits
    const currentProduct = ref({
      id: null,
      libelle: '',
      description: '',
      prixPlancher: 0,
    }); // Référence pour stocker le produit courant

    const isEditing = ref(false); // Référence pour savoir si on est en mode édition

    // Fonction pour récupérer les produits
    const fetchProduits = async () => {
      try {
        const response = await fetch('/api/produits');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des produits');
        }
        produits.value = await response.json();
      } catch (error) {
        console.error(error);
      }
    };

    // Fonction pour sauvegarder un produit
    const saveProduct = async () => {
      try {
        let response;
        if (currentProduct.value.id) {
          // Mise à jour du produit existant
          response = await fetch(`/api/produits/update/${currentProduct.value.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(currentProduct.value),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de la mise à jour du produit');
          }
        } else {
          // Ajout d'un nouveau produit
          response = await fetch('/api/produits/add', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(currentProduct.value),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de l\'ajout du produit');
          }
        }

        await fetchProduits();
        resetForm();
      } catch (error) {
        console.error(error);
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

        await fetchProduits();
      } catch (error) {
        console.error(error);
      }
    };

    // Fonction pour éditer un produit
    const editProduct = (produit) => {
      currentProduct.value = { ...produit }; // Met à jour le produit courant avec les données du produit sélectionné
      isEditing.value = true; // Active le mode édition
    };

    // Fonction pour réinitialiser le formulaire
    const resetForm = () => {
      currentProduct.value = {
        id: null,
        libelle: '',
        description: '',
        prixPlancher: 0,
      }; // Réinitialise les champs du formulaire
      isEditing.value = false; // Désactive le mode édition
    };

    // Fonction pour annuler l'édition
    const cancelEdit = () => {
      resetForm();
    };

    // Lifecycle hook monté - appelé lorsque le composant est monté
    onMounted(() => {
      fetchProduits(); // Récupère les produits initiales
    });

    // Retourne les variables et fonctions pour être utilisées dans le template
    return {
      produits,
      currentProduct,
      isEditing,
      saveProduct,
      deleteProduct,
      editProduct,
      cancelEdit,
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
  grid-template-columns: repeat(5, 1fr);
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
