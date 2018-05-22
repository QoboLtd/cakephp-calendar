<template>
  <div class="form-group text">
    <label v-if="label">{{label}}</label>
    <input ref="daterangepicker" type="text" :name="name" :value="value" class="form-control"/>
  </div>
</template>
<script>
import * as $ from 'jquery'
import moment from 'moment'
import daterangepicker from 'daterangepicker'
import inflected from 'inflected'

export default {
  props: [
    'configs',
    'configField',
    'eventClick',
    'format',
    'label',
    'name'
  ],
  data () {
    return {
      momentObject: null,
      instance: null,
      value: null,
      options: {
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        drops: 'down',
        timePicker24Hour: true,
        timePickerIncrement: 5,
        locale: {
          format: 'YYYY-MM-dd HH:mm'
        }
      }
    }
  },
  beforeMount () {
    if (this.format) {
      this.options.locale.format = this.format
    }
  },
  mounted () {
    const self = this
    console.log(this.configs)
    self.instance = $(self.$el).find('input').daterangepicker(this.options).data('daterangepicker')
    $(self.$el).find('input').on('apply.daterangepicker', function (ev, picker) {
      self.momentObject = moment(picker.startDate)
      self.value = picker.startDate.format(self.options.locale.format)

      self.$emit('date-changed', self.value, self.momentObject)
    })
  },
  watch: {
    configs: function () {
      for (let key in this.configs) {
        if (this.configField !== key) {
          continue
        }


      }
    },
    eventClick: function () {
      console.log('eventclick')
      this.momentObject = this.eventClick
    }
  }
}
</script>
