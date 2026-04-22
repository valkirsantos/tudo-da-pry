<script setup>
import { ref, computed } from 'vue'
import { useInstallments } from '@/composables/useInstallments'

const props = defineProps({
  total: { type: Number, required: true },
})
const emit = defineEmits(['update:numParcelas', 'update:diaVencimento'])

const numParcelas   = ref(2)
const diaVencimento = ref(10)

const { preview } = useInstallments(
  computed(() => props.total),
  numParcelas,
  diaVencimento,
)

function onParcelas(v) {
  numParcelas.value = Number(v)
  emit('update:numParcelas', numParcelas.value)
}
function onDia(v) {
  diaVencimento.value = Number(v)
  emit('update:diaVencimento', diaVencimento.value)
}

const dias = [1, 5, 10, 15, 20, 25, 28]
</script>

<template>
  <div class="inst-picker">
    <div class="inst-picker__row">
      <div class="inst-picker__field">
        <label class="inst-picker__label">Nº de parcelas</label>
        <select class="inst-picker__select" :value="numParcelas" @change="onParcelas($event.target.value)">
          <option v-for="n in [2,3,4,5]" :key="n" :value="n">{{ n }}x</option>
        </select>
      </div>
      <div class="inst-picker__field">
        <label class="inst-picker__label">Dia do vencimento</label>
        <select class="inst-picker__select" :value="diaVencimento" @change="onDia($event.target.value)">
          <option v-for="d in dias" :key="d" :value="d">dia {{ d }}</option>
        </select>
      </div>
    </div>

    <table class="inst-picker__table">
      <thead>
        <tr>
          <th>Parcela</th>
          <th>Valor</th>
          <th>Vencimento</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in preview" :key="row.numero">
          <td>{{ row.numero }}ª</td>
          <td>{{ row.valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) }}</td>
          <td>{{ row.data_vencimento }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.inst-picker { display: flex; flex-direction: column; gap: 16px; }

.inst-picker__row { display: flex; gap: 12px; }
.inst-picker__field { flex: 1; display: flex; flex-direction: column; gap: 6px; }

.inst-picker__label { font-size: 13px; font-weight: 600; color: var(--pry-mid); }

.inst-picker__select {
  padding: 10px 12px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  color: var(--pry-dark);
  background: #fff;
  cursor: pointer;
}
.inst-picker__select:focus { outline: none; border-color: var(--pry-accent); }

.inst-picker__table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.inst-picker__table th,
.inst-picker__table td {
  padding: 8px 10px;
  text-align: left;
  border-bottom: 1px solid var(--pry-border);
}
.inst-picker__table th {
  color: var(--pry-muted);
  font-weight: 600;
  font-size: 12px;
}
.inst-picker__table td:nth-child(2) { color: var(--pry-accent); font-weight: 600; }
</style>
