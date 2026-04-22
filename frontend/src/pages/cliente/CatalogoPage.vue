<script setup>
import { ref, watch, onMounted } from 'vue'
import { useIntersectionObserver } from '@vueuse/core'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryInput from '@/components/ui/PryInput.vue'
import ProductCard from '@/components/product/ProductCard.vue'

const CATEGORIAS = [
  { value: '', label: 'Todos' },
  { value: 'bolsas',   label: 'Bolsas' },
  { value: 'sapatos',  label: 'Sapatos' },
  { value: 'perfumes', label: 'Perfumes' },
  { value: 'relogios', label: 'Relógios' },
  { value: 'outros',   label: 'Outros' },
]

const auth        = useAuthStore()
const products    = ref([])
const broadcast   = ref(null)
const search      = ref('')
const categoria   = ref('')
const page        = ref(1)
const hasMore     = ref(true)
const loading     = ref(false)
const sentinel    = ref(null)
let debounceTimer = null

async function loadProducts(reset = false) {
  if (loading.value || (!hasMore.value && !reset)) return
  if (reset) { products.value = []; page.value = 1; hasMore.value = true }
  loading.value = true
  try {
    const params = { page: page.value, per_page: 12 }
    if (search.value)    params.search    = search.value
    if (categoria.value) params.categoria = categoria.value
    const { data } = await api.get('/products', { params })
    products.value.push(...data.data)
    hasMore.value = data.data.length === 12
    page.value++
  } catch { /* silent */ } finally {
    loading.value = false
  }
}

async function loadBroadcast() {
  if (!auth.isAuthenticated) return // endpoint requires auth
  try {
    const { data } = await api.get('/broadcasts', { params: { per_page: 1 } })
    broadcast.value = data.data?.[0] || null
  } catch { /* silent */ }
}

watch(search, () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadProducts(true), 300)
})
watch(categoria, () => loadProducts(true))

useIntersectionObserver(sentinel, ([entry]) => {
  if (entry.isIntersecting) loadProducts()
}, { threshold: 0.1 })

onMounted(() => { loadBroadcast(); loadProducts() })
</script>

<template>
  <div class="page">
    <PryTopBar title="Tudo da Pry" />

    <main class="page__content">
      <div v-if="broadcast" class="banner">
        <span class="banner__tipo">{{ broadcast.tipo.replace('_', ' ') }}</span>
        <p class="banner__titulo">{{ broadcast.titulo }}</p>
        <p class="banner__msg">{{ broadcast.mensagem }}</p>
      </div>

      <PryInput v-model="search" placeholder="Buscar produtos..." />

      <div class="categorias">
        <button
          v-for="cat in CATEGORIAS"
          :key="cat.value"
          class="cat-chip"
          :class="{ active: categoria === cat.value }"
          @click="categoria = cat.value"
        >
          {{ cat.label }}
        </button>
      </div>

      <div class="product-grid">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>

      <div v-if="loading" class="loading-row">
        <div class="skeleton" /><div class="skeleton" />
      </div>

      <div ref="sentinel" />

      <p v-if="!hasMore && products.length" class="end-msg">Todos os produtos carregados</p>
      <p v-if="!loading && !products.length" class="end-msg">Nenhum produto encontrado</p>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 14px; }

.banner {
  background: var(--pry-dark);
  color: var(--pry);
  border-radius: var(--radius-lg);
  padding: 16px;
}
.banner__tipo { font-size: 11px; font-weight: 700; color: var(--pry-accent); text-transform: uppercase; letter-spacing: 0.5px; }
.banner__titulo { font-size: 16px; font-weight: 700; margin-top: 4px; }
.banner__msg    { font-size: 13px; opacity: 0.85; margin-top: 4px; }

.categorias { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
.categorias::-webkit-scrollbar { display: none; }

.cat-chip {
  flex-shrink: 0;
  padding: 6px 14px;
  border-radius: 99px;
  border: 1.5px solid var(--pry-border);
  background: #fff;
  color: var(--pry-mid);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.cat-chip.active { background: var(--pry-dark); color: var(--pry); border-color: var(--pry-dark); }

.product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.loading-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.skeleton {
  height: 220px;
  border-radius: var(--radius-lg);
  background: linear-gradient(90deg, var(--pry-light) 25%, var(--pry-border) 50%, var(--pry-light) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
}
@keyframes shimmer { to { background-position: -200% 0; } }

.end-msg { text-align: center; font-size: 13px; color: var(--pry-muted); padding: 8px 0; }
</style>
