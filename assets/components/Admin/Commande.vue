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
  name: "AdminOrders",
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

.commandes-container {
  max-width: 1200px;
  margin-top: 3rem;
  padding: 25px;
  background-color: #fff;
  border-radius: 15px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.title {
  text-align: center;
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 30px;
  font-weight: 700;
}

.loading,
.no-orders,
.error {
  text-align: center;
  font-size: 1.3rem;
  color: #888;
}

.error {
  color: red;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.order-card {
  background-color: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.order-header {
  margin-bottom: 20px;
}

.order-title {
  display: flex;
  justify-content: space-between;
  font-size: 1.5rem;
  font-weight: 600;
  color: #ff8c00;
}

.order-id {
  color: #000000;
}

.order-status {
  font-style: italic;
  font-weight: 600;
}

.order-status.en-cours-de-creation {
  color: #f39c12;
}

.order-status.validée {
  color: #27ae60;
}

.order-date {
  color: #888;
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
  border-bottom: 1px solid #f0f0f0;
}

.product-name {
  font-weight: bold;
  color: #007bff;
}

.product-info {
  display: flex;
  justify-content: space-between;
  width: 50%;
  font-size: 1rem;
}

.product-quantity,
.product-price {
  color: #555;
}

.order-total {
  text-align: right;
  font-size: 1.3rem;
  font-weight: bold;
  color: #27ae60;
  margin-top: 25px;
}

.take-order-btn {
  margin-top: 15px;
  padding: 10px 20px;
  font-size: 1rem;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.take-order-btn:hover {
  background-color: #0056b3;
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
  background-color: #007bff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin: 0 15px;
  transition: background-color 0.3s ease;
}

.pagination-btn:hover {
  background-color: #0056b3;
}

.pagination-btn:disabled {
  background-color: #ccc;
}

.pagination-info {
  font-size: 1.2rem;
  color: #333;
}

.content-container {
  display: flex;
  gap: 20px;
  width: 100%;
}

.content-container {
  display: flex;
  gap: 20px;
  width: 100%;
  align-items: flex-start; /* Aligne les deux colonnes au début */
}

.commandes-container {
  flex: 2;
}

.optimal-path-container {
  flex: 1;
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
  margin-top: 3rem; /* Ajuster pour correspondre au conteneur des commandes */
}

.optimal-path-container h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 15px;
  text-align: center;
}

</style>
