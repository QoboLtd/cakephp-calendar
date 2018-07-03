import ApiService from '@/common/ApiService'

const state = {
  data: {},
  parentId: null,
  options: {
    background: false,
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
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/index.json', args)
        .then( response => {
          console.log(response)
          resolve(response.data)
        })
        .catch(() => reject)
    })
  },
  getItemById (id) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/view', id)
        .then(response => {
          console.log(response)
          resolve(response)
        })
        .catch(() => reject)
    })
  }
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
