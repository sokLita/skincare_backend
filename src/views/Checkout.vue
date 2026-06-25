<template>
  <div class="container" style="padding:32px 20px; max-width:700px;">
    <h1 class="page-title">Checkout</h1>
    <div v-if="success" class="alert alert-success">
      ✅ Order placed! <router-link to="/orders">View orders →</router-link>
    </div>
    <template v-else>
      <div class="card card-body" style="margin-bottom:20px;">
        <h3 style="margin-bottom:16px;">Order Summary</h3>
        <div v-for="item in cartItems" :key="item.id"
          style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
          <span>{{ item.product?.name }} × {{ item.quantity }}</span>
          <span>${{ (item.product?.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; padding-top:12px;">
          <span>Total</span><span>${{ cartTotal.toFixed(2) }}</span>
        </div>
      </div>
      <div class="card card-body">
        <h3 style="margin-bottom:16px;">Shipping Information</h3>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Shipping Address *</label>
          <textarea v-model="address" class="form-control" rows="4" placeholder="Enter your full shipping address..."></textarea>
        </div>
        <button class="btn btn-primary btn-lg" style="width:100%; justify-content:center;"
          :disabled="loading || !address" @click="placeOrder">
          {{ loading ? 'Placing Order...' : `Place Order — $${cartTotal.toFixed(2)}` }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import cartService from '../services/cart'
import ordersService from '../services/orders'

const cartItems = ref([])
const cartTotal = ref(0)
const address   = ref('')
const loading   = ref(false)
const success   = ref(false)
const error     = ref('')

onMounted(async () => {
  try {
    const { data } = await cartService.getAll()
    cartItems.value = data.items
    cartTotal.value = data.total
  } catch (error) {
    console.error('Error fetching cart:', error)
  }
})

const placeOrder = async () => {
  loading.value = true
  error.value   = ''
  try {
    await ordersService.checkout(address.value)
    success.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Checkout failed.'
  } finally {
    loading.value = false
  }
}
</script>
