<template>
  <div>
    <h2>Mes Commandes</h2>
    <div v-if="loading">Chargement des commandes...</div>
    <div v-else-if="orders.length === 0">Vous n'avez aucune commande.</div>
    <div v-else>
      <ul>
        <li v-for="order in orders" :key="order.id">
          <h3>Commande #{{ order.id }} - {{ order.statut }} ({{ order.date }})</h3>
          <ul>
            <li v-for="detail in order.details" :key="detail.produit_id">
              {{ detail.produit_nom }} - Quantité commandée: {{ detail.quantite }} - Prix: {{ detail.prix }} € 
              <br>
              Quantité en stock: {{ detail.stock_quantite !== null ? detail.stock_quantite : 'Non disponible' }}
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      orders: [],
      loading: true,
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await fetch('/api/orders/user', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`, // Assurez-vous d'avoir un token pour l'authentification
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
/* Ajoutez votre style ici */
</style>
