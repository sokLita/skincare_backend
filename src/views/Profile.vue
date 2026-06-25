<template>
  <div class="container" style="padding:32px 20px; max-width:600px;">
    <h1 class="page-title">My Profile</h1>
    <div class="card card-body" style="margin-bottom:20px;">
      <h3 style="margin-bottom:16px;">Profile Information</h3>
      <div v-if="profileMsg" class="alert alert-success">{{ profileMsg }}</div>
      <div class="form-group"><label>Name</label><input v-model="form.name" class="form-control" /></div>
      <div class="form-group"><label>Email</label><input v-model="form.email" type="email" class="form-control" /></div>
      <button class="btn btn-primary" :disabled="profileLoading" @click="updateProfile">
        {{ profileLoading ? 'Saving...' : 'Save Changes' }}
      </button>
    </div>
    <div class="card card-body">
      <h3 style="margin-bottom:16px;">Change Password</h3>
      <div v-if="pwdMsg" class="alert" :class="pwdError ? 'alert-danger' : 'alert-success'">{{ pwdMsg }}</div>
      <div class="form-group"><label>Current Password</label><input v-model="pwd.current_password" type="password" class="form-control" /></div>
      <div class="form-group"><label>New Password</label><input v-model="pwd.password" type="password" class="form-control" /></div>
      <div class="form-group"><label>Confirm New Password</label><input v-model="pwd.password_confirmation" type="password" class="form-control" /></div>
      <button class="btn btn-primary" :disabled="pwdLoading" @click="changePassword">
        {{ pwdLoading ? 'Updating...' : 'Update Password' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useStore } from 'vuex'
import authService from '../services/auth'

const store          = useStore()
const form           = reactive({ name: '', email: '' })
const pwd            = reactive({ current_password: '', password: '', password_confirmation: '' })
const profileMsg     = ref('')
const profileLoading = ref(false)
const pwdMsg         = ref('')
const pwdError       = ref(false)
const pwdLoading     = ref(false)

onMounted(async () => {
  try {
    const { data } = await authService.getProfile()
    form.name  = data.name
    form.email = data.email
  } catch (error) {
    console.error('Error fetching profile:', error)
  }
})

const updateProfile = async () => {
  profileLoading.value = true
  try {
    const { data } = await authService.updateProfile(form)
    store.commit('SET_AUTH', { user: data, token: localStorage.getItem('token') })
    profileMsg.value = '✓ Profile updated!'
    setTimeout(() => profileMsg.value = '', 3000)
  } catch (e) {
    profileMsg.value = 'Update failed.'
  } finally { profileLoading.value = false }
}

const changePassword = async () => {
  pwdLoading.value = true
  pwdError.value   = false
  try {
    await authService.changePassword(pwd)
    pwdMsg.value = '✓ Password changed!'
    Object.assign(pwd, { current_password: '', password: '', password_confirmation: '' })
  } catch (e) {
    pwdError.value = true
    pwdMsg.value   = e.response?.data?.message || 'Failed.'
  } finally { pwdLoading.value = false }
}
</script>
