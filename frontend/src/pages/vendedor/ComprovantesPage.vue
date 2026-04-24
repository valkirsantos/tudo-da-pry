<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryTag from '@/components/ui/PryTag.vue'
import PryButton from '@/components/ui/PryButton.vue'

const FILTROS = [
  { value: '',        label: 'Todos' },
  { value: 'avista',  label: 'À vista' },
  { value: 'parcela', label: 'Parcelas' },
]

const STATUS_TAG = {
  pendente:  'pendente',
  aprovado:  'ok',
  rejeitado: 'warn',
}

const STATUS_LABEL = {
  pendente:  'Pendente',
  aprovado:  'Aprovado',
  rejeitado: 'Rejeitado',
}

const proofs       = ref([])
const filtro       = ref('')
const loading      = ref(false)
const actionLoading = ref(null)

// Modal
const modal        = ref(null) // { proof, action: 'approve'|'reject' }
const motivo       = ref('')
const modalLoading = ref(false)
const modalError   = ref('')

async function load() {
  loading.value = true
  try {
    const params = {}
    if (filtro.value) params.tipo = filtro.value
    const { data } = await api.get('/payment-proofs', { params })
    proofs.value = data.data
  } finally {
    loading.value = false
  }
}

watch(filtro, load)

function openModal(proof, action) {
  modal.value    = { proof, action }
  motivo.value   = ''
  modalError.value = ''
}

function closeModal() {
  modal.value = null
}

async function submitAction() {
  if (!modal.value) return
  if (modal.value.action === 'reject' && !motivo.value.trim()) {
    modalError.value = 'Informe o motivo da rejeição'
    return
  }
  modalLoading.value = true
  modalError.value   = ''
  try {
    const payload = { action: modal.value.action }
    if (modal.value.action === 'reject') payload.motivo = motivo.value
    await api.put(`/payment-proofs/${modal.value.proof.id}`, payload)
    proofs.value = proofs.value.filter(p => p.id !== modal.value.proof.id)
    closeModal()
  } catch (e) {
    modalError.value = e.response?.data?.message || 'Erro ao processar'
  } finally {
    modalLoading.value = false
  }
}

