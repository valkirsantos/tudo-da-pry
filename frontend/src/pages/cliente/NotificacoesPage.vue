<script setup>
import { onMounted } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'

const notif = useNotificationsStore()

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => notif.fetch())
</script>

<template>
  <div class="page">
    <PryTopBar title="Notificações" />

    <main class="page__content">
      <p v-if="notif.loading" class="loading-msg">Carregando...</p>

      <PryCard
        v-for="n in notif.items"
        :key="n.id"
        padding="14px"
        class="notif-card"
        :class="{ 'notif-card--unread': !n.lida }"
        @click="notif.markRead(n.id)"
      >
        <div class="notif-card__dot" v-if="!n.lida" />
        <p class="notif-card__titulo">{{ n.titulo }}</p>
        <p class="notif-card__msg">{{ n.mensagem }}</p>
        <p class="notif-card__date">{{ formatDate(n.created_at) }}</p>
      </PryCard>

      <div v-if="!notif.loading && !notif.items.length" class="empty">
        <p class="empty__icon">🔔</p>
        <p class="empty__msg">Nenhuma notificação</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 10px; }

.loading-msg { text-align: center; color: var(--pry-muted); padding: 24px 0; font-size: 14px; }

.notif-card { position: relative; cursor: pointer; }
.notif-card--unread { border-left: 3px solid var(--pry-accent); }

.notif-card__dot {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--pry-accent);
}

.notif-card__titulo { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
.notif-card__msg    { font-size: 13px; color: var(--pry-mid); line-height: 1.4; }
.notif-card__date   { font-size: 11px; color: var(--pry-muted); margin-top: 8px; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 14px; color: var(--pry-muted); }
</style>
