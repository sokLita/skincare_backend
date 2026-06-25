import api from './api'

export default {
  login(credentials) {
    return api.post('/login', credentials)
  },
  
  register(userData) {
    return api.post('/register', userData)
  },
  
  logout() {
    return api.post('/logout')
  },
  
  getProfile() {
    return api.get('/profile')
  },
  
  updateProfile(data) {
    return api.put('/profile', data)
  },
  
  changePassword(data) {
    return api.put('/change-password', data)
  }
}