function isImage(proof) {
  return /\.(jpg|jpeg|png|webp)$/i.test(proof.nome_arquivo)
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function formatBRL(v) {
  return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

onMounted(load)
</script>

<template>
  <div class="page">
    <PryTopBar title="Comprovantes" />

    <main class="page__content">
      <!-- Filtros -->
      <div class="filtros">
        <button
          v-for="f in FILTROS"
          :key="f.value"
          class="filtro-chip"
          :class="{ active: filtro === f.value }"
          @click="filtro = f.value"
        >
          {{ f.label }}
        </button>
      </div>

      <p v-if="loading" class="hint">Carregando...</p>

      <PryCard
        v-for="proof in proofs"
        :key="proof.id"
        padding="14px"
        class="proof-card"
      >
        <!-- Preview do arquivo -->
        <div class="proof-card__preview">
          <img
            v-if="isImage(proof) && proof.download_url"
            :src="proof.download_url"
            :alt="proof.nome_arquivo"
            class="proof-card__img"
          />
          <div v-else class="proof-card__pdf">
            📄 <span>{{ proof.nome_arquivo }}</span>
          </div>
        </div>

        <!-- Download -->
        <a
          v-if="proof.download_url"
          :href="proof.download_url"
          :download="proof.nome_arquivo"
          target="_blank"
          class="download-btn"
        >
          ⬇ Baixar arquivo
        </a>
        <p v-else class="no-file">Arquivo não disponível</p>

        <div class="proof-card__meta">
          <div>
            <p class="proof-card__title">
              Pedido #{{ proof.order_id }}
              {{ proof.installment_id ? `· Parcela ${proof.installment_id}` : '· À vista' }}
            </p>
            <p class="proof-card__date">{{ formatDate(proof.created_at) }}</p>
          </div>
          <PryTag :variant="STATUS_TAG[proof.status]">{{ STATUS_LABEL[proof.status] }}</PryTag>
        </div>

        <p v-if="proof.motivo_rejeicao" class="proof-card__motivo">
          Motivo: {{ proof.motivo_rejeicao }}
        </p>

        <div v-if="proof.status === 'pendente'" class="proof-card__actions">
          <PryButton
            variant="outline"
            full
            @click="openModal(proof, 'approve')"
          >
            ✓ Validar
          </PryButton>
          <PryButton
            variant="danger"
            full
            @click="openModal(proof, 'reject')"
          >
            ✕ Rejeitar
          </PryButton>
        </div>
      </PryCard>

      <div v-if="!loading && !proofs.length" class="empty">
        <p class="empty__icon">✅</p>
        <p class="empty__msg">Nenhum comprovante encontrado</p>
      </div>
    </main>

    <!-- Modal de confirmação -->
    <div v-if="modal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <p class="modal__title">
          {{ modal.action === 'approve' ? 'Confirmar validação?' : 'Rejeitar comprovante' }}
        </p>
        <p class="modal__sub">Pedido #{{ modal.proof.order_id }}</p>

        <textarea
          v-if="modal.action === 'reject'"
          v-model="motivo"
          class="modal__motivo"
          placeholder="Informe o motivo da rejeição..."
          rows="3"
        />

        <p v-if="modalError" class="modal__err">{{ modalError }}</p>

        <div class="modal__actions">
          <PryButton variant="outline" full @click="closeModal">Cancelar</PryButton>
          <PryButton
            :variant="modal.action === 'approve' ? 'primary' : 'danger'"
            full
            :loading="modalLoading"
            @click="submitAction"
          >
            {{ modal.action === 'approve' ? 'Validar' : 'Rejeitar' }}
          </PryButton>
        </div>
      </div>
    </div>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.filtros { display: flex; gap: 8px; }
.filtro-chip {
  padding: 6px 16px;
  border-radius: 99px;
  border: 1.5px solid var(--pry-border);
  background: #fff;
  color: var(--pry-mid);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.filtro-chip.active { background: var(--pry-dark); color: var(--pry); border-color: var(--pry-dark); }

.hint { font-size: 14px; color: var(--pry-muted); text-align: center; padding: 12px 0; }

.proof-card { display: flex; flex-direction: column; gap: 10px; }
.proof-card__preview {
  width: 100%;
  max-height: 160px;
  overflow: hidden;
  border-radius: var(--radius-md);
  background: var(--pry-light);
  display: flex;
  align-items: center;
  justify-content: center;
}
.proof-card__img { width: 100%; object-fit: cover; max-height: 160px; }
.proof-card__pdf { padding: 20px; font-size: 14px; color: var(--pry-mid); display: flex; align-items: center; gap: 8px; }

.proof-card__meta { display: flex; justify-content: space-between; align-items: flex-start; }
.proof-card__title  { font-size: 13px; font-weight: 700; }
.proof-card__date   { font-size: 12px; color: var(--pry-muted); margin-top: 2px; }
.proof-card__motivo { font-size: 12px; color: #b45309; }
.proof-card__actions { display: flex; gap: 8px; }

.download-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--pry-border);
  background: var(--pry-light);
  color: var(--pry-dark);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  width: 100%;
}
.download-btn:active { background: var(--pry-border); }
.no-file { font-size: 12px; color: var(--pry-muted); text-align: center; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 48px 0; }
.empty__icon { font-size: 48px; }
.empty__msg  { font-size: 14px; color: var(--pry-muted); }

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: flex-end;
  z-index: 100;
}
.modal {
  width: 100%;
  background: #fff;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  padding: 24px 20px 32px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.modal__title  { font-size: 17px; font-weight: 700; }
.modal__sub    { font-size: 13px; color: var(--pry-muted); }
.modal__motivo {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  resize: none;
  outline: none;
  font-family: inherit;
}
.modal__motivo:focus { border-color: var(--pry-accent); }
.modal__err  { font-size: 13px; color: #b91c1c; }
.modal__actions { display: flex; gap: 8px; }
</style>
