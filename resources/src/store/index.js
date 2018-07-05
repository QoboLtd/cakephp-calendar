import Vue from 'vue'
import Vuex from 'vuex'
import calendars from '@/store/modules/calendars'
import user from '@/store/modules/user'
import event from '@/store/modules/event'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
  modules: {
    calendars,
    user,
    event
  },
  strict: debug
})
