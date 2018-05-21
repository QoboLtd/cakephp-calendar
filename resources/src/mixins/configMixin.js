import moment from 'moment'

export default {
  methods: {
    _handleStartTime (val, obj) {
      let timeFormat = 'YYYY-MM-DD HH:mm'
      let hhmm = obj.split(':')
      let date = moment(new Date(val))

      date.set('hour', hhmm[0])
      date.set('minute', hhmm[1])

      return date.format(timeFormat)
    },
    _handleEndTime (val, obj) {
      let timeFormat = 'YYYY-MM-DD HH:mm'
      let hhmm = obj.split(':')
      let date = moment(new Date(val))

      date.set('hour', hhmm[0])
      date.set('minute', hhmm[1])

      return date.format(timeFormat)
    },
    getStartDate (data, opts) {
      let val = data.value
      let field = data.key
      let config = opts[field].options

      for (let handler in config) {
        let obj = config[handler]

        if (handler === 'startTime') {
          return this._handleStartTime(val, obj)
        }
      }

    },
    getEndDate (data, opts) {
      let val = data.value
      let field = data.key
      let config = opts[field].options

      for (let handler in config) {
        let obj = config[handler]

        if (handler === 'endTime') {
          return this._handleEndTime(val, obj)
        }
      }


    }
  }
}
