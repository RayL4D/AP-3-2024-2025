// assets/app.js
import './styles/app.css';

import { createApp } from 'vue';

import ProduitsApp from './components/Admin/Produits.vue';
import CategoriesApp from './components/Admin/Categories.vue';
import HomeAdmin from './components/Admin/HomeAdmin.vue';



createApp(CategoriesApp).mount('#categories-app')
createApp(ProduitsApp).mount('#produits-app');
createApp(HomeAdmin).mount('#admin-app')


