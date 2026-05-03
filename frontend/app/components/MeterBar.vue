<template>
    <div>
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs text-muted">{{ label }}</span>
            <div class="flex items-center gap-1.5">
                <span
                    v-if="detail"
                    class="text-[0.7rem] text-muted font-mono"
                    >{{ detail }}</span
                >
                <span class="text-sm font-semibold" :style="{ color }"
                    >{{ value }}%</span
                >
            </div>
        </div>
        <div class="h-1.5 rounded-full bg-card2 overflow-hidden">
            <div
                class="h-full rounded-full transition-[width] duration-500 ease-in-out"
                :class="fillClass"
                :style="{ width: `${Math.min(value, 100)}%` }"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    const props = defineProps<{
        label: string
        value: number
        detail?: string
        warnAt?: number
        dangerAt?: number
    }>()

    const warnAt = computed(() => props.warnAt ?? 75)
    const dangerAt = computed(() => props.dangerAt ?? 90)

    const fillClass = computed(() => {
        if (props.value >= dangerAt.value) return 'bg-danger'
        if (props.value >= warnAt.value) return 'bg-warning'
        return 'bg-success'
    })

    const color = computed(() => {
        if (props.value >= dangerAt.value) return 'var(--color-danger)'
        if (props.value >= warnAt.value) return 'var(--color-warning)'
        return 'var(--color-success)'
    })
</script>
