<template>
  <div class="login-shell">
    <div class="login-card">
      <div class="login-brand">
        <div class="login-dot" />
        <span>Infra Pulse</span>
      </div>
      <p class="login-sub">Real-time infrastructure monitoring</p>

      <form class="login-form" @submit.prevent="submit">
        <div class="field">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            placeholder="admin@infrapulse.local"
            required
          />
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            required
          />
        </div>

        <p v-if="errorMsg" class="login-error">{{ errorMsg }}</p>

        <button type="submit" class="btn btn-primary login-btn" :disabled="loading">
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

const form     = reactive({ email: '', password: '' })
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

<style scoped>
.login-shell {
  min-height: 100vh;
  background: var(--bg-base);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
}

.login-brand {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.25rem;
}
.login-dot {
  width: 12px; height: 12px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 10px var(--accent);
}
.login-sub { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2rem; }

.login-form { display: flex; flex-direction: column; gap: 1rem; }

.field { display: flex; flex-direction: column; gap: 0.375rem; }
.field label { font-size: 0.8rem; font-weight: 500; color: var(--text-muted); }
.field input {
  background: var(--bg-base);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 0.625rem 0.875rem;
  color: var(--text);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.15s;
}
.field input:focus { border-color: var(--accent); }

.login-error {
  font-size: 0.8rem;
  color: var(--danger);
  background: rgba(239,68,68,0.1);
  border-radius: 0.4rem;
  padding: 0.5rem 0.75rem;
}

.login-btn { width: 100%; justify-content: center; padding: 0.7rem; font-size: 0.95rem; }
.login-btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
