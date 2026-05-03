<template>
  <div class="server-card" :class="`server-card--${server.status}`">
    <div class="card-header">
      <div class="card-title-group">
        <span class="card-name">{{ server.name }}</span>
        <span class="card-host">{{ server.ip_address }}</span>
      </div>
      <div class="card-badges">
        <span :class="['badge', envBadgeClass]">{{ server.environment }}</span>
        <span :class="['badge', `badge-${server.status}`]">{{ server.status }}</span>
      </div>
    </div>

    <template v-if="server.metric">
      <div class="metrics-grid">
        <MeterBar
          label="CPU"
          :value="server.metric.cpu_usage"
          :warnAt="75"
          :dangerAt="90"
        />
        <MeterBar
          label="Memory"
          :value="server.metric.memory_usage"
          :detail="memDetail"
          :warnAt="75"
          :dangerAt="90"
        />
        <MeterBar
          label="Disk"
          :value="server.metric.disk_usage"
          :detail="diskDetail"
          :warnAt="85"
          :dangerAt="95"
        />
      </div>

      <div class="card-footer">
        <div class="footer-stat">
          <span class="stat-label">Response</span>
          <span class="stat-value" :style="{ color: server.metric.response_time >= 1000 ? 'var(--warning)' : 'var(--success)' }">
            {{ server.metric.response_time }}ms
          </span>
        </div>
        <div class="footer-stat">
          <span class="stat-label">Req/s</span>
          <span class="stat-value">{{ server.metric.request_rate }}</span>
        </div>
        <div class="footer-stat">
          <span class="stat-label">Net In</span>
          <span class="stat-value">{{ fmtBytes(server.metric.network_in) }}</span>
        </div>
        <div class="footer-stat">
          <span class="stat-label">Net Out</span>
          <span class="stat-value">{{ fmtBytes(server.metric.network_out) }}</span>
        </div>
      </div>

      <MetricSparkline :history="server.history" />

      <div v-if="server.updatedAt" class="card-updated">
        updated {{ formatUpdated(server.updatedAt) }}
      </div>
    </template>

    <div v-else class="no-metric">
      <span>No metric data yet</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Server } from '~/composables/useServers'

const props = defineProps<{ server: Server }>()

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

<style scoped>
.server-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  transition: border-color 0.3s;
}
.server-card--warning  { border-color: rgba(245,158,11,0.4); }
.server-card--critical { border-color: rgba(239,68,68,0.5); box-shadow: 0 0 12px rgba(239,68,68,0.1); }
.server-card--offline  { opacity: 0.6; }

.card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}
.card-title-group { display: flex; flex-direction: column; gap: 0.2rem; }
.card-name  { font-size: 1rem; font-weight: 700; color: var(--text); }
.card-host  { font-size: 0.75rem; color: var(--text-muted); font-family: monospace; }
.card-badges { display: flex; flex-wrap: wrap; gap: 0.35rem; }

.metrics-grid { display: flex; flex-direction: column; gap: 0.75rem; }

.card-footer {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.5rem;
  border-top: 1px solid var(--border);
  padding-top: 0.75rem;
}
.footer-stat { display: flex; flex-direction: column; gap: 0.2rem; }

.card-updated {
  font-size: 0.65rem;
  color: var(--text-muted);
  text-align: right;
  margin-top: -0.25rem;
}

.no-metric { color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 1rem 0; }
</style>
