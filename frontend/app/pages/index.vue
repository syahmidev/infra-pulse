<template>
  <div>
    <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-fore">Dashboard</h1>
        <p class="text-xs text-muted mt-0.5">
          {{ activeServers }} active server{{ activeServers !== 1 ? 's' : '' }} · updated live
        </p>
      </div>
      <div class="flex gap-3">
        <div class="flex flex-col items-center bg-card border border-border rounded-xl px-4 py-2 min-w-[64px]">
          <span class="text-xl font-extrabold text-success">{{ statusCount('online') }}</span>
          <span class="text-[10px] uppercase tracking-widest text-muted">Online</span>
        </div>
        <div class="flex flex-col items-center bg-card border border-border rounded-xl px-4 py-2 min-w-[64px]">
          <span class="text-xl font-extrabold text-warning">{{ statusCount('warning') }}</span>
          <span class="text-[10px] uppercase tracking-widest text-muted">Warning</span>
        </div>
        <div class="flex flex-col items-center bg-card border border-border rounded-xl px-4 py-2 min-w-[64px]">
          <span class="text-xl font-extrabold text-danger">{{ statusCount('critical') }}</span>
          <span class="text-[10px] uppercase tracking-widest text-muted">Critical</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-16 text-muted text-sm">Loading servers…</div>
    <div v-else-if="error" class="text-center py-16 text-danger text-sm">{{ error }}</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-5 items-start">
      <div class="grid grid-cols-[repeat(auto-fill,minmax(320px,1fr))] gap-4">
        <ServerCard
          v-for="server in servers"
          :key="server.id"
          :server="server"
        />
      </div>
      <aside class="lg:sticky lg:top-[72px]">
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
