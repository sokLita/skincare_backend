<template>
  <div class="container" style="padding: 32px 20px;">
    <div v-if="loading" class="loading-center"><div class="spinner"></div></div>
    <div v-else-if="product">
      <div class="detail-layout">
        <div>
          <img :src="product.image_url || 'https://via.placeholder.com/500x400?text=No+Image'"
            :alt="product.name" style="width:100%; border-radius:12px; box-shadow:var(--shadow-lg);" />
        </div>
        <div>
          <p style="color:var(--primary); font-weight:600; text-transform:uppercase; font-size:.85rem;">{{ product.category?.name }}</p>
          <h1 style="font-size:2rem; margin:8px 0;">{{ product.name }}</h1>
          <div style="font-size:2rem; font-weight:800; color:var(--primary); margin:12px 0;">${{ product.price }}</div>
          <p style="color:var(--text-light); line-height:1.7; margin-bottom:16px;">{{ product.description }}</p>
          <p :style="{ color: product.stock > 0 ? 'var(--success)' : 'var(--danger)', fontWeight: 600 }">
            {{ product.stock > 0 ? `✓ In Stock (${product.stock} left)` : '✗ Out of Stock' }}
          </p>
          <div style="display:flex; align-items:center; gap:12px; margin:16px 0;">
            <label>Qty:</label>
            <input type="number" v-model="qty" min="1" :max="product.stock" class="form-control" style="width:80px" />
          </div>
          <div style="display:flex; gap:12px;">
            <button class="btn btn-primary btn-lg" :disabled="!product.stock" @click="addToCart">🛒 Add to Cart</button>
            <button class="btn btn-outline btn-lg" @click="toggleWishlist">{{ inWishlist ? '❤️ Wishlisted' : '♡ Wishlist' }}</button>
          </div>
          <div v-if="cartMsg" class="alert alert-success" style="margin-top:12px">{{ cartMsg }}</div>
        </div>
      </div>

      <h2 style="font-size:1.4rem; margin:32px 0 20px;">Customer Reviews</h2>
      <div v-if="isAuth" class="card card-body" style="margin-bottom:20px;">
        <h4 style="margin-bottom:12px">Write a Review</h4>
        <div class="form-group">
          <label>Rating</label>
          <div>
            <span v-for="s in 5" :key="s" @click="review.rating = s"
              style="font-size:1.5rem; cursor:pointer;"
              :style="{ color: s <= review.rating ? '#f59e0b' : '#d1d5db' }">★</span>
          </div>
        </div>
        <div class="form-group">
          <label>Comment</label>
          <textarea v-model="review.comment" class="form-control" rows="3"></textarea>
        </div>
        <button class="btn btn-primary" @click="submitReview">Submit Review</button>
      </div>
      <p v-if="reviews.length === 0" class="alert alert-info">No reviews yet.</p>
      <div v-for="r in reviews" :key="r.id" class="card card-body" style="margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
          <strong>{{ r.user?.name }}</strong>
          <span style="color:#f59e0b;">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</span>
          <span style="color:var(--text-light); font-size:.85rem; margin-left:auto;">{{ new Date(r.created_at).toLocaleDateString() }}</span>
        </div>
        <p style="color:var(--text-light);">{{ r.comment }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import productsService from '../services/products'
import cartService from '../services/cart'
import wishlistService from '../services/wishlist'

const route      = useRoute()
const router     = useRouter()
const store      = useStore()
const product    = ref(null)
const reviews    = ref([])
const loading    = ref(true)
const qty        = ref(1)
const cartMsg    = ref('')
const inWishlist = ref(false)
const isAuth     = computed(() => store.getters.isAuthenticated)
const review     = reactive({ rating: 5, comment: '' })

onMounted(async () => {
  try {
    const [pd, rv] = await Promise.all([
      productsService.getById(route.params.id),
      productsService.getReviews(route.params.id),
    ])
    product.value = pd.data
    reviews.value = rv.data
    loading.value = false
    if (isAuth.value) {
      const wl = await wishlistService.getAll()
      inWishlist.value = wl.data.some(w => w.product_id == route.params.id)
    }
  } catch (error) {
    console.error('Error fetching product:', error)
    loading.value = false
  }
})

const addToCart = async () => {
  if (!isAuth.value) return router.push('/login')
  await cartService.addToCart(product.value.id, qty.value)
  store.dispatch('fetchCartCount')
  cartMsg.value = 'Added to cart!'
  setTimeout(() => cartMsg.value = '', 2000)
}

const toggleWishlist = async () => {
  if (!isAuth.value) return router.push('/login')
  if (inWishlist.value) {
    await wishlistService.removeFromWishlist(product.value.id)
    inWishlist.value = false
  } else {
    await wishlistService.addToWishlist(product.value.id)
    inWishlist.value = true
  }
}

const submitReview = async () => {
  await productsService.addReview(product.value.id, review)
  const rv = await productsService.getReviews(product.value.id)
  reviews.value  = rv.data
  review.comment = ''
}
</script>

<style scoped>
.detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 32px; }
@media(max-width:768px) { .detail-layout { grid-template-columns: 1fr; } }
</style>