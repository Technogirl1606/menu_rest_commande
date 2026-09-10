<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

interface OrderLine {
  name: string
  quantite: number
}

interface OrderRow {
  id: number
  ticket_number: string
  table_label: string | null
  status: string
  status_label: string
  total: number
  created_at: string
  items: OrderLine[]
}

interface DailyStats {
  orders_count: number
  revenue: number
  top_item_name: string | null
  top_item_qty: number
  avg_prep_minutes: number | null
}

const props = defineProps<{
  orders: OrderRow[]
  stats: DailyStats
  date: string // format YYYY-MM-DD
}>()

const selectedDate = ref(props.date)

function goToDate(newDate: string) {
  router.get('/admin/history', { date: newDate }, { preserveState: true, preserveScroll: true })
}

function shiftDay(offsetDays: number) {
  const d = new Date(selectedDate.value + 'T00:00:00')
  d.setDate(d.getDate() + offsetDays)
  const iso = d.toISOString().slice(0, 10)
  selectedDate.value = iso
  goToDate(iso)
}

function isToday(): boolean {
  return selectedDate.value === new Date().toISOString().slice(0, 10)
}

function fmt(n: number): string {
  return Number(n).toLocaleString('fr-FR') + ' F'
}

function statusClasses(status: string): string {
  const map: Record<string, string> = {
    nouvelle: 'bg-blue-100 text-blue-700',
    preparation: 'bg-amber-100 text-amber-700',
    prete: 'bg-green-100 text-green-700',
    servie: 'bg-gray-100 text-gray-600',
    annulee: 'bg-red-100 text-red-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

function itemsSummary(items: OrderLine[]): string {
  return items.map(i => `${i.quantite}× ${i.name}`).join(', ')
}
</script>

<template>
  <div class="p-4 sm:p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
      <h1 class="text-xl sm:text-2xl font-bold">Historique</h1>
      <Link href="/admin/kitchen" class="text-red-600 text-sm font-semibold hover:underline whitespace-nowrap">
        ← Retour à la cuisine
      </Link>
    </div>

    <!-- Sélecteur de date -->
    <div class="flex items-center gap-2 mb-6">
      <button
        type="button" @click="shiftDay(-1)"
        class="w-9 h-9 rounded-lg border border-gray-200 text-gray-600 flex items-center justify-center shrink-0"
        aria-label="Jour précédent"
      >‹</button>
      <input
        type="date"
        v-model="selectedDate"
        @change="goToDate(selectedDate)"
        class="border border-gray-200 rounded-lg px-3 py-2 text-sm"
      >
      <button
        type="button" @click="shiftDay(1)"
        class="w-9 h-9 rounded-lg border border-gray-200 text-gray-600 flex items-center justify-center shrink-0"
        aria-label="Jour suivant"
      >›</button>
      <button
        v-if="!isToday()"
        type="button" @click="goToDate(new Date().toISOString().slice(0, 10))"
        class="text-red-600 text-xs font-semibold ml-1 whitespace-nowrap"
      >
        Revenir à aujourd'hui
      </button>
    </div>

    <!-- Statistiques du jour sélectionné -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold">{{ stats.orders_count }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Commandes ce jour-là</div>
      </div>
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold">{{ fmt(stats.revenue) }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Chiffre d'affaires</div>
      </div>
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold truncate" :title="stats.top_item_name ?? ''">
          {{ stats.top_item_name ?? '—' }}
        </div>
        <div class="text-[11px] text-gray-400 mt-0.5">
          Plat le plus vendu{{ stats.top_item_name ? ` (${stats.top_item_qty})` : '' }}
        </div>
      </div>
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold">{{ stats.avg_prep_minutes !== null ? stats.avg_prep_minutes + ' min' : '—' }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Préparation moyenne</div>
      </div>
    </div>

    <p v-if="orders.length === 0" class="text-center text-gray-400 text-sm py-8">
      Aucune commande ce jour-là.
    </p>

    <!-- Version tableau : à partir de la taille tablette -->
    <div v-if="orders.length > 0" class="hidden sm:block border border-gray-200 rounded-lg overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500">
          <tr>
            <th class="px-4 py-3 font-medium">Ticket</th>
            <th class="px-4 py-3 font-medium">Table</th>
            <th class="px-4 py-3 font-medium">Plats</th>
            <th class="px-4 py-3 font-medium">Total</th>
            <th class="px-4 py-3 font-medium">Statut</th>
            <th class="px-4 py-3 font-medium">Heure</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-t border-gray-100">
            <td class="px-4 py-3 font-mono font-semibold">{{ order.ticket_number }}</td>
            <td class="px-4 py-3 text-gray-600">{{ order.table_label ?? '—' }}</td>
            <td class="px-4 py-3 text-gray-600 max-w-xs truncate" :title="itemsSummary(order.items)">
              {{ itemsSummary(order.items) }}
            </td>
            <td class="px-4 py-3 font-medium">{{ fmt(order.total) }}</td>
            <td class="px-4 py-3">
              <span :class="['inline-block px-2 py-0.5 rounded-full text-xs font-medium', statusClasses(order.status)]">
                {{ order.status_label }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ order.created_at }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Version cartes : en dessous de la taille tablette -->
    <div v-if="orders.length > 0" class="sm:hidden space-y-3">
      <div v-for="order in orders" :key="order.id" class="border border-gray-200 rounded-lg p-3">
        <div class="flex items-center justify-between gap-2">
          <span class="font-mono font-semibold text-sm">{{ order.ticket_number }}</span>
          <span :class="['inline-block px-2 py-0.5 rounded-full text-[11px] font-medium', statusClasses(order.status)]">
            {{ order.status_label }}
          </span>
        </div>
        <div class="text-xs text-gray-400 mt-1">{{ order.table_label ?? 'Comptoir / à emporter' }} · {{ order.created_at }}</div>
        <div class="text-sm text-gray-600 mt-2">{{ itemsSummary(order.items) }}</div>
        <div class="font-semibold text-sm mt-2">{{ fmt(order.total) }}</div>
      </div>
    </div>
  </div>
</template>