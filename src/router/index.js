import { createRouter, createWebHistory } from 'vue-router'
import store from '../store'

const routes = [
  { path: '/',             component: () => import('../views/Home.vue') },
  { path: '/products',     component: () => import('../views/Products.vue') },
  { path: '/products/:id', component: () => import('../views/ProductDetail.vue') },
  { path: '/login',        component: () => import('../views/Login.vue'),      meta: { guest: true } },
  { path: '/register',     component: () => import('../views/Register.vue'),   meta: { guest: true } },
  { path: '/wishlist',     component: () => import('../views/Wishlist.vue'),   meta: { auth: true } },
  { path: '/cart',         component: () => import('../views/Cart.vue'),       meta: { auth: true } },
  { path: '/checkout',     component: () => import('../views/Checkout.vue'),   meta: { auth: true } },
  { path: '/orders',       component: () => import('../views/Order.vue'),     meta: { auth: true } },
  { path: '/orders/:id',   component: () => import('../views/OrderDetail.vue'),meta: { auth: true } },
  { path: '/profile',      component: () => import('../views/Profile.vue'),    meta: { auth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const isAuth = store.getters.isAuthenticated
  if (to.meta.auth && !isAuth) return next('/login')
  if (to.meta.guest && isAuth) return next('/')
  next()
})

export default router