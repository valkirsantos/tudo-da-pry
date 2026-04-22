<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import PryButton from '@/components/ui/PryButton.vue'

const props = defineProps({
  product: { type: Object, required: true },
})

const router = useRouter()
const cart   = useCartStore()

const foto = computed(() => props.product.fotos?.[0] || null)

const preco = computed(() =>
  Number(props.product.preco).toLocaleString('pt-BR', {
    style: 'currency', currency: 'BRL',
  }),
)

function goToDetail() {
  router.push({ name: 'produto', params: { id: props.product.id } })
}

function addToCart(e) {
  e.stopPropagation()
  cart.add(props.product)
}
</script>

<template>
  <div class="product-card" @click="goToDetail">
    <div class="product-card__img-wrap">
      <img
        v-if="foto"
        :src="foto"
        :alt="product.nome"
        class="product-card__img"
        loading="lazy"
      />
      <div v-else class="product-card__img-placeholder">🛍</div>
      <span v-if="product.estoque === 0" class="product-card__badge product-card__badge--out">
        Esgotado
      </span>
    </div>
    <div class="product-card__body">
      <p class="product-card__name">{{ product.nome }}</p>
      <p class="product-card__price">{{ preco }}</p>
      <PryButton
        variant="primary"
        full
        :disabled="product.estoque === 0"
        @click="addToCart"
      >
        Adicionar
      </PryButton>
    </div>
  </div>
</template>

<style scoped>
.product-card {
  background: #fff;
  border: 1px solid var(--pry-border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.15s;
}
.product-card:active { box-shadow: var(--shadow-md); }

.product-card__img-wrap {
  position: relative;
  aspect-ratio: 1;
  background: var(--pry-light);
}
.product-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.product-card__img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
}

.product-card__badge {
  position: absolute;
  top: 8px;
  left: 8px;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 99px;
}
.product-card__badge--out { background: #fee2e2; color: #991b1b; }

.product-card__body {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.product-card__name {
  font-size: 13px;
  font-weight: 600;
  color: var(--pry-dark);
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.product-card__price {
  font-size: 15px;
  font-weight: 700;
  color: var(--pry-accent);
}
</style>
