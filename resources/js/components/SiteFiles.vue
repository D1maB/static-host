<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="close">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[80vh] overflow-auto p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Files — {{ slug }}</h3>
        <button @click="close" class="text-gray-500 cursor-pointer hover:text-gray-700">✕</button>
      </div>
      <div v-if="loading" class="text-gray-500">Loading...</div>
      <ul v-else class="space-y-1 text-sm">
        <li v-for="f in files" :key="f.path" class="flex justify-between border-b py-1">
          <a :href="fileUrl(f.path)" target="_blank" class="text-indigo-600">{{ f.path }}</a>
          <span class="text-gray-400">{{ f.size }} B</span>
        </li>
        <li v-if="!files.length" class="text-gray-500">No files.</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ site: Object });
const emit = defineEmits(['close']);

const files = ref([]);
const loading = ref(true);
const slug = props.site.slug;

onMounted(async () => {
  const res = await axios.get('/sites/' + props.site.id + '/files');
  files.value = res.data.files;
  loading.value = false;
});

function fileUrl(path) {
  return 'http://' + slug.toLowerCase() + '.localhost/' + path;
}

function close() {
  emit('close');
}
</script>
