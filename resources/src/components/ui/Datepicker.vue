<template>
  <div class="form-group text">
    <label v-if="label">{{label}}</label>
    <input ref="daterangepicker" type="text" :name="name" :value="value" class="form-control"/>
  </div>
</template>
<script>
import * as $ from 'jquery'
import moment from 'moment'
import configMixin from '@/mixins/configMixin'
import daterangepicker from 'daterangepicker'
import { camelize } from 'inflected'
import { mapGetters } from 'vuex'

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
          format: 'YYYY-MM-DD HH:mm'
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

    if (this.eventClick) {
      self.instance.setStartDate(this.eventClick.format(this.options.locale.format))
      self.instance.setEndDate(this.eventClick.format(this.options.locale.format))
      self.momentObject = this.eventClick
      self.value = this.momentObject.format(this.options.locale.format)
    }

    /* following handler used when you choose the date from popup picker */
    $(self.$el).find('input').on('apply.daterangepicker', function (ev, picker) {
      self.momentObject = moment(picker.startDate)
      self.value = self.momentObject.format(self.options.locale.format)
      self.$emit('date-changed', self.value)
    })
  },
  watch: {
    configs: function () {
      const self = this

      for (let key in this.configs) {
        if (self.configField !== key) {
          continue
        }

        let method = 'get' + camelize(key)

        if (typeof this[method] === 'function') {
          let storeFieldName = self.getEventTypeConfigName(key)
          let args = {
            'name': camelize(key, false),
            'key': key,
            'value': this.momentObject.format(this.options.locale.format)
          }

          let storeFieldValue = self[method](args, self.configs)

          self.updateMoment(moment(new Date(storeFieldValue)))
        }
      }
    },
    /* @NOTE: not sure it this one is neeeded right now */
    eventClick: function () {
      let currentDate = this.instance.startDate
      currentDate.set('year', this.eventClick.year())
      currentDate.set('month', this.eventClick.month())
      currentDate.set('date', this.eventClick.date())

      this.updateMoment(currentDate)
    }
  },
  computed: {
    ...mapGetters({
      getEventTypeConfigName: 'event/getEventTypeField'
    })
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
