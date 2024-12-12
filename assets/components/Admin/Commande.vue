<template>
  <div class="admin-app">
    <Navbar />

    <!-- Vue des commandes administrateurs -->
    <div v-if="!selectedOrder" class="commandes-container">
      <h2 class="title">Commandes Administrateur</h2>
      <div v-if="loading" class="loading">Chargement des commandes...</div>
      <div v-else-if="orders.length === 0" class="no-orders">Aucune commande disponible.</div>
      <div v-else>
        <!-- Liste simplifiée des commandes -->
        <ul class="orders-list">
          <li v-for="order in orders" :key="order.id" class="order-item">
            <div class="order-summary">
              <h3>
                Commande #{{ order.id }} - {{ order.statut }}
              </h3>
              <p>
                Créée par : {{ order.created_by.nom }} ({{ order.created_by.email }})
              </p>
              <p>
                Date : {{ order.date }}
              </p>
            </div>
            <ul class="order-products">
              <li v-for="detail in order.details" :key="detail.produit_id">
                <strong>{{ detail.produit_nom }}</strong>
                - Quantité: {{ detail.quantite }}
                - Prix: {{ detail.prix }} €
                - Stock: {{ detail.stock_quantite !== null ? detail.stock_quantite : 'Indisponible' }}
              </li>
            </ul>
            <button @click="goToDetails(order)" class="btn-primary">
              Prendre en charge la commande
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Vue des détails de la commande -->
    <div v-if="selectedOrder" class="order-details-view">
      <h2>Détails de la Commande #{{ selectedOrder.id }}</h2>
      <p>Statut : {{ selectedOrder.statut }}</p>
      <p>Créée par : {{ selectedOrder.created_by.nom }} ({{ selectedOrder.created_by.email }})</p>
      <h3>Détails des produits :</h3>
      <ul>
        <li v-for="detail in selectedOrder.details" :key="detail.produit_id">
          <strong>{{ detail.produit_nom }}</strong>
          - Quantité: {{ detail.quantite }}
          - Prix: {{ detail.prix }} €
        </li>
      </ul>

      <h3>Itinéraire optimal pour récupérer les produits :</h3>
      <ul>
        <li v-for="(step, index) in optimalRoute" :key="index">
          Étape {{ index + 1 }} : {{ step }}
        </li>
      </ul>

      <button @click="backToOrders" class="btn-primary">Retour aux commandes</button>
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
      loading: true,
      selectedOrder: null,
      optimalRoute: [], // Stockage de l'itinéraire optimal
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
      this.selectedOrder = order;
      this.calculateOptimalRoute(order.details);
    },
    backToOrders() {
      this.selectedOrder = null;
      this.optimalRoute = [];
    },
    calculateOptimalRoute(details) {
      // Simuler les coordonnées des produits et un graphe des distances
      const productLocations = details.map((detail, index) => ({
        id: index,
        name: detail.produit_nom,
        lat: Math.random() * 90 - 45,
        lng: Math.random() * 180 - 90,
      }));

      const graph = this.generateGraph(productLocations);
      const startNode = 0; // Point de départ (simulé)
      const route = this.dijkstra(graph, startNode);

      // Construire l'itinéraire à partir des indices des nœuds
      this.optimalRoute = route.map(index => productLocations[index].name);
    },
    generateGraph(locations) {
      const graph = Array.from({ length: locations.length }, () => Array(locations.length).fill(Infinity));

      for (let i = 0; i < locations.length; i++) {
        for (let j = 0; j < locations.length; j++) {
          if (i !== j) {
            const distance = this.calculateDistance(locations[i], locations[j]);
            graph[i][j] = distance;
          }
        }
      }

      return graph;
    },
    calculateDistance(pointA, pointB) {
      // Formule de distance simplifiée (Euclidienne)
      return Math.sqrt(
        Math.pow(pointA.lat - pointB.lat, 2) + Math.pow(pointA.lng - pointB.lng, 2)
      );
    },
    dijkstra(graph, startNode) {
      const distances = Array(graph.length).fill(Infinity);
      const visited = Array(graph.length).fill(false);
      const previousNodes = Array(graph.length).fill(null);

      distances[startNode] = 0;

      for (let i = 0; i < graph.length; i++) {
        const currentNode = this.findClosestNode(distances, visited);
        if (currentNode === -1) break; // Aucun nœud atteignable

        visited[currentNode] = true;

        for (let neighbor = 0; neighbor < graph[currentNode].length; neighbor++) {
          if (!visited[neighbor] && graph[currentNode][neighbor] !== Infinity) {
            const newDist = distances[currentNode] + graph[currentNode][neighbor];
            if (newDist < distances[neighbor]) {
              distances[neighbor] = newDist;
              previousNodes[neighbor] = currentNode;
            }
          }
        }
      }

      // Reconstituer le chemin à partir des nœuds précédents
      const route = [];
      let currentNode = distances.indexOf(Math.min(...distances));

      while (currentNode !== null) {
        route.unshift(currentNode);
        currentNode = previousNodes[currentNode];
      }

      return route;
    },
    findClosestNode(distances, visited) {
      let minDistance = Infinity;
      let closestNode = -1;

      for (let i = 0; i < distances.length; i++) {
        if (!visited[i] && distances[i] < minDistance) {
          minDistance = distances[i];
          closestNode = i;
        }
      }

      return closestNode;
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
  padding: 1rem;
}

.commandes-container {
  max-width: 800px;
  margin: auto;
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.title {
  text-align: center;
  font-size: 1.8rem;
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
  border-bottom: 1px solid #ddd;
  padding: 10px 0;
}

.order-summary h3 {
  font-size: 1.2rem;
  color: #333;
  margin: 0;
}

.order-summary p {
  margin: 5px 0;
  color: #555;
}

.order-products {
  list-style: none;
  margin: 10px 0 0 20px;
  padding: 0;
}

.order-products li {
  margin: 5px 0;
}

.btn-primary {
  margin-top: 10px;
  padding: 5px 10px;
  color: #fff;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.order-details-view {
  max-width: 800px;
  margin: auto;
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
</style>
