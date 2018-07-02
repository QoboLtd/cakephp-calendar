import ApiService from '@/common/ApiService'

const state = {
  data: {},
  parentId: null,
  options: {

  }
}

const mutations = {
  setData (state, payload) {

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
  mutations
}
