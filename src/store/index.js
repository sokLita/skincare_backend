import { createStore } from 'vuex'
import authService from '../services/auth'

export default createStore({
  state: {
    user:      JSON.parse(localStorage.getItem('user')) || null,
    token:     localStorage.getItem('token')            || null,
    cartCount: 0,
  },
  getters: {
    isAuthenticated: state => !!state.token,
    currentUser:     state => state.user,
    cartCount:       state => state.cartCount,
  },
  mutations: {
    SET_AUTH(state, { user, token }) {
      state.user  = user
      state.token = token
      localStorage.setItem('user',  JSON.stringify(user))
      localStorage.setItem('token', token)
    },
    LOGOUT(state) {
      state.user  = null
      state.token = null
      localStorage.removeItem('user')
      localStorage.removeItem('token')
    },
    SET_CART_COUNT(state, count) { state.cartCount = count },
  },
  actions: {
    async login({ commit }, credentials) {
      const { data } = await authService.login(credentials)
      commit('SET_AUTH', data)
      return data
    },
    async register({ commit }, payload) {
      const { data } = await authService.register(payload)
      commit('SET_AUTH', data)
      return data
    },
    async logout({ commit }) {
      await authService.logout().catch(() => {})
      commit('LOGOUT')
    },
    async fetchCartCount({ commit, getters }) {
      if (!getters.isAuthenticated) return
      const { data } = await authService.getProfile()
      commit('SET_CART_COUNT', data.cart_count || 0)
    },
  },
})
