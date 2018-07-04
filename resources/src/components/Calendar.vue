<template>
  <div>
    <div ref="calendar"></div>

    <modal
      :show-modal="modal.showModal"
      :show-footer="modal.showFooter"
      :show-save-button="modal.showSaveButton"
      :show-edit="modal.showEdit"
      :edit-url="modal.editUrl"
      @modal-save="saveModal"
      @modal-toggle="toggleModal">
        <template slot="header-title">{{ modal.title }}</template>
        <template slot="body-content">
          <event-create v-if="modal.type === 'create'"></event-create>
          <event-view v-if="modal.type === 'view'"></event-view>
        </template>
    </modal>

  </div>
</template>
<script>
import * as $ from 'jquery'
import fullCalendar from 'fullcalendar'
import Modal from '@/components/Modal.vue'
import EventCreate from '@/components/modals/EventCreate.vue'
import EventView from '@/components/modals/EventView.vue'
import { mapGetters, mapActions } from 'vuex'


export default {
  props: ['editable', 'timezone', 'public', 'showPrintButton'],
  components: {
    Modal,
    EventCreate,
    EventView
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
      calendar: null,
      calendarEvents: [],
      format: 'YYYY-MM-DD',
      calendarConfigs: {
        header: {
          left: 'prev,next',
          center: 'title',
          right: 'month,agendaWeek,agendaDay'
        },
        buttonText: {
          today: 'today',
          month: 'month',
          week: 'week',
          day: 'day'
        },
        defaultView: 'month',
        firstDay: 1,
        editable: false,
        timeFormat: 'HH:mm'
      }
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
          if (self.public != 'true') {
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
    }
  },
  methods: {
    ...mapActions({
      getCalendarEvents: 'calendars/events/getData'
    }),
    toggleModal (state) {
      this.modal.showModal = state.value
    },
    saveModal () {
      console.log('save-modal')
    },
    createEvent (moment, event, view) {
      Object.assign(this.modal, {
        title: 'Create Event',
        showModal: true,
        showFooter: true,
        type: 'create'
      })
    },
    openEvent (event) {
      Object.assign(this.modal, {
        title: 'View Event',
        showModal: true,
        showFooter: true,
        type: 'info'
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
    }
  }
}
</script>
