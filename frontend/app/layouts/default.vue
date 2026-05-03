<template>
  <div class="flex flex-col min-h-screen">
    <header class="flex items-center justify-between px-6 h-14 bg-card border-b border-border sticky top-0 z-50">
      <div class="flex items-center gap-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-accent shadow-[0_0_8px_var(--color-accent)]" />
        <span class="text-base font-bold text-fore tracking-tight">Infra Pulse</span>
      </div>
      <div class="flex items-center gap-4">
        <span class="flex items-center gap-1.5 text-xs text-muted">
          <span
            :class="[
              'w-2 h-2 rounded-full',
              echoConnected
                ? 'bg-success shadow-[0_0_6px_var(--color-success)] animate-pulse'
                : 'bg-muted'
            ]"
          />
          {{ echoConnected ? 'Live' : 'Offline' }}
        </span>
        <button class="btn btn-ghost" @click="logout">Logout</button>
      </div>
    </header>

    <main class="flex-1 px-6 py-6 max-w-[1400px] w-full mx-auto">
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
