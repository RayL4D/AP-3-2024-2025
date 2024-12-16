<template>
  <div class="a-commande-app">
    <!-- Barre de navigation -->
    <Navbar />
    
    <div class="content-container">
      <!-- Colonne gauche : Liste des commandes -->
      <div class="commandes-container">
        <h2 class="title">Commandes Administrateur</h2>

        <!-- Affiche un message de chargement tant que les commandes ne sont pas disponibles -->
        <div v-if="loading" class="loading">Chargement des commandes...</div>

        <!-- Message si aucune commande n'est disponible -->
        <div v-else-if="orders.length === 0" class="no-orders">Vous n'avez aucune commande.</div>

        <!-- Liste des commandes (affichée uniquement si des commandes existent) -->
        <div v-else>
          <div class="orders-list">
            <!-- Affiche chaque commande avec ses détails -->
            <div v-for="order in paginatedOrders" :key="order.id" class="order-card">
              <!-- En-tête de la commande -->
              <div class="order-header">
                <h3 class="order-title">
                  <!-- ID de la commande -->
                  <span class="order-id">Commande #{{ order.id }}</span>
                  <!-- Statut de la commande, formaté pour styliser en fonction du statut -->
                  <span :class="['order-status', order.statut.toLowerCase().replace(/ /g, '-')]">
                    {{ order.statut }}
                  </span>
                  <!-- Date de la commande -->
                  <span class="order-date">{{ order.date }}</span>
                </h3>
              </div>

              <!-- Détails des produits dans la commande -->
              <div class="order-details">
                <ul>
                  <!-- Liste des produits dans la commande -->
                  <li v-for="detail in order.details" :key="detail.produit_id" class="order-detail">
                    <!-- Nom du produit -->
                    <span class="product-name">{{ detail.produit_nom }}</span>
                    <div class="product-info">
                      <!-- Quantité commandée -->
                      <span class="product-quantity">Quantité: {{ detail.quantite }}</span>
                      <!-- Prix unitaire -->
                      <span class="product-price">Prix: {{ detail.prix }} €</span>
                    </div>
                  </li>
                </ul>
              </div>

              <!-- Prix total de la commande -->
              <div class="order-total">
                <strong>Prix total: {{ getOrderTotal(order) }} €</strong>
              </div>

              <!-- Bouton pour prendre en charge la commande (visible uniquement pour les commandes "validées") -->
              <button
                v-if="order.statut.toLowerCase() === 'validée'"
                class="take-order-btn"
                @click="takeOrder(order.id)"
              >
                Prendre en charge la commande
              </button>
            </div>
          </div>

          <!-- Pagination pour naviguer entre les pages de commandes -->
          <div class="pagination">
            <!-- Bouton pour passer à la page précédente -->
            <button @click="previousPage" :disabled="currentPage === 1" class="pagination-btn">
              Précédent
            </button>
            <!-- Affiche le numéro de la page actuelle et le nombre total de pages -->
            <span class="pagination-info">Page {{ currentPage }} sur {{ totalPages }}</span>
            <!-- Bouton pour passer à la page suivante -->
            <button @click="nextPage" :disabled="currentPage === totalPages" class="pagination-btn">
              Suivant
            </button>
          </div>
        </div>
      </div>

      <!-- Colonne droite : Chemin optimal pour traiter la commande -->
      <div class="optimal-path-container" v-if="optimalPath.length">
        <h3>Chemin Optimal</h3>
        <div class="optimal-path-list">
          <!-- Liste des produits à récupérer dans l'ordre optimal -->
          <ul>
            <li v-for="(product, index) in optimalPath" :key="index">
              <!-- Nom et emplacement du produit -->
              <strong>{{ product.nom }}</strong> - Emplacement: ({{ product.x }}, {{ product.y }})
            </li>
          </ul>
        </div>
        
        <!-- Bouton pour terminer la commande (désactivé si aucune commande en cours ou si elle est déjà terminée) -->
        <button
          class="take-order-btn"
          @click="completeOrder(currentOrderId)"
          :disabled="!currentOrderId || isOrderCompleted"
        >
          Terminer la commande
        </button>
      </div>
    </div>
  </div>
</template>


<script>
import Navbar from './NavbarAdmin.vue';

