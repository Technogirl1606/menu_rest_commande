<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

interface CategoryData {
  id: number
  name: string
  available_from: string | null
  available_until: string | null
}

const props = defineProps<{
  category: CategoryData
}>()

const form = useForm({
  name: props.category.name,
  available_from: props.category.available_from ?? '',
  available_until: props.category.available_until ?? '',
})

// Pré-coché si la catégorie avait déjà une plage horaire enregistrée.
const restrictHours = ref(Boolean(props.category.available_from && props.category.available_until))

watch(restrictHours, (enabled) => {
  if (!enabled) {
    form.available_from = ''
    form.available_until = ''
  }
})

function submit() {
  form.put(`/admin/categories/${props.category.id}`)
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-6">Modifier la catégorie</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la catégorie</label>
        <input
          v-model="form.name" type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input v-model="restrictHours" type="checkbox" class="rounded border-gray-300">
        Limiter cette catégorie à certaines heures
      </label>

      <div v-if="restrictHours" class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">De</label>
          <input
            v-model="form.available_from" type="time"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
          <p v-if="form.errors.available_from" class="text-red-600 text-xs mt-1">{{ form.errors.available_from }}</p>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wide text-gray-500 mb-1">À</label>
          <input
            v-model="form.available_until" type="time"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
          <p v-if="form.errors.available_until" class="text-red-600 text-xs mt-1">{{ form.errors.available_until }}</p>
        </div>
      </div>
      <p v-if="restrictHours" class="text-xs text-gray-400 -mt-2">
        En dehors de cette plage, la catégorie n'apparaîtra pas sur le menu client. Une plage comme 18:00 → 02:00 traverse minuit et fonctionne normalement.
      </p>

      <div class="flex gap-3 pt-2">
        <button
          type="submit" :disabled="form.processing"
          class="bg-red-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm disabled:opacity-50"
        >
          {{ form.processing ? 'Enregistrement...' : 'Enregistrer les modifications' }}
        </button>
        <Link href="/admin/categories" class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 font-medium text-gray-600">
          Annuler
        </Link>
      </div>
    </form>
  </div>
</template>