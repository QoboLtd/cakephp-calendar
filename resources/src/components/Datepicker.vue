<template>
  <div class="form-group text">
    <label v-if="label">{{label}}</label>
    <input ref="daterangepicker" type="text" :name="name" :value="value" class="form-control"/>
  </div>
</template>
<script>
import * as $ from 'jquery'
import moment from 'moment'
import configMixin from './../mixins/configMixin'
import daterangepicker from 'daterangepicker'
import { camelize } from 'inflected'

export default {
  mixins: [configMixin],
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
    self.instance = $(self.$el).find('input').daterangepicker(this.options).data('daterangepicker')
    /* following handler used when you choose the date from popup picker */
    $(self.$el).find('input').on('apply.daterangepicker', function (ev, picker) {
      self.momentObject = moment(picker.startDate)
      self.value = self.momentObject.format(self.options.locale.format)
      self.$emit('date-changed', self.value)
    })
  },
  watch: {
    configs: function () {
      for (let key in this.configs) {
        if (this.configField !== key) {
          continue
        }

        let method = 'get' + camelize(key)
        if (configMixin.methods.hasOwnProperty(method)) {
          let result = configMixin.methods[method](
            {
              'name': camelize(key, false),
              'key': key,
              'value': this.momentObject.format(this.options.locale.format)
            },
            this.configs
          )

          this.updateMoment(moment(new Date(result)))
        }
      }
    },
    eventClick: function () {
      let currentDate = this.instance.startDate

      currentDate.set('year', this.eventClick.year())
      currentDate.set('month', this.eventClick.month())
      currentDate.set('date', this.eventClick.date())

      this.updateMoment(currentDate)
    }
  },
  methods: {
    updateMoment (m) {
      this.momentObject = m
      this.value = this.momentObject.format(this.options.locale.format)
      this.instance.setStartDate(this.momentObject)
      this.instance.setEndDate(this.momentObject)

      this.$emit('date-changed', this.value)
    }
  }
}
</script>
