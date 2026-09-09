<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

interface CategoryOption {
  id: number
  name: string
}

interface ItemData {
  id: number
  category_id: number
  name: string
  description: string | null
  price: number
  is_available: boolean
  image_url: string | null
}

const props = defineProps<{
  item: ItemData
  categories: CategoryOption[]
}>()

const form = useForm({
  category_id: props.item.category_id as number | null,
  name: props.item.name,
  description: props.item.description ?? '',
  price: props.item.price as number | string,
  is_available: props.item.is_available,
  image: null as File | null,
})

function onImageChange(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  form.image = file

  if (imagePreview.value) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = file ? URL.createObjectURL(file) : null
}

const imagePreview = ref<string | null>(null)

function submit() {
  // PHP ne sait pas lire un formulaire multipart/form-data (avec fichier)
  // envoyé en PUT — uniquement en POST. On envoie donc en POST, avec un
  // champ "_method" qui indique à Laravel de le traiter comme un PUT.
  form.transform((data) => ({
    ...data,
    _method: 'put',
  })).post(`/admin/items/${props.item.id}`)
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Modifier le plat</h1>

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
          v-model="form.name" type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.name" class="text-red-600 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description (optionnel)</label>
        <textarea
          v-model="form.description" rows="2"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        ></textarea>
        <p v-if="form.errors.description" class="text-red-600 text-xs mt-1">{{ form.errors.description }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA)</label>
        <input
          v-model="form.price" type="number" min="0" step="1"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
        <p v-if="form.errors.price" class="text-red-600 text-xs mt-1">{{ form.errors.price }}</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Photo du plat</label>
        <div class="flex items-center gap-4">
          <div class="h-24 w-24 shrink-0 rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center overflow-hidden">
            <img
              v-if="imagePreview || item.image_url"
              :src="imagePreview ?? item.image_url ?? ''" alt="Aperçu"
              class="h-full w-full object-cover"
            >
            <span v-else class="text-xs text-gray-400">Aperçu</span>
          </div>
          <label class="cursor-pointer inline-flex items-center border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ item.image_url || imagePreview ? 'Changer la photo' : 'Choisir une photo' }}
            <input type="file" accept="image/*" class="hidden" @change="onImageChange">
          </label>
        </div>
        <p v-if="form.errors.image" class="text-red-600 text-xs mt-1">{{ form.errors.image }}</p>
      </div>

      <label class="flex items-center gap-2 text-sm text-gray-700">
        <input v-model="form.is_available" type="checkbox" class="rounded border-gray-300">
        Disponible
      </label>

      <div class="flex gap-3 pt-2">
        <button
          type="submit" :disabled="form.processing"
          class="bg-red-600 text-white font-semibold px-5 py-2.5 rounded-lg text-sm disabled:opacity-50"
        >
          {{ form.processing ? 'Enregistrement...' : 'Enregistrer les modifications' }}
        </button>
        <Link href="/admin/items" class="px-5 py-2.5 rounded-lg text-sm border border-gray-200 font-medium text-gray-600">
          Annuler
        </Link>
      </div>
    </form>
  </div>
</template>