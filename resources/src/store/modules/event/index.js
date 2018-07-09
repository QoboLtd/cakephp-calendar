import Vue from 'vue'
import ApiService from '@/common/ApiService'

const initialState = {
  calendar: null,
  eventType: null,
  start: null,
  end: null,
  recurrence: null,
  attendeesIds: [],
  title: null,
  content: null
}

export const state = Object.assign({}, initialState)

const mutations = {
  setCalendar (state, payload) {
    state.calendar = payload
  },
  setEventType (state, payload) {
    state.eventType = payload
  },
  setStart (state, payload) {
    state.start = payload
  },
  setEnd (state, payload) {
    state.end = payload
  },
  setAttendeesIds (state, payload) {
    state.attendeesIds = payload
  },
  setTitle (state, payload) {
    state.title = payload
  },
  setContent (state, payload) {
    state.content = payload
  },
  setRecurrence (state, payload) {
    state.recurrence = payload
  },
  resetState () {
    for (let f in state) {
      Vue.set(state, f, initialState[f])
    }
  }
}

const getters = {
  attendeesIds: state => state.attendeesIds,
  calendar: state => state.calendar,
  eventType: state => state.eventType,
  start: state => state.start,
  end: state => state.end,
  recurrence: state => state.recurrence,
  title: state => state.title,
  content: state => state.content
}

const actions = {
  addCalendarEvent({ commit, dispatch, state }) {

    let args = {
      calendar_id: state.calendar.id,
      event_type: state.eventType.id,
      start_date: state.start,
      end_date: state.end,
      recurrence: state.recurrence,
      title: state.title,
      content: state.content,
      attendees_ids: []
    }

    args.attendees_ids = state.attendeesIds.map(item => item.id)

    return new Promise((resolve, reject) => {
      ApiService
        .post('/calendars/calendar-events/add.json', args)
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
  mutations
}
