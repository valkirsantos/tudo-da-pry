<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
import PryCard from '@/components/ui/PryCard.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryInput from '@/components/ui/PryInput.vue'

const TIPOS = [
  { value: 'novo_produto', label: 'Novo produto', icon: '🛍' },
  { value: 'promocao',     label: 'Promoção',     icon: '🏷' },
  { value: 'aviso_geral',  label: 'Aviso geral',  icon: '📢' },
]

const PUBLICOS = [
  { value: 'todas',              label: 'Todas as clientes' },
  { value: 'ultimo_mes',         label: 'Clientes do último mês' },
  { value: 'parcelas_abertas',   label: 'Clientes com parcelas em aberto' },
  { value: 'sem_compra_30dias',  label: 'Sem compra há 30+ dias' },
]

const tipo        = ref('novo_produto')
const publico     = ref('todas')
const titulo      = ref('')
const mensagem    = ref('')
const loading     = ref(false)
const error       = ref('')
const success     = ref(false)
const historico   = ref([])
const histLoading = ref(false)

const tituloLen   = computed(() => titulo.value.length)
const mensagemLen = computed(() => mensagem.value.length)
const canEnviar   = computed(() => titulo.value.trim() && mensagem.value.trim())

async function enviar() {
  loading.value = true
  error.value   = ''
  success.value = false
  try {
    await api.post('/broadcasts', {
      tipo:         tipo.value,
      publico_alvo: publico.value,
      titulo:       titulo.value,
      mensagem:     mensagem.value,
    })
    success.value   = true
    titulo.value    = ''
    mensagem.value  = ''
    await loadHistorico()
    setTimeout(() => { success.value = false }, 3000)
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao enviar notificação'
  } finally {
    loading.value = false
  }
}

async function loadHistorico() {
  histLoading.value = true
  try {
    const { data } = await api.get('/broadcasts', { params: { per_page: 10 } })
    historico.value = data.data
  } finally {
    histLoading.value = false
  }
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const TIPO_ICON = Object.fromEntries(TIPOS.map(t => [t.value, t.icon]))
const TIPO_LABEL = Object.fromEntries(TIPOS.map(t => [t.value, t.label]))

onMounted(loadHistorico)
</script>

<template>
  <div class="page">
    <PryTopBar title="Notificar Clientes" />

    <main class="page__content">
      <!-- Tipo -->
      <PryCard padding="16px">
        <p class="section-title">Tipo de notificação</p>
        <div class="tipo-chips">
          <button
            v-for="t in TIPOS"
            :key="t.value"
            class="tipo-chip"
            :class="{ active: tipo === t.value }"
            @click="tipo = t.value"
          >
            <span>{{ t.icon }}</span>
            {{ t.label }}
          </button>
        </div>
      </PryCard>

      <!-- Público e conteúdo -->
      <PryCard padding="16px">
        <p class="section-title">Mensagem</p>

        <div class="field">
          <label class="field-label">Público-alvo</label>
          <select class="select-field" v-model="publico">
            <option v-for="p in PUBLICOS" :key="p.value" :value="p.value">{{ p.label }}</option>
          </select>
        </div>

        <div class="field">
          <label class="field-label">Título <span class="counter">{{ tituloLen }}/80</span></label>
          <input
            v-model="titulo"
            maxlength="80"
            class="input-field"
            placeholder="Título da notificação"
          />
        </div>

        <div class="field">
          <label class="field-label">Mensagem <span class="counter">{{ mensagemLen }}/300</span></label>
          <textarea
            v-model="mensagem"
            maxlength="300"
            class="textarea-field"
            placeholder="Escreva a mensagem..."
            rows="4"
          />
        </div>
      </PryCard>

      <!-- Preview -->
      <div v-if="titulo || mensagem" class="preview-wrap">
        <p class="preview-label">Preview no app da cliente</p>
        <div class="notif-preview">
          <div class="notif-preview__icon">{{ TIPO_ICON[tipo] }}</div>
          <div class="notif-preview__body">
            <p class="notif-preview__titulo">{{ titulo || 'Título da notificação' }}</p>
            <p class="notif-preview__msg">{{ mensagem || 'Mensagem aqui...' }}</p>
          </div>
        </div>
      </div>

      <div v-if="success" class="success-msg">✅ Notificação enviada com sucesso!</div>
      <p v-if="error" class="err">{{ error }}</p>

      <PryButton full :loading="loading" :disabled="!canEnviar" @click="enviar">
        Enviar notificação
      </PryButton>

      <!-- Histórico -->
      <div class="hist-section">
        <p class="section-title">Histórico de envios</p>
        <p v-if="histLoading" class="hint">Carregando...</p>
        <PryCard
          v-for="b in historico"
          :key="b.id"
          padding="12px"
          class="hist-card"
        >
          <div class="hist-card__top">
            <span class="hist-card__tipo">{{ TIPO_ICON[b.tipo] }} {{ TIPO_LABEL[b.tipo] }}</span>
            <span class="hist-card__count">{{ b.total_enviados }} envios</span>
          </div>
          <p class="hist-card__titulo">{{ b.titulo }}</p>
          <p class="hist-card__date">{{ formatDate(b.enviado_em) }}</p>
        </PryCard>
        <p v-if="!histLoading && !historico.length" class="hint">Nenhum envio ainda</p>
      </div>
    </main>

    <PryBottomNav />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 76px; display: flex; flex-direction: column; gap: 12px; }

