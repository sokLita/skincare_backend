<template>
  <div class="container" style="padding:32px 20px; max-width:800px;">
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <template v-else-if="order">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title" style="margin-bottom:0;">Order #{{ order.id }}</h1>
        <router-link to="/orders" class="btn btn-secondary">← Back</router-link>
      </div>
      <div style="display:flex; margin-bottom:24px; border-radius:var(--radius); overflow:hidden;">
        <div v-for="s in ['pending','processing','completed']" :key="s" style="flex:1; text-align:center; padding:10px; font-size:.85rem; font-weight:600; text-transform:capitalize;"
          :style="{ background: s === order.status ? 'var(--primary)' : isDone(s) ? '#d1fae5' : 'var(--border)', color: s === order.status ? '#fff' : isDone(s) ? '#065f46' : 'var(--text)' }">
          {{ s }}
        </div>
      </div>
      <div class="card card-body" style="margin-bottom:16px;">
        <h3 style="margin-bottom:16px;">Items</h3>
        <div v-for="item in order.items" :key="item.id"
          style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
          <span>{{ item.product?.name }} × {{ item.quantity }}</span>
          <span>${{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-weight:700; padding-top:12px;">
          <span>Total</span><span>${{ Number(order.total_amount).toFixed(2) }}</span>
        </div>
      </div>
      <div class="card card-body">
        <h3>Shipping Address</h3>
        <p style="margin-top:8px; color:var(--text-light);">{{ order.shipping_address }}</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ordersService from '../services/orders'

const route   = useRoute()
const order   = ref(null)
const loading = ref(true)
const statusOrder = ['pending', 'processing', 'completed', 'cancelled']

onMounted(async () => {
  try {
    const { data } = await ordersService.getById(route.params.id)
    order.value   = data
  } catch (error) {
    console.error('Error fetching order:', error)
  } finally {
    loading.value = false
  }
})

const isDone = (s) => statusOrder.indexOf(s) < statusOrder.indexOf(order.value?.status)
</script>
