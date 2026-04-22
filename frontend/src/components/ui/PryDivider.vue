<script setup>
defineProps({ label: { type: String, default: '' } })
</script>

<template>
  <div class="pry-divider">
    <span v-if="label" class="pry-divider__label">{{ label }}</span>
  </div>
</template>

<style scoped>
.pry-divider {
  height: 1px;
  background: var(--pry-border);
  position: relative;
  margin: 16px 0;
}
.pry-divider__label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: var(--pry-surface);
  padding: 0 10px;
  font-size: 12px;
  color: var(--pry-muted);
}
</style>
