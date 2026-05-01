<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">{{ activeServers }} active server{{ activeServers !== 1 ? 's' : '' }} · updated live</p>
      </div>
      <div class="header-stats">
        <div class="hstat hstat--online">
          <span class="hstat-num">{{ statusCount('online') }}</span>
          <span class="hstat-label">Online</span>
        </div>
        <div class="hstat hstat--warning">
          <span class="hstat-num">{{ statusCount('warning') }}</span>
          <span class="hstat-label">Warning</span>
        </div>
        <div class="hstat hstat--critical">
          <span class="hstat-num">{{ statusCount('critical') }}</span>
          <span class="hstat-label">Critical</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-state">Loading servers…</div>
    <div v-else-if="error" class="error-state">{{ error }}</div>

    <div v-else class="dashboard-layout">
      <div class="servers-grid">
        <ServerCard
          v-for="server in servers"
          :key="server.id"
          :server="server"
        />
      </div>

      <aside class="sidebar">
        <AlertFeed :alerts="alerts" />
      </aside>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Alert } from '~/composables/useAlerts'

definePageMeta({ middleware: 'auth' })

const { servers, loading, error, fetchServers, updateServerMetric, updateServerStatus } = useServers()
const { initEcho, subscribeToServer, subscribeToAlerts } = useEcho()
const { fetchUnread } = useAlerts()

const alerts = ref<Alert[]>([])

const activeServers = computed(() => servers.value.filter(s => s.is_active).length)
function statusCount(status: string) {
  return servers.value.filter(s => s.status === status).length
}

function prependAlert(alert: Alert) {
  alerts.value = [alert, ...alerts.value].slice(0, 30)
}

onMounted(async () => {
  await fetchServers()
  alerts.value = await fetchUnread()

  initEcho()

  for (const server of servers.value) {
    subscribeToServer(
      server.id,
      (data: any) => {
        updateServerMetric(server.id, {
          cpu_usage:     data.cpu_usage,
          memory_usage:  data.memory_usage,
          memory_used:   data.memory_used,
          memory_total:  data.memory_total,
          disk_usage:    data.disk_usage,
          disk_used:     data.disk_used,
          disk_total:    data.disk_total,
          network_in:    data.network_in,
          network_out:   data.network_out,
          request_rate:  data.request_rate,
          response_time: data.response_time,
        })
        updateServerStatus(server.id, data.status)
      },
      (data: any) => prependAlert({
        id:           data.id,
        severity:     data.severity,
        message:      data.message,
        type:         data.type,
        triggered_at: data.triggered_at,
        is_read:      false,
        server:       { id: data.server_id, name: data.server_name },
      }),
    )
  }

  subscribeToAlerts((data: any) => prependAlert({
    id:           data.id,
    severity:     data.severity,
    message:      data.message,
    type:         data.type,
    triggered_at: data.triggered_at,
    is_read:      false,
    server:       { id: data.server_id, name: data.server_name },
  }))
})

onUnmounted(() => {
  for (const server of servers.value) {
    useEcho().leaveServer(server.id)
  }
})
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}
.page-title { font-size: 1.5rem; font-weight: 800; color: var(--text); }
.page-sub   { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; }

.header-stats { display: flex; gap: 0.75rem; }
.hstat {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.6rem;
  padding: 0.5rem 1rem;
  min-width: 64px;
}
.hstat-num   { font-size: 1.25rem; font-weight: 800; }
.hstat-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
.hstat--online   .hstat-num { color: var(--success); }
.hstat--warning  .hstat-num { color: var(--warning); }
.hstat--critical .hstat-num { color: var(--danger); }

.loading-state, .error-state {
  text-align: center;
  padding: 4rem;
  color: var(--text-muted);
  font-size: 0.9rem;
}
.error-state { color: var(--danger); }

.dashboard-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.25rem;
  align-items: start;
}

.servers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1rem;
}

.sidebar { position: sticky; top: 72px; }

@media (max-width: 900px) {
  .dashboard-layout {
    grid-template-columns: 1fr;
  }
  .sidebar { position: static; }
}
</style>
