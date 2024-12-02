<template>
  <div>
    <Navbar />
    <div class="produits-app">
      <h1>Gestion des Produits</h1>
      <form @submit.prevent="saveProduct">
        <input v-model="currentProduct.nom" placeholder="Nom du produit" required />
        <input v-model.number="currentProduct.prix" placeholder="Prix" type="number" min="0" required />
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
          <div>Catégorie</div>
          <div>Actions</div>
        </div>
        <div class="table-row" v-for="produit in produits" :key="produit.id">
          <div>{{ produit.id }}</div>
          <div>{{ produit.nom }}</div>
          <div>{{ produit.prix }}</div>
          <div>{{ getCategorieName(produit.categorieId) }}</div>
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
import Navbar from '../Navbar.vue';

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
      categorieId: '',
    });

    const isEditing = ref(false);

    const fetchProduits = async () => {
      try {
        const response = await fetch('/api/produits');
        if (!response.ok) throw new Error('Erreur lors du chargement des produits');
        produits.value = await response.json();
      } catch (error) {
        console.error(error);
        showError('Impossible de charger les produits.');
      }
    };

    const fetchCategories = async () => {
      try {
        const response = await fetch('/api/categories');
        if (!response.ok) throw new Error('Erreur lors du chargement des catégories');
        categories.value = await response.json();
      } catch (error) {
        console.error(error);
        showError('Impossible de charger les catégories.');
      }
    };

    const saveProduct = async () => {
  if (!isValidProduct()) {
    showError('Veuillez remplir tous les champs correctement.');
    return;
  }

  try {
    let response;
    const url = currentProduct.value.id
      ? `/api/produits/${currentProduct.value.id}`  // Utilisez l'ID pour la mise à jour
      : '/api/produits';  // Sinon, pour l'ajout

    response = await fetch(url, {
      method: currentProduct.value.id ? 'PUT' : 'POST', // PUT si modification, POST si ajout
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(currentProduct.value),
    });

    if (!response.ok) throw new Error(currentProduct.value.id ? 'Erreur lors de la mise à jour du produit' : "Erreur lors de l'ajout du produit");

    // Recharger les produits après l'ajout ou la mise à jour
    await fetchProduits();
    resetForm();
  } catch (error) {
    console.error(error);
    showError(error.message);
  }
};



    const deleteProduct = async (id) => {
      try {
        const response = await fetch(`/api/produits/delete/${id}`, { method: 'DELETE' });
        if (!response.ok) throw new Error('Erreur lors de la suppression du produit');
        await fetchProduits();
      } catch (error) {
        console.error(error);
        showError('Impossible de supprimer le produit.');
      }
    };

    const editProduct = (produit) => {
      currentProduct.value = { ...produit };
      isEditing.value = true;
    };

    const resetForm = () => {
      currentProduct.value = {
        id: null,
        nom: '',
        prix: 0,
        categorieId: '',
      };
      isEditing.value = false;
    };

    const cancelEdit = () => {
      resetForm();
    };

    const getCategorieName = (id) => {
      const categorie = categories.value.find((c) => c.id === id);
      return categorie ? categorie.nom : 'Inconnue';
    };

    const showError = (message) => {
      alert(message);
    };

    const isValidProduct = () => {
      return currentProduct.value.nom.trim() !== '' && currentProduct.value.prix > 0 && currentProduct.value.categorieId;
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

input,
select {
  margin-bottom: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.button-group {
  display: flex;
  gap: 10px;
  margin-top: 10px;
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
  display: table;
  width: 100%;
  border-collapse: collapse;
}

.table-header,
.table-row {
  display: table-row;
}

.table-header div,
.table-row div {
  display: table-cell;
  text-align: center;
  padding: 0.75rem;
  border: 1px solid #ddd;
}

.table-header {
  background-color: #bdc3c7;
  font-weight: bold;
}

.table-row:nth-child(even) div {
  background-color: #f4f4f4;
}

.table-row div:hover {
  background-color: #dfe6e9;
}
</style>
