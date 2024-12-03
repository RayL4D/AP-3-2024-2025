<template>
  <div>
    <h1>Mon Panier</h1>
    <ul>
      <li v-for="item in panier" :key="item.produit.id">
        {{ item.produit.nom }} - {{ item.quantite }} x {{ item.produit.prix }} €
        <button @click="supprimerProduit(item.produit.id)">Supprimer</button>
      </li>
    </ul>
    <button @click="confirmerPanier">Confirmer la commande</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      panier: []
    };
  },
  mounted() {
    this.fetchPanier();
  },
  methods: {
    async fetchPanier() {
      const response = await fetch('/api/panier');
      const data = await response.json();
      this.panier = data.panier;
    },
    async confirmerPanier() {
      const response = await fetch('/api/panier/confirmer', { method: 'POST' });
      const data = await response.json();
      alert(data.message);
      this.fetchPanier();
    },
    async supprimerProduit(produitId) {
      await fetch(`/api/panier/supprimer/${produitId}`, { method: 'DELETE' });
      this.fetchPanier();
    }
  }
};
</script>




<style scoped>
.produits-app {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 1rem;
  background: #fff;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}

.produits-app h1 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #34495e;
}

form {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

input, select {
  margin-bottom: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.button-group {
  display: flex;
  gap: 10px;
}

button {
  padding: 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-success {
  background-color: #28a745;
  color: white;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-danger {
  background-color: #dc3545;
  color: white;
}

.btn-danger:hover {
  background-color: #c82333;
}

.table-container {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 10px;
}

.table-header,
.table-row {
  background-color: #ecf0f1;
  border-radius: 5px;
  display: contents;
  font-weight: bold;
  text-align: center;
}

.table-header {
  background-color: #bdc3c7;
  font-weight: bold;
}

.table-row div {
  padding: 0.75rem;
  transition: background-color 0.3s ease;
}

.table-row:nth-child(even) div {
  background-color: #f4f4f4;
}

.table-row div:hover {
  background-color: #dfe6e9;
}

.table-row div {
  border-right: 1px solid #ddd;
}

.table-row div:last-child {
  border-right: none;
}
</style>
