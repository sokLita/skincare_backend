<!-- FILE: src/components/Navbar.vue -->
<template>
  <nav class="navbar">
    <div class="container nav-inner">
      <router-link to="/" class="nav-brand">🛒 ShopVue</router-link>
      <div class="nav-links">
        <router-link to="/">Home</router-link>
        <router-link to="/products">Products</router-link>
        <template v-if="isAuth">
          <router-link to="/wishlist">❤️ Wishlist</router-link>
          <router-link to="/cart" class="cart-link">
            🛒 Cart <span v-if="cartCount" class="cart-badge">{{ cartCount }}</span>
          </router-link>
          <router-link to="/orders">Orders</router-link>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline">{{ user?.name }} ▾</button>
            <div class="dropdown-menu">
              <router-link to="/profile">Profile</router-link>
              <a href="#" @click.prevent="handleLogout">Logout</a>
            </div>
          </div>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-outline btn-sm">Login</router-link>
          <router-link to="/register" class="btn btn-primary btn-sm">Register</router-link>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
const store  = useStore()
const router = useRouter()
const isAuth    = computed(() => store.getters.isAuthenticated)
const user      = computed(() => store.getters.currentUser)
const cartCount = computed(() => store.getters.cartCount)
const handleLogout = async () => { await store.dispatch('logout'); router.push('/login') }
</script>

<style scoped>
.navbar { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.1); position: sticky; top: 0; z-index: 100; }
.nav-inner { display: flex; align-items: center; justify-content: space-between; height: 64px; }
.nav-brand { font-size: 1.4rem; font-weight: 700; color: var(--primary); text-decoration: none; }
.nav-links { display: flex; align-items: center; gap: 16px; }
.nav-links a { color: var(--text); text-decoration: none; font-weight: 500; transition: color .2s; }
.nav-links a:hover, .nav-links a.router-link-active { color: var(--primary); }
.cart-link { position: relative; }
.cart-badge { background: var(--danger); color: #fff; border-radius: 50%; padding: 1px 6px; font-size: .7rem; font-weight: 700; margin-left: 2px; }
.dropdown { position: relative; }
.dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background: white; box-shadow: var(--shadow-lg); border-radius: var(--radius); min-width: 140px; padding: 8px 0; }
.dropdown:hover .dropdown-menu { display: block; }
.dropdown-menu a { display: block; padding: 8px 16px; color: var(--text); text-decoration: none; }
.dropdown-menu a:hover { background: var(--bg); }
</style>


<!-- FILE: src/components/Footer.vue -->
<template>
  <footer class="footer">
    <div class="container">
      <p>© 2024 ShopVue. Built with Laravel + Vue.js · Teacher YEN YON Project</p>
    </div>
  </footer>
</template>

<style scoped>
.footer { background: #1a1a2e; color: #adb5bd; text-align: center; padding: 24px 0; margin-top: 48px; }
</style>


<!-- FILE: src/components/ProductCard.vue -->
<template>
  <div class="product-card" @click="$router.push(`/products/${product.id}`)">
    <div class="product-img-wrap">
      <img :src="product.image_url || 'https://via.placeholder.com/280x200?text=No+Image'" :alt="product.name">
    </div>
    <div class="product-info">
      <p class="product-category">{{ product.category?.name }}</p>
      <h3 class="product-name">{{ product.name }}</h3>
      <div class="product-footer">
        <span class="product-price">${{ product.price }}</span>
        <button class="btn btn-primary btn-sm" @click.stop="addToCart">Add to Cart</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
const props  = defineProps(['product'])
const store  = useStore()
const router = useRouter()
const addToCart = async () => {
  if (!store.getters.isAuthenticated) return router.push('/login')
  await axios.post('/cart', { product_id: props.product.id, quantity: 1 })
  store.dispatch('fetchCartCount')
}
</script>

<style scoped>
.product-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; cursor: pointer; transition: transform .2s, box-shadow .2s; }
.product-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.product-img-wrap { height: 200px; overflow: hidden; }
.product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.product-card:hover .product-img-wrap img { transform: scale(1.05); }
.product-info { padding: 14px; }
.product-category { font-size: .78rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
.product-name { font-size: 1rem; font-weight: 600; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-footer { display: flex; justify-content: space-between; align-items: center; }
.product-price { font-size: 1.15rem; font-weight: 700; color: var(--primary); }
</style>
