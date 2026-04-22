<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useOrdersStore } from '@/stores/orders'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryTimeline from '@/components/ui/PryTimeline.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryCard from '@/components/ui/PryCard.vue'
import InstallmentPicker from '@/components/payment/InstallmentPicker.vue'

const STEPS = [
  { label: 'Catálogo' },
  { label: 'Carrinho' },
  { label: 'Pagamento' },
  { label: 'Comprovante' },
  { label: 'Concluído' },
]

const router  = useRouter()
const cart    = useCartStore()
const orders  = useOrdersStore()

const metodo        = ref('pix')
const numParcelas   = ref(2)
const diaVencimento = ref(10)
const loading       = ref(false)
const error         = ref('')

const totalFormatted = computed(() =>
  cart.total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
)

const METODOS = [
  { value: 'pix',       label: 'Pix',       icon: '⚡' },
  { value: 'dinheiro',  label: 'Dinheiro',   icon: '💵' },
  { value: 'parcelado', label: 'Parcelado',  icon: '📆' },
]

async function confirmar() {
  loading.value = true
  error.value   = ''
  try {
    const payload = {
      tipo_pagamento: metodo.value,
      items: cart.items.map(i => ({
        product_id: i.id,
        quantidade: i.quantidade,
      })),
    }
    if (metodo.value === 'parcelado') {
      payload.num_parcelas   = numParcelas.value
      payload.dia_vencimento = diaVencimento.value
    }
    const order = await orders.create(payload)
    cart.clear()
    router.push({ name: 'comprovante', params: { orderId: order.id } })
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao criar pedido'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <PryTopBar title="Pagamento" :show-back="true" @back="$router.back()" />

    <main class="page__content">
      <PryTimeline :steps="STEPS" :active="2" />

      <PryCard padding="16px">
        <p class="section-label">Forma de pagamento</p>
        <div class="metodos">
          <button
            v-for="m in METODOS"
            :key="m.value"
            class="metodo-btn"
            :class="{ active: metodo === m.value }"
            @click="metodo = m.value"
          >
            <span class="metodo-btn__icon">{{ m.icon }}</span>
            {{ m.label }}
          </button>
        </div>
      </PryCard>

      <PryCard v-if="metodo === 'parcelado'" padding="16px">
        <p class="section-label">Configurar parcelas</p>
        <InstallmentPicker
          :total="cart.total"
          @update:num-parcelas="numParcelas = $event"
          @update:dia-vencimento="diaVencimento = $event"
        />
      </PryCard>

      <PryCard padding="16px">
        <div class="total-row">
          <span class="total-label">Total do pedido</span>
          <span class="total-val">{{ totalFormatted }}</span>
        </div>
      </PryCard>

      <p v-if="error" class="err">{{ error }}</p>

      <PryButton full :loading="loading" @click="confirmar">
        Confirmar pedido
      </PryButton>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.section-label { font-size: 13px; font-weight: 700; color: var(--pry-mid); margin-bottom: 12px; }

.metodos { display: flex; gap: 8px; }
.metodo-btn {
  flex: 1;
  padding: 12px 8px;
  border-radius: var(--radius-md);
  border: 2px solid var(--pry-border);
  background: #fff;
  font-size: 13px;
  font-weight: 600;
  color: var(--pry-mid);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
}
.metodo-btn__icon { font-size: 20px; }
.metodo-btn.active { border-color: var(--pry-dark); background: var(--pry-dark); color: var(--pry); }

.total-row { display: flex; justify-content: space-between; align-items: center; }
.total-label { font-size: 15px; font-weight: 600; color: var(--pry-mid); }
.total-val   { font-size: 22px; font-weight: 800; color: var(--pry-dark); }

.err { font-size: 13px; color: #b91c1c; text-align: center; }
</style>
