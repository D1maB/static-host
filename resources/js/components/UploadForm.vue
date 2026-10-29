<template>
  <div class="max-w-lg">
    <h2 class="text-xl font-semibold mb-4">Upload site (zip)</h2>
    <form @submit.prevent="submit" class="bg-white shadow rounded p-4 space-y-4">
      <div>
        <label class="block mb-1">Name</label>
        <input v-model="name" required class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label class="block mb-1">Zip archive (max 50 MB)</label>
        <input type="file" ref="fileInput" accept=".zip" required class="w-full">
      </div>
      <button :disabled="uploading" class="bg-emerald-600 text-white px-4 py-2 rounded">
        {{ uploading ? 'Uploading...' : 'Upload' }}
      </button>
      <p v-if="message" :class="['text-sm', isError ? 'text-red-600' : 'text-green-600']">{{ message }}</p>
      <p class="text-sm text-gray-600 mt-2">Allowed: html, htm, css, js, json, xml, txt, md, svg, png, jpg, jpeg, gif, webp, ico, pdf, woff, woff2, ttf, otf, eot, webmanifest, map.</p>
    </form>
    <p class="mt-4"><router-link to="/" class="text-emerald-600">Back to sites</router-link></p>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const name = ref('');
const fileInput = ref(null);
const uploading = ref(false);
const message = ref('');
const isError = ref(false);
const router = useRouter();

async function submit() {
  uploading.value = true;
  message.value = '';
  const formData = new FormData();
  formData.append('name', name.value);
  formData.append('archive', fileInput.value.files[0]);
  try {
    const res = await axios.post('/sites', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    isError.value = false;
    message.value = 'Uploaded: ' + res.data.site.slug;
    await new Promise(r => setTimeout(r, 800));
    router.push('/');
  } catch (e) {
    isError.value = true;
    message.value = e.response?.data?.message || 'Upload failed';
  } finally {
    uploading.value = false;
  }
}
</script>
