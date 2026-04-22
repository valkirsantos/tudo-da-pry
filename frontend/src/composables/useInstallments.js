import { computed } from 'vue'

export function useInstallments(total, numParcelas, diaVencimento) {
  const preview = computed(() => {
    const n   = numParcelas.value
    const dia = diaVencimento.value
    if (!n || !dia || !total.value) return []

    const base      = Math.floor((total.value / n) * 100) / 100
    const remainder = Math.round((total.value - base * n) * 100) / 100

    const now = new Date()
    return Array.from({ length: n }, (_, i) => {
      const valor = i === 0 ? Math.round((base + remainder) * 100) / 100 : base
      const d     = new Date(now.getFullYear(), now.getMonth() + i + 1, dia)
      if (d.getMonth() !== (now.getMonth() + i + 1) % 12) {
        d.setDate(0) // last day of month if day overflows
      }
      return {
        numero:          i + 1,
        valor,
        data_vencimento: d.toLocaleDateString('pt-BR'),
      }
    })
  })

  return { preview }
}
