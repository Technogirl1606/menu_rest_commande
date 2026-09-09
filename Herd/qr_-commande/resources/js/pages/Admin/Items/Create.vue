<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

interface CategoryOption {
  id: number
  name: string
}

defineProps<{
  categories: CategoryOption[]
}>()

const form = useForm({
  category_id: null as number | null,
  name: '',
  description: '',
  price: '' as number | string,
  is_available: true,
  image: null as File | null,
})

const imagePreview = ref<string | null>(null)

function onImageChange(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  form.image = file

  // Libère l'aperçu précédent avant d'en créer un nouveau, pour éviter
  // d'accumuler des URL temporaires inutiles en mémoire dans le navigateur.
  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = file ? URL.createObjectURL(file) : null
}

function submit() {
  form.post('/admin/items')
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Ajouter un plat</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
        <select
          v-model="form.category_id"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
          <option :value="null" disabled>Choisir une catégorie</option>
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="form.errors.category_id" class="text-red-600 text-xs mt-1">{{ form.errors.category_id }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du plat</label>
        <input
          v-model="form.name" type="text" placeholder="Ex : Thiéboudienne"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description (optionnel)</label>
        <textarea
          v-model="form.description" rows="2" placeholder="Ex : Riz brisé, poisson, légumes mijotés"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        ></textarea>
        <p v-if="form.errors.description" class="text-red-600 text-xs mt-1">{{ form.errors.description }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
        <input
          v-model="form.price" type="number" min="0" step="1" placeholder="Ex : 2000"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.price" class="text-red-600 text-xs mt-1">{{ form.errors.price }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Photo du plat (optionnel)</label>
        <div class="flex items-center gap-4">
          <div class="h-24 w-24 shrink-0 rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden">
            <img v-if="imagePreview" :src="imagePreview" alt="Aperçu" class="h-full w-full object-cover">
            <span v-else class="text-xs text-gray-400">Aperçu</span>
          </div>
          <label class="cursor-pointer inline-flex items-center border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ imagePreview ? 'Changer la photo' : 'Choisir une photo' }}
            <input type="file" accept="image/*" class="hidden" @change="onImageChange">
          </label>
        </div>
        <p v-if="form.errors.image" class="text-red-600 text-xs mt-1">{{ form.errors.image }}</p>
      </div>

      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input v-model="form.is_available" type="checkbox" class="rounded border-gray-300">
        Disponible dès maintenant
      </label>

      <div class="flex gap-3 pt-2">
        <button
          type="submit" :disabled="form.processing"
          class="bg-red-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm disabled:opacity-50"
        >
          {{ form.processing ? 'Enregistrement...' : 'Ajouter le plat' }}
        </button>
        <Link href="/admin/items" class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 font-medium text-gray-600">
          Annuler
        </Link>
      </div>
    </form>
  </div>
</template>