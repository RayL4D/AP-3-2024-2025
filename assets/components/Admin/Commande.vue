<template>
  <div class="a-commande-app">
    <Navbar />
    <div class="content-container">
      <!-- Colonne gauche : Commandes -->
      <div class="commandes-container">
        <h2 class="title">Commandes Administrateur</h2>

        <div v-if="loading" class="loading">Chargement des commandes...</div>
        <div v-else-if="orders.length === 0" class="no-orders">Vous n'avez aucune commande.</div>
        <div v-else>
          <div class="orders-list">
            <div v-for="order in paginatedOrders" :key="order.id" class="order-card">
              <div class="order-header">
                <h3 class="order-title">
                  <span class="order-id">Commande #{{ order.id }}</span>
                  <span :class="['order-status', order.statut.toLowerCase().replace(/ /g, '-')]">{{ order.statut }}</span>
                  <span class="order-date">{{ order.date }}</span>
                </h3>
              </div>

              <div class="order-details">
                <ul>
                  <li v-for="detail in order.details" :key="detail.produit_id" class="order-detail">
                    <span class="product-name">{{ detail.produit_nom }}</span>
                    <div class="product-info">
                      <span class="product-quantity">Quantité: {{ detail.quantite }}</span>
                      <span class="product-price">Prix: {{ detail.prix }} €</span>
                    </div>
                  </li>
                </ul>
              </div>

              <div class="order-total">
                <strong>Prix total: {{ getOrderTotal(order) }} €</strong>
              </div>

              <button class="take-order-btn" @click="takeOrder(order.id)">Prendre en charge la commande</button>
            </div>
          </div>

          <div class="pagination">
            <button @click="previousPage" :disabled="currentPage === 1" class="pagination-btn">Précédent</button>
            <span class="pagination-info">Page {{ currentPage }} sur {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="pagination-btn">Suivant</button>
          </div>
        </div>
      </div>

      <!-- Colonne droite : Chemin optimal -->
      <div class="optimal-path-container" v-if="optimalPath.length">
        <h3>Chemin Optimal</h3>
        <div class="optimal-path-list">
          <ul>
            <li v-for="(product, index) in optimalPath" :key="index">
              <strong>{{ product.nom }}</strong> - Emplacement: ({{ product.x }}, {{ product.y }})
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import Navbar from './NavbarAdmin.vue';

export default {
  name: "Commandes Admin  ",
  components: { Navbar },
  data() {
    return {
      orders: [],
      produits: [], // Ajout des produits pour les récupérer et les utiliser
      loading: true,
      currentPage: 1,
      ordersPerPage: 3,
      optimalPath: [],
      errorMessage: '', // Message d'erreur
    };
  },
  computed: {
    paginatedOrders() {
      const startIndex = (this.currentPage - 1) * this.ordersPerPage;
      const endIndex = startIndex + this.ordersPerPage;
      return this.orders.slice(startIndex, endIndex);
    },
    totalPages() {
      return Math.ceil(this.orders.length / this.ordersPerPage);
    },
  },
  async mounted() {
    try {
      // Récupération des commandes
      const response = await fetch('/api/orders/user/admin', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        },
      });

      if (response.ok) {
        const data = await response.json();
        this.orders = data.sort((a, b) => new Date(b.date) - new Date(a.date));
      } else {
        throw new Error('Erreur lors de la récupération des commandes.');
      }

      // Récupérer les produits via une API
      const produitsResponse = await fetch('/api/produits', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (produitsResponse.ok) {
        const produitsData = await produitsResponse.json();
        this.produits = produitsData;
      } else {
        throw new Error('Erreur lors de la récupération des produits.');
      }

    } catch (error) {
      this.errorMessage = error.message;
      console.error('Erreur de connexion', error);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },

    getOrderTotal(order) {
      return order.details.reduce((total, detail) => {
        return total + detail.prix * detail.quantite;
      }, 0).toFixed(2);
    },

    takeOrder(orderId) {
      const order = this.orders.find(order => order.id === orderId);
      const orderedProducts = order.details.map(detail => {
        return this.produits.find(produit => produit.id === detail.produit_id);
      });

      const startProduct = orderedProducts[0];
      this.optimalPath = this.dijkstra(startProduct, orderedProducts);
    },

    // Implémentation basique de l'algorithme de Dijkstra
    dijkstra(startProduct, orderedProducts) {
      // Exemple simple : trier les produits en fonction de leur position
      // Vous pouvez adapter cette fonction selon les positions réelles des produits dans votre base de données.
      return orderedProducts.sort((a, b) => {
        const distanceA = Math.sqrt(Math.pow(a.x - startProduct.x, 2) + Math.pow(a.y - startProduct.y, 2));
        const distanceB = Math.sqrt(Math.pow(b.x - startProduct.x, 2) + Math.pow(b.y - startProduct.y, 2));
        return distanceA - distanceB;
      });
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
  white-space: nowrap; /* Empêche la coupure en cas de texte long */
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
