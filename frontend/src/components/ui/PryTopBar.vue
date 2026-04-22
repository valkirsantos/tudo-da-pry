<script setup>
defineProps({
  title: { type: String, default: 'Tudo da Pry' },
  showBack: { type: Boolean, default: false },
})
const emit = defineEmits(['back'])
</script>

<template>
  <header class="pry-topbar">
    <button v-if="showBack" class="pry-topbar__back" @click="emit('back')">
      ←
    </button>
    <span class="pry-topbar__title">{{ title }}</span>
    <div class="pry-topbar__actions">
      <slot />
    </div>
  </header>
</template>

<style scoped>
.pry-topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  height: 56px;
  padding: 0 16px;
  background: var(--pry-dark);
  color: var(--pry);
  gap: 12px;
}

.pry-topbar__back {
  background: none;
  border: none;
  color: var(--pry);
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  line-height: 1;
}

.pry-topbar__title {
  flex: 1;
  font-size: 17px;
  font-weight: 700;
  letter-spacing: 0.3px;
  color: var(--pry);
}

.pry-topbar__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--pry);
}
</style>
