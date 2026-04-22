<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryTag from '@/components/ui/PryTag.vue'

const router = useRouter()
const orders = useOrdersStore()

const STATUS_CONFIG = {
  aguardando_pagamento: { label: 'Aguardando pagamento', tag: 'pendente', progress: 10 },
  confirmado:           { label: 'Confirmado',           tag: 'ok',       progress: 30 },
  separando:            { label: 'Separando',            tag: 'novo',     progress: 55 },
  em_entrega:           { label: 'Em entrega',           tag: 'novo',     progress: 80 },
  entregue:             { label: 'Entregue',             tag: 'ok',       progress: 100 },
  cancelado:            { label: 'Cancelado',            tag: 'warn',     progress: 0 },
}

function tagVariant(status) { return STATUS_CONFIG[status]?.tag || 'pendente' }
function progressVal(status) { return STATUS_CONFIG[status]?.progress || 0 }
function statusLabel(status) { return STATUS_CONFIG[status]?.label || status }

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}
function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

onMounted(() => orders.fetch())
</script>

<template>
  <div class="page">
    <PryTopBar title="Meus Pedidos" />

    <main class="page__content">
      <div v-if="orders.loading" class="loading-msg">Carregando...</div>

      <PryCard
        v-for="order in orders.list"
        :key="order.id"
        padding="14px"
        class="order-card"
        @click="order.tipo_pagamento === 'parcelado'
          ? $router.push({ name: 'parcelas', params: { id: order.id } })
          : null"
      >
        <div class="order-card__header">
          <div>
            <p class="order-card__id">Pedido #{{ order.id }}</p>
            <p class="order-card__date">{{ formatDate(order.created_at) }}</p>
          </div>
          <PryTag :variant="tagVariant(order.status_pedido)">
            {{ statusLabel(order.status_pedido) }}
          </PryTag>
        </div>

        <div class="progress-wrap">
          <div class="progress-bar">
            <div
              class="progress-bar__fill"
              :class="{ 'progress-bar__fill--canceled': order.status_pedido === 'cancelado' }"
              :style="{ width: progressVal(order.status_pedido) + '%' }"
            />
          </div>
        </div>

        <div class="order-card__footer">
          <span class="order-card__meta">
            {{ order.tipo_pagamento === 'parcelado'
              ? `${order.num_parcelas}x parcelas`
              : order.tipo_pagamento }}
          </span>
          <span class="order-card__total">{{ formatBRL(order.total) }}</span>
        </div>

        <p v-if="order.tipo_pagamento === 'parcelado'" class="order-card__link">
          Ver parcelas →
        </p>
      </PryCard>

      <div v-if="!orders.loading && !orders.list.length" class="empty">
        <p class="empty__icon">📦</p>
        <p class="empty__msg">Você ainda não fez pedidos</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 10px; }

.loading-msg { text-align: center; color: var(--pry-muted); font-size: 14px; padding: 24px 0; }

.order-card { cursor: pointer; }
.order-card__header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.order-card__id     { font-size: 14px; font-weight: 700; }
.order-card__date   { font-size: 12px; color: var(--pry-muted); margin-top: 2px; }

.progress-wrap { margin-bottom: 10px; }
.progress-bar  { height: 6px; background: var(--pry-light); border-radius: 99px; overflow: hidden; }
.progress-bar__fill {
  height: 100%;
  background: var(--pry-accent);
  border-radius: 99px;
  transition: width 0.4s;
}
.progress-bar__fill--canceled { background: #fca5a5; }

.order-card__footer { display: flex; justify-content: space-between; align-items: center; }
.order-card__meta   { font-size: 12px; color: var(--pry-muted); text-transform: capitalize; }
.order-card__total  { font-size: 15px; font-weight: 700; color: var(--pry-dark); }
.order-card__link   { font-size: 12px; color: var(--pry-accent); font-weight: 600; margin-top: 6px; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 14px; color: var(--pry-muted); }
</style>
