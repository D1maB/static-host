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
          <a :href="'http://' + site.slug.toLowerCase() + '.localhost/'" target="_blank" class="text-sm text-indigo-600">{{ site.slug.toLowerCase() }}.localhost</a>
          <div v-if="site.custom_domain" class="text-xs text-gray-500">
            Custom domain: {{ site.custom_domain }}
            <span :class="['ml-2 text-xs font-medium', site.domain_status === 'ready' ? 'text-green-600' : 'text-amber-600']">
              ({{ site.domain_status || 'pending' }})
            </span>
            <span v-if="domainStatus[site.id]" class="ml-2" :class="domainStatus[site.id].ready ? 'text-green-600' : 'text-red-600'">
              {{ domainStatus[site.id].message }}
            </span>
          </div>
          <div class="text-xs text-gray-500">{{ site.size_mb }} MB</div>
        </div>
        <div class="flex gap-3 items-center">
          <button @click="viewFiles(site)" class="text-indigo-600 text-sm cursor-pointer hover:underline">Files</button>
          <button @click="editDomain(site)" class="text-indigo-600 text-sm cursor-pointer hover:underline">Domain</button>
          <button @click="remove(site)" class="text-red-600 text-sm cursor-pointer hover:underline">Delete</button>
        </div>
      </div>
      <div v-if="!sites.length" class="text-gray-500">No sites yet.</div>
    </div>
    <SiteFiles v-if="selectedSite" :site="selectedSite" @close="selectedSite = null" />
    <SiteDomain v-if="domainSite" :site="domainSite" @close="domainSite = null" @updated="onDomainUpdated" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SiteFiles from './SiteFiles.vue';
import SiteDomain from './SiteDomain.vue';

const sites = ref([]);
const loading = ref(true);
const selectedSite = ref(null);
const domainSite = ref(null);
const domainStatus = ref({});

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

function editDomain(site) {
  domainSite.value = site;
}

async function checkDomain(site) {
  try {
    const res = await axios.get('/sites/' + site.id + '/check-domain');
    domainStatus.value[site.id] = res.data;
  } catch (e) {
    domainStatus.value[site.id] = { ready: false, message: 'Check failed.' };
  }
}

function onDomainUpdated(updated) {
  const idx = sites.value.findIndex(s => s.id === updated.id);
  if (idx !== -1) {
    sites.value[idx] = { ...sites.value[idx], ...updated };
  }
}

onMounted(load);
</script>
