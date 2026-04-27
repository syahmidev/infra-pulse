const user = ref<{ id: number; name: string; email: string } | null>(null)
const isAuthenticated = computed(() => !!user.value)

function getToken() {
  return useCookie('auth_token').value ?? null
}

function authHeaders() {
  const token = getToken()
  return {
    'Accept':        'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
  }
}

async function login(email: string, password: string) {
  const config = useRuntimeConfig()
  const token  = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7 })

  const data = await $fetch<{ token: string; user: typeof user.value }>(
    `${config.public.apiUrl}/api/login`,
    {
      method: 'POST',
      body:    { email, password },
      headers: { 'Accept': 'application/json' },
    },
  )

  token.value  = data.token
  user.value   = data.user
  return data
}

async function logout() {
  const config = useRuntimeConfig()
  const token  = useCookie('auth_token')

  await $fetch(`${config.public.apiUrl}/api/logout`, {
    method:  'POST',
    headers: authHeaders(),
  }).catch(() => {})

  token.value = null
  user.value  = null
}

async function fetchUser() {
  const config = useRuntimeConfig()
  if (!getToken()) return null

  try {
    const data = await $fetch<typeof user.value>(
      `${config.public.apiUrl}/api/user`,
      { headers: authHeaders() },
    )
    user.value = data
    return data
  } catch {
    user.value = null
    return null
  }
}

export function useAuth() {
  return { user, isAuthenticated, login, logout, fetchUser, authHeaders }
}
