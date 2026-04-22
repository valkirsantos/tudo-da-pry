<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth  = useAuthStore()
const route = useRoute()

const clienteLinks = [
  { to: '/catalogo',      label: 'Catálogo',  icon: '🛍' },
  { to: '/carrinho',      label: 'Carrinho',   icon: '🛒' },
  { to: '/pedidos',       label: 'Pedidos',    icon: '📦' },
  { to: '/notificacoes',  label: 'Avisos',     icon: '🔔' },
]

const vendedorLinks = [
  { to: '/vendedor/dashboard',   label: 'Início',     icon: '🏠' },
  { to: '/vendedor/nova-venda',  label: 'Venda',      icon: '➕' },
  { to: '/vendedor/pedidos',     label: 'Pedidos',    icon: '📦' },
  { to: '/vendedor/comprovantes', label: 'Comprov.',  icon: '🧾' },
  { to: '/vendedor/clientes',    label: 'Clientes',   icon: '👥' },
]

const links = computed(() => auth.isVendedor ? vendedorLinks : clienteLinks)

function isActive(to) {
  return route.path.startsWith(to)
}
</script>

<template>
  <nav class="pry-bottom-nav">
    <RouterLink
      v-for="link in links"
      :key="link.to"
      :to="link.to"
      class="pry-bottom-nav__item"
      :class="{ active: isActive(link.to) }"
    >
      <span class="pry-bottom-nav__icon">{{ link.icon }}</span>
      <span class="pry-bottom-nav__label">{{ link.label }}</span>
    </RouterLink>
  </nav>
</template>

<style scoped>
.pry-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50;
  display: flex;
  height: 60px;
  background: var(--pry-dark);
  border-top: 1px solid var(--pry-mid);
  padding-bottom: env(safe-area-inset-bottom);
}

.pry-bottom-nav__item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  color: var(--pry-mid);
  font-size: 10px;
  font-weight: 500;
  transition: color 0.15s;
  text-decoration: none;
}
.pry-bottom-nav__item.active { color: var(--pry-accent); }

.pry-bottom-nav__icon { font-size: 20px; line-height: 1; }
.pry-bottom-nav__label { font-size: 10px; }
</style>
