<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="close">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <div class="bg-blue-600 text-black rounded p-3 mb-4 text-sm">
        Create an A record (or CNAME) in your DNS provider pointing this domain to your server's IP. DNS propagation can take some time.
      </div>
      <h3 class="text-lg font-semibold mb-4">Custom domain — {{ site.name }}</h3>
      <form @submit.prevent="submit">
        <label class="block mb-1">Custom domain (leave empty to clear)</label>
        <input v-model="domain" placeholder="example.com" class="w-full border rounded px-3 py-2 mb-3">        <p v-if="error" class="text-red-600 text-sm mb-3">{{ error }}</p>
        <p v-if="success" class="text-green-600 text-sm mb-3">{{ success }}</p>
        <div class="flex justify-end gap-3">
          <button type="button" @click="close" class="px-4 py-2 rounded border">Close</button>
          <button type="submit" :disabled="saving" class="bg-indigo-600 text-white px-4 py-2 rounded">
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ site: Object });
const emit = defineEmits(['close', 'updated']);

const domain = ref('');
const saving = ref(false);
const error = ref('');
const success = ref('');

onMounted(() => {
  domain.value = props.site.custom_domain || '';
});

async function submit() {
  const value = domain.value.trim().toLowerCase();
  const domainRegex = /^(?=.{1,253}$)(?!-)([a-z0-9-]{1,63}\.)+[a-z]{2,63}$/;
  if (value && !domainRegex.test(value)) {
    error.value = 'Enter a valid domain, e.g. example.com';
    return;
  }

  saving.value = true;
  error.value = '';
  success.value = '';
  try {
    const res = await axios.patch('/sites/' + props.site.id + '/domain', {
      custom_domain: value || null,
    });
    emit('updated', res.data.site);
    close();
  } catch (e) {
    error.value = e.response?.data?.message || e.response?.data?.errors?.custom_domain?.[0] || 'Failed to save.';
  } finally {
    saving.value = false;
  }
}

function close() {
  emit('close');
}
</script>
