import ApiService from '@/common/ApiService'
import events from '@/store/modules/events'

const state = {
  data: [],
  activeIds: [],
  options: {
    editable: false,
    end: null,
    start: null,
    timezone: null,
    public: false,
    printable: false
  }
}

const mutations = {
  setDataItems (state, payload) {
    state.data = payload
  },
  setOptions (state, payload) {
    state.options = payload
  },
  setOption (state, payload) {
    state.options[payload.key] = payload.value
  },
  addItem (state, payload) {
    state.data.push(payload)
  },
  removeItem (state, payload) {
    let index = null

    state.data.forEach( (element, key) => {
      if (element.id === payload) {
        index = key
        console.log(index)
      }
    })
    if (index) {
      state.data.splice(index, 1)
    }
  },
  setActiveIds (state, payload) {
    state.activeIds = payload
  },
  resetActiveIds (state, payload) {
    state.activeIds = []
  }
}

const getters = {
  data: state => state.data,
  options: (state) => { return state.options },
  getOption: (state) => (key) => {
    return state.options[key]
  },
}

const actions = {
  getData ({ commit, dispatch, state }, data) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendars/index.json', data)
        .then(response => {
          commit('setDataItems', response.data)
          resolve(response.data)
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
  mutations,
  modules: {
    events
  }
}
