<template>
  <div class="container" style="padding: 32px 20px;">
    <h1 class="page-title">Shopping Cart</h1>
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="items.length === 0" style="text-align:center; padding:60px 20px;">
      <p style="font-size:1.5rem; margin-bottom:20px; color:var(--text-light);">🛒 Your cart is empty</p>
      <router-link to="/products" class="btn btn-primary">Start Shopping</router-link>
    </div>
    <div v-else class="cart-layout">
      <div>
        <div v-for="item in items" :key="item.id" class="card card-body" style="display:flex; align-items:center; gap:16px; margin-bottom:12px;">
          <img :src="item.product?.image_url || 'https://via.placeholder.com/80'"
            style="width:80px; height:80px; object-fit:cover; border-radius:var(--radius);" />
          <div style="flex:1;">
            <h3 style="font-size:1rem; margin-bottom:4px;">{{ item.product?.name }}</h3>
            <p style="color:var(--primary); font-weight:600;">${{ item.product?.price }}</p>
          </div>
          <div style="display:flex; align-items:center; gap:12px;">
            <input type="number" :value="item.quantity" min="1" class="form-control" style="width:70px;"
              @change="updateQty(item, $event.target.value)" />
            <span style="font-weight:700;">${{ (item.product?.price * item.quantity).toFixed(2) }}</span>
            <button class="btn btn-danger btn-sm" @click="removeItem(item.id)">✕</button>
          </div>
        </div>
      </div>
      <div class="card card-body" style="height:fit-content;">
        <h3 style="margin-bottom:16px;">Order Summary</h3>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border);">
          <span>Subtotal</span><span>${{ total.toFixed(2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--border);">
          <span>Shipping</span><span style="color:var(--success);">Free</span>
        </div>
        <div style="display:flex; justify-content:space-between; padding-top:12px; font-size:1.1rem; font-weight:700;">
          <span>Total</span><span>${{ total.toFixed(2) }}</span>
        </div>
        <router-link to="/checkout" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:16px;">
          Proceed to Checkout
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useStore } from 'vuex'
import cartService from '../services/cart'

const store   = useStore()
const items   = ref([])
const total   = ref(0)
const loading = ref(true)

const fetchCart = async () => {
  loading.value = true
  try {
    const { data } = await cartService.getAll()
    items.value   = data.items
    total.value   = data.total
  } catch (error) {
    console.error('Error fetching cart:', error)
  } finally {
    loading.value = false
  }
}
const updateQty = async (item, qty) => {
  await cartService.updateQuantity(item.id, parseInt(qty))
  fetchCart()
  store.dispatch('fetchCartCount')
}
const removeItem = async (id) => {
  await cartService.removeFromCart(id)
  fetchCart()
  store.dispatch('fetchCartCount')
}
onMounted(fetchCart)
</script>

<style scoped>
.cart-layout { display: grid; grid-template-columns: 1fr 300px; gap: 24px; }
@media(max-width:768px) { .cart-layout { grid-template-columns: 1fr; } }
</style>