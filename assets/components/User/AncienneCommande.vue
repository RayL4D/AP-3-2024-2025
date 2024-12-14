<template>
  <div class="client-app">
    <!-- Navbar avec le composant NavbarClient -->
    <Navbar />

    <div class="commandes-container">
      <h2 class="title">Mes Commandes</h2>

      <!-- Indicateurs de chargement et absence de commandes -->
      <div v-if="loading" class="loading">Chargement des commandes...</div>
      <div v-else-if="orders.length === 0" class="no-orders">Vous n'avez aucune commande.</div>

      <!-- Liste des commandes -->
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

            <!-- Prix total de la commande -->
            <div class="order-total">
              <strong>Prix total: {{ getOrderTotal(order) }} €</strong>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button @click="previousPage" :disabled="currentPage === 1" class="pagination-btn">Précédent</button>
          <span class="pagination-info">Page {{ currentPage }} sur {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="pagination-btn">Suivant</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from "./NavbarClient.vue"; // Importation de Navbar

export default {
  name: "ClientOrders",
  components: {
    Navbar, // Déclaration du composant Navbar
  },
  data() {
    return {
      orders: [],
      loading: true,
      currentPage: 1,
      ordersPerPage: 3, // Nombre de commandes par page modifié à 3
    };
  },
  computed: {
    // Calculer les commandes à afficher sur la page actuelle
    paginatedOrders() {
      const startIndex = (this.currentPage - 1) * this.ordersPerPage;
      const endIndex = startIndex + this.ordersPerPage;
      return this.orders.slice(startIndex, endIndex);
    },
    // Calculer le nombre total de pages
    totalPages() {
      return Math.ceil(this.orders.length / this.ordersPerPage);
    },
  },
  mounted() {
    this.fetchOrders(); // Récupération des commandes lors du montage
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await fetch('/api/orders/user', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          },
        });

        if (response.ok) {
          const data = await response.json();
          // Tri des commandes par date (les plus récentes en premier)
          this.orders = data.sort((a, b) => new Date(b.date) - new Date(a.date));
        } else {
          console.error('Erreur lors de la récupération des commandes');
        }
      } catch (error) {
        console.error('Erreur de connexion à l\'API', error);
      } finally {
        this.loading = false;
      }
    },
    // Fonction pour passer à la page précédente
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    // Fonction pour passer à la page suivante
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    // Fonction pour calculer le prix total d'une commande
    getOrderTotal(order) {
      return order.details.reduce((total, detail) => {
        return total + (detail.prix * detail.quantite);
      }, 0).toFixed(2); // Limite à deux décimales
    },
  },
};
</script>

<style scoped>
.client-app {
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
.no-orders {
  text-align: center;
  font-size: 1.3rem;
  color: #888;
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
  color: #f39c12; /* Jaune orangé */
}

.order-status.validée {
  color: #27ae60; /* Vert foncé */
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

.product-quantity, .product-price {
  color: #555;
}

.order-total {
  text-align: right;
  font-size: 1.3rem;
  font-weight: bold;
  color: #27ae60;
  margin-top: 25px;
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
</style>
