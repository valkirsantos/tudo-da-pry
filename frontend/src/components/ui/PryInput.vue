<script setup>
defineProps({
  label:       { type: String, default: '' },
  modelValue:  { default: '' },
  type:        { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error:       { type: String, default: '' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="pry-input-wrap">
    <label v-if="label" class="pry-input__label">{{ label }}</label>
    <input
      class="pry-input"
      :class="{ 'pry-input--error': error }"
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="pry-input__error">{{ error }}</p>
  </div>
</template>

<style scoped>
.pry-input-wrap { display: flex; flex-direction: column; gap: 6px; }

.pry-input__label {
  font-size: 13px;
  font-weight: 600;
  color: var(--pry-mid);
}

.pry-input {
  padding: 12px 14px;
  border: 1.5px solid var(--pry-border);
  border-radius: var(--radius-md);
  font-size: 15px;
  color: var(--pry-dark);
  background: #fff;
  outline: none;
  transition: border-color 0.15s;
}
.pry-input:focus { border-color: var(--pry-accent); }
.pry-input--error { border-color: #b91c1c; }

.pry-input__error { font-size: 12px; color: #b91c1c; }
</style>
