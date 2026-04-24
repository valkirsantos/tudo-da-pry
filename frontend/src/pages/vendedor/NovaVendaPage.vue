<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useInstallments } from '@/composables/useInstallments'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryInput from '@/components/ui/PryInput.vue'
import InstallmentPicker from '@/components/payment/InstallmentPicker.vue'

const router = useRouter()

// ── Seção 1: Cliente ─────────────────────────────────────────
const clienteQuery    = ref('')
const clienteResults  = ref([])
const clienteSelecionado = ref(null)
const showNovoCliente = ref(false)
const novoNome        = ref('')
const novoCelular     = ref('')
const clienteLoading  = ref(false)
let   clienteTimer    = null

watch(clienteQuery, (v) => {
  clearTimeout(clienteTimer)
  if (!v) { clienteResults.value = []; return }
  clienteTimer = setTimeout(() => buscarCliente(v), 300)
})

async function buscarCliente(q) {
  clienteLoading.value = true
  try {
    const { data } = await api.get('/clients', { params: { q, per_page: 5 } })
    clienteResults.value = data.data
  } finally {
    clienteLoading.value = false
  }
}

function selecionarCliente(c) {
  clienteSelecionado.value = c
  clienteQuery.value       = ''
  clienteResults.value     = []
  showNovoCliente.value    = false
}

async function cadastrarCliente() {
  clienteLoading.value = true
  try {
    const { data } = await api.post('/clients', { nome: novoNome.value, celular: novoCelular.value.replace(/\D/g, '') })
    selecionarCliente(data.data)
    novoNome.value    = ''
    novoCelular.value = ''
  } finally {
    clienteLoading.value = false
  }
}

// ── Seção 2: Produtos ─────────────────────────────────────────
const allProducts = ref([])
const quantities  = ref({})
const prodLoading = ref(false)

async function loadProducts() {
  prodLoading.value = true
  try {
    const { data } = await api.get('/products', { params: { per_page: 50 } })
    allProducts.value = data.data
  } finally {
    prodLoading.value = false
  }
}

function qty(id)      { return quantities.value[id] || 0 }
function inc(id)      { quantities.value = { ...quantities.value, [id]: qty(id) + 1 } }
function dec(id)      { const q = qty(id) - 1; if (q <= 0) { const c = { ...quantities.value }; delete c[id]; quantities.value = c } else quantities.value = { ...quantities.value, [id]: q } }

const selectedItems = computed(() =>
  allProducts.value.filter(p => qty(p.id) > 0).map(p => ({ ...p, quantidade: qty(p.id) }))
)

const total = computed(() =>
  selectedItems.value.reduce((s, i) => s + Number(i.preco) * i.quantidade, 0)
)

// ── Seção 3: Pagamento ────────────────────────────────────────
const metodo        = ref('pix')
const numParcelas   = ref(2)
const diaVencimento = ref(10)
const confirmLoading = ref(false)
const error          = ref('')

const { preview } = useInstallments(total, numParcelas, diaVencimento)

const METODOS = [
  { value: 'pix',       label: 'Pix',      icon: '⚡' },
  { value: 'dinheiro',  label: 'Dinheiro', icon: '💵' },
  { value: 'parcelado', label: 'Parcelado', icon: '📆' },
]

async function confirmarVenda() {
  if (!clienteSelecionado.value || !selectedItems.value.length) return
  confirmLoading.value = true
  error.value          = ''
  try {
    const payload = {
      celular:        clienteSelecionado.value.celular,
      nome:           clienteSelecionado.value.nome,
      tipo_pagamento: metodo.value,
      items: selectedItems.value.map(i => ({ product_id: i.id, quantidade: i.quantidade })),
    }
    if (metodo.value === 'parcelado') {
      payload.num_parcelas   = numParcelas.value
      payload.dia_vencimento = diaVencimento.value
    }
    await api.post('/orders/seller', payload)
    router.push('/vendedor/pedidos')
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao confirmar venda'
  } finally {
    confirmLoading.value = false
  }
}

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

loadProducts()
</script>

