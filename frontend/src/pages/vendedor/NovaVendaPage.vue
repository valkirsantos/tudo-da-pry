<script setup>
import PryTopBar from '@/components/ui/PryTopBar.vue'
import PryBottomNav from '@/components/ui/PryBottomNav.vue'
</script>
<template>
  <div class="page">
    <PryTopBar />
    <main class="page__content"><!-- TODO --></main>
    <PryBottomNav />
  </div>
</template>
<style scoped>
.page { display: flex; flex-direction: column; min-height: 100dvh; }
.page__content { flex: 1; padding: 16px; padding-bottom: 76px; }
</style>
