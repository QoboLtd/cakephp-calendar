import Vue from 'vue'
import CalendarLink from './components/CalendarLink.vue'
import CalendarItem from './components/CalendarItem.vue'
import CalendarModal from './components/CalendarModal.vue'
import Calendar from './components/Calendar.vue'
import $ from 'jquery'

new Vue({
  el: '#qobo-calendar-app',
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
    public: null
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

      $.ajax({
        method: 'post',
        dataType: 'json',
        url: '/calendars/calendars/index'
      }).done(function (resp) {
        self.calendars = resp
      })
    },
    getPublicCalendars () {
      var self = this

      $.ajax({
        method: 'post',
        dataType: 'json',
        url: '/calendars/calendars/index',
        data: { public: self.public }
      }).done(function (resp) {
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
      var url = '/calendars/calendar-events/index'
      $.ajax({
        method: 'POST',
        dataType: 'json',
        url: url,
        data: {
          'calendar_id': id,
          'period': {
            'start_date': this.start,
            'end_date': this.end
          },
          'timezone': this.timezone
        }
      }).then(function (response) {
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
      var url = '/calendars/calendar-events/view'
      var post = {
        id: calendarEvent.id,
        calendar_id: calendarEvent.calendar_id,
        event_type: calendarEvent.event_type
      }

      $.ajax({
        method: 'POST',
        url: url,
        data: post
      }).done(function (resp) {
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
        window.location.reload()
      }
    }
  }
})
