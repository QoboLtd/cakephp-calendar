<template>
    <div ref="calendar"></div>
</template>
<script>
import * as $ from 'jquery'
import fullCalendar from 'fullcalendar'

export default {
  props: ['ids', 'events', 'editable', 'start', 'end', 'timezone', 'public', 'showPrintButton'],
  data () {
    return {
      cal: null,
      calendarEvents: [],
      format: 'YYYY-MM-DD'
    }
  },

  watch: {
    events: function () {
      var self = this
      this.calendarEvents = []

      if (!this.events.length) {
        return
      }

      self.setCalendarEvents(this.events)
    },
    calendarEvents: function () {
      this.cal.fullCalendar('removeEvents')
      this.cal.fullCalendar('addEventSource', this.calendarEvents)
      this.cal.fullCalendar('rerenderEvents')
    }
  },

  mounted () {
    var self = this
    self.cal = $(self.$refs.calendar)

    var args = {
      header: {
        left: 'today, prev,next printButton',
        center: 'title',
        right: 'month,agendaWeek,agendaDay'
      },
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      firstDay: 1,
      editable: this.editable,
      eventClick (event) {
        self.$emit('event-info', event)
      },
      viewRender (view, element) {
        self.$emit('interval-update', view.start.format(this.format), view.end.format(this.format))
      }
    }

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

    if (this.public != 'true') {
      args.dayClick = function (date, jsEvent, view) {
        self.$emit('modal-add-event', date, jsEvent, view)
      }
    }

    self.cal.fullCalendar(args)
  },
  methods: {
    setCalendarEvents (events) {
      const self = this

      if (!events.length) {
        return
      }

      events.forEach( (event) => {
        self.calendarEvents.push({
          id: event.id,
          title: event.title,
          color: event.color,
          start: event.start_date,
          end: event.end_date,
          calendar_id: event.calendar_id,
          event_type: event.event_type,
          allDay: true
        })
      })

    }
  }
}
</script>
