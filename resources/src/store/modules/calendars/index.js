import ApiService from '@/common/ApiService'
import events from '@/store/modules/events'

const initialState = {
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

export const state = Object.assign({}, initialState)

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

    state.data.forEach((element, key) => {
      if (element.id === payload) {
        index = key
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
  },
  addActiveId (state, payload) {
    state.activeIds.push(payload)
  },
  removeActiveId (state, payload) {
    const index = state.activeIds.indexOf(payload)
    state.activeIds.splice(index, 1)
  }
}

const getters = {
  activeIds: state => state.activeIds,
  data: state => state.data,
  options: (state) => state.options,
  getOption: (state) => (key) => {
    return state.options[key]
  },
  rangeChecksum: (state) => {
    const delimiter = '_'
    return state.options['start'] + delimiter + state.options['end']
  },
  getItemById: (state) => (id) => {
    return state.data.find(function (element) {
      if (element.id === id) {
        return element
      }
    })
  }
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
  },
  getEventTypes ({ commit, dispatch, state }, data) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/get-event-types.json', data)
        .then(response => {
          resolve(response)
        })
        .catch(() => reject)
    })
  },
  getEventTypeConfig ({ commit, dispatch, state }, data) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/event-type-config.json', data)
        .then(response => resolve(response))
        .catch(() => reject)
    })
  },
  getAttendees ({ commit, dispatch, state}, data) {
    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-attendees/lookup.json', data)
        .then(response => resolve(response))
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
