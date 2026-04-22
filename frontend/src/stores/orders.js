import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useOrdersStore = defineStore('orders', () => {
  const list    = ref([])
  const current = ref(null)
  const loading = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const { data } = await api.get('/orders')
      list.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/orders/${id}`)
      current.value = data.data
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    const { data } = await api.post('/orders', payload)
    current.value = data.data
    return data.data
  }

  return { list, current, loading, fetch, fetchOne, create }
})
