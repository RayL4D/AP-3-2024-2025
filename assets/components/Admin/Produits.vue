<template>
  <div>
    <Navbar />
    <div class="produits-app">
      <h1>Gestion des Produits</h1>
      <form @submit.prevent="saveProduct">
        <input v-model="currentProduct.nom" placeholder="Nom du produit" required>
        <input v-model.number="currentProduct.prix" placeholder="Prix" required>
        <input v-model.number="currentProduct.quantiteStock" placeholder="Quantité en stock" required>
        <select v-model="currentProduct.categorieId" required>
          <option disabled value="">Sélectionnez une catégorie</option>
          <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
            {{ categorie.nom }}
          </option>
        </select>
        <div class="button-group">
          <button type="submit" class="btn btn-success">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          <button type="button" class="btn btn-secondary" @click="cancelEdit" v-if="isEditing">Annuler</button>
        </div>
      </form>

      <div class="table-container">
        <div class="table-header">
          <div>ID</div>
          <div>Nom</div>
          <div>Prix</div>
          <div>Quantité en stock</div>
          <div>Catégorie</div>
          <div>Actions</div>
        </div>
        <div class="table-row" v-for="produit in produits" :key="produit.id">
          <div>{{ produit.id }}</div>
          <div>{{ produit.nom }}</div>
          <div>{{ produit.prix }}</div>
          <div>{{ produit.quantiteStock }}</div>
          <div>{{ getCategorieName(produit.categorie_id) }}</div>
          <div>
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
import Navbar from './NavbarAdmin.vue';

export default {
  name: 'ProduitsApp',
  components: {
    Navbar,
  },
  setup() {
    const produits = ref([]);
    const categories = ref([]);
    const currentProduct = ref({
      id: null,
      nom: '',
      prix: 0,
      quantiteStock: 0,
      categorieId: null,
    });
    const isEditing = ref(false);
    const errorMessage = ref('');

    const fetchProduits = async () => {
      try {
        const response = await fetch('/api/produits');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des produits');
        }
        produits.value = await response.json();
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;
      }
    };

    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/categories');
        if (!response.ok) {
          throw new Error('Erreur lors du chargement des catégories');
        }
        categories.value = await response.json();
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;
      }
    };

    const saveProduct = async () => {
      try {
        let response;
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
              categorie_id: currentProduct.value.categorieId
            }),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de la mise à jour du produit');
          }
        } else {
          response = await fetch('/api/produits/add', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              nom: currentProduct.value.nom,
              prix: currentProduct.value.prix,
              quantiteStock: currentProduct.value.quantiteStock,
              categorie_id: currentProduct.value.categorieId
            }),
          });

          if (!response.ok) {
            throw new Error('Erreur lors de l\'ajout du produit');
          }
        }

        await fetchProduits();
        resetForm();
      } catch (error) {
        console.error(error);
        errorMessage.value = error.message;
      }
    };

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
        errorMessage.value = error.message;
      }
    };

    const editProduct = (produit) => {
      currentProduct.value = {
        id: produit.id,
        nom: produit.nom,
        prix: produit.prix,
        quantiteStock: produit.quantiteStock,
        categorieId: produit.categorie_id
      };
      isEditing.value = true;
    };

    const resetForm = () => {
      currentProduct.value = {
        id: null,
        nom: '',
        prix: 0,
        quantiteStock: 0,
        categorieId: null,
      };
      isEditing.value = false;
    };

    const cancelEdit = () => {
      resetForm();
    };

    const getCategorieName = (categorieId) => {
      const categorie = categories.value.find(cat => cat.id === categorieId);
      return categorie ? categorie.nom : 'Catégorie non trouvée';
    };

    onMounted(() => {
      fetchProduits();
      fetchCategories();
    });

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
  grid-template-columns: repeat(6, 1fr);
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
