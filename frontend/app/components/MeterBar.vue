<template>
  <div>
    <div class="stat-row">
      <span class="stat-label">{{ label }}</span>
      <span class="stat-value" :style="{ color: color }">{{ value }}%</span>
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
