<template>
  <div class="form-group text">
    <label v-if="label">{{label}}</label>
    <input ref="daterangepicker" type="text" :disabled="disabled" :name="name" :value="value" :class="className" class="form-control"/>
  </div>
</template>
<script>
import * as $ from 'jquery'
import moment from 'moment'
import daterangepicker from 'daterangepicker'

export default {
  props: ['name', 'className', 'label', 'disabled', 'isStart', 'dateMoment', 'format', 'isUp'],
  beforeMount () {
    if (this.format) {
      this.options.format = this.format
    } else {
      this.options.format = 'YYYY-MM-DD HH:mm'
    }

    if (this.isUp) {
      this.options.drops = 'up'
    }
  },
  mounted () {
    const self = this
    self.instance = $(self.$el).find('input').daterangepicker(this.options).data('daterangepicker')

    $(self.$el).find('input').on('apply.daterangepicker', function (ev, picker) {
      self.momentObject = moment(picker.startDate)
      self.value = picker.startDate.format(self.options.format)
      self.$emit('date-changed', self.value, self.momentObject)
    })
  },
  watch: {
    dateMoment: function () {
      this.momentObject = moment(this.dateMoment)
      this.instance.setStartDate(this.momentObject)
      this.instance.setEndDate(this.momentObject)
      this.value = this.momentObject.format(this.options.format)
    }
  },
  data () {
    return {
      instance: null,
      value: null,
      momentObject: null,
      options: {
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        drops: 'down',
        timePicker12Hour: false,
        timePickerIncrement: 5
      }
    }
  }
}
</script>
