import Vue from 'vue'
import Vuex from 'vuex'
import calendars from '@/store/modules/calendars'
import user from '@/store/modules/user'
import event from '@/store/modules/event'

Vue.use(Vuex)

/**
 * @Note: had to disable strict mode for vuex, due
 * to vue-select multiple error.
 * @see https://github.com/sagalbot/vue-select/issues/529
 * */
const debug = false

export default new Vuex.Store({
  modules: {
    calendars,
    user,
    event
  },
  strict: debug
})
