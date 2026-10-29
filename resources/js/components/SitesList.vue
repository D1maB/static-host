<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">My Sites</h2>
      <router-link to="/upload" class="bg-indigo-600 text-white px-4 py-2 rounded">Upload new</router-link>
    </div>
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else class="space-y-3">
      <div v-for="site in sites" :key="site.id" class="bg-white shadow rounded p-4 flex justify-between items-center">
        <div>
          <div class="font-medium">{{ site.name }}</div>
          <a :href="'/sites/' + site.slug" target="_blank" class="text-sm text-indigo-600">/sites/{{ site.slug }}</a>
        </div>
        <button @click="remove(site)" class="text-red-600 text-sm">Delete</button>
      </div>
      <div v-if="!sites.length" class="text-gray-500">No sites yet.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const sites = ref([]);
const loading = ref(true);

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

onMounted(load);
</script>
