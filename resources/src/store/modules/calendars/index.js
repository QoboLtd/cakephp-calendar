import ApiService from '@/common/ApiService'
import events from '@/store/modules/events'

const state = {
  data: {},
  options: {
    editable: false,
    end: null,
    start: null,
    timezone: null,
    printable: false
  }
}

const mutations = {
  setData (state, payload) {
    state.data = payload
  },
  setOptions (state, payload) {

  },
  setOption (state, payload) {

  },
  add (state, payload) {

  },
  remove (state, payload) {

  }
}

const getters = {
  data: (state) => { state.data },
  options: (state) => { state.options },
  getOption: (state) => (key) => {
    console.log(key)
  }
}

const actions = {
  getData (args) {

  }
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
  modules: {
    events
  }
}
