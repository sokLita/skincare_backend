import api from './api'

export default {
  getAll() {
    return api.get('/categories')
  },
  
  getById(id) {
    return api.get(`/categories/${id}`)
  }
}