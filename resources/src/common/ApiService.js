import Vue from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import { API_URL } from '@/common/config'

export default {
  init () {
    Vue.use(VueAxios, axios)
    Vue.axios.defaults.baseURL = API_URL
  },

  setHeader (token) {
    Vue.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  },

  post (resource, params) {
    return Vue.axios.post(`${resource}`, params)
  },

  get (resource, slug = '') {
    return Vue.axios
      .get(`${resource}/${slug}`)
      .catch((error) => {
        throw new Error(`[ApiService] ${error}`)
      })
  }

}
