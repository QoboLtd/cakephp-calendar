import moment from 'moment'

export default {
  methods: {
    _handleStartTime (val, obj) {
      const timeFormat = 'YYYY-MM-DD HH:mm'
      const hhmm = obj.split(':')
      let date = moment(new Date(val))

      date.set('hour', hhmm[0])
      date.set('minute', hhmm[1])

      return date.format(timeFormat)
    },
    _handleEndTime (val, obj) {
      const timeFormat = 'YYYY-MM-DD HH:mm'
      const hhmm = obj.split(':')
      let date = moment(new Date(val))

      date.set('hour', hhmm[0])
      date.set('minute', hhmm[1])

      return date.format(timeFormat)
    },
    getStartDate (data, opts) {
      const val = data.value
      const field = data.key
      const config = opts[field].options

      for (let handler in config) {
        const obj = config[handler]

        if (handler === 'startTime') {
          return this._handleStartTime(val, obj)
        }
      }

    },
    getEndDate (data, opts) {
      const val = data.value
      const field = data.key
      const config = opts[field].options

      for (let handler in config) {
        let obj = config[handler]

        if (handler === 'endTime') {
          return this._handleEndTime(val, obj)
        }
      }


    }
  }
}
