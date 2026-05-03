<template>
  <div :class="['bg-card border rounded-xl p-5 flex flex-col gap-4 transition-[border-color,box-shadow] duration-300', cardClass]">
    <div class="flex items-start justify-between gap-2">
      <div class="flex flex-col gap-0.5">
        <span class="text-base font-bold text-fore">{{ server.name }}</span>
        <span class="text-xs text-muted font-mono">{{ server.ip_address }}</span>
      </div>
      <div class="flex flex-wrap gap-1.5">
        <span :class="['badge', envBadgeClass]">{{ server.environment }}</span>
        <span :class="['badge', `badge-${server.status}`]">{{ server.status }}</span>
      </div>
    </div>

    <template v-if="server.metric">
      <div class="flex flex-col gap-3">
        <MeterBar label="CPU"    :value="server.metric.cpu_usage"    :warnAt="75" :dangerAt="90" />
        <MeterBar label="Memory" :value="server.metric.memory_usage" :detail="memDetail"  :warnAt="75" :dangerAt="90" />
        <MeterBar label="Disk"   :value="server.metric.disk_usage"   :detail="diskDetail" :warnAt="85" :dangerAt="95" />
      </div>

      <div class="grid grid-cols-4 gap-2 border-t border-border pt-3">
        <div class="flex flex-col gap-0.5">
          <span class="text-[0.65rem] text-muted uppercase tracking-wide">Response</span>
          <span
            class="text-sm font-semibold"
            :style="{ color: server.metric.response_time >= 1000 ? 'var(--color-warning)' : 'var(--color-success)' }"
          >{{ server.metric.response_time }}ms</span>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[0.65rem] text-muted uppercase tracking-wide">Req/s</span>
          <span class="text-sm font-semibold text-fore">{{ server.metric.request_rate }}</span>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[0.65rem] text-muted uppercase tracking-wide">Net In</span>
          <span class="text-sm font-semibold text-fore">{{ fmtBytes(server.metric.network_in) }}</span>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[0.65rem] text-muted uppercase tracking-wide">Net Out</span>
          <span class="text-sm font-semibold text-fore">{{ fmtBytes(server.metric.network_out) }}</span>
        </div>
      </div>

      <MetricSparkline :history="server.history" />

      <div v-if="server.updatedAt" class="text-[0.65rem] text-muted text-right -mt-1">
        updated {{ formatUpdated(server.updatedAt) }}
      </div>
    </template>

    <div v-else class="text-muted text-sm text-center py-4">
      No metric data yet
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Server } from '~/composables/useServers'

const props = defineProps<{ server: Server }>()

const cardClass = computed(() => {
  switch (props.server.status) {
    case 'warning':  return 'border-warning/40'
    case 'critical': return 'border-danger/50 shadow-[0_0_12px_rgba(239,68,68,0.1)]'
    case 'offline':  return 'border-border opacity-60'
    default:         return 'border-border'
  }
})

const envBadgeClass = computed(() => {
  const map: Record<string, string> = {
    production:  'badge-prod',
    staging:     'badge-staging',
    development: 'badge-dev',
  }
  return map[props.server.environment] ?? 'badge-offline'
})

function fmtBytes(bytes: number): string {
  if (bytes >= 1_048_576) return `${(bytes / 1_048_576).toFixed(1)} MB/s`
  if (bytes >= 1_024)    return `${(bytes / 1_024).toFixed(0)} KB/s`
  return `${Math.round(bytes)} B/s`
}

function fmtGB(bytes: number): string {
  return (bytes / (1024 ** 3)).toFixed(1)
}

const memDetail = computed(() => {
  const m = props.server.metric
  if (!m) return undefined
  return `${fmtGB(m.memory_used)} / ${fmtGB(m.memory_total)} GB`
})

const diskDetail = computed(() => {
  const m = props.server.metric
  if (!m) return undefined
  const usedGB  = m.disk_used / (1024 ** 3)
  const totalGB = m.disk_total / (1024 ** 3)
  if (totalGB >= 1000) return `${(usedGB / 1000).toFixed(1)} / ${(totalGB / 1000).toFixed(1)} TB`
  return `${usedGB.toFixed(0)} / ${totalGB.toFixed(0)} GB`
})

function formatUpdated(ts: number): string {
  return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}
</script>
