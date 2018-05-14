import Vue from 'vue'
import ajaxMixin from './mixins/ajaxMixin'
import CalendarLink from './components/CalendarLink.vue'
import CalendarItem from './components/CalendarItem.vue'
import CalendarModal from './components/CalendarModal.vue'
import Calendar from './components/Calendar.vue'
import $ from 'jquery'

new Vue({
  el: '#qobo-calendar-app',
  mixins: [ajaxMixin],
  components: {
    'calendar': Calendar,
    'calendar-item': CalendarItem,
    'calendar-link': CalendarLink,
    'calendar-modal': CalendarModal
  },
  data: {
    ids: [],
    events: [],
    calendars: [],
    calendarsList: [],
    editable: false,
    start: null,
    end: null,
    timezone: null,
    eventClick: null,
    public: null,
    apiToken:null
  },
  computed: {
    isIntervalChanged: function () {
      return [this.start, this.end].join('')
    }
  },
  watch: {
    calendars: function () {
      var self = this
      this.calendarsList = []

      if (this.calendars) {
        this.calendars.forEach((elem, key) => {
          if (elem.permissions.edit && elem.editable != false) {
            self.calendarsList.push({ value: elem.id, label: elem.name })
          }
        })
      }
    },
    isIntervalChanged: function () {
      var self = this

      if (this.ids.length) {
        self.events = []
        this.ids.forEach(function (calendarId, key) {
          self.getEvents(calendarId)
        })
      }
    }
  },
  beforeMount () {
    this.start = this.$el.attributes.start.value
    this.end = this.$el.attributes.end.value
    this.timezone = this.$el.attributes.timezone.value
    this.apiToken = this.$el.attributes.token.value

    if (this.$el.attributes.public) {
      this.public = this.$el.attributes.public.value
    }

    if (this.public == 'true') {
      this.getPublicCalendars()
    } else {
      this.getCalendars()
    }
  },
  methods: {
    updateStartEnd (start, end) {
      this.start = start
      this.end = end
    },
    getCalendars () {
      var self = this
      this.apiGetCalendars().then(function (response) {
        self.calendars = response
      })
    },
    getPublicCalendars () {
      var self = this

      this.apiGetPublicCalendars().then(function (resp) {
        self.calendars = resp
        if (self.calendars) {
          self.calendars.forEach(function (elem, key) {
            if (elem.active == true && elem.is_public == true) {
              self.ids.push(elem.id)
              self.getEvents(elem.id)
            }
          })
        }
      })
    },
    getEvents (id) {
      var self = this
      this.apiGetEvents(id).then( function (response) {
        if (!response) {
          return
        }

        let eventIds = self.events.map(element => element.id)

        response.forEach(function (element, index) {
          if (!eventIds.includes(element.id)) {
            self.events.push(element)
          }
        })
      })
    },
    removeEvents (id) {
      this.events = this.events.filter(function (item) {
        if (item.calendar_id !== id) {
          return item
        }
      })
    },
    updateCalendarIds (state, id) {
      var self = this
      var found = false

      this.ids.forEach(function (elem, key) {
        if (elem == id) {
          if (state === false) {
            self.ids.splice(key, 1)
            self.removeEvents(id)
          } else {
            found = true
          }
        }
      })

      if (state === true && !found) {
        this.ids.push(id)
        this.getEvents(id)
      }
    },
    getEventInfo (calendarEvent) {
      this.apiGetEventInfo(calendarEvent).then(function (response) {
        if (response) {
          $('#calendar-modal-view-event').find('.modal-content').empty()
          $('#calendar-modal-view-event').find('.modal-content').append(response)
          $('#calendar-modal-view-event').modal('toggle')
        }
      })
    },
    addCalendarEvent (date, event, view) {
      this.eventClick = date
      $('#calendar-modal-add-event').modal('toggle')
    },
    addEventToResources (event) {
      if (event) {
        this.events.push(event)
        /* @NOTE: remove reload and just clear up modals */
        window.location.reload()
      }
    }
  }
})
