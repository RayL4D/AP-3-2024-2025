<template>
  <div class="commande-app">
    <NavbarClient />
    <div class="commande-container">
      <div class="commande-header">
        <h1>Votre Commande</h1>
        <p class="commande-subtitle">Ajoutez des produits et vérifiez les détails avant validation.</p>
      </div>

      <!-- Section principale -->
      <div class="commande-main">
        <!-- Liste des produits disponibles -->
        <div class="produits-container">
          <h2>Produits Disponibles</h2>
          <div v-if="loadingCategories || loadingProduits">Chargement...</div>
          <div v-else>
            <!-- Filtre par catégorie et tri -->
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
              <div class="sort-filter">
                <label for="sortSelect">Trier par prix :</label>
                <select id="sortSelect" v-model="ordreTri">
                  <option value="asc">Croissant</option>
                  <option value="desc">Décroissant</option>
                </select>
              </div>
            </div>

            <!-- Afficher les produits -->
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
                  @click="ajouterProduit(produit) + decrementStock(produit)"
                  :disabled="loadingCommande"
                >
                  Ajouter
                </button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Détails du panier -->
        <div class="commande-details">
          <h2>Votre Panier</h2>
          <div v-if="loadingCommande">Chargement des produits déjà ajoutés...</div>
          <div v-else-if="produitsAnciens.length">
            <ul class="produits-anciens-list">
              <li v-for="produit in produitsAnciens" :key="produit.produit_id" class="produit-ancien-item">
                <div class="produit-info">
                  <span class="produit-name">{{ produit.produit_nom }}</span>
                  <span class="produit-quantity">Quantité : {{ produit.quantite }}</span>
                  <span class="produit-price">Prix unitaire : {{ produit.prix.toFixed(2) }} €</span>
                  <span class="produit-total">Total : {{ (produit.quantite * produit.prix).toFixed(2) }} €</span>
                </div>
                <button
                  class="remove-button"
                  @click="decrementProduit(produit)"
                  :aria-label="'Retirer ' + produit.produit_nom"
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
              aria-label="Valider la commande"
            >
              Valider la commande
            </button>
          </div>
          <div v-else>
            <p>Aucun produit ajouté dans les détails précédents.</p>
          </div>
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
      produits: [],
      categories: [],
      commande: {
        items: [],
      },
      produitsAnciens: [], // Nouveaux produits stockés depuis l'API
      loadingProduits: true,
      loadingCategories: true,
      loadingCommande: true,
      categorieFiltre: "",
      ordreTri: "asc",
      produitId: 1,
      quantite: 1,
      commandeId: null,
    };
  },
  computed: {
    produitsFiltresTries() {
      return this.produits
        .filter(produit => !this.categorieFiltre || produit.categorie_id === this.categorieFiltre)
        .sort((a, b) => this.ordreTri === "asc" ? a.prix - b.prix : b.prix - a.prix);
    },
    commandeTotal() {
    // Calculer le total de la commande en ajoutant les produits actuels et les anciens produits
    const totalCommandeItems = this.commande.items
      .reduce((sum, item) => sum + item.prix * item.quantity, 0);

    const totalProduitsAnciens = this.produitsAnciens
      .reduce((sum, produit) => sum + produit.prix * produit.quantite, 0);

    return (totalCommandeItems + totalProduitsAnciens).toFixed(2);
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
        this.commandeId = data.id;
        this.commande.items = data.items || [];

          // Récupérer les détails des produits déjà ajoutés
          const detailsResponse = await fetch(`/api/orders/${this.commandeId}/details`);
          if (detailsResponse.ok) {
            this.produitsAnciens = await detailsResponse.json(); // Remplir produitsAnciens
          } else {
            console.error("Erreur lors de la récupération des détails.");
          }
      } else {
        const createResponse = await fetch('/api/orders', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
        });
        const newOrderData = await createResponse.json();
        this.commandeId = newOrderData.id;
        this.commande.items = newOrderData.items || [];
      }
    } catch (error) {
      console.error("Erreur réseau :", error);
    } finally {
      this.loadingCommande = false;
    }
  },

  ajouterProduit(produit) {
    
  // Ajout immédiat dans produitsAnciens pour l'affichage dans "Produits déjà ajoutés"
  const existingProduitAncien = this.produitsAnciens.find(item => item.produit_id === produit.id);
  if (existingProduitAncien) {
    existingProduitAncien.quantite += 1; // Incrémenter la quantité si déjà présent
  } else {
    this.produitsAnciens.push({ produit_id: produit.id, produit_nom: produit.nom, quantite: 1, prix: produit.prix });
  }


  // Appeler la méthode pour envoyer la mise à jour au serveur
  this.addProduitToCommande(produit);

},

