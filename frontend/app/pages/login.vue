<template>
  <div class="min-h-screen bg-surface flex items-center justify-center p-4">
    <div class="bg-card border border-border rounded-2xl p-10 w-full max-w-sm">
      <div class="flex items-center gap-2.5 text-xl font-bold text-fore mb-1">
        <div class="w-3 h-3 rounded-full bg-accent shadow-[0_0_10px_var(--color-accent)]" />
        <span>Infra Pulse</span>
      </div>
      <p class="text-sm text-muted mb-8">Real-time infrastructure monitoring</p>

      <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="flex flex-col gap-1.5">
          <label for="email" class="text-xs font-medium text-muted">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="admin@infrapulse.local"
            required
            class="bg-surface border border-border rounded-lg px-3.5 py-2.5 text-fore text-sm outline-none focus:border-accent transition-colors"
          />
        </div>
        <div class="flex flex-col gap-1.5">
          <label for="password" class="text-xs font-medium text-muted">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            required
            class="bg-surface border border-border rounded-lg px-3.5 py-2.5 text-fore text-sm outline-none focus:border-accent transition-colors"
          />
        </div>

        <p v-if="errorMsg" class="text-xs text-danger bg-danger/10 rounded-md px-3 py-2">
          {{ errorMsg }}
        </p>

        <button
          type="submit"
          class="btn btn-primary w-full justify-center py-2.5 text-[0.95rem] disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="loading"
        >
          <span v-if="loading">Signing in…</span>
          <span v-else>Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: false })

const { login } = useAuth()
const router    = useRouter()

const form     = reactive({ email: 'admin@infrapulse.local', password: 'password' })
const loading  = ref(false)
const errorMsg = ref('')

async function submit() {
  loading.value  = true
  errorMsg.value = ''
  try {
    await login(form.email, form.password)
    await router.push('/')
  } catch (e: any) {
    errorMsg.value = e?.data?.message ?? 'Login failed. Check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>
