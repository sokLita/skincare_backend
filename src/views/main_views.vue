<!-- ============================================================ -->
<!-- FILE: src/views/Home.vue -->
<!-- ============================================================ -->
<template>
  <div>
    <!-- Hero -->
    <section class="hero">
      <div class="container">
        <h1>Welcome to ShopVue</h1>
        <p>Discover amazing products at great prices</p>
        <router-link to="/products" class="btn btn-primary btn-lg">Shop Now →</router-link>
      </div>
    </section>

    <!-- Categories -->
    <section class="container" style="margin-top:40px">
      <h2 class="section-title">Shop by Category</h2>
      <div class="category-grid">
        <div v-for="cat in categories" :key="cat.id" class="category-card"
             @click="$router.push(`/products?category=${cat.id}`)">
          <h3>{{ cat.name }}</h3>
          <p>{{ cat.products_count }} products</p>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="container" style="margin-top:40px; padding-bottom:48px">
      <h2 class="section-title">Featured Products</h2>
      <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
      <div v-else class="product-grid">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
      <div class="text-center" style="margin-top:28px">
        <router-link to="/products" class="btn btn-outline">View All Products</router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ProductCard from '../components/ProductCard.vue'
const products   = ref([])
const categories = ref([])
const loading    = ref(true)
onMounted(async () => {
  const [p, c] = await Promise.all([axios.get('/products?per_page=8'), axios.get('/categories')])
  products.value   = p.data.data
  categories.value = c.data
  loading.value    = false
})
</script>

<style scoped>
.hero { background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%); color: white; padding: 80px 0; text-align: center; }
.hero h1 { font-size: 3rem; font-weight: 800; margin-bottom: 16px; }
.hero p  { font-size: 1.2rem; opacity: .85; margin-bottom: 28px; }
.section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 24px; }
.category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
.category-card { background: white; border-radius: var(--radius); padding: 20px; text-align: center; cursor: pointer; box-shadow: var(--shadow); transition: transform .2s; border-top: 3px solid var(--primary); }
.category-card:hover { transform: translateY(-3px); }
.category-card h3 { font-weight: 600; margin-bottom: 4px; }
.category-card p  { color: var(--text-light); font-size: .85rem; }
.text-center { text-align: center; }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Products.vue -->
<!-- ============================================================ -->
<template>
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">All Products</h1>
    </div>
    <div class="products-layout">
      <!-- Sidebar filter -->
      <aside class="filters">
        <h3>Filters</h3>
        <div class="filter-group">
          <label>Category</label>
          <select v-model="filters.category_id" class="form-select" @change="fetchProducts(1)">
            <option value="">All Categories</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Search</label>
          <input v-model="filters.search" class="form-control" placeholder="Search..." @input="debounceSearch">
        </div>
        <div class="filter-group">
          <label>Min Price</label>
          <input v-model="filters.min_price" type="number" class="form-control" @change="fetchProducts(1)">
        </div>
        <div class="filter-group">
          <label>Max Price</label>
          <input v-model="filters.max_price" type="number" class="form-control" @change="fetchProducts(1)">
        </div>
        <button class="btn btn-secondary btn-sm w-100" @click="clearFilters">Clear Filters</button>
      </aside>

      <!-- Products grid -->
      <div class="products-main">
        <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
        <template v-else>
          <p class="results-count">{{ pagination.total }} products found</p>
          <div class="product-grid">
            <ProductCard v-for="p in products" :key="p.id" :product="p" />
          </div>
          <!-- Pagination -->
          <div class="pagination" v-if="pagination.last_page > 1">
            <button v-for="p in pagination.last_page" :key="p" class="page-btn"
                    :class="{ active: p === pagination.current_page }" @click="fetchProducts(p)">{{ p }}</button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import axios from 'axios'
import ProductCard from '../components/ProductCard.vue'
const products   = ref([])
const categories = ref([])
const loading    = ref(true)
const filters    = reactive({ category_id: '', search: '', min_price: '', max_price: '' })
const pagination = ref({})
let searchTimer  = null

const fetchProducts = async (page = 1) => {
  loading.value = true
  const params  = { page, ...filters }
  Object.keys(params).forEach(k => !params[k] && delete params[k])
  const { data } = await axios.get('/products', { params })
  products.value   = data.data
  pagination.value = { total: data.total, last_page: data.last_page, current_page: data.current_page }
  loading.value    = false
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchProducts(1), 400)
}

const clearFilters = () => { Object.assign(filters, { category_id: '', search: '', min_price: '', max_price: '' }); fetchProducts(1) }

