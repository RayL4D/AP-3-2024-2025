<template>
  <div class="commande-app">
    <NavbarClient />  <!-- Composant de la barre de navigation client -->

    <div class="commande-container">
      <!-- En-tête de la page de commande -->
      <div class="commande-header">
        <h1>Votre Commande</h1>
        <p class="commande-subtitle">Ajoutez des produits et vérifiez les détails avant validation.</p>
      </div>

      <!-- Section principale -->
      <div class="commande-main">
        
        <!-- Liste des produits disponibles -->
        <div class="produits-container">
          <h2>Produits Disponibles</h2>
          <!-- Affichage d'un message pendant le chargement des produits ou des catégories -->
          <div v-if="loadingCategories || loadingProduits">Chargement...</div>
          <div v-else>
            <!-- Filtrage des produits par catégorie et tri par prix -->
            <div class="filters">
              <div class="category-filter">
                <label for="categorySelect">Filtrer par catégorie :</label>
                <!-- Sélecteur de catégorie pour filtrer les produits -->
                <select id="categorySelect" v-model="categorieFiltre">
                  <option value="">Toutes les catégories</option>
                  <option v-for="categorie in categories" :key="categorie.id" :value="categorie.id">
                    {{ categorie.nom }}
                  </option>
                </select>
              </div>
              <div class="sort-filter">
                <label for="sortSelect">Trier par prix :</label>
                <!-- Sélecteur pour trier les produits par prix (croissant ou décroissant) -->
                <select id="sortSelect" v-model="ordreTri">
                  <option value="asc">Croissant</option>
                  <option value="desc">Décroissant</option>
                </select>
              </div>
            </div>

            <!-- Affichage des produits filtrés et triés -->
            <ul class="produits-list">
              <li
                v-for="produit in produitsFiltresTries"
                :key="produit.id"
                class="produit-item"
                :class="{ 'is-selected': commande.items.some(item => item.id === produit.id) }"
              >
                <!-- Informations sur chaque produit -->
                <div class="produit-info">
                  <span class="produit-name">{{ produit.nom }}</span>
                  <span class="produit-category">Catégorie : {{ getCategorieName(produit.categorie_id) }}</span>
                  <span class="produit-price">{{ produit.prix.toFixed(2) }} €</span>
                </div>
                <!-- Bouton pour ajouter le produit au panier -->
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

        <!-- Détails du panier -->
        <div class="commande-details">
          <h2>Votre Panier</h2>
          <!-- Affichage du message pendant le chargement des produits déjà ajoutés au panier -->
          <div v-if="loadingCommande">Chargement des produits déjà ajoutés...</div>
          <!-- Affichage des produits déjà ajoutés au panier -->
          <div v-else-if="produitsAnciens.length">
            <ul class="produits-anciens-list">
              <li v-for="produit in produitsAnciens" :key="produit.produit_id" class="produit-ancien-item">
                <!-- Informations sur chaque produit dans le panier -->
                <div class="produit-info">
                  <span class="produit-name">{{ produit.produit_nom }}</span>
                  <span class="produit-quantity">Quantité : {{ produit.quantite }}</span>
                  <span class="produit-price">Prix unitaire : {{ produit.prix.toFixed(2) }} €</span>
                  <span class="produit-total">Total : {{ (produit.quantite * produit.prix).toFixed(2) }} €</span>
                </div>
                <!-- Bouton pour retirer un produit du panier -->
                <button
                  class="remove-button"
                  @click="decrementProduit(produit)"
                  :aria-label="'Retirer ' + produit.produit_nom"
                >
                  Retirer
                </button>
              </li>
            </ul>
            <!-- Affichage du total de la commande -->
            <div class="commande-total">
              <span>Total :</span>
              <strong>{{ commandeTotal }} €</strong>
            </div>
            <!-- Bouton pour valider la commande -->
            <button
              class="cta-button"
              @click="validerCommande"
              aria-label="Valider la commande"
            >
              Valider la commande
            </button>
          </div>
          <!-- Affichage lorsque le panier est vide -->
          <div v-else>
            <p>Votre commande est vide. Ajoutez des articles pour commencer !</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>



<script>
import NavbarClient from "./NavbarClient.vue";  // Importation du composant de la barre de navigation

