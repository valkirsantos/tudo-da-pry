import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // Public
  { path: '/',          redirect: '/catalogo' },
  { path: '/catalogo',  component: () => import('@/pages/cliente/CatalogoPage.vue'),  name: 'catalogo' },
  { path: '/produto/:id', component: () => import('@/pages/cliente/ProdutoPage.vue'), name: 'produto' },
  { path: '/login',     component: () => import('@/pages/cliente/LoginOtpPage.vue'),  name: 'login' },

  // Cliente (autenticado)
  {
    path: '/carrinho',
    component: () => import('@/pages/cliente/CarrinhoPage.vue'),
    name: 'carrinho',
    meta: { requiresAuth: true },
  },
  {
    path: '/pagamento',
    component: () => import('@/pages/cliente/PagamentoPage.vue'),
    name: 'pagamento',
    meta: { requiresAuth: true },
  },
  {
    path: '/comprovante/:orderId',
    component: () => import('@/pages/cliente/ComprovantePage.vue'),
    name: 'comprovante',
    meta: { requiresAuth: true },
  },
  {
    path: '/pedidos',
    component: () => import('@/pages/cliente/PedidosPage.vue'),
    name: 'pedidos',
    meta: { requiresAuth: true },
  },
  {
    path: '/pedidos/:id/parcelas',
    component: () => import('@/pages/cliente/ParcelasPage.vue'),
    name: 'parcelas',
    meta: { requiresAuth: true },
  },
  {
    path: '/notificacoes',
    component: () => import('@/pages/cliente/NotificacoesPage.vue'),
    name: 'notificacoes',
    meta: { requiresAuth: true },
  },

  // Vendedor
  {
    path: '/vendedor',
    redirect: '/vendedor/dashboard',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/dashboard',
    component: () => import('@/pages/vendedor/DashboardPage.vue'),
    name: 'vendedor-dashboard',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/nova-venda',
    component: () => import('@/pages/vendedor/NovaVendaPage.vue'),
    name: 'vendedor-nova-venda',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/pedidos',
    component: () => import('@/pages/vendedor/PedidosPage.vue'),
    name: 'vendedor-pedidos',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/comprovantes',
    component: () => import('@/pages/vendedor/ComprovantesPage.vue'),
    name: 'vendedor-comprovantes',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/notificar',
    component: () => import('@/pages/vendedor/NotificarPage.vue'),
    name: 'vendedor-notificar',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/clientes',
    component: () => import('@/pages/vendedor/ClientesPage.vue'),
    name: 'vendedor-clientes',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/produtos',
    component: () => import('@/pages/vendedor/ProdutosPage.vue'),
    name: 'vendedor-produtos',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/entregas',
    component: () => import('@/pages/vendedor/EntregasPage.vue'),
    name: 'vendedor-entregas',
    meta: { requiresAuth: true, role: 'vendedor' },
  },
  {
    path: '/vendedor/relatorios',
    component: () => import('@/pages/vendedor/RelatoriosPage.vue'),
    name: 'vendedor-relatorios',
    meta: { requiresAuth: true, role: 'vendedor' },
  },

  { path: '/:pathMatch(.*)*', redirect: '/catalogo' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
  const token = localStorage.getItem('auth_token')
  const user  = JSON.parse(localStorage.getItem('auth_user') || 'null')

  if (to.meta.requiresAuth && !token) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.role && user?.role !== to.meta.role) {
    return { name: 'catalogo' }
  }
})

export default router
