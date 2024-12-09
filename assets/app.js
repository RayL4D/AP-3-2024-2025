// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Admin/Produits.vue';
import CategoriesApp from './components/Admin/Categories.vue';
import HomeAdmin from './components/Admin/HomeAdmin.vue';
import HomeClient from './components/User/HomeClient.vue';
import PanierApp from './components/User/Panier.vue'


createApp(CategoriesApp).mount('#categories-app')
createApp(ProduitsApp).mount('#produits-app')
createApp(HomeAdmin).mount('#admin-app')
createApp(HomeClient).mount('#client-app')
createApp(PanierApp).mount('#panier-app')
