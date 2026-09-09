<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'

interface CategoryRow {
  id: number
  name: string
  items_count: number
  available_from: string | null
  available_until: string | null
}

defineProps<{
  categories: CategoryRow[]
}>()

function hoursLabel(category: CategoryRow): string {
  if (!category.available_from || !category.available_until) return 'Toute la journée'
  return `${category.available_from} - ${category.available_until}`
}

function destroyCategory(id: number, name: string, itemsCount: number) {
  const warning = itemsCount > 0
    ? `Supprimer "${name}" effacera aussi ses ${itemsCount} plat${itemsCount > 1 ? 's' : ''} associé${itemsCount > 1 ? 's' : ''}. Cette action est définitive.`
    : `Supprimer "${name}" ? Cette action est définitive.`

  if (confirm(warning)) {
    router.delete(`/admin/categories/${id}`)
  }
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-3 flex-wrap mb-6">
      <h1 class="text-xl sm:text-2xl font-bold">Gérer les catégories</h1>
      <Link
        href="/admin/categories/create"
        class="bg-red-600 text-white font-semibold px-4 py-2 rounded-lg text-sm whitespace-nowrap"
      >
        + Ajouter une catégorie
      </Link>
    </div>

    <p v-if="categories.length === 0" class="text-center text-gray-400 text-sm py-8">
      Aucune catégorie pour l'instant.
    </p>

    <!-- Version tableau : à partir de la taille tablette -->
    <div v-if="categories.length > 0" class="hidden sm:block border border-gray-200 rounded-lg overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
          <tr>
            <th class="px-4 py-3 font-medium">Nom</th>
            <th class="px-4 py-3 font-medium">Plats</th>
            <th class="px-4 py-3 font-medium">Horaires</th>
            <th class="px-4 py-3 font-medium"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id" class="border-t border-gray-100">
            <td class="px-4 py-3 font-medium text-gray-900">{{ category.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ category.items_count }}</td>
            <td class="px-4 py-3">
              <span
                :class="[
                  'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                  category.available_from ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'
                ]"
              >
                {{ hoursLabel(category) }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-3">
              <Link :href="`/admin/categories/${category.id}/edit`" class="text-red-600 text-xs font-semibold hover:underline">
                Modifier
              </Link>
              <button
                type="button"
                @click="destroyCategory(category.id, category.name, category.items_count)"
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
    <div v-if="categories.length > 0" class="sm:hidden space-y-3">
      <div v-for="category in categories" :key="category.id" class="border border-gray-200 rounded-lg p-3">
        <div class="flex items-center justify-between gap-3">
          <div>
            <div class="font-medium text-gray-900 text-sm">{{ category.name }}</div>
            <div class="text-gray-400 text-xs mt-0.5">{{ category.items_count }} plat{{ category.items_count > 1 ? 's' : '' }}</div>
          </div>
          <span
            :class="[
              'shrink-0 inline-block px-2 py-0.5 rounded-full text-[11px] font-medium',
              category.available_from ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'
            ]"
          >
            {{ hoursLabel(category) }}
          </span>
        </div>
        <div class="flex gap-4 mt-3 pt-3 border-t border-gray-100">
          <Link :href="`/admin/categories/${category.id}/edit`" class="text-red-600 text-xs font-semibold">
            Modifier
          </Link>
          <button
            type="button"
            @click="destroyCategory(category.id, category.name, category.items_count)"
            class="text-gray-400 text-xs font-semibold"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>