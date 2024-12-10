// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Admin/Produits.vue';
import CategoriesApp from './components/Admin/Categories.vue';
import HomeAdmin from './components/Admin/HomeAdmin.vue';
import HomeClient from './components/User/HomeClient.vue';
import CommandeApp from './components/User/Commande.vue'
import AncienneCommandeApp from './components/User/AncienneCommande.vue'

createApp(CategoriesApp).mount('#categories-app')
createApp(ProduitsApp).mount('#produits-app')
createApp(HomeAdmin).mount('#admin-app')
createApp(HomeClient).mount('#client-app')
createApp(CommandeApp).mount('#commande-app')
createApp(AncienneCommandeApp).mount('#ancienne-commande-app')
