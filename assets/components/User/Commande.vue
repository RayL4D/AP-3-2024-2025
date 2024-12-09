<template>
    <div class="commande-app">
      <NavbarClient />
      <div class="commande-container">
        <div class="commande-header">
          <h1>Votre Commande</h1>
          <p class="commande-subtitle">Vérifiez les détails de votre commande avant validation.</p>
        </div>
        
        <div class="commande-details">
          <h2>Détails de la commande</h2>
          <div v-if="commande">
            <ul class="commande-items">
              <li v-for="item in commande.items" :key="item.id" class="commande-item">
                <div class="item-info">
                  <span class="item-name">{{ item.name }}</span>
                  <span class="item-quantity">Quantité : {{ item.quantity }}</span>
                </div>
                <span class="item-price">{{ item.price.toFixed(2) }} €</span>
              </li>
            </ul>
            <div class="commande-total">
              <span>Total :</span>
              <strong>{{ commande.total.toFixed(2) }} €</strong>
            </div>
            <button class="cta-button" @click="validerCommande">Valider la commande</button>
          </div>
          <div v-else>
            <p>Votre commande est vide. Ajoutez des articles pour commencer !</p>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import NavbarClient from "./NavbarClient.vue";
  
  export default {
    name: "Commande",
    components: {
      NavbarClient,
    },
    data() {
      return {
        commande: null, // Exemple de structure : { items: [{ id, name, quantity, price }], total }
      };
    },
    mounted() {
      this.fetchCommande();
    },
    methods: {
      async fetchCommande() {
        try {
          // Exemple de requête API pour récupérer la commande
          const response = await fetch('/api/orders/current');
          if (response.ok) {
            this.commande = await response.json();
          } else {
            console.error("Erreur lors de la récupération de la commande");
          }
        } catch (error) {
          console.error("Erreur réseau :", error);
        }
      },
      async validerCommande() {
        try {
          const response = await fetch('/api/orders/validate', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
          });
          if (response.ok) {
            alert('Votre commande a été validée avec succès !');
            this.commande = null; // Réinitialiser après validation
          } else {
            alert("Erreur lors de la validation de la commande.");
          }
        } catch (error) {
          console.error("Erreur réseau :", error);
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .commande-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .commande-header {
    text-align: center;
    margin-bottom: 30px;
  }
  
  .commande-subtitle {
    color: #555;
    font-size: 1rem;
  }
  
  .commande-details {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }
  
  .commande-items {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
  }
  
  .commande-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eaeaea;
  }
  
  .item-info {
    display: flex;
    flex-direction: column;
  }
  
  .item-name {
    font-weight: bold;
  }
  
  .item-quantity {
    color: #777;
    font-size: 0.9rem;
  }
  
  .item-price {
    font-weight: bold;
    color: #333;
  }
  
  .commande-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
    margin-top: 10px;
  }
  
  .cta-button {
    display: inline-block;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 20px;
    text-align: center;
  }
  
  .cta-button:hover {
    background-color: #0056b3;
  }
  
  .cta-link {
    color: #007bff;
    text-decoration: none;
  }
  
  .cta-link:hover {
    text-decoration: underline;
  }
  </style>
  