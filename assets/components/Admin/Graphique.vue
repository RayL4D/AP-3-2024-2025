<template>
  <div class="category-table">
    <div v-for="categorie in categories" :key="categorie.id" class="category-row">
      <div class="category-header">
        <h3>{{ categorie.nom }}</h3>
      </div>
      <div class="product-grid">
        <div
          v-for="produit in categorie.produits"
          :key="produit.id"
          class="product-cell"
        >
          <span class="product-name">{{ produit.nom }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ProductTable",
  data() {
    return {
      categories: [],
    };
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await fetch("/api/categories-with-products");
        if (response.ok) {
          const data = await response.json();
          this.categories = data;
        } else {
          console.error("Erreur lors de la récupération des catégories.");
        }
      } catch (error) {
        console.error("Erreur de connexion :", error);
      }
    },
  },
  async mounted() {
    await this.fetchCategories();
  },
};
</script>

<style scoped>
.category-table {
  width: 100%;
  padding: 20px;
  background-color: #f9f9f9;
}

.category-row {
  margin-bottom: 30px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #fff;
  padding: 10px;
}

.category-header {
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
  text-align: center;
  border-bottom: 1px solid #ddd;
  padding-bottom: 5px;
}

.product-grid {
  display: grid;
  /* Définir une taille fixe pour les colonnes */
  grid-template-columns: repeat(auto-fill, 120px);  /* chaque colonne fait 120px */
  gap: 10px;
  padding: 10px;
}

.product-cell {
  width: 120px; /* Largeur fixe */
  height: 120px; /* Hauteur fixe */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 10px;
  background-color: #f4f4f4;
  border: 1px solid #ccc;
  border-radius: 8px;
  text-align: center;
}

.product-name {
  font-size: 1rem;
  font-weight: bold;
  color: #007bff;
  margin-bottom: 5px;
}

.product-price {
  font-size: 0.9rem;
  color: #555;
}
</style>
