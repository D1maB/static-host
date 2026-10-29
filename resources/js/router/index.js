import { createRouter, createWebHistory } from 'vue-router';
import SitesList from '../components/SitesList.vue';
import UploadForm from '../components/UploadForm.vue';

const routes = [
  { path: '/', name: 'sites', component: SitesList },
  { path: '/upload', name: 'upload', component: UploadForm },
];

export default createRouter({
  history: createWebHistory('/dashboard'),
  routes,
});
