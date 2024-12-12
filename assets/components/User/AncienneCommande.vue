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
              <h3 class="order-title">Commande #{{ order.id }} - {{ order.statut }} ({{ order.date }})</h3>
            </div>
            <ul class="order-details">
              <li v-for="detail in order.details" :key="detail.produit_id" class="order-detail">
                <span class="product-name">{{ detail.produit_nom }}</span>
                <span class="product-quantity">Quantité commandée : {{ detail.quantite }}</span>
                <span class="product-price">Prix : {{ detail.prix }} €</span>
              </li>
            </ul>
            <!-- Prix total de la commande -->
            <div class="order-total">
              <strong>Prix total : {{ getOrderTotal(order) }} €</strong>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <button @click="previousPage" :disabled="currentPage === 1">Précédent</button>
          <span>Page {{ currentPage }} sur {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages">Suivant</button>
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
  background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
}

.commandes-container {
  max-width: 1200px;
  margin-top: 2rem;
  padding: 20px;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.title {
  text-align: center;
  font-size: 2rem;
  color: #333;
  margin-bottom: 20px;
}

.loading,
.no-orders {
  text-align: center;
  font-size: 1.2rem;
  color: #888;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
}

.order-card {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
}

.order-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
}

.order-header {
  margin-bottom: 15px;
}

.order-title {
  font-size: 1.4rem;
  color: #333;
}

.order-total {
  text-align: right;
  font-size: 1.2rem;
  font-weight: bold;
  color: #007bff;
  margin-top: 20px;
}

.order-details {
  list-style: none;
  padding: 0;
}

.order-detail {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 1rem;
  color: #555;
}

.order-detail span {
  display: block;
  margin-bottom: 5px;
}

.product-name {
  font-weight: bold;
  color: #007bff;
}

.product-quantity,
.product-price {
  color: #333;
}

.product-price {
  font-weight: bold;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

.pagination button {
  padding: 10px 20px;
  font-size: 1rem;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin: 0 10px;
}

.pagination button:disabled {
  background-color: #ccc;
}

.pagination span {
  font-size: 1.2rem;
  color: #333;
}
</style>