export default {
  name: "CommandesAdmin",
  components: { Navbar },
  
  // Déclaration des données du composant
  data() {
    return {
      orders: [],  // Liste des commandes
      produits: [],  // Liste des produits
      loading: true,  // État de chargement des données
      currentPage: 1,  // Page courante pour la pagination
      ordersPerPage: 3,  // Nombre de commandes par page
      optimalPath: [],  // Liste des produits dans l'ordre optimal
      errorMessage: '',  // Message d'erreur
      currentOrderId: null,  // ID de la commande active
      isOrderCompleted: false,  // État de la commande active (si terminée ou non)
    };
  },
  
  // Calcul des commandes à afficher pour la page courante
  computed: {
    paginatedOrders() {
      const startIndex = (this.currentPage - 1) * this.ordersPerPage;
      const endIndex = startIndex + this.ordersPerPage;
      return this.orders.slice(startIndex, endIndex);  // Sélectionne les commandes pour la page courante
    },
    
    // Calcul du nombre total de pages pour la pagination
    totalPages() {
      return Math.ceil(this.orders.length / this.ordersPerPage);
    },
  },
  
  // Récupération des données des commandes et des produits au montage du composant
  async mounted() {
    try {
      // Récupère les commandes depuis l'API
      const response = await fetch('/api/orders/user/admin', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        },
      });

      // Si la réponse est correcte, on trie les commandes par date
      if (response.ok) {
        const data = await response.json();
        this.orders = data.sort((a, b) => new Date(b.date) - new Date(a.date));
      } else {
        throw new Error('Erreur lors de la récupération des commandes.');
      }

      // Récupère les produits depuis l'API
      const produitsResponse = await fetch('/api/produits', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      // Si la réponse est correcte, on récupère les produits
      if (produitsResponse.ok) {
        const produitsData = await produitsResponse.json();
        this.produits = produitsData;
      } else {
        throw new Error('Erreur lors de la récupération des produits.');
      }
    } catch (error) {
      // Si une erreur se produit, on affiche le message d'erreur
      this.errorMessage = error.message;
      console.error('Erreur de connexion', error);
    } finally {
      this.loading = false;  // Met à jour l'état de chargement
    }
  },
  
  // Définition des méthodes du composant
  methods: {
    // Gérer la page précédente de la pagination
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;  // Diminue la page courante
      }
    },
    
    // Gérer la page suivante de la pagination
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;  // Augmente la page courante
      }
    },
    
    // Calcul du total d'une commande
    getOrderTotal(order) {
      return order.details.reduce((total, detail) => {
        return total + detail.prix * detail.quantite;  // Calcule le total de la commande
      }, 0).toFixed(2);  // Retourne le total formaté en 2 décimales
    },
    
    // Marquer une commande comme prise en charge et calculer le chemin optimal
    takeOrder(orderId) {
      const order = this.orders.find(order => order.id === orderId);
      // Récupère les produits associés à la commande
      const orderedProducts = order.details.map(detail => {
        return this.produits.find(produit => produit.id === detail.produit_id);
      });

      // Calcule le chemin optimal pour récupérer les produits
      const startProduct = orderedProducts[0];
      this.optimalPath = this.dijkstra(startProduct, orderedProducts);
      this.currentOrderId = orderId;  // Définit la commande active
      this.isOrderCompleted = order.statut === 'Terminée';  // Vérifie si la commande est terminée
    },
    
    // Marquer la commande comme terminée dans l'API
    async completeOrder(orderId) {
      try {
        const response = await fetch(`/api/orders/complete/${orderId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          },
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Erreur lors de la mise à jour de la commande.');
        }

        // Mise à jour locale de l'état de la commande
        const updatedOrder = this.orders.find(order => order.id === orderId);
        if (updatedOrder) {
          updatedOrder.statut = 'Terminée';
          this.isOrderCompleted = true;  // Marque la commande comme terminée
        }

        alert('Commande terminée avec succès.');
      } catch (error) {
        console.error(error);
        alert('Impossible de terminer la commande. Veuillez réessayer.');
      }
    },
    
    // Algorithme de Dijkstra pour déterminer le chemin optimal des produits
    dijkstra(startProduct, orderedProducts) {
      const graph = this.buildGraph(orderedProducts);
      const path = [];
      const unvisitedProducts = [...orderedProducts];
      let currentId = startProduct.id;

      path.push(startProduct);

      // Recherche du produit le plus proche à chaque étape
      while (unvisitedProducts.length > 0) {
        const currentProduct = unvisitedProducts.find(p => p.id === currentId);
        unvisitedProducts.splice(unvisitedProducts.indexOf(currentProduct), 1);

        const nextProduct = unvisitedProducts.reduce((closest, product) => {
          const distance = Math.sqrt(
            Math.pow(product.x - currentProduct.x, 2) + Math.pow(product.y - currentProduct.y, 2)
          );
          return distance < closest.distance ? { product, distance } : closest;
        }, { product: null, distance: Infinity });

        if (nextProduct.product) {
          path.push(nextProduct.product);  // Ajoute le produit suivant au chemin
          currentId = nextProduct.product.id;
        } else {
          break;
        }
      }

      return path;  // Retourne le chemin optimal
    },
    
    // Construction du graphe des produits pour l'algorithme de Dijkstra
    buildGraph(products) {
      const graph = {};
      products.forEach(product => {
        graph[product.id] = products
          .filter(p => p.id !== product.id)
          .map(p => ({
            id: p.id,
            distance: Math.sqrt(
              Math.pow(p.x - product.x, 2) + Math.pow(p.y - product.y, 2)
            )
          }));
      });
      return graph;  // Retourne le graphe des distances
    },
  },
};
</script>


<style scoped>
.a-commande-app {
  font-family: 'Arial', sans-serif;
  color: #333;
  background: #f4f6f9;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem;
}

.content-container {
  display: flex;
  gap: 30px;
  width: 100%;
  align-items: flex-start; /* Alignement parfait au niveau du haut */
  justify-content: center;
}

.commandes-container,
.optimal-path-container {
  background-color: #fff;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 30px;
  width: 100%;
  max-width: 600px; /* Largeur maximale pour un rendu centré et équilibré */
}

.commandes-container {
  flex: 2;
}

.optimal-path-container {
  flex: 1;
  margin-top: 3rem;
}

.title {
  text-align: center;
  font-size: 2.8rem;
  color: #2c3e50;
  margin-bottom: 40px;
  font-weight: bold;
}

.loading,
.no-orders,
.error {
  text-align: center;
  font-size: 1.4rem;
  color: #888;
}

.error {
  color: #e74c3c;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background-color: #fafafa;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
}

.order-header {
  margin-bottom: 15px;
}

.order-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.4rem;
  font-weight: bold;
  color: #34495e;
  gap: 15px; /* Ajout d'espace entre les éléments */
}

.order-id {
  color: #34495e;
}

.order-status {
  font-style: italic;
  font-weight: bold;
  padding: 5px 10px;
  border-radius: 8px;
  text-transform: capitalize;
  white-space: nowrap;
}

.order-status.terminée {
  background-color: #e74c3c;
  color: #fff;
}

.order-status {
  background-color: #f39c12;
  color: #fff;
}

.order-status.validée {
  background-color: #27ae60;
  color: #fff;
}

.order-date {
  font-size: 1rem;
  color: #7f8c8d;
  margin-left: 10px; /* Espace spécifique entre statut et date */
}

.order-details ul {
  padding: 0;
  margin: 0;
  list-style: none;
}

.order-detail {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #ecf0f1;
}

.product-name {
  font-weight: bold;
  color: #3498db;
}

.product-info {
  display: flex;
  justify-content: space-between;
  width: 50%;
  font-size: 1rem;
}

.product-quantity,
.product-price {
  color: #7f8c8d;
}

.order-total {
  text-align: right;
  font-size: 1.3rem;
  font-weight: bold;
  color: #27ae60;
  margin-top: 20px;
}

.take-order-btn {
  margin-top: 15px;
  padding: 10px 20px;
  font-size: 1rem;
  color: #fff;
  background-color: #3498db;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.take-order-btn:hover {
  background-color: #2980b9;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 30px;
}

.pagination-btn {
  padding: 12px 25px;
  font-size: 1.1rem;
  color: #fff;
  background-color: #3498db;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin: 0 15px;
  transition: background-color 0.3s ease;
}

.pagination-btn:hover {
  background-color: #2980b9;
}

.pagination-btn:disabled {
  background-color: #bdc3c7;
}

.pagination-info {
  font-size: 1.2rem;
  color: #333;
}

/* Chemin optimal */
.optimal-path-container h3 {
  font-size: 2rem;
  font-weight: bold;
  margin-bottom: 20px;
  text-align: center;
  color: #2c3e50;
}

.optimal-path-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.optimal-path-list li {
  background-color: #f0f4f8;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease-in-out;
  display: flex;
  justify-content: space-between;
}

.optimal-path-list li:hover {
  transform: scale(1.02);
}

.optimal-path-list li strong {
  color: #34495e;
}


</style>
