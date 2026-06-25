import api from './api'

export default {
  getAll() {
    return api.get('/orders')
  },
  
  getById(id) {
    return api.get(`/orders/${id}`)
  },
  
  checkout(shippingAddress) {
    return api.post('/checkout', { shipping_address: shippingAddress })
  }
}