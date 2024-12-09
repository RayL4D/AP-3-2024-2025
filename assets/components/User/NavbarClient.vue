<template>
    <nav class="navbar">
      <ul>
        <li><a href="/home"><i class="fas fa-home"></i> Accueil</a></li>
        <li><a href="/commande"><i class="fas fa-shopping-cart"></i> Commande</a></li>
        <li><a href="/panier"><i class="fas fa-shopping-cart"></i> Panier</a></li>
        <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
      </ul>
    </nav>
  </template>
  
  <script>
  export default {
    name: 'NavbarClient',
  };
  </script>
  
  <style scoped>
  .navbar {
    background-color: #2c3e50;
    padding: 0.5rem 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .navbar ul {
    list-style-type: none;
    display: flex;
    align-items: center;
  }
  
  .navbar li {
    margin: 0 1rem;
  }
  
  .navbar a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    align-items: center;
    transition: color 0.3s ease, transform 0.3s ease;
  }
  
  .navbar a i {
    margin-right: 0.5rem;
    font-size: 1.2rem;
  }
  
  .navbar a:hover {
    color: #1abc9c;
    transform: scale(1.05);
  }
  </style>
  
  <!-- Incluez ce lien dans votre fichier HTML principal pour Font Awesome -->
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> -->
  