async decrementStock(produit) {
  try {
    // Si le stock est géré par une entité distincte, utilisez l'id de l'entité stock
    const response = await fetch(`/api/stock/${produit.id}/decrement`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ quantite: 1 }), // Décrémente de 1
    });

    if (!response.ok) {
      const errorData = await response.json();
      alert(`Erreur: ${errorData.status}`);
    }
  } catch (error) {
    console.error('Erreur réseau lors de la décrémentation du stock:', error);
    alert('Erreur réseau');
  }
},
    async decrementProduit(produit) {
    try {
      // Si le produit existe déjà dans la commande
      const produitCommande = this.commande.items.find(item => item.id === produit.produit_id);
      if (produitCommande) {
        if (produitCommande.quantity > 1) {
          produitCommande.quantity -= 1; // Diminue la quantité
        } else {
          // Si la quantité est 1, on supprime l'item de la commande
          this.commande.items = this.commande.items.filter(item => item.id !== produit.produit_id);
        }
      } else {
        // Si le produit est dans les produits anciens mais pas encore dans la commande
        const produitAncien = this.produitsAnciens.find(item => item.produit_id === produit.produit_id);
        if (produitAncien) {
          if (produitAncien.quantite > 1) {
            produitAncien.quantite -= 1; // Diminue la quantité
          } else {
            // Retirer le produit des produits anciens s'il a une quantité de 1
            this.produitsAnciens = this.produitsAnciens.filter(item => item.produit_id !== produit.produit_id);
          }
        }
      }

      // Envoyer la mise à jour au serveur pour la commande
      const response = await fetch(`/api/orders/${this.commandeId}/remove-detail`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ produit_id: produit.produit_id })
      });

      if (!response.ok) {
        console.error("Erreur lors de la décrémentation.");
      }
    } catch (error) {
      console.error("Erreur réseau :", error);
    }
  },

    async addProduitToCommande(produit) {
      try {
        await fetch(`/api/orders/${this.commandeId}/add-detail`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ produit_id: produit.id, quantite: this.quantite }),
        });
      } catch (error) {
        console.error("Erreur lors de l'ajout du produit :", error);
      }
    },
    async validerCommande() {
      if (!confirm("Êtes-vous sûr de vouloir valider cette commande ?")) {
        return;
      }
      try {
        const response = await fetch("/api/orders/validate", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ items: this.commande.items }),
        });
        if (response.ok) {
          alert("Commande validée !");
          this.commande.items = [];
          window.location.href = "/home";
        } else {
          alert("Erreur lors de la validation.");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      }
      
    },
  },
};
</script>



<style scoped>
/* Conteneur principal de la commande */
.commande-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f3f4f6;
  border-radius: 12px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Header de la commande */
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

/* Mise en page de la commande */
.commande-main {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

/* Sections produits et détails de commande */
.produits-container, .commande-details {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.produits-container h2, .commande-details h2 {
  font-size: 1.5rem;
  color: #333;
  border-bottom: 2px solid #007bff;
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.produits-list, .commande-items {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* Cartes de produit dans la commande */
.produit-item, .commande-item, .produit-ancien-item {
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

.produit-item:hover, .commande-item:hover, .produit-ancien-item:hover {
  background-color: #f0f8ff;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Informations sur les produits */
.produit-info {
  display: flex;
  flex-direction: column;
}

.produit-name {
  font-size: 1.1rem;
  color: #333;
  font-weight: bold;
}

.produit-category {
  color: #666;
  font-size: 0.9rem;
}

.produit-price {
  font-size: 1rem;
  color: #007bff;
  font-weight: bold;
}

/* Boutons d'action */
.add-button, .cta-button, .remove-button {
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.add-button:hover, .cta-button:hover, .remove-button:hover {
  background-color: #0056b3;
  transform: scale(1.05);
}

.cta-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.remove-button {
  background-color: #dc3545;
}

.remove-button:hover {
  background-color: #c82333;
}

/* Total de la commande */
.commande-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.2rem;
  font-weight: bold;
  color: #333;
  margin-top: 15px;
}

/* Filtres */
.filters, .category-filter, .sort-filter {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.category-filter label, .sort-filter label {
  font-size: 1rem;
  font-weight: bold;
  color: #555;
}

.category-filter select, .sort-filter select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  transition: border-color 0.3s;
}

.category-filter select:focus, .sort-filter select:focus {
  border-color: #007bff;
  outline: none;
}

.produit-item.is-selected {
  background-color: #e6f7ff;
  border-color: #91d5ff;
}

/* Produits dans le panier */
.produits-anciens-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.produit-ancien-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  background-color: #ffffff;
  border-radius: 8px;
  border: 1px solid #ddd;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.produit-ancien-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  border-color: #28a745;
}

.produit-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.produit-name {
  font-size: 1.2rem;
  color: #34495e;
  font-weight: 600;
}

.produit-quantity, .produit-price {
  font-size: 1rem;
  color: #2b88eb;
}

.produit-total {
  text-align: right;
  font-weight: bold;
  color: #28a745;
}
</style>