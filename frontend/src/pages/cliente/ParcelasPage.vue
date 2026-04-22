<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useUpload } from '@/composables/useUpload'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryTag from '@/components/ui/PryTag.vue'
import PryButton from '@/components/ui/PryButton.vue'

const route = useRoute()
const orderId = route.params.id

const installments = ref([])
const loading      = ref(false)
const uploading_id = ref(null)
const files        = ref({})

const { progress, uploading, error: uploadError, uploadProof } = useUpload()

const TAG_MAP = {
  pendente:            'pendente',
  aguardando_validacao: 'novo',
  pago:                'ok',
  atrasado:            'warn',
}
const LABEL_MAP = {
  pendente:            'Pendente',
  aguardando_validacao: 'Aguardando validação',
  pago:                'Pago',
  atrasado:            'Atrasado',
}

async function load() {
  loading.value = true
  try {
    const { data } = await api.get(`/orders/${orderId}/installments`)
    installments.value = data.data
  } finally {
    loading.value = false
  }
}

function onFileSelect(installmentId, e) {
  files.value = { ...files.value, [installmentId]: e.target.files?.[0] }
}

async function send(installment) {
  const file = files.value[installment.id]
  if (!file) return
  uploading_id.value = installment.id
  try {
    await uploadProof({ file, orderId, installmentId: installment.id })
    await load()
  } finally {
    uploading_id.value = null
  }
}

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR')
}

onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Parcelas" :show-back="true" @back="$router.back()" />

    <main class="page__content">
      <p v-if="loading" class="loading-msg">Carregando...</p>

      <PryCard
        v-for="inst in installments"
        :key="inst.id"
        padding="14px"
      >
        <div class="inst-header">
          <div>
            <p class="inst-num">{{ inst.numero_parcela }}ª parcela</p>
            <p class="inst-date">Vence em {{ formatDate(inst.data_vencimento) }}</p>
          </div>
          <div class="inst-right">
            <p class="inst-val">{{ formatBRL(inst.valor) }}</p>
            <PryTag :variant="TAG_MAP[inst.status]">{{ LABEL_MAP[inst.status] }}</PryTag>
          </div>
        </div>

        <template v-if="inst.status === 'pendente' || inst.status === 'atrasado'">
          <div class="upload-area">
            <input
              :id="`file-${inst.id}`"
              type="file"
              accept="image/*,application/pdf"
              hidden
              @change="onFileSelect(inst.id, $event)"
            />
            <label :for="`file-${inst.id}`" class="upload-label">
              {{ files[inst.id] ? files[inst.id].name : '📎 Selecionar comprovante' }}
            </label>

            <div v-if="uploading && uploading_id === inst.id" class="progress-wrap">
              <div class="progress-bar">
                <div class="progress-bar__fill" :style="{ width: progress + '%' }" />
              </div>
              <span class="progress-pct">{{ progress }}%</span>
            </div>

            <PryButton
              variant="outline"
              full
              :loading="uploading && uploading_id === inst.id"
              :disabled="!files[inst.id]"
              @click="send(inst)"
            >
              Enviar comprovante
            </PryButton>
          </div>
          <p v-if="uploadError && uploading_id === inst.id" class="err">{{ uploadError }}</p>
        </template>

        <p v-if="inst.status === 'aguardando_validacao'" class="waiting-msg">
          Comprovante enviado — aguardando validação da vendedora
        </p>
      </PryCard>

      <div v-if="!loading && !installments.length" class="empty">
        <p>Nenhuma parcela encontrada</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 10px; }

.loading-msg { text-align: center; color: var(--pry-muted); padding: 24px 0; font-size: 14px; }

.inst-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
.inst-num    { font-size: 14px; font-weight: 700; }
.inst-date   { font-size: 12px; color: var(--pry-muted); margin-top: 2px; }
.inst-right  { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
.inst-val    { font-size: 16px; font-weight: 800; color: var(--pry-accent); }

.upload-area { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
.upload-label {
  display: block;
  padding: 10px 14px;
  border: 1.5px dashed var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 13px;
  color: var(--pry-mid);
  cursor: pointer;
  text-align: center;
}

.progress-wrap { display: flex; align-items: center; gap: 8px; }
.progress-bar  { flex: 1; height: 6px; background: var(--pry-light); border-radius: 99px; overflow: hidden; }
.progress-bar__fill { height: 100%; background: var(--pry-accent); border-radius: 99px; transition: width 0.2s; }
.progress-pct  { font-size: 12px; color: var(--pry-muted); }

.waiting-msg { font-size: 12px; color: var(--pry-mid); font-style: italic; margin-top: 4px; }
.err         { font-size: 12px; color: #b91c1c; }

.empty { text-align: center; color: var(--pry-muted); font-size: 14px; padding: 32px 0; }
</style>
