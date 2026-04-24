<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryTag from '@/components/ui/PryTag.vue'
import PryButton from '@/components/ui/PryButton.vue'

const router = useRouter()

const STATUS_OPTIONS = [
  { value: '',                    label: 'Todos' },
  { value: 'aguardando_pagamento', label: 'Aguardando pagamento' },
  { value: 'confirmado',           label: 'Confirmado' },
  { value: 'separando',            label: 'Separando' },
  { value: 'em_entrega',           label: 'Em entrega' },
  { value: 'entregue',             label: 'Entregue' },
  { value: 'cancelado',            label: 'Cancelado' },
]

const STATUS_NEXT = {
  aguardando_pagamento: 'confirmado',
  confirmado:           'separando',
  separando:            'em_entrega',
  em_entrega:           'entregue',
}

const TAG_MAP = {
  aguardando_pagamento: 'pendente',
  confirmado:           'ok',
  separando:            'novo',
  em_entrega:           'novo',
  entregue:             'ok',
  cancelado:            'warn',
}

const orders        = ref([])
const loading       = ref(false)
const filtroStatus  = ref('')
const actionLoading = ref(null)

async function load() {
  loading.value = true
  try {
    const params = {}
    if (filtroStatus.value) params.status = filtroStatus.value
    const { data } = await api.get('/orders', { params })
    orders.value = data.data
  } finally {
    loading.value = false
  }
}

async function avancarStatus(order) {
  const next = STATUS_NEXT[order.status_pedido]
  if (!next) return
  actionLoading.value = order.id
  try {
    const { data } = await api.put(`/orders/${order.id}/status`, { status_pedido: next })
    const idx = orders.value.findIndex(o => o.id === order.id)
    if (idx !== -1) orders.value[idx] = { ...orders.value[idx], status_pedido: next }
  } finally {
    actionLoading.value = null
  }
}

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}
function nextLabel(status) {
  const labels = {
    aguardando_pagamento: 'Confirmar',
    confirmado:           'Separando',
    separando:            'Enviar',
    em_entrega:           'Entregue',
  }
  return labels[status] ?? null
}

watch(filtroStatus, load)
onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Pedidos" />

    <main class="page__content">
      <!-- Filtro de status -->
      <div class="filtros">
        <button
          v-for="s in STATUS_OPTIONS"
          :key="s.value"
          class="filtro-chip"
          :class="{ active: filtroStatus === s.value }"
          @click="filtroStatus = s.value"
        >
          {{ s.label }}
        </button>
      </div>

      <p v-if="loading" class="hint">Carregando...</p>

      <PryCard
        v-for="order in orders"
        :key="order.id"
        padding="14px"
        class="order-card"
      >
        <!-- Cabeçalho -->
        <div class="order-card__header">
          <div>
            <p class="order-card__id">Pedido #{{ order.id }}</p>
            <p class="order-card__cliente">{{ order.user?.nome ?? '—' }}</p>
            <p class="order-card__date">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="order-card__right">
            <PryTag :variant="TAG_MAP[order.status_pedido]">
              {{ STATUS_OPTIONS.find(s => s.value === order.status_pedido)?.label ?? order.status_pedido }}
            </PryTag>
            <p class="order-card__total">{{ formatBRL(order.total) }}</p>
          </div>
        </div>

        <!-- Itens resumidos -->
        <div class="order-card__items">
          <span
            v-for="item in order.items?.slice(0, 3)"
            :key="item.id"
            class="item-pill"
          >
            {{ item.quantidade }}x {{ item.product?.nome }}
          </span>
          <span v-if="order.items?.length > 3" class="item-pill item-pill--more">
            +{{ order.items.length - 3 }} itens
          </span>
        </div>

        <!-- Tipo de pagamento -->
        <p class="order-card__pagamento">
          {{ order.tipo_pagamento === 'parcelado'
            ? `${order.num_parcelas}x parcelas`
            : order.tipo_pagamento }}
          ·
          <span :class="order.status_pagamento === 'pago' ? 'pago' : 'pendente-pag'">
            {{ order.status_pagamento }}
          </span>
        </p>

        <!-- Ações -->
        <div class="order-card__actions">
          <PryButton
            v-if="nextLabel(order.status_pedido)"
            variant="outline"
            :loading="actionLoading === order.id"
            @click="avancarStatus(order)"
          >
            {{ nextLabel(order.status_pedido) }}
          </PryButton>

          <PryButton
            variant="outline"
            @click="router.push('/vendedor/comprovantes')"
          >
            Comprovantes
          </PryButton>
        </div>
      </PryCard>

      <div v-if="!loading && !orders.length" class="empty">
        <p class="empty__icon">📦</p>
        <p class="empty__msg">Nenhum pedido encontrado</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.filtros { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
.filtros::-webkit-scrollbar { display: none; }
.filtro-chip {
  flex-shrink: 0;
  padding: 6px 14px;
  border-radius: 99px;
  border: 1.5px solid var(--pry-border);
  background: #fff;
  color: var(--pry-mid);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s;
}
.filtro-chip.active { background: var(--pry-dark); color: var(--pry); border-color: var(--pry-dark); }

.hint { text-align: center; color: var(--pry-muted); font-size: 14px; padding: 16px 0; }

.order-card { display: flex; flex-direction: column; gap: 10px; }

.order-card__header { display: flex; justify-content: space-between; align-items: flex-start; }
.order-card__id      { font-size: 14px; font-weight: 800; }
.order-card__cliente { font-size: 13px; color: var(--pry-mid); margin-top: 2px; }
.order-card__date    { font-size: 11px; color: var(--pry-muted); margin-top: 2px; }
.order-card__right   { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
.order-card__total   { font-size: 16px; font-weight: 800; color: var(--pry-dark); }

.order-card__items { display: flex; flex-wrap: wrap; gap: 6px; }
.item-pill {
  padding: 3px 10px;
  border-radius: 99px;
  background: var(--pry-light);
  font-size: 12px;
  color: var(--pry-mid);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 160px;
}
.item-pill--more { color: var(--pry-accent); background: transparent; border: 1px solid var(--pry-border); }

.order-card__pagamento { font-size: 12px; color: var(--pry-muted); text-transform: capitalize; }
.pago       { color: #166534; font-weight: 600; }
.pendente-pag { color: #b45309; font-weight: 600; }

.order-card__actions { display: flex; gap: 8px; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 14px; color: var(--pry-muted); }
</style>
