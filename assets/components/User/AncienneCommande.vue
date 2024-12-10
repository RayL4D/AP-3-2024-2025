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
        <ul class="orders-list">
          <li v-for="order in orders" :key="order.id" class="order-item">
            <div class="order-header">
              <h3 class="order-title">Commande #{{ order.id }} - {{ order.statut }} ({{ order.date }})</h3>
            </div>
            <ul class="order-details">
              <li v-for="detail in order.details" :key="detail.produit_id" class="order-detail">
                <span class="product-name">{{ detail.produit_nom }}</span>
                <span class="product-quantity">Quantité commandée : {{ detail.quantite }}</span>
                <span class="product-price">Prix : {{ detail.prix }} €</span>
                <span class="stock-quantity">
                  Quantité en stock : {{ detail.stock_quantite !== null ? detail.stock_quantite : 'Non disponible' }}
                </span>
              </li>
            </ul>
          </li>
        </ul>
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
    };
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
          this.orders = data;
        } else {
          console.error('Erreur lors de la récupération des commandes');
        }
      } catch (error) {
        console.error('Erreur de connexion à l\'API', error);
      } finally {
        this.loading = false;
      }
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
  list-style: none;
  padding: 0;
  margin: 0;
}

.order-item {
  background-color: #fff;
  padding: 20px;
  margin-bottom: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
}

.order-item:hover {
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
.product-price,
.stock-quantity {
  color: #333;
}

.product-price {
  font-weight: bold;
}

.stock-quantity {
  font-size: 0.9rem;
  color: #888;
}
</style>
