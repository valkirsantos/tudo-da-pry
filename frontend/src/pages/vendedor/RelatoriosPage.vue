<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'

const NOW    = new Date()
const months = Array.from({ length: 12 }, (_, i) => {
  const d = new Date(NOW.getFullYear(), NOW.getMonth() - i, 1)
  return {
    value: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`,
    label: d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
  }
})

const periodo      = ref(months[0].value)
const metrics      = ref({ total: 0, ticket_medio: 0, pedidos: 0, inadimplencia: 0 })
const topProdutos  = ref([])
const parcelasOpen = ref([])
const loading      = ref(false)

async function load() {
  loading.value = true
  try {
    const [salesRes, instRes] = await Promise.all([
      api.get('/reports/sales',        { params: { periodo: periodo.value } }),
      api.get('/reports/installments', { params: { periodo: periodo.value, status: 'pendente' } }),
    ])
    const s = salesRes.data.data
    metrics.value     = { total: s.total ?? 0, ticket_medio: s.ticket_medio ?? 0, pedidos: s.pedidos ?? 0, inadimplencia: s.inadimplencia ?? 0 }
    topProdutos.value = s.top_produtos ?? []
    parcelasOpen.value = instRes.data.data ?? []
  } finally {
    loading.value = false
  }
}

const maxProd = computed(() => Math.max(...topProdutos.value.map(p => p.quantidade), 1))

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR')
}

function exportPdf() {
  window.print()
}

onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Relatórios">
      <button class="print-btn" @click="exportPdf">🖨 PDF</button>
    </PryTopBar>

    <main class="page__content" id="print-area">
      <!-- Seletor de período -->
      <div class="periodo-wrap">
        <label class="periodo-label">Período</label>
        <select class="periodo-select" v-model="periodo" @change="load">
          <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
      </div>

      <p v-if="loading" class="hint">Carregando relatório...</p>

      <!-- Cards de métricas -->
      <div class="metrics-grid">
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Total do período</p>
          <p class="metric-card__val">{{ formatBRL(metrics.total) }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Ticket médio</p>
          <p class="metric-card__val metric-card__val--accent">{{ formatBRL(metrics.ticket_medio) }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Pedidos</p>
          <p class="metric-card__val">{{ metrics.pedidos }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Inadimplência</p>
          <p class="metric-card__val metric-card__val--warn">{{ formatBRL(metrics.inadimplencia) }}</p>
        </PryCard>
      </div>

      <!-- Top produtos -->
      <PryCard v-if="topProdutos.length" padding="16px">
        <p class="section-title">Produtos mais vendidos</p>
        <div class="bar-chart">
          <div v-for="prod in topProdutos" :key="prod.product_id" class="bar-row">
            <span class="bar-label" :title="prod.nome">{{ prod.nome }}</span>
            <div class="bar-track">
              <div
                class="bar-fill"
                :style="{ width: (prod.quantidade / maxProd * 100) + '%' }"
              />
            </div>
            <span class="bar-qty">{{ prod.quantidade }} un.</span>
          </div>
        </div>
      </PryCard>

      <!-- Parcelas em aberto -->
      <PryCard v-if="parcelasOpen.length" padding="16px">
        <p class="section-title">Parcelas em aberto</p>
        <table class="table">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Parcela</th>
              <th>Valor</th>
              <th>Vencimento</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inst in parcelasOpen" :key="inst.id">
              <td>{{ inst.order?.user?.nome ?? '—' }}</td>
              <td>{{ inst.numero_parcela }}ª</td>
              <td>{{ formatBRL(inst.valor) }}</td>
              <td>{{ formatDate(inst.data_vencimento) }}</td>
              <td :class="inst.status === 'atrasado' ? 'td--warn' : 'td--pending'">
                {{ inst.status }}
              </td>
            </tr>
          </tbody>
        </table>
      </PryCard>

      <div
        v-if="!loading && !topProdutos.length && !parcelasOpen.length && metrics.pedidos === 0"
        class="empty"
      >
        <p class="empty__icon">📊</p>
        <p class="empty__msg">Sem dados para este período</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 14px; }

.print-btn { background: none; border: none; color: var(--pry); font-size: 13px; font-weight: 600; cursor: pointer; }

.periodo-wrap  { display: flex; flex-direction: column; gap: 6px; }
.periodo-label { font-size: 13px; font-weight: 600; color: var(--pry-mid); }
.periodo-select {
  padding: 11px 13px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  color: var(--pry-dark);
  background: #fff;
  outline: none;
}
.periodo-select:focus { border-color: var(--pry-accent); }

.hint { font-size: 14px; color: var(--pry-muted); text-align: center; padding: 12px 0; }

.metrics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.metric-card__label { font-size: 12px; color: var(--pry-muted); font-weight: 600; margin-bottom: 6px; }
.metric-card__val   { font-size: 18px; font-weight: 800; color: var(--pry-dark); }
.metric-card__val--accent { color: var(--pry-accent); }
.metric-card__val--warn   { color: #b45309; }

.section-title { font-size: 14px; font-weight: 700; color: var(--pry-dark); margin-bottom: 12px; }

.bar-chart { display: flex; flex-direction: column; gap: 10px; }
.bar-row   { display: flex; align-items: center; gap: 8px; }
.bar-label {
  font-size: 12px;
  color: var(--pry-mid);
  width: 90px;
  flex-shrink: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.bar-track { flex: 1; height: 8px; background: var(--pry-light); border-radius: 99px; overflow: hidden; }
.bar-fill  { height: 100%; background: var(--pry-accent); border-radius: 99px; transition: width 0.4s; }
.bar-qty   { font-size: 11px; color: var(--pry-mid); width: 40px; text-align: right; flex-shrink: 0; }

.table { width: 100%; border-collapse: collapse; font-size: 12px; }
.table th,
.table td { padding: 8px 6px; text-align: left; border-bottom: 1px solid var(--pry-border); }
.table th { color: var(--pry-muted); font-weight: 700; font-size: 11px; }
.td--warn    { color: #b45309; font-weight: 700; }
.td--pending { color: var(--pry-mid); }

.empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 14px; color: var(--pry-muted); }

@media print {
  .pry-topbar,
  .pry-bottom-nav,
  .periodo-wrap,
  .print-btn { display: none !important; }
  .page__content { padding: 0; }
  #print-area { padding: 0; }
}
</style>