onMounted(async () => {
  const { data } = await axios.get('/categories')
  categories.value = data
  fetchProducts()
})
</script>

<style scoped>
.products-layout { display: grid; grid-template-columns: 220px 1fr; gap: 28px; }
.filters { background: white; border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow); height: fit-content; position: sticky; top: 80px; }
.filters h3 { font-size: 1rem; font-weight: 700; margin-bottom: 16px; }
.filter-group { margin-bottom: 16px; }
.filter-group label { display: block; font-size: .85rem; font-weight: 500; margin-bottom: 5px; color: var(--text-light); }
.w-100 { width: 100%; }
.results-count { color: var(--text-light); font-size: .9rem; margin-bottom: 16px; }
@media(max-width:768px){ .products-layout { grid-template-columns: 1fr; } .filters { position: static; } }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/ProductDetail.vue -->
<!-- ============================================================ -->
<template>
  <div class="container" style="padding: 32px 20px">
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="product" class="detail-layout">
      <!-- Image -->
      <div class="detail-img">
        <img :src="product.image_url || 'https://via.placeholder.com/500x400?text=No+Image'" :alt="product.name">
      </div>
      <!-- Info -->
      <div class="detail-info">
        <p class="detail-cat">{{ product.category?.name }}</p>
        <h1>{{ product.name }}</h1>
        <div class="detail-price">${{ product.price }}</div>
        <p class="detail-desc">{{ product.description }}</p>
        <p class="stock-info" :class="product.stock > 0 ? 'in-stock' : 'out-stock'">
          {{ product.stock > 0 ? `✓ In Stock (${product.stock} left)` : '✗ Out of Stock' }}
        </p>
        <div class="qty-row">
          <label>Qty:</label>
          <input type="number" v-model="qty" min="1" :max="product.stock" class="form-control qty-input">
        </div>
        <div class="detail-btns">
          <button class="btn btn-primary btn-lg" :disabled="!product.stock" @click="addToCart">🛒 Add to Cart</button>
          <button class="btn btn-outline btn-lg" @click="toggleWishlist">{{ inWishlist ? '❤️ Wishlisted' : '♡ Wishlist' }}</button>
        </div>
        <div v-if="cartMsg" class="alert alert-success mt-2">{{ cartMsg }}</div>
      </div>
    </div>

    <!-- Reviews -->
    <div class="reviews-section" v-if="product">
      <h2>Customer Reviews</h2>
      <div v-if="isAuth" class="review-form card card-body mb-4">
        <h4>Write a Review</h4>
        <div class="form-group">
          <label>Rating</label>
          <div class="star-picker">
            <span v-for="s in 5" :key="s" @click="review.rating = s" class="star" :class="{ active: s <= review.rating }">★</span>
          </div>
        </div>
        <div class="form-group">
          <label>Comment</label>
          <textarea v-model="review.comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
        </div>
        <button class="btn btn-primary" @click="submitReview">Submit Review</button>
      </div>
      <div v-if="reviews.length === 0" class="alert alert-info">No reviews yet. Be the first!</div>
      <div class="review-list">
        <div v-for="r in reviews" :key="r.id" class="review-card card card-body mb-3">
          <div class="review-header">
            <strong>{{ r.user?.name }}</strong>
            <span class="stars">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</span>
            <span class="review-date">{{ new Date(r.created_at).toLocaleDateString() }}</span>
          </div>
          <p style="margin-top:8px; color:var(--text-light)">{{ r.comment }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import axios from 'axios'
const route   = useRoute()
const store   = useStore()
const router  = useRouter()
const product    = ref(null)
const reviews    = ref([])
const loading    = ref(true)
const qty        = ref(1)
const cartMsg    = ref('')
const inWishlist = ref(false)
const isAuth     = computed(() => store.getters.isAuthenticated)
const review     = reactive({ rating: 5, comment: '' })

onMounted(async () => {
  const [pd, rv] = await Promise.all([
    axios.get(`/products/${route.params.id}`),
    axios.get(`/products/${route.params.id}/reviews`),
  ])
  product.value = pd.data
  reviews.value = rv.data
  loading.value = false
  if (isAuth.value) {
    const wl = await axios.get('/wishlist')
    inWishlist.value = wl.data.some(w => w.product_id == route.params.id)
  }
})

const addToCart = async () => {
  if (!isAuth.value) return router.push('/login')
  await axios.post('/cart', { product_id: product.value.id, quantity: qty.value })
  store.dispatch('fetchCartCount')
  cartMsg.value = 'Added to cart!'
  setTimeout(() => cartMsg.value = '', 2000)
}

const toggleWishlist = async () => {
  if (!isAuth.value) return router.push('/login')
  if (inWishlist.value) {
    await axios.delete(`/wishlist/${product.value.id}`)
    inWishlist.value = false
  } else {
    await axios.post('/wishlist', { product_id: product.value.id })
    inWishlist.value = true
  }
}

const submitReview = async () => {
  await axios.post(`/products/${product.value.id}/reviews`, review)
  const rv = await axios.get(`/products/${route.params.id}/reviews`)
  reviews.value = rv.data
  review.comment = ''
}
</script>

<style scoped>
.detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 48px; }
.detail-img img { width: 100%; border-radius: 12px; box-shadow: var(--shadow-lg); }
.detail-cat { color: var(--primary); font-weight: 600; text-transform: uppercase; font-size: .85rem; }
.detail-info h1 { font-size: 2rem; margin: 8px 0; }
.detail-price { font-size: 2rem; font-weight: 800; color: var(--primary); margin: 12px 0; }
.detail-desc { color: var(--text-light); line-height: 1.7; margin-bottom: 16px; }
.in-stock { color: var(--success); font-weight: 600; }
.out-stock { color: var(--danger); font-weight: 600; }
.qty-row { display: flex; align-items: center; gap: 12px; margin: 16px 0; }
.qty-input { width: 80px; }
.detail-btns { display: flex; gap: 12px; }
.reviews-section h2 { font-size: 1.4rem; margin-bottom: 20px; }
.review-header { display: flex; align-items: center; gap: 12px; }
.stars { color: #f59e0b; }
.review-date { color: var(--text-light); font-size: .85rem; margin-left: auto; }
.star-picker .star { font-size: 1.5rem; cursor: pointer; color: #d1d5db; }
.star-picker .star.active { color: #f59e0b; }
.mt-2 { margin-top: 12px; }
@media(max-width:768px){ .detail-layout { grid-template-columns: 1fr; } }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Login.vue -->
<!-- ============================================================ -->
<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card-body">
        <h2 class="auth-title">Welcome Back</h2>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="Your password">
        </div>
        <button class="btn btn-primary w-full" :disabled="loading" @click="handleLogin">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
        <p class="auth-switch">Don't have an account? <router-link to="/register">Register here</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
const store   = useStore()
const router  = useRouter()
const form    = reactive({ email: '', password: '' })
const loading = ref(false)
const error   = ref('')
const handleLogin = async () => {
  loading.value = true; error.value = ''
  try {
    await store.dispatch('login', form)
    store.dispatch('fetchCartCount')
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally { loading.value = false }
}
</script>

<style scoped>
.auth-page { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; }
.auth-card { width: 100%; max-width: 400px; }
.auth-title { text-align: center; font-size: 1.6rem; font-weight: 700; margin-bottom: 24px; }
.w-full { width: 100%; justify-content: center; padding: 12px; }
.auth-switch { text-align: center; margin-top: 16px; color: var(--text-light); }
.auth-switch a { color: var(--primary); }
</style>


<!-- ============================================================ -->
<!-- FILE: src/views/Register.vue -->
<!-- ============================================================ -->
<template>
  <div class="auth-page">
    <div class="auth-card card">
      <div class="card-body">
        <h2 class="auth-title">Create Account</h2>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Name</label>
          <input v-model="form.name" class="form-control" placeholder="Your full name">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="you@example.com">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="Min. 6 characters">
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" class="form-control" placeholder="Repeat password">
        </div>
        <button class="btn btn-primary w-full" :disabled="loading" @click="handleRegister">
          {{ loading ? 'Creating...' : 'Create Account' }}
        </button>
        <p class="auth-switch">Already have an account? <router-link to="/login">Login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
const store   = useStore()
const router  = useRouter()
const form    = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error   = ref('')
const handleRegister = async () => {
  loading.value = true; error.value = ''
  try { await store.dispatch('register', form); router.push('/') }
  catch (e) { error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Registration failed' }
  finally { loading.value = false }
}
</script>

<style scoped>
.auth-page { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; }
.auth-card { width: 100%; max-width: 420px; }
.auth-title { text-align: center; font-size: 1.6rem; font-weight: 700; margin-bottom: 24px; }
.w-full { width: 100%; justify-content: center; padding: 12px; }
.auth-switch { text-align: center; margin-top: 16px; color: var(--text-light); }
.auth-switch a { color: var(--primary); }
</style>
