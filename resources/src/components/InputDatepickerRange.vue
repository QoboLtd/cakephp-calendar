<template>
  <div><div class="col-xs-12 col-md-6">
    <input-datepicker
      v-bind:date-moment="startMoment"
      :name="startName"
      :label="startLabel"
      :is-start="true"
      :class-name="startClass"
      format="YYYY-MM-DD HH:mm"
      @date-changed="setStartDate">
    </input-datepicker>
  </div>
  <div class="col-xs-12 col-md-6">
    <input-datepicker
      :date-moment="endMoment"
      :name="endName"
      :is-start="false"
      :label="endLabel"
      :class-name="endClass"
      format="YYYY-MM-DD HH:mm"
      @date-changed="setEndDate">
    </input-datepicker>
  </div></div>
</template>
<script>
import moment from 'moment'

export default {
  props: ['startName', 'endName', 'startLabel', 'endLabel', 'startClass', 'endClass', 'configs', 'eventClick'],
  data: function () {
    return {
      startDate: null,
      startMoment: null,
      endDate: null,
      endMoment: null,
      format: 'YYYY-MM-DD HH:mm'
    }
  },
  computed: {
    /* using compute var to avoid multiple event calls for start/end changes. */
    intervalChanged: function () {
      return [this.startDate, this.endDate].join('')
    }
  },
  watch: {
    configs: function () {
      var st = []
      var en = []

      if (!this.configs) {
        return
      }
      if (this.configs.hasOwnProperty('start_time')) {
        st = this.configs.start_time.split(':')
        var newStart = moment(this.startMoment).set({ 'hour': st[0], 'minute': st[1] })

        this.setStartDate(newStart.format(this.format), newStart)
      }

      if (this.configs.hasOwnProperty('end_time')) {
        en = this.configs.end_time.split(':')
        var newEnd = moment(this.endMoment).set({ 'hour': en[0], 'minute': en[1] })

        this.setEndDate(newEnd.format(this.format), newEnd)
      }
    },
    eventClick: function () {
      this.startMoment = moment(this.eventClick)
      this.endMoment = moment(this.eventClick)

      this.startDate = this.startMoment.format(this.format)
      this.endDate = this.endMoment.format(this.format)
    },
    intervalChanged: function () {
      this.$emit('date-updated', this.startDate, this.endDate)
    }
  },
  methods: {
    setStartDate: function (val, momentObj) {
      this.startDate = val
      this.startMoment = moment(momentObj)
    },
    setEndDate: function (val, momentObj) {
      this.endDate = val
      this.endMoment = moment(momentObj)
    }
  }
}
</script>
