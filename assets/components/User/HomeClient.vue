<template>
  <div class="client-app">
    <Navbar />
    <div class="dashboard">
      <div class="hero-section">
        <h1>Bienvenue dans notre boutique</h1>
        <p class="welcome-text">
          Découvrez nos produits exclusifs et profitez de promotions exceptionnelles.
        </p>
        <button 
          @click="handleOrderAction" 
          :disabled="isLoading" 
          class="cta-button"
        >
          {{ isLoading ? 'Chargement...' : (hasOrder ? 'Continuer votre commande' : 'Passer une commande') }}
        </button>
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
      </div>

      <!-- Section des caractéristiques -->
      <div class="features-section">
        <div class="feature">
          <img src="/images/shopping-cart.png" alt="Shopping Cart" class="feature-icon" />
          <h2>Produits de qualité</h2>
          <p>Explorez notre large gamme de produits soigneusement sélectionnés pour vous.</p>
        </div>
        <div class="feature">
          <img src="/images/delivery-truck.png" alt="Delivery Truck" class="feature-icon" />
          <h2>Livraison rapide</h2>
          <p>Recevez vos commandes en un temps record grâce à notre service efficace.</p>
        </div>
        <div class="feature">
          <img src="/images/support.png" alt="Support" class="feature-icon" />
          <h2>Support client</h2>
          <p>Notre équipe est disponible pour répondre à toutes vos questions.</p>
        </div>
      </div>

      <!-- Section Catalogue de Produits -->
      <div class="catalogue-container">
        <h1>Catalogue des Produits</h1>
        <p>Explorez notre sélection de produits disponibles.</p>

        <div v-if="loadingProduits">Chargement des produits...</div>

        <div v-else>
          <!-- Filtres -->
          <div class="filters">
            <div class="category-filter">
              <label for="categorySelect">Filtrer par catégorie :</label>
              <select id="categorySelect" v-model="categorieFiltre">
                <option value="">Toutes les catégories</option>
                <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
                  {{ categorie.nom }}
                </option>
              </select>
            </div>
            <div class="price-sort">
              <label for="priceSort">Trier par prix :</label>
              <select id="priceSort" v-model="prixSort">
                <option value="asc">Prix croissant</option>
                <option value="desc">Prix décroissant</option>
              </select>
            </div>
          </div>

          <!-- Affichage des produits -->
          <div class="produits-grid">
            <div v-for="produit in produitsFiltresTries" :key="produit.id" class="produit-item">
              <div class="produit-info">
                <span class="produit-name">{{ produit.nom }}</span>
                <span class="produit-category">Catégorie : {{ getCategorieName(produit.categorie_id) }}</span>
                <span class="produit-price">{{ produit.prix.toFixed(2) }} €</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import Navbar from "./NavbarClient.vue";

