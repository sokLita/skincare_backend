import api from './api'

export default {
  getAll() {
    return api.get('/wishlist')
  },
  
  addToWishlist(productId) {
    return api.post('/wishlist', { product_id: productId })
  },
  
  removeFromWishlist(wishlistItemId) {
    return api.delete(`/wishlist/${wishlistItemId}`)
  }
}