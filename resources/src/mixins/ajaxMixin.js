import $ from 'jquery'

export default {
  methods: {
    apiPostCall (url, data) {
      let args = {
        'method': 'post',
        'dataType': 'json',
        'headers': {
          Authorization: 'Bearer ' + this.apiToken
        },
        'url': url
      }

      if (data !== undefined) {
        args = Object.assign(args, { 'data': data })
      }

      return $.ajax(args)
    },
    apiGetCall (url, data) {
      let args = {
        'url': url,
        'dataType': 'json',
        'headers': {
          Authorization: 'Bearer ' + this.apiToken
        },
        'method': 'get',
        'contentType': 'application/json',
        'accepts': {
          'json': 'application/json'
        }
      }

      if (data !== undefined) {
        args = Object.assign(args, { 'data': data })
      }

      return $.ajax(args)
    },
    apiGetCalendars () {
      const url = '/calendars/calendars/index'

      return this.apiPostCall(url)
    },
    apiGetPublicCalendars () {
      const self = this
      const url = '/calendars/calendars/index'

      return this.apiPostCall(url, {
        'public': self.public
      })
    },
    apiGetEvents (id) {
      const url = '/calendars/calendar-events/index'

      const args = {
        'calendar_id': id,
        'period': {
          'start_date': this.start,
          'end_date': this.end
        },
        'timezone': this.timezone
      }

      return this.apiPostCall(url, args)
    },
    apiGetEventInfo (calendarEvent) {
      const url = '/calendars/calendar-events/view'
      var post = {
        id: calendarEvent.id,
        calendar_id: calendarEvent.calendar_id,
        event_type: calendarEvent.event_type
      }

      /* @NOTE: returned result is html */
      return $.ajax({
        method: 'post',
        'url': url,
        'data': post
      })
    },
    apiAddCalendarEvent (postdata) {
      const url = '/calendars/calendar-events/add'

      return this.apiPostCall(url, postdata)
    },
    apiGetAttendees (term) {
      const url = '/calendars/calendar-attendees/lookup'
      const args = {
        'term': term,
        'calendarId': this.calendarId
      }

      return this.apiGetCall(url, args)
    },
    apiGetEventTypes (calendarId) {
      const url = '/calendars/calendar-events/get-event-types'
      const args = { 'calendar_id': calendarId }

      return this.apiPostCall(url, args)
    },
    apiEventTypeConfig (calendarId, eventType, options) {
      const url = '/calendars/calendar-events/event-type-config'
      let args = { 'calendar_id': calendarId, 'event_type': eventType }

      if (options !== undefined) {
        args = Object.assign(args, options)
      }

      return this.apiPostCall(url, args)
    }
  }
}
