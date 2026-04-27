<template>
  <div class="app-shell">
    <header class="topbar">
      <div class="topbar-brand">
        <span class="brand-dot" />
        <span class="brand-name">Infra Pulse</span>
      </div>
      <div class="topbar-right">
        <span class="topbar-status">
          <span :class="['pulse-dot', echoConnected ? 'pulse-dot--live' : 'pulse-dot--offline']" />
          {{ echoConnected ? 'Live' : 'Offline' }}
        </span>
        <button class="btn btn-ghost" @click="logout">Logout</button>
      </div>
    </header>

    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { echoConnected, disconnectEcho } = useEcho()
const { logout: doLogout } = useAuth()
const router = useRouter()

async function logout() {
  disconnectEcho()
  await doLogout()
  await router.push('/login')
}
</script>

<style scoped>
.app-shell { display: flex; flex-direction: column; min-height: 100vh; }

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  height: 56px;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-brand { display: flex; align-items: center; gap: 0.625rem; }
.brand-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}
.brand-name { font-size: 1rem; font-weight: 700; color: var(--text); letter-spacing: -0.01em; }

.topbar-right { display: flex; align-items: center; gap: 1rem; }
.topbar-status { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-muted); }

.pulse-dot {
  width: 8px; height: 8px; border-radius: 50%;
}
.pulse-dot--live    { background: var(--success); box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite; }
.pulse-dot--offline { background: var(--text-muted); }

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

.main-content { flex: 1; padding: 1.5rem; max-width: 1400px; width: 100%; margin: 0 auto; }
</style>
