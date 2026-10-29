<template>
  <div class="min-h-screen">
    <nav class="bg-white shadow mb-6">
      <div class="max-w-5xl mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="font-bold text-lg">Static Host</h1>
        <div class="flex gap-4 items-center">
          <span class="text-sm text-gray-600">{{ user?.email }}</span>
          <button @click="logout" class="text-sm text-red-600">Logout</button>
        </div>
      </div>
    </nav>
    <main class="max-w-5xl mx-auto px-4">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const user = ref(null);
const router = useRouter();

onMounted(async () => {
  try {
    const res = await axios.get('/me');
    user.value = res.data.user;
  } catch (e) {
    window.location.href = '/login';
  }
});

async function logout() {
  await axios.post('/logout');
  window.location.href = '/login';
}
</script>
