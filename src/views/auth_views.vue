<!-- ============================================================ -->
<!-- FILE: src/views/Cart.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px">
    <h1 class="page-title">Shopping Cart</h1>
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="items.length === 0" class="empty-state">
      <p>🛒 Your cart is empty</p>
      <router-link to="/products" class="btn btn-primary">Start Shopping</router-link>
    </div>
    <div v-else class="cart-layout">
      <div class="cart-items">
        <div v-for="item in items" :key="item.id" class="cart-item card card-body mb-3">
          <img :src="item.product?.image_url || 'https://via.placeholder.com/80'" :alt="item.product?.name" class="item-img">
          <div class="item-details">
            <h3>{{ item.product?.name }}</h3>
            <p class="item-price">${{ item.product?.price }}</p>
          </div>
          <div class="item-actions">
            <input type="number" :value="item.quantity" min="1" class="qty-input form-control"
                   @change="updateQty(item, $event.target.value)">
            <span class="item-total">${{ (item.product?.price * item.quantity).toFixed(2) }}</span>
            <button class="btn btn-danger btn-sm" @click="removeItem(item.id)">✕</button>
          </div>
        </div>
      </div>
      <div class="cart-summary card card-body">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><span>${{ total.toFixed(2) }}</span></div>
        <div class="summary-row"><span>Shipping</span><span>Free</span></div>
        <div class="summary-row total-row"><strong>Total</strong><strong>${{ total.toFixed(2) }}</strong></div>
        <router-link to="/checkout" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px">Proceed to Checkout</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useStore } from 'vuex'
const store = useStore()
const items = ref([])
const total = ref(0)
const loading = ref(true)

const fetchCart = async () => {
  loading.value = true
  const { data } = await axios.get('/cart')
  items.value = data.items
  total.value = data.total
  loading.value = false
}
const updateQty = async (item, qty) => {
  await axios.put(`/cart/${item.id}`, { quantity: parseInt(qty) })
  fetchCart(); store.dispatch('fetchCartCount')
}
const removeItem = async (id) => {
  await axios.delete(`/cart/${id}`)
  fetchCart(); store.dispatch('fetchCartCount')
}
onMounted(fetchCart)
</script>

<style scoped>
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state p { font-size: 1.5rem; margin-bottom: 20px; color: var(--text-light); }
.cart-layout { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
.cart-item { display: flex; align-items: center; gap: 16px; }
.item-img { width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius); }
.item-details { flex: 1; }
.item-details h3 { font-size: 1rem; margin-bottom: 4px; }
.item-price { color: var(--primary); font-weight: 600; }
.item-actions { display: flex; align-items: center; gap: 12px; }
.qty-input { width: 70px; }
.item-total { font-weight: 700; min-width: 60px; text-align: right; }
.summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
.total-row { font-size: 1.1rem; border-bottom: none; margin-top: 8px; }
.cart-summary h3 { margin-bottom: 16px; }
@media(max-width:768px){ .cart-layout { grid-template-columns: 1fr; } .cart-item { flex-wrap: wrap; } }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Wishlist.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px">
    <h1 class="page-title">My Wishlist</h1>
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="items.length === 0" class="empty-state">
      <p>❤️ Your wishlist is empty</p>
      <router-link to="/products" class="btn btn-primary">Discover Products</router-link>
    </div>
    <div v-else class="product-grid">
      <div v-for="item in items" :key="item.id" class="wishlist-card card">
        <img :src="item.product?.image_url || 'https://via.placeholder.com/280x180'" :alt="item.product?.name" class="w-img">
        <div class="card-body">
          <p class="wl-cat">{{ item.product?.category?.name }}</p>
          <h3 class="wl-name">{{ item.product?.name }}</h3>
          <p class="wl-price">${{ item.product?.price }}</p>
          <div class="wl-btns">
            <button class="btn btn-primary btn-sm" @click="addToCart(item.product)">Add to Cart</button>
            <button class="btn btn-danger btn-sm" @click="remove(item.product_id)">Remove</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useStore } from 'vuex'
const store = useStore()
const items = ref([])
const loading = ref(true)
onMounted(async () => { const { data } = await axios.get('/wishlist'); items.value = data; loading.value = false })
const remove = async (id) => { await axios.delete(`/wishlist/${id}`); items.value = items.value.filter(i => i.product_id !== id) }
const addToCart = async (p) => { await axios.post('/cart', { product_id: p.id, quantity: 1 }); store.dispatch('fetchCartCount') }
</script>

