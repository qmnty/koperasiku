import axios from 'axios'
import Cookies from 'js-cookie'

const api = axios.create({
  baseURL: import.meta.env.VITE_APP_URL || '/',
  withCredentials: true,
  maxRedirects: 0,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
})

api.interceptors.request.use(async config => {
  if (
    ['post', 'put', 'patch', 'delete'].includes(config.method) &&
    !config.url.includes('/sanctum/csrf-cookie')
  ) {
    const token  =  Cookies.get('XSRF-TOKEN')
    if(!token) {
      await axios.get('/sanctum/csrf-cookie')
    }
    config.headers['X-XSRF-TOKEN'] = Cookies.get('XSRF-TOKEN')
  }

  return config
})

export default api