<template>
    <div ref="calendar"></div>
</template>
<script>
import * as $ from 'jquery'
import moment from 'moment'
import fullCalendar from 'fullcalendar'

export default {
  props: ['ids', 'events', 'editable', 'start', 'end', 'timezone', 'public', 'showPrintButton'],
  data () {
    return {
      calendarInstance: null,
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

      let event = this.events[0]
      this.events.forEach(function (event, index) {
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
    },
    calendarEvents: function () {
      this.calendarInstance.fullCalendar('removeEvents')
      this.calendarInstance.fullCalendar('addEventSource', this.calendarEvents)
      this.calendarInstance.fullCalendar('rerenderEvents');
    }
  },

  mounted () {
    var self = this
    self.calendarInstance = $(self.$refs.calendar)

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

    self.calendarInstance.fullCalendar(args)
  }
}
</script>
