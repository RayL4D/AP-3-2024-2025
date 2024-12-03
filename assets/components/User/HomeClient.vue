<template>
  <div>
    <h1>Produits</h1>
    <div v-for="categorie in categories" :key="categorie.id">
      <h2>{{ categorie.nom }}</h2>
      <ul>
        <li v-for="produit in categorie.produits" :key="produit.id">
          {{ produit.nom }} - {{ produit.prix }} €
          <input 
            type="number" 
            v-model.number="quantities[produit.id]" 
            min="1" 
          />
          <button @click="addToCart(produit, quantities[produit.id] || 1)">
            Ajouter au panier
          </button>
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
    fetch('/api/categories')
      .then(response => response.json())
      .then(data => {
        this.categories = data;
      });
  },
  methods: {
    addToCart(produit, quantity) {
        console.log('Tentative d\'ajout au panier :', produit, quantity);

        const data = {
            produit_id: produit.id,
            quantite: quantity || 1
        };

        fetch('/api/panier/ajouter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de l\'ajout au panier.');
            }
            return response.json();
        })
        .then(result => {
            console.log('Résultat de l\'API :', result);
        })
        .catch(error => {
            console.error('Erreur lors de l\'appel API :', error);
        });
    }
  }
};
</script>

  
  <style scoped>
  /* Ajoutez votre style ici */
  </style>
  