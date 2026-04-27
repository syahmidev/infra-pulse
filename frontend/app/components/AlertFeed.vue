<template>
  <div class="alert-feed">
    <div class="feed-header">
      <span class="feed-title">Live Alerts</span>
      <span v-if="alerts.length" class="feed-count">{{ alerts.length }}</span>
    </div>

    <div class="feed-empty" v-if="!alerts.length">
      <span>All clear — no active alerts</span>
    </div>

    <TransitionGroup name="alert" tag="div" class="feed-list">
      <div
        v-for="alert in alerts"
        :key="alert.id"
        :class="['alert-item', `alert-item--${alert.severity}`]"
      >
        <div class="alert-top">
          <span :class="['badge', `badge-${alert.severity === 'critical' ? 'critical' : alert.severity === 'warning' ? 'warning' : 'info'}`]">
            {{ alert.severity }}
          </span>
          <span class="alert-server">{{ alert.server?.name ?? 'Unknown' }}</span>
          <span class="alert-time">{{ formatTime(alert.triggered_at) }}</span>
        </div>
        <p class="alert-msg">{{ alert.message }}</p>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  alerts: Array<{
    id: number
    severity: string
    message: string
    type: string
    triggered_at: string
    server?: { id: number; name: string }
  }>
}>()

function formatTime(iso: string) {
  return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}
</script>

<style scoped>
.alert-feed {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 400px;
  overflow: hidden;
}

.feed-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.feed-title { font-weight: 700; font-size: 0.875rem; color: var(--text); }
.feed-count {
  background: var(--danger);
  color: #fff;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.4rem;
}

.feed-empty { color: var(--text-muted); font-size: 0.8rem; text-align: center; padding: 1rem 0; }

.feed-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  overflow-y: auto;
}

.alert-item {
  background: var(--bg-card-2);
  border-left: 3px solid var(--border);
  border-radius: 0.5rem;
  padding: 0.625rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.alert-item--critical { border-left-color: var(--danger); }
.alert-item--warning  { border-left-color: var(--warning); }
.alert-item--info     { border-left-color: var(--info); }

.alert-top {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.alert-server { font-size: 0.8rem; font-weight: 600; color: var(--text); flex: 1; }
.alert-time   { font-size: 0.7rem; color: var(--text-muted); }
.alert-msg    { font-size: 0.8rem; color: var(--text-muted); }

.alert-enter-active { transition: all 0.3s ease; }
.alert-enter-from   { opacity: 0; transform: translateY(-8px); }
</style>
