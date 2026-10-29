<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">My Sites</h2>
      <router-link to="/upload" class="bg-emerald-600 text-white px-4 py-2 rounded">Upload new</router-link>
    </div>
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else class="space-y-3">
      <div v-for="site in sites" :key="site.id" class="bg-white shadow rounded p-4 flex justify-between items-center">
        <div>
          <div class="font-medium">{{ site.name }}</div>
          <a :href="'http://' + site.slug.toLowerCase() + '.localhost/'" target="_blank" class="text-sm text-emerald-600">{{ site.slug.toLowerCase() }}.localhost</a>
          <div class="text-xs text-gray-500">{{ site.size_mb }} MB</div>
        </div>
        <div class="flex gap-3 items-center">
          <button @click="viewFiles(site)" class="text-emerald-600 text-sm">Files</button>
          <button @click="remove(site)" class="text-red-600 text-sm">Delete</button>
        </div>
      </div>
      <div v-if="!sites.length" class="text-gray-500">No sites yet.</div>
    </div>
    <SiteFiles v-if="selectedSite" :site="selectedSite" @close="selectedSite = null" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SiteFiles from './SiteFiles.vue';

const sites = ref([]);
const loading = ref(true);
const selectedSite = ref(null);

async function load() {
  loading.value = true;
  const res = await axios.get('/sites/mine');
  sites.value = res.data.sites;
  loading.value = false;
}

async function remove(site) {
  if (!confirm('Delete site?')) return;
  await axios.delete('/sites/' + site.id);
  await load();
}

function viewFiles(site) {
  selectedSite.value = site;
}

onMounted(load);
</script>
