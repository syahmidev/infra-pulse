const user = ref<{ id: number; name: string; email: string } | null>(null)
const isAuthenticated = computed(() => !!user.value)

async function fetchCsrf() {
  const config = useRuntimeConfig()
  await $fetch(`${config.public.apiUrl}/sanctum/csrf-cookie`, {
    credentials: 'include',
  })
}

async function login(email: string, password: string) {
  const config = useRuntimeConfig()
  await fetchCsrf()

  const data = await $fetch<{ user: typeof user.value }>(`${config.public.apiUrl}/api/login`, {
    method: 'POST',
    body: { email, password },
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })

  user.value = data.user
  return data
}

async function logout() {
  const config = useRuntimeConfig()
  await $fetch(`${config.public.apiUrl}/api/logout`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  }).catch(() => {})
  user.value = null
}

async function fetchUser() {
  const config = useRuntimeConfig()
  try {
    const data = await $fetch<typeof user.value>(`${config.public.apiUrl}/api/user`, {
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    user.value = data
    return data
  } catch {
    user.value = null
    return null
  }
}

export function useAuth() {
  return { user, isAuthenticated, login, logout, fetchUser }
}
