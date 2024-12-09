<template>
  <div class="panier-app">
    <NavbarClient />
    <div class="panier">
      <h1>Votre Panier</h1>
      <div v-if="details.length">
        <div v-for="(detail, index) in details" :key="index" class="panier-item">
          <h2>{{ detail.produit }}</h2>
          <p>Quantité : {{ detail.quantite }}</p>
          <p>Prix unitaire : {{ detail.prix_unitaire }}€</p>
          <p>Total : {{ detail.total }}€</p>
        </div>
      </div>
      <div v-else>
        <p>Votre panier est vide.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import NavbarClient from './NavbarClient.vue';

export default {
  name: 'Panier',
  components: {
    NavbarClient,
  },
  setup() {
    const details = ref([]);

    const fetchPanierDetails = async () => {
      try {
        const response = await fetch('/api/panier/details');
        const result = await response.json();
        if (result.status === 'success') {
          details.value = result.details;
        } else {
          details.value = [];
        }
      } catch (error) {
        console.error('Erreur lors du chargement des détails du panier:', error);
      }
    };

    onMounted(() => {
      fetchPanierDetails();
    });

    return {
      details,
    };
  },
};
</script>

  
  <style scoped>
  .panier-app {
    font-family: 'Arial', sans-serif;
    padding: 2rem;
  }
  
  .panier {
    background: #fff;
    padding: 1rem;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .panier-item {
    margin-bottom: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #ddd;
  }
  </style>
  