<template>
  <div class="page">
    <PryTopBar title="Nova Venda" :show-back="true" @back="$router.back()" />

    <main class="page__content">

      <!-- ── Seção 1: Cliente ── -->
      <PryCard padding="16px">
        <p class="section-title">1. Cliente</p>

        <template v-if="!clienteSelecionado">
          <PryInput v-model="clienteQuery" placeholder="Buscar por nome ou celular..." />

          <div v-if="clienteLoading" class="search-hint">Buscando...</div>

          <div v-if="clienteResults.length" class="results-list">
            <button
              v-for="c in clienteResults"
              :key="c.id"
              class="result-item"
              @click="selecionarCliente(c)"
            >
              <span class="result-item__name">{{ c.nome }}</span>
              <span class="result-item__cel">{{ c.celular }}</span>
            </button>
          </div>

          <div v-if="clienteQuery && !clienteLoading && !clienteResults.length" class="not-found">
            <p>Nenhum cliente encontrado.</p>
            <button class="cadastrar-btn" @click="showNovoCliente = !showNovoCliente">
              + Cadastrar novo cliente
            </button>
          </div>

          <div v-if="showNovoCliente" class="novo-cliente-form">
            <PryInput v-model="novoNome"     label="Nome"    placeholder="Nome completo" />
            <PryInput v-model="novoCelular"  label="Celular" placeholder="(11) 99999-9999" type="tel" />
            <PryButton full :loading="clienteLoading" @click="cadastrarCliente">
              Cadastrar e selecionar
            </PryButton>
          </div>
        </template>

        <div v-else class="cliente-chip">
          <div class="cliente-chip__info">
            <span class="cliente-chip__name">{{ clienteSelecionado.nome }}</span>
            <span class="cliente-chip__cel">{{ clienteSelecionado.celular }}</span>
          </div>
          <button class="cliente-chip__troca" @click="clienteSelecionado = null">Trocar</button>
        </div>
      </PryCard>

      <!-- ── Seção 2: Produtos ── -->
      <PryCard padding="16px">
        <p class="section-title">2. Produtos</p>
        <p v-if="prodLoading" class="hint">Carregando produtos...</p>

        <div class="product-list">
          <div v-for="p in allProducts" :key="p.id" class="prod-row">
            <div class="prod-row__info">
              <p class="prod-row__name">{{ p.nome }}</p>
              <p class="prod-row__price">{{ formatBRL(p.preco) }}</p>
            </div>
            <div class="qty-ctrl">
              <button class="qty-btn" :disabled="qty(p.id) === 0" @click="dec(p.id)">−</button>
              <span class="qty-val">{{ qty(p.id) }}</span>
              <button class="qty-btn" :disabled="qty(p.id) >= p.estoque" @click="inc(p.id)">+</button>
            </div>
          </div>
        </div>

        <div v-if="selectedItems.length" class="total-bar">
          <span class="total-bar__label">Total selecionado</span>
          <span class="total-bar__val">{{ formatBRL(total) }}</span>
        </div>
      </PryCard>

      <!-- ── Seção 3: Pagamento ── -->
      <PryCard padding="16px">
        <p class="section-title">3. Pagamento</p>
        <div class="metodos">
          <button
            v-for="m in METODOS"
            :key="m.value"
            class="metodo-btn"
            :class="{ active: metodo === m.value }"
            @click="metodo = m.value"
          >
            <span>{{ m.icon }}</span>
            {{ m.label }}
          </button>
        </div>

        <div v-if="metodo === 'parcelado'" class="inst-section">
          <InstallmentPicker
            :total="total"
            @update:num-parcelas="numParcelas = $event"
            @update:dia-vencimento="diaVencimento = $event"
          />
        </div>
      </PryCard>

      <p v-if="error" class="err">{{ error }}</p>

      <PryButton
        full
        :loading="confirmLoading"
        :disabled="!clienteSelecionado || !selectedItems.length"
        @click="confirmarVenda"
      >
        Confirmar venda
      </PryButton>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.section-title { font-size: 14px; font-weight: 700; color: var(--pry-dark); margin-bottom: 12px; }
.hint          { font-size: 13px; color: var(--pry-muted); }

.results-list { margin-top: 8px; border: 1px solid var(--pry-border); border-radius: var(--radius-md); overflow: hidden; }
.result-item  {
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 10px 14px;
  background: #fff;
  border: none;
  border-bottom: 1px solid var(--pry-border);
  cursor: pointer;
  text-align: left;
}
.result-item:last-child { border-bottom: none; }
.result-item:active     { background: var(--pry-light); }
.result-item__name { font-size: 14px; font-weight: 600; }
.result-item__cel  { font-size: 12px; color: var(--pry-muted); }

.search-hint { font-size: 13px; color: var(--pry-muted); margin-top: 6px; }
.not-found   { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }
.not-found p { font-size: 13px; color: var(--pry-muted); }
.cadastrar-btn {
  background: none;
  border: 1.5px dashed var(--pry-border);
  border-radius: var(--radius-md);
  padding: 10px;
  font-size: 13px;
  color: var(--pry-accent);
  font-weight: 600;
  cursor: pointer;
  width: 100%;
}

.novo-cliente-form { margin-top: 10px; display: flex; flex-direction: column; gap: 10px; }

.cliente-chip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--pry-light);
  border-radius: var(--radius-md);
  padding: 10px 14px;
}
.cliente-chip__info { display: flex; flex-direction: column; gap: 2px; }
.cliente-chip__name { font-size: 14px; font-weight: 700; }
.cliente-chip__cel  { font-size: 12px; color: var(--pry-muted); }
.cliente-chip__troca { background: none; border: none; color: var(--pry-accent); font-size: 13px; font-weight: 600; cursor: pointer; }

.product-list { display: flex; flex-direction: column; gap: 2px; }
.prod-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid var(--pry-border);
}
.prod-row:last-child { border-bottom: none; }
.prod-row__info  { flex: 1; min-width: 0; }
.prod-row__name  { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-row__price { font-size: 13px; color: var(--pry-accent); font-weight: 700; margin-top: 2px; }

.qty-ctrl { display: flex; align-items: center; gap: 8px; flex-shrink: 0; margin-left: 8px; }
.qty-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1.5px solid var(--pry-border);
  background: #fff;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qty-btn:disabled { opacity: 0.35; cursor: default; }
.qty-val { font-size: 15px; font-weight: 700; min-width: 20px; text-align: center; }

.total-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1.5px solid var(--pry-border);
  margin-top: 8px;
}
.total-bar__label { font-size: 14px; font-weight: 600; color: var(--pry-mid); }
.total-bar__val   { font-size: 20px; font-weight: 800; color: var(--pry-dark); }

.metodos { display: flex; gap: 8px; margin-bottom: 12px; }
.metodo-btn {
  flex: 1;
  padding: 10px 6px;
  border-radius: var(--radius-md);
  border: 2px solid var(--pry-border);
  background: #fff;
  font-size: 12px;
  font-weight: 600;
  color: var(--pry-mid);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
}
.metodo-btn.active { border-color: var(--pry-dark); background: var(--pry-dark); color: var(--pry); }

.inst-section { margin-top: 4px; }
.err { font-size: 13px; color: #b91c1c; text-align: center; }
</style>
