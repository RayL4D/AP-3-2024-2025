<template>
  <div>
    <h1>Produits</h1>
    
    <!-- Affichage des catégories et des produits -->
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
    
    <!-- Panier -->
    <div v-if="cart.length > 0">
      <h2>Mon Panier</h2>
      <ul>
        <li v-for="item in cart" :key="item.produit.id">
          {{ item.produit.nom }} - {{ item.quantity }} x {{ item.produit.prix }} €
          <button @click="removeFromCart(item.produit.id)">Retirer</button>
        </li>
      </ul>
      <p><strong>Total: {{ totalPrice }} €</strong></p>
      <button @click="checkout">Passer à la commande</button>
    </div>
    <div v-else>
      <p>Le panier est vide.</p>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: [],
      quantities: {},
      cart: [], // Le panier
    };
  },
  mounted() {
    // Charger les catégories et les produits depuis l'API backend
    fetch('/api/categories')
      .then(response => response.json())
      .then(data => {
        this.categories = data;
      });
  },
  computed: {
    // Calculer le total du panier
    totalPrice() {
      return this.cart.reduce((total, item) => total + item.quantity * item.produit.prix, 0).toFixed(2);
    }
  },
  
  methods: {
    async checkout() {
    const produits = this.cart.map(item => ({
      id: item.produit.id,
      quantite: item.quantity
    }));

    try {
      const response = await fetch('/api/panier/ajouter', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ produits }),
      });

      const result = await response.json();
      if (response.ok) {
        console.log('Commande réussie:', result);
      } else {
        console.error('Erreur lors de la commande:', result);
      }
    } catch (error) {
      console.error('Erreur réseau:', error);
    }
  },
    // Ajouter un produit au panier
    addToCart(produit, quantity) {
      // Vérifier si le produit est déjà dans le panier
      const existingItem = this.cart.find(item => item.produit.id === produit.id);
      if (existingItem) {
        // Si le produit est déjà dans le panier, on augmente la quantité
        existingItem.quantity += quantity;
      } else {
        // Sinon, on l'ajoute au panier
        this.cart.push({ produit, quantity });
      }
      console.log(`Ajouté ${quantity} de ${produit.nom} au panier.`);

      // Envoi des données au backend pour ajouter au panier
      this.addProductToBackend(produit.id, quantity);
    },

    // Envoi des données du produit au backend pour ajout au panier
    addProductToBackend(produitId, quantity) {
      fetch(`/api/panier/${produitId}/ajouter`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ quantite: quantity }),
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'Produit ajouté au panier') {
            console.log('Produit ajouté au panier sur le backend.');
          } else {
            console.error('Erreur lors de l\'ajout au panier:', data);
          }
        })
        .catch(error => {
          console.error('Erreur réseau:', error);
        });
    },

    // Retirer un produit du panier
    removeFromCart(produitId) {
      this.cart = this.cart.filter(item => item.produit.id !== produitId);
      console.log(`Produit avec l'ID ${produitId} retiré du panier.`);
    },

    // Passer à la commande (simulé ici)
    checkout() {
      // Logique pour passer à la commande
      console.log('Commande passée:', this.cart);
      alert('Commande passée !');
      this.cart = []; // Vider le panier après la commande

      // Optionnel: envoyer la commande au backend pour traitement
      this.placeOrder();
    },

    placeOrder() {
      // Envoi de la commande au backend (si nécessaire)
      fetch('/api/commande', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ cart: this.cart }),
      })
        .then(response => response.json())
        .then(data => {
          console.log('Commande passée sur le backend:', data);
        })
        .catch(error => {
          console.error('Erreur lors de la commande:', error);
        });
    },
  }
};
</script>

<style scoped>
/* Style pour le composant panier */
button {
  margin-top: 10px;
  padding: 5px 10px;
  cursor: pointer;
}

h2 {
  margin-top: 20px;
}

ul {
  list-style-type: none;
  padding: 0;
}

li {
  margin-bottom: 10px;
}

input[type="number"] {
  width: 60px;
  margin-left: 10px;
}
</style>
