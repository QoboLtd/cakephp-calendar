import ApiService from '@/common/ApiService'

const initialState = {
  data: [],
  options: {
    background: false,
  }
}

export const state = Object.assign({}, initialState)

const mutations = {
  setOptions (state, payload) {
    state.options = payload
  },
  setOption (state, payload) {
    state.options[payload.key] = payload.value
  },
  addDataSource (state, payload) {
    let found = false
    state.data.forEach( (element, index) => {
      if (element.id == payload.id) {
        state.data[index] = payload
        found = true
      }
    })

    if (!found) {
      state.data.push(payload)
    }

    /**
     * re-indexing the array
     * @NOTE: otherwise it won't spot the change
     * */
    state.data = state.data.filter(function() { return true })
  },
  removeDataSource (state, payload) {
    let key = undefined
    state.data.forEach((element,index) => {
      if (element.id == payload.id) {
        key = index
      }
    })

    if (key !== undefined) {
      state.data.splice(key, 1)
    }
    /* re-indexing the array */
    state.data = state.data.filter(function() { return true })
  }
}

const getters = {
  data: state => state.data,
  options: state => state.options,
  getOption: (state) => (key) => {
    return state.options[key]
  }
}

const actions = {
  getData ({ state, commit, rootState, rootGetters }, args) {
    let data = {
      period: {
        start_date: rootGetters['calendars/getOption']('start'),
        end_date: rootGetters['calendars/getOption']('end')
      }
    }
    data = Object.assign(data, args)

    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/index.json', data)
        .then( response => {
          if (response.data.length) {
            let source = {
              id: data.calendar_id,
              events: response.data
            }
            commit('addDataSource', source)
            resolve(response.data)
          }
        })
        .catch(() => reject)
    })
  },
  getItemById ({ state, commit, rootState }, id) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/view.json', id)
        .then(response => {
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
  mutations
}
