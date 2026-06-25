import api from './api'

export default {
  getAll() {
    return api.get('/cart')
  },
  
  addToCart(productId, quantity = 1) {
    return api.post('/cart', { product_id: productId, quantity })
  },
  
  updateQuantity(cartItemId, quantity) {
    return api.put(`/cart/${cartItemId}`, { quantity })
  },
  
  removeFromCart(cartItemId) {
    return api.delete(`/cart/${cartItemId}`)
  },
  
  clearCart() {
    return api.delete('/cart')
  }
}