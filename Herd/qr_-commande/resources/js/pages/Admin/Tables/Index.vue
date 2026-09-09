<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import QRCode from 'qrcode'

interface TableRow {
  id: number
  name: string
  url: string
}

const props = defineProps<{
  tables: TableRow[]
}>()

const qrImages = ref<Record<number, string>>({})

onMounted(async () => {
  for (const table of props.tables) {
    qrImages.value[table.id] = await QRCode.toDataURL(table.url, { width: 220, margin: 1 })
  }
})

function destroyTable(id: number, name: string) {
  if (confirm(`Supprimer "${name}" ? Le QR code déjà imprimé pour cette table ne fonctionnera plus.`)) {
    router.delete(`/admin/tables/${id}`)
  }
}

function copyLink(url: string) {
  navigator.clipboard.writeText(url)
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between gap-3 flex-wrap mb-6">
      <h1 class="text-xl sm:text-2xl font-bold">Gérer les tables</h1>
      <Link
        href="/admin/tables/create"
        class="bg-red-600 text-white font-semibold px-4 py-2 rounded-lg text-sm whitespace-nowrap"
      >
        + Ajouter une table
      </Link>
    </div>

    <p v-if="tables.length === 0" class="text-gray-400 text-sm">Aucune table pour l'instant.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
      <div v-for="table in tables" :key="table.id" class="border border-gray-200 rounded-lg p-4 text-center">
        <div class="font-semibold text-gray-900 mb-3">{{ table.name }}</div>

        <img v-if="qrImages[table.id]" :src="qrImages[table.id]" alt="" class="mx-auto w-40 h-40">
        <div v-else class="w-40 h-40 mx-auto bg-gray-50 rounded animate-pulse"></div>

        <div class="flex gap-3 mt-3 justify-center flex-wrap">
          <a
            v-if="qrImages[table.id]"
            :href="qrImages[table.id]" :download="`qr-${table.name}.png`"
            class="text-xs font-semibold text-red-600 hover:underline"
          >
            Télécharger
          </a>
          <button type="button" @click="copyLink(table.url)" class="text-xs font-semibold text-gray-500 hover:underline">
            Copier le lien
          </button>
          <button
            type="button" @click="destroyTable(table.id, table.name)"
            class="text-xs font-semibold text-gray-400 hover:text-red-600 hover:underline"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>