import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance: InstanceType<typeof Echo> | null = null
const echoConnected = ref(false)

export function useEcho() {
    const config = useRuntimeConfig()
    const authToken = useCookie('auth_token')

    function initEcho() {
        if (!import.meta.client || echoInstance) return

        window.Pusher = Pusher

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: config.public.reverbKey,
            wsHost: config.public.reverbHost,
            wsPort: Number(config.public.reverbPort),
            wssPort: Number(config.public.reverbPort),
            forceTLS: config.public.reverbScheme === 'https',
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: `${config.public.apiUrl}/broadcasting/auth`,
            auth: {
                headers: {
                    Authorization: authToken.value
                        ? `Bearer ${authToken.value}`
                        : '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        })

        const conn = (echoInstance.connector as any).pusher.connection
        conn.bind('connected', () => {
            echoConnected.value = true
        })
        conn.bind('disconnected', () => {
            echoConnected.value = false
        })
        conn.bind('failed', () => {
            echoConnected.value = false
        })
        conn.bind('unavailable', () => {
            echoConnected.value = false
        })
    }

    function subscribeToServer(
        serverId: number,
        onMetric: (data: any) => void,
        onAlert: (data: any) => void,
    ) {
        if (!echoInstance) return

        echoInstance
            .private(`server.${serverId}`)
            .listen('.MetricUpdated', onMetric)
            .listen('.AlertTriggered', onAlert)
    }

    function subscribeToAlerts(onAlert: (data: any) => void) {
        if (!echoInstance) return
        echoInstance.private('alerts').listen('.AlertTriggered', onAlert)
    }

    function leaveServer(serverId: number) {
        echoInstance?.leave(`server.${serverId}`)
    }

    function disconnectEcho() {
        echoInstance?.disconnect()
        echoInstance = null
        echoConnected.value = false
    }

    return {
        echoConnected,
        initEcho,
        subscribeToServer,
        subscribeToAlerts,
        leaveServer,
        disconnectEcho,
    }
}
