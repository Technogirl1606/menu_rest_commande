<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  name: '',
})

function submit() {
  form.post('/admin/tables')
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-6">Ajouter une table</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la table</label>
        <input
          v-model="form.name" type="text" placeholder="Ex : Table 4"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div class="flex gap-3 pt-2">
        <button
          type="submit" :disabled="form.processing"
          class="bg-red-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm disabled:opacity-50"
        >
          {{ form.processing ? 'Enregistrement...' : 'Ajouter la table' }}
        </button>
        <Link href="/admin/tables" class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 font-medium text-gray-600">
          Annuler
        </Link>
      </div>
    </form>
  </div>
</template>