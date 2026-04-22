<script setup>
defineProps({
  variant: { type: String, default: 'primary' }, // primary | outline | danger
  type:    { type: String, default: 'button' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  full:    { type: Boolean, default: false },
})
</script>

<template>
  <button
    :type="type"
    class="pry-btn"
    :class="[`pry-btn--${variant}`, { 'pry-btn--full': full, 'pry-btn--loading': loading }]"
    :disabled="disabled || loading"
  >
    <span v-if="loading" class="pry-btn__spinner" />
    <slot />
  </button>
</template>

<style scoped>
.pry-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: var(--radius-md);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  border: 2px solid transparent;
  transition: opacity 0.15s, transform 0.1s;
}
.pry-btn:active { transform: scale(0.97); }
.pry-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pry-btn--full { width: 100%; }

.pry-btn--primary {
  background: var(--pry-dark);
  color: var(--pry);
}
.pry-btn--primary:hover:not(:disabled) { opacity: 0.88; }

.pry-btn--outline {
  background: transparent;
  border-color: var(--pry-dark);
  color: var(--pry-dark);
}
.pry-btn--outline:hover:not(:disabled) { background: var(--pry-light); }

.pry-btn--danger {
  background: #b91c1c;
  color: #fff;
}
.pry-btn--danger:hover:not(:disabled) { opacity: 0.88; }

.pry-btn__spinner {
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
