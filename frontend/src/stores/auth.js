import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const isVendedor      = computed(() => user.value?.role === 'vendedor')
  const isCliente       = computed(() => user.value?.role === 'cliente')

  async function sendOtp(celular) {
    const { data } = await api.post('/auth/send-otp', { celular })
    return data
  }

  async function verifyOtp(celular, codigo) {
    const { data } = await api.post('/auth/verify-otp', { celular, codigo })
    _persist(data.data.token, data.data.user)
    return data.data
  }

  function logout() {
    user.value  = null
    token.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }

  function _persist(newToken, newUser) {
    token.value = newToken
    user.value  = newUser
    localStorage.setItem('auth_token', newToken)
    localStorage.setItem('auth_user', JSON.stringify(newUser))
  }

  return { user, token, isAuthenticated, isVendedor, isCliente, sendOtp, verifyOtp, logout }
})
