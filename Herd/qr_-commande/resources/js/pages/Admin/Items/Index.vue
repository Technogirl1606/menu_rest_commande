<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

interface CategoryOption {
  id: number
  name: string
}

interface ItemRow {
  id: number
  category_id: number
  name: string
  description: string | null
  price: number
  is_available: boolean
  category_name: string
  image_url: string | null
}

const props = defineProps<{
  items: ItemRow[]
  categories: CategoryOption[]
}>()

// null = "Tous" (aucun filtre)
const activeCategory = ref<number | null>(null)

const filteredItems = computed(() =>
  activeCategory.value === null
    ? props.items
    : props.items.filter(i => i.category_id === activeCategory.value)
)

function fmt(n: number): string {
  return Number(n).toLocaleString('fr-FR') + ' F'
}

function destroyItem(id: number, name: string) {
  if (confirm(`Supprimer "${name}" du menu ? Cette action est définitive.`)) {
    router.delete(`/admin/items/${id}`)
  }
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
      <h1 class="text-xl sm:text-2xl font-bold">Gérer les plats</h1>
      <Link
        href="/admin/items/create"
        class="bg-red-600 text-white font-semibold px-4 py-2 rounded-lg text-sm whitespace-nowrap"
      >
        + Ajouter un plat
      </Link>
    </div>

    <!-- Pilules de catégories, comme sur /menu -->
    <div class="flex gap-2 overflow-x-auto pb-4 mb-2">
      <button
        type="button"
        @click="activeCategory = null"
        :class="[
          'whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium border transition-colors',
          activeCategory === null
            ? 'bg-red-600 text-white border-red-600'
            : 'bg-white text-gray-700 border-gray-200 hover:border-red-300'
        ]"
      >
        Tous ({{ items.length }})
      </button>
      <button
        v-for="c in categories"
        :key="c.id"
        type="button"
        @click="activeCategory = c.id"
        :class="[
          'whitespace-nowrap px-4 py-1.5 rounded-full text-sm font-medium border transition-colors',
          activeCategory === c.id
            ? 'bg-red-600 text-white border-red-600'
            : 'bg-white text-gray-700 border-gray-200 hover:border-red-300'
        ]"
      >
        {{ c.name }} ({{ items.filter(i => i.category_id === c.id).length }})
      </button>
    </div>

    <p v-if="filteredItems.length === 0" class="text-center text-gray-400 text-sm py-8">
      Aucun plat dans cette catégorie.
    </p>

    <!-- Version tableau : à partir de la taille tablette -->
    <div v-if="filteredItems.length > 0" class="hidden sm:block border border-gray-200 rounded-lg overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
          <tr>
            <th class="px-4 py-3 font-medium">Plat</th>
            <th class="px-4 py-3 font-medium">Catégorie</th>
            <th class="px-4 py-3 font-medium">Prix</th>
            <th class="px-4 py-3 font-medium">Disponible</th>
            <th class="px-4 py-3 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredItems" :key="item.id" class="border-t border-gray-100">
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <img
                  v-if="item.image_url" :src="item.image_url" alt=""
                  class="h-10 w-10 rounded-lg object-cover border border-gray-100"
                >
                <div v-else class="h-10 w-10 rounded-lg bg-gray-50 border border-gray-100"></div>
                <div>
                  <div class="font-medium text-gray-900">{{ item.name }}</div>
                  <div v-if="item.description" class="text-gray-400 text-xs mt-0.5">{{ item.description }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-600">{{ item.category_name }}</td>
            <td class="px-4 py-3 font-medium">{{ fmt(item.price) }}</td>
            <td class="px-4 py-3">
              <span
                :class="[
                  'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                  item.is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                ]"
              >
                {{ item.is_available ? 'Oui' : 'Non' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-3">
              <Link :href="`/admin/items/${item.id}/edit`" class="text-red-600 text-xs font-semibold hover:underline">
                Modifier
              </Link>
              <button
                type="button"
                @click="destroyItem(item.id, item.name)"
                class="text-gray-400 text-xs font-semibold hover:text-red-600 hover:underline"
              >
                Supprimer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Version cartes : en dessous de la taille tablette -->
    <div v-if="filteredItems.length > 0" class="sm:hidden space-y-3">
      <div v-for="item in filteredItems" :key="item.id" class="border border-gray-200 rounded-lg p-3">
        <div class="flex gap-3">
          <img
            v-if="item.image_url" :src="item.image_url" alt=""
            class="h-14 w-14 shrink-0 rounded-lg object-cover border border-gray-100"
          >
          <div v-else class="h-14 w-14 shrink-0 rounded-lg bg-gray-50 border border-gray-100"></div>

          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-2">
              <div class="font-medium text-gray-900 text-sm truncate">{{ item.name }}</div>
              <span
                :class="[
                  'shrink-0 inline-block px-2 py-0.5 rounded-full text-[11px] font-medium',
                  item.is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                ]"
              >
                {{ item.is_available ? 'Dispo' : 'Indispo' }}
              </span>
            </div>
            <div class="text-gray-400 text-xs mt-0.5">{{ item.category_name }}</div>
            <div class="font-semibold text-sm mt-1">{{ fmt(item.price) }}</div>
          </div>
        </div>

        <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100">
          <Link :href="`/admin/items/${item.id}/edit`" class="text-red-600 text-xs font-semibold">
            Modifier
          </Link>
          <button
            type="button"
            @click="destroyItem(item.id, item.name)"
            class="text-gray-400 text-xs font-semibold"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>