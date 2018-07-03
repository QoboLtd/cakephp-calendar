import Vue from 'vue'
import ajaxMixin from './mixins/ajaxMixin'
import Calendar from '@/components/Calendar.vue'
import $ from 'jquery'

import store from '@/store'
import { mapActions, mapGetters } from 'vuex'
import ApiService from '@/common/ApiService'
import Notifications from 'vue-notification'
import Sidebar from '@/components/Sidebar.vue'

Vue.use(Notifications)

ApiService.init()

new Vue({
  el: '#qobo-calendar-app',
  store,
  mixins: [ajaxMixin],
  components: {
    Calendar,
    Sidebar
  },
  data: {
    ids: [],
    events: [],
    calendarsList: [],
    eventClick: null,
    apiToken: null
  },
  computed: {
    ...mapGetters({
      calendars: 'calendars/data'
    }),
    start () {
      return this.$store.getters['calendars/getOption']('start')
    },
    end () {
      return this.$store.getters['calendars/getOption']('end')
    },
    editable () {
      return this.$store.getters['calendars/getOption']('editable')
    },
    timezone () {
      return this.$store.getters['calendars/getOption']('timezone')
    },
    public () {
      return this.$store.getters['calendars/getOption']('public')
    },
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
          if (elem.permissions.edit && elem.editable !== false) {
            self.calendarsList.push({ value: elem.id, label: elem.name })
          }
        })
      }
    },
    isIntervalChanged: function () {
      const self = this
      if (this.ids.length) {
        self.events = []
        this.ids.forEach(function (calendarId, key) {
          self.getEvents(calendarId)
        })
      }
    }
  },
  beforeMount () {
    this.apiToken = this.$el.attributes.token.value

    this.$store.commit('calendars/setOption', { key: 'start', value: this.$el.attributes.start.value })
    this.$store.commit('calendars/setOption', { key: 'end', value: this.$el.attributes.end.value })
    this.$store.commit('calendars/setOption', { key: 'timezone', value: this.$el.attributes.timezone.value })

    ApiService.setHeader(this.apiToken)

    if (this.$el.attributes.public) {
      let isPublic = (this.$el.attributes.public.value == 'true')
      this.$store.commit('calendars/setOption', { key: 'public', value: isPublic })
    }

    this.getCalendars({ public: this.public })
  },
  methods: {
    ...mapActions({
      getCalendars: 'calendars/getData',
    }),
    getEvents (id) {
      let args = {
        calendar_id: id,
        start: this.start,
        end: this.end
      }

      this.$store.dispatch('calendars/events/getData', args)
    },
    updateStartEnd (start, end) {
      this.$store.commit('calendars/setOption', { key: 'start', value: start })
      this.$store.commit('calendars/setOption', { key: 'end', value: end })
    },

    removeEvents (id) {
      this.events = this.events.filter(function (item) {
        if (item.calendar_id !== id) {
          return item
        }
      })
    },
    updateCalendarIds (state, id) {
      console.log('update calendar ids')
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
      console.log('add event to resources')
    }
  }
})
