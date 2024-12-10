<template>
  <div class="commande-app">
    <NavbarClient />
    <div class="commande-container">
      <div class="commande-header">
        <h1>Votre Commande</h1>
        <p class="commande-subtitle">Ajoutez des produits et vérifiez les détails avant validation.</p>
      </div>

      <!-- Liste des produits disponibles -->
      <div class="produits-container">
        <h2>Produits Disponibles</h2>
        <div v-if="loadingCategories || loadingProduits">Chargement...</div>
        <div v-else>
          <!-- Filtre par catégorie -->
          <div class="category-filter">
            <label for="categorySelect">Filtrer par catégorie :</label>
            <select id="categorySelect" v-model="categorieFiltre">
              <option value="">Toutes les catégories</option>
              <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
                {{ categorie.nom }}
              </option>
            </select>
          </div>

          <!-- Trier par prix -->
          <div class="sort-filter">
            <label for="sortSelect">Trier par prix :</label>
            <select id="sortSelect" v-model="ordreTri">
              <option value="asc">Croissant</option>
              <option value="desc">Décroissant</option>
            </select>
          </div>

          <ul class="produits-list">
            <li
              v-for="produit in produitsFiltresTries"
              :key="produit.id"
              class="produit-item"
              :class="{ 'is-selected': commande.items.some(item => item.id === produit.id) }"
            >
              <div class="produit-info">
                <span class="produit-name">{{ produit.nom }}</span>
                <span class="produit-category">Catégorie : {{ getCategorieName(produit.categorie_id) }}</span>
                <span class="produit-price">{{ produit.prix.toFixed(2) }} €</span>
              </div>
              <button
                class="add-button"
                @click="ajouterProduit(produit)"
                :disabled="loadingCommande"
              >
                Ajouter
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- Détails de la commande -->
      <div class="commande-details">
        <h2>Détails de la commande</h2>
        <div v-if="loadingCommande">Chargement de la commande...</div>
        <div v-else-if="commande.items.length">
          <ul class="commande-items">
            <li v-for="item in commande.items" :key="item.id" class="commande-item">
              <div class="item-info">
                <span class="item-name">{{ item.nom }}</span>
                <span class="item-quantity">Quantité : {{ item.quantity }}</span>
              </div>
              <span class="item-price">{{ (item.prix * item.quantity).toFixed(2) }} €</span>
              <button
                class="remove-button"
                @click="retirerProduit(item)"
                :aria-label="'Retirer ' + item.nom"
              >
                Retirer
              </button>
            </li>
          </ul>
          <div class="commande-total">
            <span>Total :</span>
            <strong>{{ commandeTotal }} €</strong>
          </div>
          <button
            class="cta-button"
            @click="validerCommande"
            :disabled="!commande.items.length || loadingCommande"
          >
            Valider la commande
          </button>
        </div>
        <div v-else>
          <p>Votre commande est vide. Ajoutez des articles pour commencer !</p>
        </div>
      </div>
    </div>
  </div>
</template>



<script>
import NavbarClient from "./NavbarClient.vue";

export default {
  name: "Commande",
  components: {
    NavbarClient,
  },
  data() {
    return {
      produits: [], // Liste des produits disponibles
      categories: [], // Liste des catégories
      commande: {
        items: [], // Liste des produits dans la commande
      },
      loadingProduits: true,
      loadingCategories: true,
      loadingCommande: true,
      categorieFiltre: "", // Catégorie sélectionnée pour le filtre
      ordreTri: "asc", // Ordre de tri : "asc" pour croissant, "desc" pour décroissant
    };
  },
  computed: {
    produitsFiltres() {
      // Filtrer les produits selon la catégorie sélectionnée
      if (!this.categorieFiltre) {
        return this.produits;
      }
      return this.produits.filter(produit => produit.categorie_id === this.categorieFiltre);
    },
    produitsFiltresTries() {
      // Trier les produits filtrés selon l'ordre sélectionné
      return [...this.produitsFiltres].sort((a, b) => {
        return this.ordreTri === "asc" ? a.prix - b.prix : b.prix - a.prix;
      });
    },
    commandeTotal() {
      return this.commande.items
        .reduce((sum, item) => sum + item.prix * item.quantity, 0)
        .toFixed(2);
    },
  },
  mounted() {
    this.fetchProduits();
    this.fetchCategories();
    this.fetchCommande();
  },
  methods: {
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
    async fetchCommande() {
      this.loadingCommande = true;
      try {
        const response = await fetch("/api/orders/current");
        if (response.ok) {
          const data = await response.json();
          this.commande.items = data.items || [];
        } else {
          console.error("Erreur lors de la récupération de la commande");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingCommande = false;
      }
    },
    ajouterProduit(produit) {
      const existingItem = this.commande.items.find(item => item.id === produit.id);
      if (existingItem) {
        existingItem.quantity += 1;
      } else {
        this.commande.items.push({ ...produit, quantity: 1 });
      }
    },
    retirerProduit(produit) {
      const index = this.commande.items.findIndex(item => item.id === produit.id);
      if (index !== -1) {
        const item = this.commande.items[index];
        item.quantity -= 1;
        if (item.quantity <= 0) {
          this.commande.items.splice(index, 1);
        }
      }
    },
    async validerCommande() {
      if (!confirm("Êtes-vous sûr de vouloir valider cette commande ?")) {
        return;
      }
      try {
        const response = await fetch("/api/orders/validate", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ items: this.commande.items }),
        });
        if (response.ok) {
          alert("Votre commande a été validée avec succès !");
          this.commande.items = []; // Réinitialiser après validation
        } else {
          alert("Erreur lors de la validation de la commande.");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      }
    },
  },
};
</script>


  
<style scoped>
.commande-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f3f4f6;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.commande-header {
  text-align: center;
  margin-bottom: 30px;
}

.commande-header h1 {
  font-size: 2rem;
  color: #333;
}

.commande-subtitle {
  color: #666;
  font-size: 1.1rem;
  margin-top: 8px;
}

.produits-container,
.commande-details {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.produits-container h2,
.commande-details h2 {
  font-size: 1.5rem;
  color: #333;
  border-bottom: 2px solid #007bff;
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.produits-list,
.commande-items {
  list-style: none;
  padding: 0;
  margin: 0;
}

.produit-item,
.commande-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  background-color: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #ddd;
  transition: background-color 0.3s, box-shadow 0.3s;
}

.produit-item:hover,
.commande-item:hover {
  background-color: #f0f8ff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.produit-info,
.item-info {
  display: flex;
  flex-direction: column;
}

.produit-name,
.item-name {
  font-size: 1.1rem;
  color: #333;
  font-weight: bold;
}

.produit-category {
  color: #666;
  font-size: 0.9rem;
}

.produit-price,
.item-price {
  font-size: 1rem;
  color: #007bff;
  font-weight: bold;
}

.add-button,
.cta-button,
.remove-button {
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.add-button:hover,
.cta-button:hover,
.remove-button:hover {
  background-color: #0056b3;
  transform: scale(1.05);
}

.cta-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.commande-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
  margin-top: 15px;
}

.category-filter,
.sort-filter {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  gap: 10px;
}

.category-filter label,
.sort-filter label {
  font-size: 1rem;
  font-weight: bold;
  color: #555;
}

.category-filter select,
.sort-filter select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s;
}

.category-filter select:focus,
.sort-filter select:focus {
  border-color: #007bff;
  outline: none;
}

/* Selected product styling */
.produit-item.is-selected {
  background-color: #e6f7ff;
  border-color: #91d5ff;
}
</style>

  