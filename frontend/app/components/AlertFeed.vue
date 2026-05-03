<template>
    <div
        class="bg-card border border-border rounded-xl p-4 flex flex-col gap-3 max-h-[520px] overflow-hidden"
    >
        <div class="flex items-center justify-between shrink-0">
            <span class="font-bold text-sm text-fore">Live Alerts</span>
            <span
                v-if="alerts.length"
                class="bg-danger text-white rounded-full text-[0.7rem] font-bold min-w-5 h-5 flex items-center justify-center px-1.5"
                >{{ alerts.length }}</span
            >
        </div>

        <div
            v-if="!alerts.length"
            class="text-muted text-xs text-center py-4 shrink-0"
        >
            All clear — no active alerts
        </div>

        <TransitionGroup
            name="alert"
            tag="div"
            class="flex flex-col gap-2 overflow-y-auto flex-1 min-h-0"
        >
            <div
                v-for="alert in alerts"
                :key="alert.id"
                :class="[
                    'bg-card2 rounded-lg px-3 py-2.5 flex flex-col gap-1.5 shrink-0 border-l-[3px]',
                    alertBorderClass(alert.severity),
                ]"
            >
                <div class="flex items-center gap-2">
                    <span :class="['badge', alertBadgeClass(alert.severity)]">{{
                        alert.severity
                    }}</span>
                    <span class="text-xs font-semibold text-fore flex-1">{{
                        alert.server?.name ?? 'Unknown'
                    }}</span>
                    <span class="text-[0.7rem] text-muted">{{
                        formatTime(alert.triggered_at)
                    }}</span>
                </div>
                <p class="text-xs text-muted">{{ alert.message }}</p>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup lang="ts">
    import type { Alert } from '~/composables/useAlerts'
    defineProps<{ alerts: Alert[] }>()

    function formatTime(iso: string) {
        return new Date(iso).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        })
    }

    function alertBorderClass(severity: string): string {
        if (severity === 'critical') return 'border-l-danger'
        if (severity === 'warning') return 'border-l-warning'
        return 'border-l-info'
    }

    function alertBadgeClass(severity: string): string {
        if (severity === 'critical') return 'badge-critical'
        if (severity === 'warning') return 'badge-warning'
        return 'badge-info'
    }
</script>

<style scoped>
    .alert-enter-active {
        transition: all 0.3s ease;
    }
    .alert-enter-from {
        opacity: 0;
        transform: translateY(-8px);
    }
</style>
