<script setup>
import { ref, computed, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import PryButton from '@/components/ui/PryButton.vue'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()

const celular      = ref('')
const otpDigits    = ref(['', '', '', '', '', ''])
const step         = ref('phone') // phone | otp
const loading      = ref(false)
const error        = ref('')
const countdown    = ref(0)
let countdownTimer = null
const digitRefs    = ref([])

const celularFormatted = computed(() => {
  const d = celular.value.replace(/\D/g, '')
  if (d.length <= 2)  return `(${d}`
  if (d.length <= 7)  return `(${d.slice(0,2)}) ${d.slice(2)}`
  if (d.length <= 11) return `(${d.slice(0,2)}) ${d.slice(2,7)}-${d.slice(7)}`
  return `(${d.slice(0,2)}) ${d.slice(2,7)}-${d.slice(7,11)}`
})

function onCelularInput(e) {
  celular.value = e.target.value.replace(/\D/g, '').slice(0, 11)
}

async function sendOtp() {
  error.value   = ''
  loading.value = true
  try {
    await auth.sendOtp(celular.value)
    step.value = 'otp'
    startCountdown()
    await nextTick()
    digitRefs.value[0]?.focus()
  } catch (e) {
    error.value = e.response?.data?.message || 'Erro ao enviar código'
  } finally {
    loading.value = false
  }
}

function startCountdown() {
  countdown.value = 60
  clearInterval(countdownTimer)
  countdownTimer = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) clearInterval(countdownTimer)
  }, 1000)
}

function onDigitInput(i, e) {
  const val = e.target.value.replace(/\D/g, '')
  if (!val) { otpDigits.value[i] = ''; return }
  otpDigits.value[i] = val[val.length - 1]
  if (i < 5) digitRefs.value[i + 1]?.focus()
  if (otpDigits.value.every(d => d)) verifyOtp()
}

function onDigitKeydown(i, e) {
  if (e.key === 'Backspace' && !otpDigits.value[i] && i > 0) {
    digitRefs.value[i - 1]?.focus()
  }
}

async function verifyOtp() {
  const codigo = otpDigits.value.join('')
  if (codigo.length !== 6) return
  error.value   = ''
  loading.value = true
  try {
    const user = await auth.verifyOtp(celular.value, codigo)
    const defaultRedirect = user.role === 'vendedor' ? '/vendedor/dashboard' : '/catalogo'
    const redirect = route.query.redirect || defaultRedirect
    router.push(redirect)
  } catch (e) {
    error.value        = e.response?.data?.message || 'Código inválido'
    otpDigits.value    = ['', '', '', '', '', '']
    await nextTick()
    digitRefs.value[0]?.focus()
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-page__header">
      <h1 class="login-page__logo">Tudo da Pry</h1>
      <p class="login-page__sub">Faça login para continuar</p>
    </div>

    <div class="login-page__card">
      <!-- Etapa 1: celular -->
      <template v-if="step === 'phone'">
        <p class="field-label">Seu celular</p>
        <input
          class="pry-input-field"
          type="tel"
          placeholder="(11) 99999-9999"
          :value="celularFormatted"
          inputmode="numeric"
          @input="onCelularInput"
          @keydown.enter="sendOtp"
        />
        <p v-if="error" class="err">{{ error }}</p>
        <PryButton
          full
          :loading="loading"
          :disabled="celular.replace(/\D/g,'').length < 10"
          @click="sendOtp"
        >
          Enviar código
        </PryButton>
      </template>

      <!-- Etapa 2: OTP -->
      <template v-else>
        <p class="field-label">
          Código enviado para {{ celularFormatted }}
        </p>
        <div class="otp-row">
          <input
            v-for="(_, i) in otpDigits"
            :key="i"
            :ref="el => digitRefs[i] = el"
            class="otp-digit"
            type="text"
            inputmode="numeric"
            maxlength="1"
            :value="otpDigits[i]"
            @input="onDigitInput(i, $event)"
            @keydown="onDigitKeydown(i, $event)"
          />
        </div>
        <p v-if="error" class="err">{{ error }}</p>
        <PryButton full :loading="loading" @click="verifyOtp">
          Confirmar
        </PryButton>
        <button
          class="resend-btn"
          :disabled="countdown > 0"
          @click="sendOtp"
        >
          {{ countdown > 0 ? `Reenviar em ${countdown}s` : 'Reenviar código' }}
        </button>
        <button class="back-btn" @click="step = 'phone'">← Trocar número</button>
      </template>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100dvh;
  background: var(--pry-dark);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  gap: 32px;
}

.login-page__header { text-align: center; }
.login-page__logo {
  font-size: 32px;
  font-weight: 800;
  color: var(--pry);
  letter-spacing: 0.5px;
}
.login-page__sub { font-size: 14px; color: var(--pry-mid); margin-top: 6px; }

.login-page__card {
  width: 100%;
  max-width: 360px;
  background: #fff;
  border-radius: var(--radius-lg);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field-label { font-size: 13px; font-weight: 600; color: var(--pry-mid); }

.pry-input-field {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 16px;
  color: var(--pry-dark);
  outline: none;
}
.pry-input-field:focus { border-color: var(--pry-accent); }

.otp-row { display: flex; gap: 8px; justify-content: center; }
.otp-digit {
  width: 44px;
  height: 52px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  text-align: center;
  font-size: 22px;
  font-weight: 700;
  color: var(--pry-dark);
  outline: none;
}
.otp-digit:focus { border-color: var(--pry-accent); }

.err { font-size: 13px; color: #b91c1c; }

.resend-btn {
  background: none;
  border: none;
  font-size: 13px;
  color: var(--pry-accent);
  cursor: pointer;
  text-align: center;
}
.resend-btn:disabled { color: var(--pry-muted); cursor: default; }

.back-btn {
  background: none;
  border: none;
  font-size: 13px;
  color: var(--pry-muted);
  cursor: pointer;
  text-align: center;
}
</style>