.section-title { font-size: 14px; font-weight: 700; color: var(--pry-dark); margin-bottom: 12px; }

.tipo-chips { display: flex; gap: 8px; }
.tipo-chip {
  flex: 1;
  padding: 10px 6px;
  border-radius: var(--radius-md);
  border: 2px solid var(--pry-border);
  background: #fff;
  font-size: 12px;
  font-weight: 600;
  color: var(--pry-mid);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  transition: all 0.15s;
}
.tipo-chip.active { border-color: var(--pry-dark); background: var(--pry-dark); color: var(--pry); }

.field       { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--pry-mid); display: flex; justify-content: space-between; }
.counter     { font-size: 11px; color: var(--pry-muted); font-weight: 400; }

.select-field,
.input-field {
  padding: 11px 13px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  color: var(--pry-dark);
  background: #fff;
  outline: none;
  width: 100%;
}
.select-field:focus,
.input-field:focus { border-color: var(--pry-accent); }

.textarea-field {
  padding: 11px 13px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 14px;
  color: var(--pry-dark);
  background: #fff;
  outline: none;
  width: 100%;
  resize: none;
  font-family: inherit;
  line-height: 1.5;
}
.textarea-field:focus { border-color: var(--pry-accent); }

/* Preview */
.preview-wrap  { display: flex; flex-direction: column; gap: 6px; }
.preview-label { font-size: 12px; color: var(--pry-muted); font-weight: 600; }
.notif-preview {
  background: var(--pry-dark);
  border-radius: var(--radius-lg);
  padding: 14px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
}
.notif-preview__icon  { font-size: 24px; flex-shrink: 0; }
.notif-preview__body  { flex: 1; }
.notif-preview__titulo { font-size: 14px; font-weight: 700; color: var(--pry); }
.notif-preview__msg    { font-size: 13px; color: var(--pry-mid); margin-top: 4px; line-height: 1.4; }

.success-msg { font-size: 14px; color: #166534; font-weight: 600; text-align: center; padding: 8px; background: #dcfce7; border-radius: var(--radius-md); }
.err  { font-size: 13px; color: #b91c1c; text-align: center; }
.hint { font-size: 13px; color: var(--pry-muted); text-align: center; padding: 8px 0; }

.hist-section { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
.hist-card    { display: flex; flex-direction: column; gap: 4px; }
.hist-card__top   { display: flex; justify-content: space-between; align-items: center; }
.hist-card__tipo  { font-size: 12px; font-weight: 600; color: var(--pry-accent); }
.hist-card__count { font-size: 12px; color: var(--pry-muted); }
.hist-card__titulo { font-size: 14px; font-weight: 700; }
.hist-card__date   { font-size: 11px; color: var(--pry-muted); }
</style>
