<script setup>
defineProps({
  steps:  { type: Array, required: true }, // [{ label: String }]
  active: { type: Number, default: 0 },    // index of active step (0-based)
})
</script>

<template>
  <div class="pry-timeline">
    <div
      v-for="(step, i) in steps"
      :key="i"
      class="pry-timeline__step"
      :class="{
        'pry-timeline__step--done':   i < active,
        'pry-timeline__step--active': i === active,
      }"
    >
      <div class="pry-timeline__dot">
        <span v-if="i < active">✓</span>
        <span v-else>{{ i + 1 }}</span>
      </div>
      <div class="pry-timeline__connector" v-if="i < steps.length - 1" />
      <span class="pry-timeline__label">{{ step.label }}</span>
    </div>
  </div>
</template>

<style scoped>
.pry-timeline {
  display: flex;
  align-items: flex-start;
  padding: 12px 0;
  overflow-x: auto;
}

.pry-timeline__step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  min-width: 60px;
}

.pry-timeline__dot {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid var(--pry-border);
  background: #fff;
  color: var(--pry-muted);
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.pry-timeline__step--done .pry-timeline__dot {
  background: var(--pry-accent);
  border-color: var(--pry-accent);
  color: #fff;
}

.pry-timeline__step--active .pry-timeline__dot {
  background: var(--pry-dark);
  border-color: var(--pry-dark);
  color: var(--pry);
}

.pry-timeline__connector {
  position: absolute;
  top: 14px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: var(--pry-border);
  z-index: 0;
}
.pry-timeline__step--done .pry-timeline__connector {
  background: var(--pry-accent);
}

.pry-timeline__label {
  margin-top: 6px;
  font-size: 11px;
  color: var(--pry-muted);
  text-align: center;
  line-height: 1.3;
}
.pry-timeline__step--active .pry-timeline__label { color: var(--pry-dark); font-weight: 600; }
.pry-timeline__step--done  .pry-timeline__label  { color: var(--pry-accent); }
</style>
