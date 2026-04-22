<script setup>
defineProps({
  padding: { type: String, default: '16px' },
})
</script>

<template>
  <div class="pry-card" :style="{ padding }">
    <slot />
  </div>
</template>

<style scoped>
.pry-card {
  background: #fff;
  border: 1px solid var(--pry-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}
</style>
