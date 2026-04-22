<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryTimeline from '@/components/ui/PryTimeline.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryCard from '@/components/ui/PryCard.vue'

const STEPS = [
  { label: 'Catálogo' },
  { label: 'Carrinho' },
  { label: 'Pagamento' },
  { label: 'Comprovante' },
  { label: 'Concluído' },
]

const router = useRouter()
const cart   = useCartStore()
const auth   = useAuthStore()

const totalFormatted = computed(() =>
  cart.total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
)

function continuar() {
  if (!auth.isAuthenticated) {
    router.push('/login?redirect=/pagamento')
  } else {
    router.push('/pagamento')
  }
}
</script>

<template>
  <div class="page">
    <PryTopBar title="Carrinho" :show-back="true" @back="$router.back()" />

    <main class="page__content">
      <PryTimeline :steps="STEPS" :active="1" />

      <template v-if="!cart.isEmpty">
        <PryCard v-for="item in cart.items" :key="item.id" padding="12px">
          <div class="item">
            <div class="item__img-wrap">
              <img v-if="item.fotos?.[0]" :src="item.fotos[0]" :alt="item.nome" class="item__img" />
              <span v-else class="item__img-placeholder">🛍</span>
            </div>
            <div class="item__info">
              <p class="item__name">{{ item.nome }}</p>
              <p class="item__price">
                {{ Number(item.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}
              </p>
            </div>
            <div class="item__controls">
              <button class="qty-btn" @click="cart.updateQty(item.id, item.quantidade - 1)">−</button>
              <span class="qty-val">{{ item.quantidade }}</span>
              <button class="qty-btn" @click="cart.updateQty(item.id, item.quantidade + 1)">+</button>
            </div>
            <button class="remove-btn" @click="cart.remove(item.id)" title="Remover">✕</button>
          </div>
        </PryCard>

        <PryCard padding="16px">
          <div class="summary-row">
            <span class="summary-label">Total</span>
            <span class="summary-total">{{ totalFormatted }}</span>
          </div>
        </PryCard>

        <PryButton full @click="continuar">
          Continuar para pagamento
        </PryButton>
      </template>

      <div v-else class="empty">
        <p class="empty__icon">🛒</p>
        <p class="empty__msg">Seu carrinho está vazio</p>
        <PryButton variant="outline" @click="$router.push('/catalogo')">
          Ver catálogo
        </PryButton>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.item { display: flex; align-items: center; gap: 10px; }

.item__img-wrap {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--pry-light);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.item__img { width: 100%; height: 100%; object-fit: cover; }
.item__img-placeholder { font-size: 24px; }

.item__info { flex: 1; min-width: 0; }
.item__name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item__price { font-size: 13px; color: var(--pry-accent); font-weight: 700; margin-top: 2px; }

.item__controls { display: flex; align-items: center; gap: 8px; }
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
  color: var(--pry-dark);
}
.qty-val { font-size: 15px; font-weight: 700; min-width: 20px; text-align: center; }

.remove-btn {
  background: none;
  border: none;
  font-size: 14px;
  color: var(--pry-muted);
  cursor: pointer;
  padding: 4px;
}

.summary-row { display: flex; justify-content: space-between; align-items: center; }
.summary-label { font-size: 15px; font-weight: 600; color: var(--pry-mid); }
.summary-total { font-size: 20px; font-weight: 800; color: var(--pry-dark); }

.empty { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 40px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 15px; color: var(--pry-muted); }
</style>
