<template>
  <div class="form-group text">
    <label v-if="label">{{label}}</label>
    <input type="text" :disabled="disabled" :name="name" :value="value" :class="className" class="form-control"/>
  </div>
</template>
<script>
import jQuery from 'jquery'
import moment from 'moment'

export default {
  props: ['name', 'className', 'label', 'disabled', 'isStart', 'dateMoment', 'format', 'isUp'],
  beforeMount: function () {
    if (this.format) {
      this.pickerOptions.format = this.format
    } else {
      this.pickerOptions.format = 'YYYY-MM-DD HH:mm'
    }

    if (this.isUp) {
      this.pickerOptions.drops = 'up'
    }
  },
  mounted: function () {
    var self = this
    self.instance = jQuery(self.$el).find('input').daterangepicker(this.pickerOptions).data('daterangepicker')

    jQuery(self.$el).find('input').on('apply.daterangepicker', function (ev, picker) {
      self.momentObject = moment(picker.startDate)
      self.value = picker.startDate.format(self.pickerOptions.format)
      self.$emit('date-changed', self.value, self.momentObject)
    })
  },
  watch: {
    dateMoment: function () {
      this.momentObject = moment(this.dateMoment)
      this.instance.setStartDate(this.momentObject)
      this.instance.setEndDate(this.momentObject)
      this.value = this.momentObject.format(this.pickerOptions.format)
    }
  },
  data: function () {
    return {
      instance: null,
      value: null,
      momentObject: null,
      pickerOptions: {
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
