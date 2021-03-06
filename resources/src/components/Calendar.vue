<template>
  <div>
    <div ref="calendar"></div>

    <modal
      :modal="modal"
      @modal-save="saveModal"
      @modal-toggle="toggleModal">
        <template slot="header-title">{{ modal.title }}</template>
        <template slot="body-content">
          <event-create v-if="modal.type === 'create'" :current-moment="currentMoment"></event-create>
          <event-view v-if="modal.type === 'view'" :event-info="eventInfo"></event-view>
        </template>
    </modal>

  </div>
</template>
<script>
import * as $ from 'jquery'
import fullCalendar from 'fullcalendar'
import EventCreate from '@/components/modals/EventCreate.vue'
import EventView from '@/components/modals/EventView.vue'
import Modal from '@/components/Modal.vue'
import { mapGetters, mapActions } from 'vuex'
import { CALENDAR_CONFIG } from '@/common/calendar.config'

export default {
  props: ['editable', 'timezone', 'public', 'showPrintButton'],
  components: {
    EventCreate,
    EventView,
    Modal
  },
  data () {
    return {
      modal: {
        showModal: false,
        showFooter: true,
        showEdit: false,
        showSaveButton: false,
        editUrl: null,
        title: null,
        type: null
      },
      currentMoment: null,
      calendar: null,
      eventInfo: null,
      format: 'YYYY-MM-DD',
      calendarConfigs: CALENDAR_CONFIG
    }
  },
  beforeMount () {
    const self = this

    if (this.public == true) {
      this.getCalendars({ public: this.public }).then(response => {
        if (self.public == true) {
          let activeIds = []
          response.forEach(element => {
            activeIds.push(element.id)
          })

          if (activeIds.length) {
            self.$store.commit('calendars/setActiveIds', activeIds)
          }
        }
      })
    }
  },
  mounted () {
    const self = this
    self.calendar = $(self.$refs.calendar)

    let args = Object.assign(
      this.calendarConfigs,
      {
        timezone: false,
        eventClick (event) {
          self.openEvent(event)
        },
        dayClick (dateMoment, event, view) {
          if (self.public !== true) {
            self.createEvent(dateMoment, event, view)
          }
        },
        viewRender (view, element) {
          self.$store.commit('calendars/setOption', { key: 'start', value: view.start.format(this.format) })
          self.$store.commit('calendars/setOption', { key: 'end', value: view.end.format(this.format) })
        }
      },
    )

    if (true === this.showPrintButton) {
      args.customButtons = {
        printButton: {
          text: 'Print',
          click: function () {
            window.print()
          }
        }
      }
    }

    self.calendar.fullCalendar(args)
  },
  computed: {
    ...mapGetters({
      activeIds: 'calendars/activeIds',
      eventSources: 'calendars/events/data',
      rangeChecksum: 'calendars/rangeChecksum'
    }),
  },
  watch: {
    eventSources () {
      this.updateEventSources()
    },
    rangeChecksum () {
      const self = this
      if (this.activeIds.length) {
        this.activeIds.forEach(id => {
          self.getCalendarEvents({calendar_id: id})
        })
        this.updateEventSources()
      }
    },
    activeIds () {
      const self = this

      if (this.public == true && this.activeIds.length) {
        this.activeIds.forEach(id => {
          self.getCalendarEvents({calendar_id: id})
        })
        this.updateEventSources()
      }
    }
  },
  methods: {
    ...mapActions({
      getCalendars: 'calendars/getData',
      getCalendarEvents: 'calendars/events/getData',
      getCalendarInfo: 'calendars/events/getItemById',
      addCalendarEvent: 'event/addCalendarEvent',
      resetCalendarEvent: 'event/resetEvent',
    }),
    toggleModal (state) {
      if (this.modal.type == 'create') {
        this.updateEventSources()
      }
      this.modal.showModal = state.value
    },
    saveModal () {
      const self = this
      if (this.modal.type === 'create') {
        this.addCalendarEvent().then(response => {
          if (response.data.success == true) {
            self.getCalendarEvents({ calendar_id: response.data.data.calendar_id })
            self.toggleModal({ state: false })
            self.resetCalendarEvent()
          }
        })
      }
    },
    createEvent (moment, event, view) {
      Object.assign(this.modal, {
        title: 'Create Event',
        showModal: true,
        showSaveButton: true,
        showFooter: true,
        type: 'create'
      })
      this.currentMoment = moment
      this.$store.commit('event/setStart', moment.format('YYYY-MM-DD HH:mm'))
      this.$store.commit('event/setEnd', moment.format('YYYY-MM-DD HH:mm'))
    },
    openEvent (event) {
      const self = this
      this.getCalendarInfo({ id: event.id }).then(response => {
        self.eventInfo = response.data

        Object.assign(self.modal, {
          title: response.data.title,
          showModal: true,
          showSaveButton: false,
          showFooter: true,
          type: 'view'
        })
      })
    },
    updateEventSources () {
      const self = this

      let oldSources = this.calendar.fullCalendar('getEventSources')
      let sourceIds = oldSources.map(item => item.id)

      this.calendar.fullCalendar('removeEventSources')

      this.eventSources.forEach( element => {
        if (self.activeIds.includes(element.id)) {
          self.calendar.fullCalendar('addEventSource', element)
        }
      })

      self.calendar.fullCalendar('refetchEventSources')
    }
  }
}
</script>
