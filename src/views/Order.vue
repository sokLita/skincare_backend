<template>
  <div class="container" style="padding:32px 20px;">
    <h1 class="page-title">My Orders</h1>
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="orders.length === 0" class="alert alert-info">
      No orders yet. <router-link to="/products">Start shopping!</router-link>
    </div>
    <div v-else>
      <div v-for="order in orders" :key="order.id" class="card card-body" style="margin-bottom:14px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <div>
            <strong>Order #{{ order.id }}</strong>
            <span style="margin-left:10px; padding:3px 10px; border-radius:20px; font-size:.75rem; font-weight:600; color:#fff;"
              :style="{ background: order.status === 'completed' ? 'var(--success)' : order.status === 'pending' ? 'var(--secondary)' : 'var(--primary)' }">
              {{ order.status }}
            </span>
          </div>
          <div style="display:flex; align-items:center; gap:16px;">
            <strong>${{ Number(order.total_amount).toFixed(2) }}</strong>
            <span style="color:var(--text-light); font-size:.88rem;">{{ new Date(order.created_at).toLocaleDateString() }}</span>
            <router-link :to="`/orders/${order.id}`" class="btn btn-sm btn-primary">Details</router-link>
          </div>
        </div>
        <p style="color:var(--text-light); font-size:.88rem;">
          {{ order.items?.length }} item(s): {{ order.items?.map(i => i.product?.name).join(', ') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ordersService from '../services/orders'

const orders  = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await ordersService.getAll()
    orders.value  = data
  } catch (error) {
    console.error('Error fetching orders:', error)
  } finally {
    loading.value = false
  }
})
</script>
