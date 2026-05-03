<template>
  <div>
    <div class="stat-row">
      <span class="stat-label">{{ label }}</span>
      <div class="stat-right">
        <span v-if="detail" class="stat-detail">{{ detail }}</span>
        <span class="stat-value" :style="{ color: color }">{{ value }}%</span>
      </div>
    </div>
    <div class="meter-bar">
      <div class="meter-fill" :class="fillClass" :style="{ width: `${Math.min(value, 100)}%` }" />
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  label: string
  value: number
  detail?: string
  warnAt?:  number
  dangerAt?: number
}>()

const warnAt   = computed(() => props.warnAt   ?? 75)
const dangerAt = computed(() => props.dangerAt ?? 90)

const fillClass = computed(() => {
  if (props.value >= dangerAt.value) return 'danger'
  if (props.value >= warnAt.value)   return 'warning'
  return 'success'
})

const color = computed(() => {
  if (props.value >= dangerAt.value) return 'var(--danger)'
  if (props.value >= warnAt.value)   return 'var(--warning)'
  return 'var(--success)'
})
</script>

<style scoped>
.stat-right {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.stat-detail {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-family: monospace;
}
</style>
