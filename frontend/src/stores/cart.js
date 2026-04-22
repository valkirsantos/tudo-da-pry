import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
  const items = ref(JSON.parse(localStorage.getItem('cart') || '[]'))

  const total   = computed(() => items.value.reduce((s, i) => s + i.preco * i.quantidade, 0))
  const count   = computed(() => items.value.reduce((s, i) => s + i.quantidade, 0))
  const isEmpty = computed(() => items.value.length === 0)

  function add(product, quantidade = 1) {
    const existing = items.value.find((i) => i.id === product.id)
    if (existing) existing.quantidade += quantidade
    else items.value.push({ ...product, quantidade })
    _save()
  }

  function remove(productId) {
    items.value = items.value.filter((i) => i.id !== productId)
    _save()
  }

  function updateQty(productId, quantidade) {
    const item = items.value.find((i) => i.id === productId)
    if (item) {
      if (quantidade <= 0) remove(productId)
      else { item.quantidade = quantidade; _save() }
    }
  }

  function clear() {
    items.value = []
    _save()
  }

  function _save() {
    localStorage.setItem('cart', JSON.stringify(items.value))
  }

  return { items, total, count, isEmpty, add, remove, updateQty, clear }
})
