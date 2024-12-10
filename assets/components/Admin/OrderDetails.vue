<template>
  <div class="admin-app">
    <Navbar />

    <!-- Vue des commandes administrateurs -->
    <div v-if="!selectedOrder" class="commandes-container">
      <h2 class="title">Commandes Administrateur</h2>

      <div v-if="loading" class="loading">Chargement des commandes...</div>
      <div v-else-if="orders.length === 0" class="no-orders">Aucune commande disponible.</div>

      <div v-else>
        <ul class="orders-list">
          <li v-for="order in orders" :key="order.id" class="order-item">
            <div class="order-header">
              <h3 class="order-title">
                Commande #{{ order.id }} - {{ order.statut }} ({{ order.date }})
              </h3>
              <p class="order-user">
                Créée par : {{ order.created_by.nom }} ({{ order.created_by.email }})
              </p>
            </div>
            <ul class="order-details">
              <li v-for="detail in order.details" :key="detail.produit_id" class="order-detail">
                <span class="product-name">{{ detail.produit_nom }}</span>
                <span class="product-quantity">Quantité : {{ detail.quantite }}</span>
                <span class="product-price">Prix : {{ detail.prix }} €</span>
                <span class="stock-quantity">
                  Stock : {{ detail.stock_quantite !== null ? detail.stock_quantite : 'Indisponible' }}
                </span>
              </li>
            </ul>
            <div class="order-actions">
              <button @click="goToDetails(order)" class="btn-primary">
                Prendre en charge la commande
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <!-- Vue des détails de la commande avec itinéraire -->
    <div v-if="selectedOrder" class="order-details-view">
      <h2>Détails de la Commande #{{ selectedOrder.id }}</h2>
      <p>Statut : {{ selectedOrder.statut }}</p>
      <p>Créée par : {{ selectedOrder.created_by.nom }} ({{ selectedOrder.created_by.email }})</p>

      <h3>Détails des produits :</h3>
      <ul>
        <li v-for="detail in selectedOrder.details" :key="detail.produit_id">
          <strong>{{ detail.produit_nom }}</strong> - Quantité: {{ detail.quantite }} - Prix: {{ detail.prix }} €
        </li>
      </ul>

      <h3>Itinéraire pour récupérer les produits :</h3>
      <canvas id="routeChart" width="400" height="200"></canvas>

      <button @click="backToOrders" class="btn-primary">Retour aux commandes</button>
    </div>
  </div>
</template>

<script>
import Navbar from './NavbarAdmin.vue';
import { Chart } from 'chart.js';

export default {
  name: "AdminOrders",
  components: { Navbar },
  data() {
    return {
      orders: [],
      loading: true,
      selectedOrder: null, // Commande sélectionnée
    };
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await fetch('/api/orders/user/admin', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          },
        });
        if (response.ok) {
          this.orders = await response.json();
        } else {
          console.error('Erreur lors de la récupération des commandes.');
        }
      } catch (error) {
        console.error('Erreur de connexion', error);
      } finally {
        this.loading = false;
      }
    },
    goToDetails(order) {
      // Stocke la commande sélectionnée
      this.selectedOrder = order;
      this.displayRoute(order.details);
    },
    backToOrders() {
      this.selectedOrder = null; // Retour à la liste des commandes
    },
    displayRoute(details) {
      // Simuler les coordonnées des produits dans un entrepôt
      const productLocations = details.map(detail => ({
        name: detail.produit_nom,
        x: Math.random() * 100, // Coordonnée X simulée
        y: Math.random() * 100, // Coordonnée Y simulée
      }));

      // Algorithme basique pour afficher un itinéraire (par exemple, juste les produits dans un ordre aléatoire)
      const routeData = {
        labels: productLocations.map(item => item.name),
        datasets: [{
          label: 'Itinéraire des produits',
          data: productLocations.map(item => ({x: item.x, y: item.y})),
          borderColor: '#007bff',
          fill: false,
        }],
      };

      // Créer le graphique avec Chart.js
      const ctx = document.getElementById('routeChart').getContext('2d');
      new Chart(ctx, {
        type: 'scatter',
        data: routeData,
        options: {
          responsive: true,
          scales: {
            x: {
              min: 0,
              max: 100,
              ticks: {
                stepSize: 10,
              },
            },
            y: {
              min: 0,
              max: 100,
              ticks: {
                stepSize: 10,
              },
            },
          },
        },
      });
    },
  },
  mounted() {
    this.fetchOrders();
  },
};
</script>

<style scoped>
.admin-app {
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
  background: #fff;
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
}

.order-item {
  background: #fff;
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

.order-user {
  font-size: 1rem;
  color: #555;
}

.order-details {
  list-style: none;
  padding: 0;
}

.order-detail {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  font-size: 1rem;
  color: #555;
}

.product-name {
  font-weight: bold;
  color: #007bff;
}

.order-actions {
  text-align: right;
  margin-top: 15px;
}

.btn-primary {
  background-color: #007bff;
  color: #fff;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.order-details-view {
  max-width: 1200px;
  margin-top: 2rem;
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

canvas {
  margin-top: 20px;
  max-width: 100%;
}
</style>
