<template>
    <div></div>
</template>
<script>
import { jQuery } from 'jquery'
import moment from 'moment'

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

      this.events.forEach((event, index) => {
        self.calendarEvents.push({
          id: event.id,
          title: event.title,
          color: event.color,
          start: moment().format(event.start_date),
          end: moment().format(event.end_date),
          calendar_id: event.calendar_id,
          allDay: event.is_allday,
          event_type: event.event_type
        })
      })
    },
    calendarEvents: function () {
      this.calendarInstance.fullCalendar('removeEvents')
      this.calendarInstance.fullCalendar('addEventSource', this.calendarEvents)
    }
  },

  mounted () {
    var self = this
    self.calendarInstance = jQuery(self.$el)

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

    this.calendarInstance.fullCalendar(args)
  }
}
</script>
