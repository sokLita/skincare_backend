<template>
  <div class="product-card" @click="$router.push(`/products/${product.id}`)">
    <div class="product-img-wrap">
      <img 
        :src="product.image_url || 'https://via.placeholder.com/280x200?text=No+Image'" 
        :alt="product.name"
        loading="lazy"
      />
    </div>
    <div class="product-info">
      <p class="product-category">{{ product.category?.name }}</p>
      <h3 class="product-name">{{ product.name }}</h3>
      <div class="product-footer">
        <span class="product-price">${{ product.price }}</span>
        <button class="btn btn-primary btn-sm" @click.stop="addToCart">+ Cart</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import api from '../services/api'

const props  = defineProps({
  product: {
    type: Object,
    required: true
  }
})
const store  = useStore()
const router = useRouter()

const addToCart = async () => {
  if (!store.getters.isAuthenticated) return router.push('/login')
  await api.post('/cart', { product_id: props.product.id, quantity: 1 })
  store.dispatch('fetchCartCount')
}
</script>

<style scoped>
.product-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; cursor: pointer; transition: transform .2s, box-shadow .2s; }
.product-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.product-img-wrap { height: 200px; overflow: hidden; }
.product-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.product-info { padding: 14px; }
.product-category { font-size: .78rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
.product-name { font-size: 1rem; font-weight: 600; margin-bottom: 10px; }
.product-footer { display: flex; justify-content: space-between; align-items: center; }
.product-price { font-size: 1.15rem; font-weight: 700; color: var(--primary); }
</style>