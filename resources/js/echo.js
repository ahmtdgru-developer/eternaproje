import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http'
const reverbHost = import.meta.env.VITE_REVERB_HOST || '127.0.0.1'
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT || 8080)

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: reverbHost,
  wsPort: reverbPort,
  wssPort: reverbPort,
  forceTLS: reverbScheme === 'https',
  enabledTransports: ['ws', 'wss'],
  authorizer: channel => {
    return {
      authorize: (socketId, callback) => {
        const token = localStorage.getItem('token')

        axios.post(
          '/broadcasting/auth',
          {
            socket_id: socketId,
            channel_name: channel.name,
          },
          {
            headers: {
              Authorization: token ? `Bearer ${token}` : '',
              Accept: 'application/json',
              'X-Socket-ID': socketId,
            },
          }
        )
          .then(response => callback(false, response.data))
          .catch(error => callback(true, error))
      },
    }
  },
})
