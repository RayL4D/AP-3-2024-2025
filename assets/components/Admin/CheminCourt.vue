<template>
  <div>
    <Navbar />
    <div class="chemin-court-app">
      <div class="category-grid">
        <!-- On boucle sur chaque catégorie -->
        <div 
          v-for="category in categories" 
          :key="category.id" 
          class="category-row"
        >
          <div class="category-name">
            <h3>{{ category.nom }}</h3>
          </div>
          <div class="product-grid">
            <!-- On boucle sur chaque produit de la catégorie -->
            <div 
              v-for="produit in category.produits" 
              :key="produit.id" 
              class="product-card"
            >
              <p><strong>{{ produit.nom }}</strong></p>
              <p>Emplacement: ({{ produit.x || "N/A" }}, {{ produit.y || "N/A" }})</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: [], // Contient les catégories et leurs produits
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        const [categoriesResponse, produitsResponse] = await Promise.all([
          fetch('/api/categories'),
          fetch('/api/produits')
        ]);

        const categories = await categoriesResponse.json();
        const produits = await produitsResponse.json();

        // Ajoute les produits à leurs catégories
        this.categories = categories.map(category => ({
          ...category,
          produits: produits.filter(produit => produit.categorie_id === category.id),
        }));
      } catch (error) {
        console.error('Erreur lors de la récupération des données :', error);
      }
    },
  },
};
</script>

<style scoped>
.chemin-court-app {
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.category-grid {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.category-row {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: flex-start;
  margin-bottom: 20px;
  border: 1px solid #ddd;
  padding: 10px;
  background-color: #f9f9f9;
  border-radius: 8px;
  gap: 20px;
}

.category-name {
  flex: 1;
  font-weight: bold;
  padding: 10px;
  text-align: center;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
  flex: 3;
  width: 100%;
}

.product-card {
  border: 1px solid #ddd;
  padding: 10px;
  background-color: #fafafa;
  border-radius: 6px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
