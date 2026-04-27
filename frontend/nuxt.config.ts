export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  runtimeConfig: {
    public: {
      apiUrl:      process.env.NUXT_PUBLIC_API_URL      ?? 'https://infra-pulse.test',
      reverbHost:  process.env.NUXT_PUBLIC_REVERB_HOST  ?? 'infra-pulse.test',
      reverbPort:  process.env.NUXT_PUBLIC_REVERB_PORT  ?? '8080',
      reverbKey:   process.env.NUXT_PUBLIC_REVERB_KEY   ?? 'fdv4cq1ogvk6x7sfw4f1',
      reverbScheme: process.env.NUXT_PUBLIC_REVERB_SCHEME ?? 'http',
    },
  },

  css: ['~/assets/css/main.css'],

  vite: {
    server: {
      proxy: {
        '/api': {
          target: process.env.NUXT_PUBLIC_API_URL ?? 'http://infra-pulse.test',
          changeOrigin: true,
        },
        '/sanctum': {
          target: process.env.NUXT_PUBLIC_API_URL ?? 'http://infra-pulse.test',
          changeOrigin: true,
        },
        '/broadcasting': {
          target: process.env.NUXT_PUBLIC_API_URL ?? 'http://infra-pulse.test',
          changeOrigin: true,
        },
      },
    },
  },
})
