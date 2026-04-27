import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance: Echo<'pusher'> | null = null
const echoConnected = ref(false)

export function useEcho() {
  function initEcho() {
    if (!import.meta.client || echoInstance) return

    const config = useRuntimeConfig()

    // @ts-ignore — pusher-js needs window.Pusher
    window.Pusher = Pusher

    echoInstance = new Echo({
      broadcaster:      'pusher',
      key:              config.public.reverbKey,
      wsHost:           config.public.reverbHost,
      wsPort:           Number(config.public.reverbPort),
      wssPort:          Number(config.public.reverbPort),
      forceTLS:         config.public.reverbScheme === 'https',
      disableStats:     true,
      enabledTransports: ['ws', 'wss'],
      authEndpoint:     `${config.public.apiUrl}/broadcasting/auth`,
      auth: {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
        withCredentials: true,
      },
    })

    echoInstance.connector.pusher.connection.bind('connected', () => {
      echoConnected.value = true
    })
    echoInstance.connector.pusher.connection.bind('disconnected', () => {
      echoConnected.value = false
    })
    echoInstance.connector.pusher.connection.bind('failed', () => {
      echoConnected.value = false
    })
  }

  function subscribeToServer(
    serverId: number,
    onMetric: (data: any) => void,
    onAlert: (data: any) => void,
  ) {
    if (!echoInstance) return

    echoInstance.private(`server.${serverId}`)
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
