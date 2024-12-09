<template>
  <div class="client-app"> 
    <Navbar />
    <div class="dashboard">
      <div class="hero-section">
        <h1>Bienvenue dans notre boutique</h1>
        <p class="welcome-text">
          Découvrez nos produits exclusifs et profitez de promotions exceptionnelles.
        </p>
        <!-- Bouton désactivé si l'utilisateur a déjà une commande en cours -->
        <button 
          @click="createOrder" 
          class="cta-button" 
          :disabled="hasOrder"
        >
          Passer une commande
        </button>
      </div>
      <div class="features-section">
        <div class="feature">
          <img src="/images/shopping-cart.png" alt="Shopping Cart" class="feature-icon" />
          <h2>Produits de qualité</h2>
          <p>Explorez notre large gamme de produits soigneusement sélectionnés pour vous.</p>
        </div>
        <div class="feature">
          <img src="/images/delivery-truck.png" alt="Delivery Truck" class="feature-icon" />
          <h2>Livraison rapide</h2>
          <p>Recevez vos commandes en un temps record grâce à notre service efficace.</p>
        </div>
        <div class="feature">
          <img src="/images/support.png" alt="Support" class="feature-icon" />
          <h2>Support client</h2>
          <p>Notre équipe est disponible pour répondre à toutes vos questions.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from "./NavbarClient.vue";

export default {
  name: "ClientHome",
  components: {
    Navbar,
  },
  data() {
    return {
      hasOrder: false,  // Etat de la commande en cours
    };
  },
  mounted() {
    // Vérifier si l'utilisateur a une commande en cours lors du chargement de la page
    this.checkOrderStatus();
  },
  methods: {
    async checkOrderStatus() {
      try {
        const response = await fetch('/api/orders/check');
        const data = await response.json();
        this.hasOrder = data.hasOrder;  // Met à jour l'état en fonction de la réponse
      } catch (error) {
        console.error("Erreur lors de la vérification de la commande :", error);
      }
    },
    async createOrder() {
      try {
        const response = await fetch("/api/orders", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            date: new Date().toISOString(),
            statut: "En cours de création",
          }),
        });

        if (response.ok) {
          alert("Commande créée avec succès !");
          window.location.href = "/commande";
        } else {
          alert("Une erreur s'est produite lors de la création de la commande.");
        }
      } catch (error) {
        console.error("Erreur lors de la création de la commande :", error);
        alert("Une erreur s'est produite. Veuillez réessayer plus tard.");
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

.dashboard {
  width: 100%;
  max-width: 1200px;
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero-section {
  text-align: center;
  margin-bottom: 3rem;
}

.hero-section h1 {
  font-size: 3rem;
  color: #34495e;
  margin-bottom: 1rem;
}

.hero-section .welcome-text {
  font-size: 1.5rem;
  color: #7f8c8d;
  margin-bottom: 2rem;
}

.cta-button {
  padding: 1rem 2rem;
  font-size: 1.2rem;
  color: white;
  background-color: #e74c3c;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.cta-button:hover {
  background-color: #c0392b;
}

.features-section {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
  gap: 2rem;
  width: 100%;
}

.feature {
  background: white;
  border-radius: 10px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  padding: 1.5rem;
  text-align: center;
  flex: 1 1 300px;
  max-width: 300px;
}

.feature-icon {
  width: 80px;
  height: 80px;
  margin-bottom: 1rem;
}

.feature h2 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.feature p {
  font-size: 1rem;
  color: #7f8c8d;
}

.cta-button:disabled {
  background-color: #ccc;  /* Couleur grise pour le bouton désactivé */
  cursor: not-allowed;  /* Curseur "non autorisé" */
}

</style>
