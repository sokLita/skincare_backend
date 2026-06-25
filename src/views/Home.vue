<template>
  <div>
    <section class="hero">
      <div class="container">
        <h1>Welcome to ShopVue</h1>
        <p>Discover amazing products at great prices</p>
        <router-link to="/products" class="btn btn-lg" style="background:#fff; color:var(--primary)">Shop Now →</router-link>
      </div>
    </section>
    <div class="container" style="padding: 40px 20px;">
      <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else class="product-grid">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
      <div v-if="!loading && products.length === 0" class="text-center py-12 text-gray-500">
        <i class="fas fa-box-open text-5xl mb-3 text-gray-300"></i>
        <p class="text-lg">No products found</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ProductCard from '../components/ProductCard.vue'
import productsService from '../services/products'
import categoriesService from '../services/categories'

const products   = ref([])
const categories = ref([])
const loading    = ref(true)

onMounted(async () => {
  try {
    const [p, c] = await Promise.all([
      productsService.getAll({ per_page: 8 }),
      categoriesService.getAll()
    ])
    products.value   = p.data.data
    categories.value = c.data
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.hero { background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%); color: white; padding: 80px 0; text-align: center; }
.hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 16px; }
.hero p { font-size: 1.2rem; opacity: .85; margin-bottom: 28px; }
.section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 20px; }
.category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
.category-card { background: white; border-radius: var(--radius); padding: 20px; text-align: center; cursor: pointer; box-shadow: var(--shadow); border-top: 3px solid var(--primary); transition: transform .2s; }
.category-card:hover { transform: translateY(-3px); }
.category-card h3 { font-weight: 600; margin-bottom: 4px; }
.category-card p { color: var(--text-light); font-size: .85rem; }
</style>