export default {
  name: "Commande",  // Nom du composant
  components: {
    NavbarClient,  // Déclaration du composant NavbarClient utilisé dans ce composant
  },
  data() {
    return {
      produits: [],  // Liste des produits disponibles
      categories: [],  // Liste des catégories de produits
      commande: {
        items: [],  // Liste des produits dans le panier de la commande
      },
      produitsAnciens: [],  // Liste des produits déjà ajoutés au panier (stockés depuis l'API)
      loadingProduits: true,  // Indicateur de chargement pour les produits
      loadingCategories: true,  // Indicateur de chargement pour les catégories
      loadingCommande: true,  // Indicateur de chargement pour la commande
      categorieFiltre: "",  // Filtre pour la catégorie sélectionnée
      ordreTri: "asc",  // Ordre de tri des produits par prix ("asc" ou "desc")
      produitId: 1,  // ID du produit à ajouter (initialisé à 1)
      quantite: 1,  // Quantité du produit à ajouter
      commandeId: null,  // ID de la commande
    };
  },
  computed: {
    // Calcul des produits filtrés et triés en fonction de la catégorie et du prix
    produitsFiltresTries() {
      return this.produits
        .filter(produit => !this.categorieFiltre || produit.categorie_id === this.categorieFiltre)  // Filtre par catégorie
        .sort((a, b) => this.ordreTri === "asc" ? a.prix - b.prix : b.prix - a.prix);  // Tri par prix (croissant ou décroissant)
    },
    // Calcul du total de la commande (produits actuels + produits anciens)
    commandeTotal() {
      // Calcul du total des produits dans la commande
      const totalCommandeItems = this.commande.items
        .reduce((sum, item) => sum + item.prix * item.quantity, 0);

      // Calcul du total des produits anciens (déjà ajoutés au panier)
      const totalProduitsAnciens = this.produitsAnciens
        .reduce((sum, produit) => sum + produit.prix * produit.quantite, 0);

      return (totalCommandeItems + totalProduitsAnciens).toFixed(2);  // Total avec 2 décimales
    },
  },
  mounted() {
    // Appels à l'API pour récupérer les produits, catégories et la commande lorsque le composant est monté
    this.fetchProduits();
    this.fetchCategories();
    this.fetchCommande();
  },
  methods: {
    // Fonction pour récupérer les produits depuis l'API
    async fetchProduits() {
      this.loadingProduits = true;  // Indiquer que les produits sont en cours de chargement
      try {
        const response = await fetch("/api/produits");  // Appel à l'API pour récupérer les produits
        if (response.ok) {
          this.produits = await response.json();  // Mise à jour des produits
        } else {
          console.error("Erreur lors de la récupération des produits");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingProduits = false;  // Terminer le chargement des produits
      }
    },

    // Fonction pour récupérer les catégories depuis l'API
    async fetchCategories() {
      this.loadingCategories = true;  // Indiquer que les catégories sont en cours de chargement
      try {
        const response = await fetch("/api/categories");  // Appel à l'API pour récupérer les catégories
        if (response.ok) {
          this.categories = await response.json();  // Mise à jour des catégories
        } else {
          console.error("Erreur lors de la récupération des catégories");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingCategories = false;  // Terminer le chargement des catégories
      }
    },

    // Fonction pour obtenir le nom de la catégorie en fonction de l'ID
    getCategorieName(categorieId) {
      const categorie = this.categories.find(cat => cat.id === categorieId);  // Recherche de la catégorie par ID
      return categorie ? categorie.nom : "Catégorie non trouvée";  // Retourner le nom ou un message d'erreur
    },

    // Fonction pour récupérer les informations de la commande actuelle depuis l'API
    async fetchCommande() {
      this.loadingCommande = true;  // Indiquer que la commande est en cours de chargement
      try {
        const response = await fetch("/api/orders/current");  // Appel à l'API pour récupérer la commande actuelle
        if (response.ok) {
          const data = await response.json();  // Mise à jour des données de la commande
          this.commandeId = data.id;  // Récupérer l'ID de la commande
          this.commande.items = data.items || [];  // Récupérer les articles de la commande ou un tableau vide

          // Récupérer les détails des produits déjà ajoutés à la commande
          const detailsResponse = await fetch(`/api/orders/${this.commandeId}/details`);
          if (detailsResponse.ok) {
            this.produitsAnciens = await detailsResponse.json();  // Mise à jour des produits anciens (produits déjà ajoutés)
          } else {
            console.error("Erreur lors de la récupération des détails.");
          }
        } else {
          // Si la commande n'existe pas, créer une nouvelle commande
          const createResponse = await fetch('/api/orders', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
          });
          const newOrderData = await createResponse.json();  // Création d'une nouvelle commande
          this.commandeId = newOrderData.id;  // Récupérer l'ID de la nouvelle commande
          this.commande.items = newOrderData.items || [];  // Initialiser les articles de la commande
        }
      } catch (error) {
        console.error("Erreur réseau :", error);
      } finally {
        this.loadingCommande = false;  // Terminer le chargement de la commande
      }
    },

    // Fonction pour ajouter un produit à la commande
    async ajouterProduit(produit) {
      try {
        // Décrémenter le stock du produit via l'API
        const response = await fetch(`/api/stock/${produit.id}/decrement`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ quantite: 1 }),  // Décrémente de 1 le stock
        });

        if (!response.ok) {
          const errorData = await response.json();  // Si une erreur se produit (par exemple stock insuffisant)
          alert(`Erreur: ${errorData.message || "Stock insuffisant"}`);
          return;  // Sortir si le stock est insuffisant
        }

        // Mise à jour locale de produitsAnciens (si déjà ajouté au panier, on incrémente la quantité)
        const existingProduitAncien = this.produitsAnciens.find(
          (item) => item.produit_id === produit.id
        );
        if (existingProduitAncien) {
          existingProduitAncien.quantite += 1;  // Incrémenter la quantité si déjà présent dans le panier
        } else {
          this.produitsAnciens.push({
            produit_id: produit.id,
            produit_nom: produit.nom,
            quantite: 1,  // Initialisation de la quantité à 1
            prix: produit.prix,
          });
        }

        // Mise à jour de la commande côté serveur (ajouter le produit au panier)
        await this.addProduitToCommande(produit);
      } catch (error) {
        console.error("Erreur lors de l'ajout du produit :", error);
        alert("Erreur réseau ou problème serveur.");
      }
    },

    // Fonction pour incrémenter le stock d'un produit
    async incrementStock(produit) {
      try {
        // Appel à l'API pour incrémenter le stock du produit
        const response = await fetch(`/api/stock/${produit.produit_id}/increment`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ quantite: 1 }),  // Incrémente de 1 le stock
        });

        if (!response.ok) {
          const errorData = await response.json();
          alert(`Erreur: ${errorData.status}`);  // Affichage d'une erreur si la requête échoue
        }
      } catch (error) {
        console.error('Erreur réseau lors de l\'incrémentation du stock:', error);
        alert('Erreur réseau');
      }
    },

    // Fonction pour décrémenter le stock d'un produit
    async decrementStock(produit) {
      try {
        // Appel à l'API pour décrémenter le stock d'un produit (de 1)
        const response = await fetch(`/api/stock/${produit.id}/decrement`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ quantite: 1 }),  // Décrémente de 1 unité
        });

        if (!response.ok) {
          const errorData = await response.json();  // Si l'API retourne une erreur
          alert(`Erreur: ${errorData.status}`);  // Affiche le message d'erreur
        }
      } catch (error) {
        console.error('Erreur réseau lors de la décrémentation du stock:', error);
        alert('Erreur réseau');  // En cas de problème réseau
      }
    },

    // Fonction pour décrémenter la quantité d'un produit dans la commande
    async decrementProduit(produit) {
      try {
        // Recherche du produit dans la commande en cours
        const produitCommande = this.commande.items.find(item => item.id === produit.produit_id);
        
        if (produitCommande) {
          // Si le produit existe déjà dans la commande
          if (produitCommande.quantity > 1) {
            produitCommande.quantity -= 1;  // Diminue la quantité de 1
          } else {
            // Si la quantité est 1, on supprime le produit de la commande
            this.commande.items = this.commande.items.filter(item => item.id !== produit.produit_id);
          }
        } else {
          // Si le produit n'est pas dans la commande, chercher dans les produits anciens
          const produitAncien = this.produitsAnciens.find(item => item.produit_id === produit.produit_id);
          if (produitAncien) {
            // Si le produit existe dans les produits anciens
            if (produitAncien.quantite > 1) {
              produitAncien.quantite -= 1;  // Diminue la quantité de 1
            } else {
              // Si la quantité est 1, on retire le produit des produits anciens
              this.produitsAnciens = this.produitsAnciens.filter(item => item.produit_id !== produit.produit_id);
            }
          }
        }

        // Incrémenter le stock sur le serveur après modification
        await this.incrementStock(produit);

        // Mise à jour de la commande côté serveur en supprimant le produit
        const response = await fetch(`/api/orders/${this.commandeId}/remove-detail`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ produit_id: produit.produit_id })
        });

        if (!response.ok) {
          console.error("Erreur lors de la décrémentation.");
        }
      } catch (error) {
        console.error("Erreur réseau :", error);  // Erreur de réseau
      }
    },

    // Fonction pour ajouter un produit à la commande côté serveur
    async addProduitToCommande(produit) {
      try {
        await fetch(`/api/orders/${this.commandeId}/add-detail`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ produit_id: produit.id, quantite: this.quantite }),  // Envoie du produit avec sa quantité
        });
      } catch (error) {
        console.error("Erreur lors de l'ajout du produit :", error);  // Erreur lors de l'ajout du produit
      }
    },

    // Fonction pour valider la commande
    async validerCommande() {
      // Demande de confirmation avant de valider la commande
      if (!confirm("Êtes-vous sûr de vouloir valider cette commande ?")) {
        return;  // Annuler si l'utilisateur n'est pas sûr
      }
      try {
        const response = await fetch("/api/orders/validate", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ items: this.commande.items }),  // Envoie des articles de la commande pour validation
        });
        if (response.ok) {
          alert("Commande validée !");  // Confirmation de la commande validée
          this.commande.items = [];  // Réinitialise les articles de la commande
          window.location.href = "/home";  // Redirection vers la page d'accueil
        } else {
          alert("Erreur lors de la validation.");  // Message d'erreur si la validation échoue
        }
      } catch (error) {
        console.error("Erreur réseau :", error);  // Erreur réseau lors de la validation
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