<template>
  <div class="auth-page">
    <div class="card" style="width:100%; max-width:400px;">
      <div class="card-body">
        <h2 style="text-align:center; font-size:1.6rem; font-weight:700; margin-bottom:24px;">Welcome Back</h2>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="you@example.com" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="Your password" />
        </div>
        <button class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;"
          :disabled="loading" @click="handleLogin">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
        <p style="text-align:center; margin-top:16px; color:var(--text-light);">
          No account? <router-link to="/register" style="color:var(--primary)">Register here</router-link>
        </p>
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
  loading.value = true
  error.value   = ''
  try {
    await store.dispatch('login', form)
    store.dispatch('fetchCartCount')
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; }
</style>