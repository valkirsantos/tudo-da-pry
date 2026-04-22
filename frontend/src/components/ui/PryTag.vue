<script setup>
// variant: novo | promo | ok | warn | pendente
defineProps({
  variant: { type: String, default: 'pendente' },
})
</script>

<template>
  <span class="pry-tag" :class="`pry-tag--${variant}`">
    <slot />
  </span>
</template>

<style scoped>
.pry-tag {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 99px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.6;
}
.pry-tag--novo     { background: #dbeafe; color: #1e40af; }
.pry-tag--promo    { background: #fef3c7; color: #92400e; }
.pry-tag--ok       { background: #dcfce7; color: #166534; }
.pry-tag--warn     { background: #fee2e2; color: #991b1b; }
.pry-tag--pendente { background: var(--pry-light); color: var(--pry-mid); }
</style>
