import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  const items   = ref([])
  const unread  = ref(0)
  const loading = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const { data } = await api.get('/notifications')
      items.value  = data.data
      unread.value = items.value.filter((n) => !n.lida).length
    } finally {
      loading.value = false
    }
  }

  async function markRead(id) {
    await api.post('/notifications/read', { id })
    const n = items.value.find((n) => n.id === id)
    if (n) { n.lida = true; unread.value = Math.max(0, unread.value - 1) }
  }

  return { items, unread, loading, fetch, markRead }
})
