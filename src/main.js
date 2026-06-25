import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'
import './style.css'

axios.defaults.baseURL = '/api'

axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

axios.interceptors.response.use(
  res => res,
  err => {
    if (err.response?.status === 401) {
      store.commit('LOGOUT')
    }
    return Promise.reject(err)
  }
)

createApp(App).use(router).use(store).mount('#app')