export interface Alert {
    id: number
    severity: 'warning' | 'critical' | 'info'
    message: string
    type: string
    triggered_at: string
    is_read: boolean
    server?: { id: number; name: string }
}

export function useAlerts() {
    const config = useRuntimeConfig()

    async function fetchUnread(): Promise<Alert[]> {
        const { authHeaders } = useAuth()
        try {
            return await $fetch<Alert[]>(
                `${config.public.apiUrl}/api/alerts/unread`,
                {
                    headers: authHeaders(),
                },
            )
        } catch {
            return []
        }
    }

    return { fetchUnread }
}
