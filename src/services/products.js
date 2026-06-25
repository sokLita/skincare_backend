import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/products', { params })
  },
  
  getById(id) {
    return api.get(`/products/${id}`)
  },
  
  getByCategory(categoryId) {
    return api.get(`/products`, { params: { category_id: categoryId } })
  },
  
  getReviews(productId) {
    return api.get(`/products/${productId}/reviews`)
  },
  
  addReview(productId, reviewData) {
    return api.post(`/products/${productId}/reviews`, reviewData)
  }
}