<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useCartStore } from '@/stores/cart'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryTag from '@/components/ui/PryTag.vue'

const route  = useRoute()
const router = useRouter()
const cart   = useCartStore()

const product     = ref(null)
const loading     = ref(false)
const activePhoto = ref(0)
const added       = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/products/${route.params.id}`)
    product.value = data.data
  } finally {
    loading.value = false
  }
}

const preco = computed(() =>
  product.value
    ? Number(product.value.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
    : '',
)

function addToCart() {
  cart.add(product.value)
  added.value = true
  setTimeout(() => { added.value = false }, 1500)
}

onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Produto" :show-back="true" @back="router.back()" />

    <main class="page__content" v-if="product">
      <!-- Galeria -->
      <div class="gallery">
        <div class="gallery__main">
          <img
            v-if="product.fotos?.length"
            :src="product.fotos[activePhoto]"
            :alt="product.nome"
            class="gallery__img"
          />
          <div v-else class="gallery__placeholder">🛍</div>
        </div>
        <div v-if="product.fotos?.length > 1" class="gallery__thumbs">
          <img
            v-for="(foto, i) in product.fotos"
            :key="i"
            :src="foto"
            :alt="`Foto ${i + 1}`"
            class="gallery__thumb"
            :class="{ active: activePhoto === i }"
            @click="activePhoto = i"
          />
        </div>
      </div>

      <!-- Info -->
      <div class="info">
        <div class="info__top">
          <h1 class="info__name">{{ product.nome }}</h1>
          <PryTag variant="novo">{{ product.categoria }}</PryTag>
        </div>
        <p class="info__price">{{ preco }}</p>
        <p class="info__desc">{{ product.descricao }}</p>
        <p v-if="product.estoque === 0" class="info__stock--out">Produto esgotado</p>
        <p v-else class="info__stock">{{ product.estoque }} em estoque</p>
      </div>

      <PryButton
        full
        :disabled="product.estoque === 0"
        @click="addToCart"
      >
        {{ added ? '✓ Adicionado!' : 'Adicionar ao carrinho' }}
      </PryButton>
    </main>

    <div v-else-if="loading" class="page__content loading-msg">Carregando...</div>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 16px; }

.gallery__main {
  aspect-ratio: 1;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: var(--pry-light);
  display: flex;
  align-items: center;
  justify-content: center;
}
.gallery__img { width: 100%; height: 100%; object-fit: cover; }
.gallery__placeholder { font-size: 64px; }
.gallery__thumbs { display: flex; gap: 8px; margin-top: 10px; overflow-x: auto; }
.gallery__thumb {
  width: 60px;
  height: 60px;
  border-radius: var(--radius-md);
  object-fit: cover;
  border: 2px solid transparent;
  cursor: pointer;
  flex-shrink: 0;
}
.gallery__thumb.active { border-color: var(--pry-accent); }

.info__top  { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.info__name { font-size: 20px; font-weight: 800; flex: 1; }
.info__price { font-size: 24px; font-weight: 800; color: var(--pry-accent); }
.info__desc  { font-size: 14px; color: var(--pry-mid); line-height: 1.6; }
.info__stock      { font-size: 13px; color: #166534; }
.info__stock--out { font-size: 13px; color: #991b1b; }

.loading-msg { text-align: center; color: var(--pry-muted); padding: 32px 0; font-size: 14px; }
</style>