export default {
  name: "ClientHome",
  components: {
    Navbar,
  },
  data() {
    return {
      hasOrder: false,
      isLoading: false,
      errorMessage: '',
      produits: [],
      categories: [],
      categorieFiltre: "",
      prixSort: "asc", // Valeur par défaut pour le tri
      loadingProduits: true,
      loadingCategories: true,
    };
  },
  computed: {
    produitsFiltres() {
      return this.produits.filter(produit => 
        !this.categorieFiltre || produit.categorie_id === this.categorieFiltre
      );
    },
    produitsFiltresTries() {
      return [...this.produitsFiltres].sort((a, b) => {
        return this.prixSort === "asc" ? a.prix - b.prix : b.prix - a.prix;
      });
    },
  },
  mounted() {
    this.checkOrderStatus();
    this.fetchProduits();
    this.fetchCategories();
  },
  methods: {
    async checkOrderStatus() {
      this.isLoading = true;
      this.errorMessage = '';
      try {
        const response = await fetch('/api/orders/check');
        const data = await response.json();
        if (response.ok) {
          this.hasOrder = data.hasOrder;
        } else {
          this.errorMessage = "Impossible de vérifier l'état de la commande. Veuillez réessayer plus tard.";
        }
      } catch (error) {
        console.error("Erreur lors de la vérification de la commande :", error);
        this.errorMessage = "Une erreur s'est produite. Veuillez réessayer plus tard.";
      } finally {
        this.isLoading = false;
      }
    },
    async handleOrderAction() {
      this.isLoading = true;
      this.errorMessage = '';
      if (this.hasOrder) {
        window.location.href = "/commande";
      } else {
        await this.createOrder();
      }
    },
    async createOrder() {
      try {
        const response = await fetch("/api/orders", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            date: new Date().toISOString(),
            statut: "En cours de création",
          }),
        });

        if (response.ok) {
          alert("Commande créée avec succès !");
          window.location.href = "/commande";
        } else {
          this.errorMessage = "Une erreur s'est produite lors de la création de la commande.";
        }
      } catch (error) {
        console.error("Erreur lors de la création de la commande :", error);
        this.errorMessage = "Une erreur s'est produite. Veuillez réessayer plus tard.";
      } finally {
        this.isLoading = false;
      }
    },
    async fetchProduits() {
      this.loadingProduits = true;
      try {
        const response = await fetch("/api/produits");
        if (response.ok) {
          this.produits = await response.json();
        } else {
          console.error("Erreur lors de la récupération des produits");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingProduits = false;
      }
    },
    async fetchCategories() {
      this.loadingCategories = true;
      try {
        const response = await fetch("/api/categories");
        if (response.ok) {
          this.categories = await response.json();
        } else {
          console.error("Erreur lors de la récupération des catégories");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingCategories = false;
      }
    },
    getCategorieName(categorieId) {
      const categorie = this.categories.find(cat => cat.id === categorieId);
      return categorie ? categorie.nom : "Catégorie non trouvée";
    },
  },
};
</script>

<style scoped>
.client-app {
  font-family: 'Arial', sans-serif;
  color: #2c3e50;
  background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
}

.dashboard {
  width: 100%;
  max-width: 1200px;
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero-section {
  text-align: center;
  margin-bottom: 3rem;
}

.hero-section h1 {
  font-size: 3rem;
  color: #34495e;
  margin-bottom: 1rem;
}

.hero-section .welcome-text {
  font-size: 1.5rem;
  color: #7f8c8d;
  margin-bottom: 2rem;
}

.cta-button {
  padding: 1rem 2rem;
  font-size: 1.2rem;
  color: white;
  background-color: #e74c3c;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cta-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.cta-button:hover:not(:disabled) {
  background-color: #c0392b;
}

.error-message {
  color: red;
  margin-top: 1rem;
  font-size: 1.2rem;
}

.features-section {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 2rem;
  width: 100%;
}

.feature {
  background: white;
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  padding: 1.5rem;
  text-align: center;
  flex: 1 1 300px;
  max-width: 300px;
}

.feature-icon {
  width: 80px;
  height: 80px;
  margin-bottom: 1rem;
}

.feature h2 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.feature p {
  font-size: 1rem;
  color: #7f8c8d;
}

/* Style du catalogue */
.catalogue-container {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 20px;
  background-color: #f3f4f6;
  border-radius: 12px;
}

.catalogue-container h1 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 20px;
}

/* Style de tri ajouté */
.filters {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.price-sort label {
  font-size: 1rem;
  font-weight: bold;
  margin-right: 10px;
}

.price-sort select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
}

.category-filter label {
  font-size: 1rem;
  font-weight: bold;
  color: #555;
}

.category-filter select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s;
}

.produits-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.produit-item {
  background-color: #fff;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.produit-item:hover {
  background-color: #f0f8ff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

.produit-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.produit-name {
  font-size: 1.1rem;
  color: #333;
  font-weight: bold;
}

.produit-category {
  font-size: 0.9rem;
  color: #666;
}

.produit-price {
  font-size: 1rem;
  color: #007bff;
  font-weight: bold;
}
</style>
