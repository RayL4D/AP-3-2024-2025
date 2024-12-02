<template>
    <div>
      <h1>Produits</h1>
      <div v-for="categorie in categories" :key="categorie.id">
        <h2>{{ categorie.nom }}</h2>
        <ul>
          <li v-for="produit in categorie.produits" :key="produit.id">
            {{ produit.nom }} - {{ produit.prix }} €
            <input type="number" v-model="quantities[produit.id]" min="1" />
            <button @click="addToCart(produit, quantities[produit.id] || 1)">Ajouter au panier</button>
          </li>
        </ul>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        categories: [],
        quantities: {}
      };
    },
    mounted() {
      // Fetch categories and products from backend
      fetch('/api/categories')
        .then(response => response.json())
        .then(data => {
          this.categories = data;
        });
    },
    methods: {
      addToCart(produit, quantity) {
        // Logic to add the product to the cart
        console.log(`Ajouté ${quantity} de ${produit.nom} au panier.`);
      }
    }
  };
  </script>
  
  <style scoped>
  /* Ajoutez votre style ici */
  </style>
  