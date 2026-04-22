<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUpload } from '@/composables/useUpload'
import api from '@/services/api'
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryButton from '@/components/ui/PryButton.vue'
import PryCard from '@/components/ui/PryCard.vue'

const router = useRouter()
const route  = useRoute()
const { progress, uploading, error, uploadProof } = useUpload()

const order    = ref(null)
const file     = ref(null)
const dragOver = ref(false)
const done     = ref(false)

const orderId = route.params.orderId

async function loadOrder() {
  try {
    const { data } = await api.get(`/orders/${orderId}`)
    order.value = data.data
  } catch { /* handled silently */ }
}

function onFileSelect(e) {
  const f = e.target.files?.[0] || e.dataTransfer?.files?.[0]
  if (f) file.value = f
}

function onDrop(e) {
  dragOver.value = false
  onFileSelect(e)
}

async function upload() {
  if (!file.value) return
  try {
    await uploadProof({ file: file.value, orderId })
    done.value = true
    setTimeout(() => router.push('/pedidos'), 1500)
  } catch { /* error shown via composable */ }
}

const totalFormatted = (v) =>
  Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

const METODO_LABEL = { pix: 'Pix', dinheiro: 'Dinheiro', parcelado: 'Parcelado' }

onMounted(loadOrder)
</script>

<template>
  <div class="page">
    <PryTopBar title="Comprovante" :show-back="true" @back="$router.back()" />

    <main class="page__content">
      <PryCard v-if="order" padding="16px">
        <div class="order-info">
          <div>
            <p class="order-info__label">Pedido #{{ order.id }}</p>
            <p class="order-info__meta">{{ METODO_LABEL[order.tipo_pagamento] }}</p>
          </div>
          <p class="order-info__total">{{ totalFormatted(order.total) }}</p>
        </div>
      </PryCard>

      <div
        v-if="!done"
        class="drop-zone"
        :class="{ 'drop-zone--over': dragOver, 'drop-zone--selected': file }"
        @dragover.prevent="dragOver = true"
        @dragleave="dragOver = false"
        @drop.prevent="onDrop"
        @click="$refs.fileInput.click()"
      >
        <input ref="fileInput" type="file" accept="image/*,application/pdf" hidden @change="onFileSelect" />
        <span class="drop-zone__icon">{{ file ? '📄' : '📤' }}</span>
        <p class="drop-zone__label">
          {{ file ? file.name : 'Toque para selecionar ou arraste o comprovante' }}
        </p>
        <p class="drop-zone__hint">JPG, PNG ou PDF até 5MB</p>
      </div>

      <div v-if="uploading" class="progress-wrap">
        <div class="progress-bar">
          <div class="progress-bar__fill" :style="{ width: progress + '%' }" />
        </div>
        <p class="progress-label">{{ progress }}%</p>
      </div>

      <div v-if="done" class="success">
        <span class="success__icon">✅</span>
        <p class="success__msg">Comprovante enviado! Redirecionando...</p>
      </div>

      <p v-if="error" class="err">{{ error }}</p>

      <PryButton
        v-if="!done"
        full
        :loading="uploading"
        :disabled="!file"
        @click="upload"
      >
        Enviar comprovante
      </PryButton>
    </main>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 12px 16px 32px; display: flex; flex-direction: column; gap: 16px; }

.order-info { display: flex; justify-content: space-between; align-items: center; }
.order-info__label { font-size: 15px; font-weight: 700; }
.order-info__meta  { font-size: 13px; color: var(--pry-muted); margin-top: 2px; }
.order-info__total { font-size: 20px; font-weight: 800; color: var(--pry-accent); }

.drop-zone {
  border: 2px dashed var(--pry-border);
  border-radius: var(--radius-lg);
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.15s;
  background: var(--pry-light);
}
.drop-zone--over    { border-color: var(--pry-accent); background: #fef3e2; }
.drop-zone--selected { border-color: var(--pry-dark); border-style: solid; }
.drop-zone__icon    { font-size: 40px; }
.drop-zone__label   { font-size: 14px; font-weight: 600; color: var(--pry-dark); text-align: center; }
.drop-zone__hint    { font-size: 12px; color: var(--pry-muted); }

.progress-wrap { display: flex; flex-direction: column; gap: 6px; }
.progress-bar  { height: 8px; background: var(--pry-light); border-radius: 99px; overflow: hidden; }
.progress-bar__fill { height: 100%; background: var(--pry-accent); border-radius: 99px; transition: width 0.2s; }
.progress-label { font-size: 12px; color: var(--pry-muted); text-align: right; }

.success { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 24px 0; }
.success__icon { font-size: 48px; }
.success__msg  { font-size: 15px; font-weight: 600; color: var(--pry-dark); }

.err { font-size: 13px; color: #b91c1c; text-align: center; }
</style>
