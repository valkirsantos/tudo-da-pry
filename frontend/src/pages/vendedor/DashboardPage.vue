<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryTag from '@/components/ui/PryTag.vue'
import PryButton from '@/components/ui/PryButton.vue'

const router = useRouter()

const metrics    = ref({ vendas_hoje: 0, pedidos_abertos: 0, parcelas_vencendo: 0, notificacoes_enviadas: 0 })
const categorias = ref([])
const pendentes  = ref([])
const loading    = ref(false)
const actionLoading = ref(null)

async function load() {
  loading.value = true
  try {
    const [salesRes, proofsRes] = await Promise.all([
      api.get('/reports/sales', { params: { periodo: 'hoje' } }).catch(() => ({ data: { data: {} } })),
      api.get('/payment-proofs', { params: { status: 'pendente', per_page: 5 } }).catch(() => ({ data: { data: [] } })),
    ])
    const s = salesRes.data.data
    metrics.value = {
      vendas_hoje:          s.total              ?? 0,
      pedidos_abertos:      s.pedidos_abertos   ?? 0,
      parcelas_vencendo:    s.parcelas_vencendo ?? 0,
      notificacoes_enviadas: s.notificacoes      ?? 0,
    }
    categorias.value = s.por_categoria ?? []
    pendentes.value  = proofsRes.data.data
  } finally {
    loading.value = false
  }
}

const maxCat = () => Math.max(...categorias.value.map(c => c.total), 1)

async function approve(id) {
  actionLoading.value = id
  try {
    await api.put(`/payment-proofs/${id}`, { action: 'approve' })
    pendentes.value = pendentes.value.filter(p => p.id !== id)
  } finally {
    actionLoading.value = null
  }
}

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Dashboard" />

    <main class="page__content">
      <!-- Métricas -->
      <div class="metrics-grid">
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Vendas hoje</p>
          <p class="metric-card__val">{{ formatBRL(metrics.vendas_hoje) }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Pedidos abertos</p>
          <p class="metric-card__val metric-card__val--accent">{{ metrics.pedidos_abertos }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Parcelas a vencer</p>
          <p class="metric-card__val metric-card__val--warn">{{ metrics.parcelas_vencendo }}</p>
        </PryCard>
        <PryCard padding="14px" class="metric-card">
          <p class="metric-card__label">Notificações enviadas</p>
          <p class="metric-card__val">{{ metrics.notificacoes_enviadas }}</p>
        </PryCard>
      </div>

      <!-- Gráfico por categoria -->
      <PryCard v-if="categorias.length" padding="16px">
        <p class="section-title">Vendas por categoria</p>
        <div class="bar-chart">
          <div v-for="cat in categorias" :key="cat.categoria" class="bar-row">
            <span class="bar-label">{{ cat.categoria }}</span>
            <div class="bar-track">
              <div
                class="bar-fill"
                :style="{ width: (cat.total / maxCat() * 100) + '%' }"
              />
            </div>
            <span class="bar-val">{{ formatBRL(cat.total) }}</span>
          </div>
        </div>
      </PryCard>

      <!-- Comprovantes pendentes -->
      <div>
        <div class="section-header">
          <p class="section-title">Comprovantes pendentes</p>
          <button class="ver-todos" @click="router.push('/vendedor/comprovantes')">Ver todos →</button>
        </div>

        <div v-if="!pendentes.length && !loading" class="empty-msg">
          Nenhum comprovante pendente 🎉
        </div>

        <PryCard
          v-for="proof in pendentes"
          :key="proof.id"
          padding="12px"
          class="proof-card"
        >
          <div class="proof-card__info">
            <div>
              <p class="proof-card__name">{{ proof.nome_arquivo }}</p>
              <p class="proof-card__sub">
                Pedido #{{ proof.order_id }}
                {{ proof.installment_id ? `· Parcela #${proof.installment_id}` : '' }}
              </p>
            </div>
            <PryTag variant="pendente">Pendente</PryTag>
          </div>
          <div class="proof-card__actions">
            <PryButton
              variant="outline"
              :loading="actionLoading === proof.id"
              @click="approve(proof.id)"
            >
              ✓ Validar
            </PryButton>
            <PryButton
              variant="danger"
              @click="router.push('/vendedor/comprovantes')"
            >
              Ver detalhes
            </PryButton>
          </div>
        </PryCard>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 16px; }

.metrics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.metric-card__label { font-size: 12px; color: var(--pry-muted); font-weight: 600; margin-bottom: 6px; }
.metric-card__val   { font-size: 20px; font-weight: 800; color: var(--pry-dark); }
.metric-card__val--accent { color: var(--pry-accent); }
.metric-card__val--warn   { color: #b45309; }

.section-title  { font-size: 14px; font-weight: 700; color: var(--pry-dark); margin-bottom: 12px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.section-header .section-title { margin-bottom: 0; }
.ver-todos { background: none; border: none; font-size: 13px; color: var(--pry-accent); font-weight: 600; cursor: pointer; }

.bar-chart { display: flex; flex-direction: column; gap: 10px; }
.bar-row   { display: flex; align-items: center; gap: 8px; }
.bar-label { font-size: 12px; color: var(--pry-mid); width: 72px; flex-shrink: 0; text-transform: capitalize; }
.bar-track { flex: 1; height: 8px; background: var(--pry-light); border-radius: 99px; overflow: hidden; }
.bar-fill  { height: 100%; background: var(--pry-accent); border-radius: 99px; transition: width 0.4s; }
.bar-val   { font-size: 11px; color: var(--pry-mid); width: 68px; text-align: right; flex-shrink: 0; }

.empty-msg { font-size: 14px; color: var(--pry-muted); text-align: center; padding: 16px 0; }

.proof-card { display: flex; flex-direction: column; gap: 10px; margin-bottom: 10px; }
.proof-card__info    { display: flex; justify-content: space-between; align-items: flex-start; }
.proof-card__name    { font-size: 13px; font-weight: 600; }
.proof-card__sub     { font-size: 12px; color: var(--pry-muted); margin-top: 2px; }
.proof-card__actions { display: flex; gap: 8px; }
.proof-card__actions .pry-btn { flex: 1; padding: 8px 12px; font-size: 13px; }
</style>
