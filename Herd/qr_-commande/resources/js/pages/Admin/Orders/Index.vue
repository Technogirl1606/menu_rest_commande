<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'

interface OrderLine {
  name: string
  quantite: number
}

interface OrderRow {
  id: number
  ticket_number: string
  table_label: string | null
  note: string | null
  status: 'nouvelle' | 'preparation' | 'prete'
  created_at: string
  items: OrderLine[]
}

interface DailyStats {
  orders_count_today: number
  revenue_today: number
  top_item_name: string | null
  top_item_qty: number
  avg_prep_minutes: number | null
}

const props = defineProps<{
  orders: OrderRow[]
  stats: DailyStats
}>()

const activeCount = computed(() => props.orders.length)

function fmt(n: number): string {
  return Number(n).toLocaleString('fr-FR') + ' F'
}

// --- Recherche par table ---
const searchQuery = ref('')

function columnOrders(status: OrderRow['status']) {
  return props.orders
    .filter(o => o.status === status)
    .filter(o => {
      if (!searchQuery.value.trim()) return true
      const q = searchQuery.value.toLowerCase()
      return (o.table_label ?? '').toLowerCase().includes(q) || o.ticket_number.toLowerCase().includes(q)
    })
}

// --- Alerte sonore à l'arrivée d'une nouvelle commande ---
const soundEnabled = ref(localStorage.getItem('kitchen_sound') !== 'off')
const knownOrderIds = ref<Set<number>>(new Set(props.orders.map(o => o.id)))

function toggleSound() {
  soundEnabled.value = !soundEnabled.value
  localStorage.setItem('kitchen_sound', soundEnabled.value ? 'on' : 'off')
}

function playNewOrderSound() {
  if (!soundEnabled.value) return
  try {
    const AudioCtx = window.AudioContext || (window as any).webkitAudioContext
    const ctx = new AudioCtx()
    const gain = ctx.createGain()
    gain.gain.setValueAtTime(0.15, ctx.currentTime)
    gain.connect(ctx.destination)

    const beep = (freq: number, delay: number) => {
      const osc = ctx.createOscillator()
      osc.type = 'sine'
      osc.frequency.value = freq
      osc.connect(gain)
      osc.start(ctx.currentTime + delay)
      osc.stop(ctx.currentTime + delay + 0.15)
    }
    beep(880, 0)
    beep(1046, 0.18)
  } catch (e) {
    // Silencieux : si le navigateur bloque l'audio (pas encore d'interaction), on ignore.
  }
}

watch(() => props.orders, (newOrders) => {
  const hasNewOrder = newOrders.some(o => !knownOrderIds.value.has(o.id))
  if (hasNewOrder) playNewOrderSound()
  knownOrderIds.value = new Set(newOrders.map(o => o.id))
})

const tick = ref(0)
let tickTimer: ReturnType<typeof setInterval> | undefined
let pollTimer: ReturnType<typeof setInterval> | undefined

onMounted(() => {
  tickTimer = setInterval(() => tick.value++, 30_000)

  pollTimer = setInterval(() => {
    router.reload({ only: ['orders', 'stats'], preserveScroll: true, preserveState: true })
  }, 5_000)
})

onUnmounted(() => {
  clearInterval(tickTimer)
  clearInterval(pollTimer)
})

function elapsedLabel(createdAt: string): { text: string; cls: string } {
  tick.value
  const minutes = Math.floor((Date.now() - new Date(createdAt).getTime()) / 60000)
  let cls = 'text-gray-500'
  if (minutes >= 10) cls = 'text-red-600 font-semibold'
  else if (minutes >= 5) cls = 'text-amber-600 font-medium'
  return { text: minutes <= 0 ? "à l'instant" : `il y a ${minutes} min`, cls }
}

function advance(orderId: number) {
  router.patch(`/admin/kitchen/${orderId}/advance`, {}, {
    preserveScroll: true,
    preserveState: true,
    only: ['orders'],
  })
}

const columns = computed(() => [
  { status: 'nouvelle' as const, label: 'Nouvelles', action: 'Démarrer la préparation' },
  { status: 'preparation' as const, label: 'En préparation', action: 'Marquer prête' },
  { status: 'prete' as const, label: 'Prêtes', action: 'Marquer servie' },
])
</script>

<template>
  <div class="p-4 sm:p-6 max-w-6xl mx-auto">
    <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
      <h1 class="text-xl sm:text-2xl font-bold">Cuisine — commandes en direct</h1>
      <div class="flex items-center gap-4">
        <Link href="/admin/history" class="text-red-600 text-sm font-semibold hover:underline whitespace-nowrap">
          Historique
        </Link>
        <button
          type="button" @click="toggleSound"
          class="text-xs font-semibold flex items-center gap-1 whitespace-nowrap"
          :class="soundEnabled ? 'text-gray-600' : 'text-gray-300'"
        >
          {{ soundEnabled ? '🔔 Son activé' : '🔕 Son coupé' }}
        </button>
        <span class="text-xs text-gray-400 flex items-center gap-1.5 whitespace-nowrap">
          <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
          mise à jour automatique
        </span>
      </div>
    </div>

    <input
      v-model="searchQuery"
      type="text"
      placeholder="Rechercher par table ou numéro de ticket..."
      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-4"
    >

    <!-- Bandeau : compteur global + statistiques du jour -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold text-red-600">{{ activeCount }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Commandes actives</div>
      </div>
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold">{{ stats.orders_count_today }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Commandes aujourd'hui</div>
      </div>
      <div class="border border-gray-200 rounded-lg p-3">
        <div class="text-xl font-bold">{{ fmt(stats.revenue_today) }}</div>
        <div class="text-[11px] text-gray-400 mt-0.5">Chiffre d'affaires du jour</div>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div v-for="col in columns" :key="col.status">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
          {{ col.label }}
          <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
            {{ columnOrders(col.status).length }}
          </span>
        </h2>

        <div class="space-y-3">
          <p v-if="columnOrders(col.status).length === 0" class="text-xs text-gray-300 italic">
            Rien ici pour le moment.
          </p>

          <div
            v-for="order in columnOrders(col.status)"
            :key="order.id"
            class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm"
          >
            <div class="flex items-center justify-between gap-2">
              <span class="font-mono font-bold text-sm">{{ order.ticket_number }}</span>
              <span v-if="order.table_label" class="bg-gray-900 text-white text-[11px] font-semibold px-1.5 py-0.5 rounded">
                {{ order.table_label }}
              </span>
              <span :class="['text-[11px] ml-auto', elapsedLabel(order.created_at).cls]">
                {{ elapsedLabel(order.created_at).text }}
              </span>
            </div>

            <p class="text-xs text-gray-600 mt-2 pt-2 border-t border-gray-100 leading-snug">
              <span v-for="(line, i) in order.items" :key="i">
                <span class="text-gray-400">{{ line.quantite }}×</span> {{ line.name }}<span v-if="i < order.items.length - 1">, </span>
              </span>
            </p>

            <p v-if="order.note" class="text-[11px] italic text-gray-400 mt-1.5">
              "{{ order.note }}"
            </p>

            <button
              type="button"
              @click="advance(order.id)"
              class="w-full mt-2.5 bg-gray-900 text-white text-xs font-semibold py-1.5 rounded-lg hover:bg-gray-700"
            >
              {{ col.action }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>