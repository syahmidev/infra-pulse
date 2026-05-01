export interface MetricPoint {
  t: number
  cpu: number
  mem: number
  disk: number
}

export interface ServerMetric {
  cpu_usage:     number
  memory_usage:  number
  memory_used:   number
  memory_total:  number
  disk_usage:    number
  disk_used:     number
  disk_total:    number
  network_in:    number
  network_out:   number
  request_rate:  number
  response_time: number
}

export interface Server {
  id:          number
  name:        string
  hostname:    string
  ip_address:  string
  environment: string
  status:      'online' | 'warning' | 'critical' | 'offline'
  is_active:   boolean
  metric:      ServerMetric | null
  history:     MetricPoint[]
}

const HISTORY_MAX = 30

function toMetricPoint(m: ServerMetric): MetricPoint {
  return { t: Date.now(), cpu: m.cpu_usage, mem: m.memory_usage, disk: m.disk_usage }
}

export function useServers() {
  const config = useRuntimeConfig()
  const servers = ref<Server[]>([])
  const loading = ref(false)
  const error   = ref<string | null>(null)

  async function fetchServers() {
    loading.value = true
    error.value   = null
    const { authHeaders } = useAuth()
    try {
      const data = await $fetch<Omit<Server, 'history'>[]>(`${config.public.apiUrl}/api/servers`, {
        headers: authHeaders(),
      })
      servers.value = data.map(s => ({
        ...s,
        history: s.metric ? [toMetricPoint(s.metric)] : [],
      }))
    } catch (e: any) {
      error.value = e?.data?.message ?? 'Failed to load servers'
    } finally {
      loading.value = false
    }
  }

  function updateServerMetric(serverId: number, metric: ServerMetric) {
    const server = servers.value.find(s => s.id === serverId)
    if (server) {
      server.metric  = metric
      server.history = [...server.history.slice(-(HISTORY_MAX - 1)), toMetricPoint(metric)]
    }
  }

  function updateServerStatus(serverId: number, status: Server['status']) {
    const server = servers.value.find(s => s.id === serverId)
    if (server) {
      server.status = status
    }
  }

  return { servers, loading, error, fetchServers, updateServerMetric, updateServerStatus }
}