<style scoped>
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state p { font-size: 1.5rem; margin-bottom: 20px; color: var(--text-light); }
.wishlist-card { overflow: hidden; }
.w-img { width: 100%; height: 180px; object-fit: cover; }
.wl-cat { color: var(--primary); font-size: .78rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
.wl-name { font-weight: 600; margin-bottom: 6px; }
.wl-price { color: var(--primary); font-weight: 700; font-size: 1.1rem; margin-bottom: 12px; }
.wl-btns { display: flex; gap: 8px; }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Checkout.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px;max-width:700px">
    <h1 class="page-title">Checkout</h1>
    <div v-if="success" class="alert alert-success">
      ✅ Order placed successfully! <router-link to="/orders">View your orders</router-link>
    </div>
    <template v-else>
      <div class="card card-body mb-4">
        <h3 style="margin-bottom:16px">Order Summary</h3>
        <div v-for="item in cartItems" :key="item.id" class="checkout-item">
          <span>{{ item.product?.name }} × {{ item.quantity }}</span>
          <span>${{ (item.product?.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div class="checkout-total">
          <strong>Total: ${{ cartTotal.toFixed(2) }}</strong>
        </div>
      </div>
      <div class="card card-body">
        <h3 style="margin-bottom:16px">Shipping Information</h3>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Shipping Address *</label>
          <textarea v-model="address" class="form-control" rows="4" placeholder="Enter your full shipping address..."></textarea>
        </div>
        <button class="btn btn-primary btn-lg" style="width:100%;justify-content:center" :disabled="loading || !address" @click="placeOrder">
          {{ loading ? 'Placing Order...' : `Place Order - $${cartTotal.toFixed(2)}` }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
const cartItems = ref([])
const cartTotal = ref(0)
const address   = ref('')
const loading   = ref(false)
const success   = ref(false)
const error     = ref('')

onMounted(async () => {
  const { data } = await axios.get('/cart')
  cartItems.value = data.items
  cartTotal.value = data.total
})

const placeOrder = async () => {
  loading.value = true; error.value = ''
  try {
    await axios.post('/checkout', { shipping_address: address.value })
    success.value = true
  } catch (e) {
    error.value = e.response?.data?.message || 'Checkout failed'
  } finally { loading.value = false }
}
</script>

<style scoped>
.checkout-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); }
.checkout-total { font-size: 1.1rem; text-align: right; padding-top: 12px; }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Orders.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px">
    <h1 class="page-title">My Orders</h1>
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="orders.length === 0" class="alert alert-info">No orders yet. <router-link to="/products">Start shopping!</router-link></div>
    <div v-else>
      <div v-for="order in orders" :key="order.id" class="order-card card card-body mb-3">
        <div class="order-header">
          <div>
            <strong>Order #{{ order.id }}</strong>
            <span class="badge" :class="`badge-${order.status === 'completed' ? 'success' : order.status === 'pending' ? 'warning' : 'primary'}`" style="margin-left:10px">{{ order.status }}</span>
          </div>
          <div class="order-meta">
            <span>${{ Number(order.total_amount).toFixed(2) }}</span>
            <span>{{ new Date(order.created_at).toLocaleDateString() }}</span>
            <router-link :to="`/orders/${order.id}`" class="btn btn-sm btn-primary">Details</router-link>
          </div>
        </div>
        <p class="order-items-preview">{{ order.items?.length }} item(s): {{ order.items?.map(i => i.product?.name).join(', ') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
const orders  = ref([])
const loading = ref(true)
onMounted(async () => { const { data } = await axios.get('/orders'); orders.value = data; loading.value = false })
</script>

<style scoped>
.order-card { }
.order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.order-meta { display: flex; align-items: center; gap: 16px; }
.order-items-preview { color: var(--text-light); font-size: .88rem; }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/OrderDetail.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px;max-width:800px">
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <template v-else-if="order">
      <div class="d-flex" style="justify-content:space-between;align-items:center;margin-bottom:24px">
        <h1>Order #{{ order.id }}</h1>
        <router-link to="/orders" class="btn btn-secondary">← Back</router-link>
      </div>
      <div class="order-status-bar">
        <div v-for="s in ['pending','processing','completed']" :key="s"
             class="status-step" :class="{ active: s === order.status, done: statusDone(s) }">
          {{ s }}
        </div>
      </div>
      <div class="card card-body mb-4">
        <h3 style="margin-bottom:16px">Items</h3>
        <div v-for="item in order.items" :key="item.id" class="checkout-item">
          <span>{{ item.product?.name }} × {{ item.quantity }}</span>
          <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div class="checkout-total"><strong>Total: ${{ Number(order.total_amount).toFixed(2) }}</strong></div>
      </div>
      <div class="card card-body">
        <h3>Shipping Address</h3>
        <p style="margin-top:8px;color:var(--text-light)">{{ order.shipping_address }}</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
const route   = useRoute()
const order   = ref(null)
const loading = ref(true)
const statusOrder = ['pending','processing','completed','cancelled']
const statusDone  = (s) => statusOrder.indexOf(s) < statusOrder.indexOf(order.value?.status)
onMounted(async () => { const { data } = await axios.get(`/orders/${route.params.id}`); order.value = data; loading.value = false })
</script>

<style scoped>
.order-status-bar { display: flex; gap: 0; margin-bottom: 28px; }
.status-step { flex: 1; text-align: center; padding: 10px; background: var(--border); font-size: .85rem; font-weight: 600; text-transform: capitalize; border-right: 2px solid white; transition: background .3s; }
.status-step.done   { background: #a7f3d0; color: #065f46; }
.status-step.active { background: var(--primary); color: white; }
.checkout-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); }
.checkout-total { font-size: 1.1rem; text-align: right; padding-top: 12px; }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Profile.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding:32px 20px;max-width:600px">
    <h1 class="page-title">My Profile</h1>
    <div class="card card-body mb-4">
      <h3 style="margin-bottom:16px">Profile Information</h3>
      <div v-if="profileMsg" class="alert alert-success">{{ profileMsg }}</div>
      <div class="form-group"><label>Name</label><input v-model="form.name" class="form-control"></div>
      <div class="form-group"><label>Email</label><input v-model="form.email" type="email" class="form-control"></div>
      <button class="btn btn-primary" @click="updateProfile" :disabled="profileLoading">Save Changes</button>
    </div>

    <div class="card card-body">
      <h3 style="margin-bottom:16px">Change Password</h3>
      <div v-if="pwdMsg" class="alert" :class="pwdError ? 'alert-danger' : 'alert-success'">{{ pwdMsg }}</div>
      <div class="form-group"><label>Current Password</label><input v-model="pwd.current_password" type="password" class="form-control"></div>
      <div class="form-group"><label>New Password</label><input v-model="pwd.password" type="password" class="form-control"></div>
      <div class="form-group"><label>Confirm New Password</label><input v-model="pwd.password_confirmation" type="password" class="form-control"></div>
      <button class="btn btn-warning" @click="changePassword" :disabled="pwdLoading">Update Password</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios'
const store = useStore()
const form  = reactive({ name: '', email: '' })
const pwd   = reactive({ current_password: '', password: '', password_confirmation: '' })
const profileMsg = ref(''); const profileLoading = ref(false)
const pwdMsg = ref(''); const pwdError = ref(false); const pwdLoading = ref(false)

onMounted(async () => {
  const { data } = await axios.get('/profile')
  form.name  = data.name
  form.email = data.email
})

const updateProfile = async () => {
  profileLoading.value = true
  try {
    const { data } = await axios.put('/profile', form)
    store.commit('SET_AUTH', { user: data, token: localStorage.getItem('token') })
    profileMsg.value = 'Profile updated!'
    setTimeout(() => profileMsg.value = '', 3000)
  } catch(e) { profileMsg.value = 'Update failed' }
  finally { profileLoading.value = false }
}

const changePassword = async () => {
  pwdLoading.value = true; pwdError.value = false
  try {
    await axios.put('/change-password', pwd)
    pwdMsg.value = 'Password changed successfully!'
    Object.assign(pwd, { current_password: '', password: '', password_confirmation: '' })
  } catch(e) { pwdError.value = true; pwdMsg.value = e.response?.data?.message || 'Failed' }
  finally { pwdLoading.value = false }
}
</script>
