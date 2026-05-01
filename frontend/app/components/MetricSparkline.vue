<template>
  <div class="sparkline-wrap">
    <div v-if="history.length < 2" class="sparkline-empty">
      Waiting for data…
    </div>
    <ClientOnly v-else>
      <apexchart
        type="area"
        :height="150"
        :options="chartOptions"
        :series="series"
      />
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import type { MetricPoint } from '~/composables/useServers'

const props = defineProps<{ history: MetricPoint[] }>()

const series = computed(() => [
  { name: 'CPU',  data: props.history.map(p => [p.t, p.cpu]) },
  { name: 'Mem',  data: props.history.map(p => [p.t, p.mem]) },
  { name: 'Disk', data: props.history.map(p => [p.t, p.disk]) },
])

const chartOptions = {
  chart: {
    type: 'area',
    background: 'transparent',
    toolbar: { show: false },
    zoom: { enabled: false },
    animations: {
      enabled: true,
      easing: 'linear',
      dynamicAnimation: { speed: 800 },
    },
    sparkline: { enabled: false },
  },
  colors: ['#06B6D4', '#F59E0B', '#8B5CF6'],
  stroke: { curve: 'smooth', width: 1.5 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.3,
      opacityTo: 0.03,
    },
  },
  dataLabels: { enabled: false },
  grid: {
    borderColor: 'rgba(255,255,255,0.06)',
    padding: { left: 4, right: 4, top: 0, bottom: 0 },
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: true } },
  },
  xaxis: {
    type: 'datetime',
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    min: 0,
    max: 100,
    tickAmount: 4,
    labels: {
      style: { colors: ['#475569'], fontSize: '9px' },
      formatter: (v: number) => `${Math.round(v)}%`,
    },
  },
  tooltip: {
    theme: 'dark',
    x: { format: 'HH:mm:ss' },
    y: { formatter: (v: number) => `${v.toFixed(1)}%` },
  },
  legend: {
    show: true,
    position: 'top',
    horizontalAlign: 'left',
    labels: { colors: '#64748B' },
    fontSize: '10px',
    markers: { size: 5 },
    itemMargin: { horizontal: 6 },
    offsetY: -4,
  },
}
</script>

<style scoped>
.sparkline-wrap {
  margin-top: 0.25rem;
  border-top: 1px solid var(--border);
  padding-top: 0.5rem;
}

.sparkline-empty {
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: var(--text-muted);
}
</style>
