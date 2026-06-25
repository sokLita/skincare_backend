<template>
  <div class="auth-page">
    <div class="card" style="width:100%; max-width:420px;">
      <div class="card-body">
        <h2 style="text-align:center; font-size:1.6rem; font-weight:700; margin-bottom:24px;">Create Account</h2>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <div class="form-group">
          <label>Name</label>
          <input v-model="form.name" class="form-control" placeholder="Your full name" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="you@example.com" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="Min. 6 characters" />
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" class="form-control" placeholder="Repeat password" />
        </div>
        <button class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;"
          :disabled="loading" @click="handleRegister">
          {{ loading ? 'Creating...' : 'Create Account' }}
        </button>
        <p style="text-align:center; margin-top:16px; color:var(--text-light);">
          Already have an account? <router-link to="/login" style="color:var(--primary)">Login</router-link>
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
const form    = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error   = ref('')

const handleRegister = async () => {
  loading.value = true
  error.value   = ''
  try {
    await store.dispatch('register', form)
    router.push('/')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value  = errors ? Object.values(errors)[0][0] : (e.response?.data?.message || 'Registration failed.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px; }
</style>