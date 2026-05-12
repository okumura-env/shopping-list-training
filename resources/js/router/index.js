import { createRouter, createWebHistory } from 'vue-router';
import ItemListView from '../views/ItemListView.vue';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'items.index', component: ItemListView },
  ],
});

export default router;
