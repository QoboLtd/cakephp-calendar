<template>
  <div class="form-group">
    <span><strong>Ends:</strong></span>
    <div class='form-group radio'>
      <label><input type="radio" v-model="rtype" value="infinity"/>Never</label>
    </div>
    <div class='form-group radio'>
      <label>
        <input type="radio" v-model="rtype" value="occurrence"/>
        After # of occurrences:
        <div class="form-group text">
          <input class="form-control" type="text" v-model="valueOcc" :disabled="rtype !== 'occurrence'"/>
        </div>
      </label>
    </div>
    <div class='form-group radio'>
      <label>
        <input type="radio" v-model="rtype" value="date">
        Until Date (not including):
        <input-datepicker
            name="CalendarEvents[until]"
            :disabled="rtype !== 'date'"
            class-name="calendar-until-datetimepicker"
            format="YYYY-MM-DD"
            isUp="true"
            @date-changed="setUntilDate">
        </input-datepicker>
      </label>
    </div>
  </div>
</template>
<script>

import Datepicker from './Datepicker.vue'

export default {
  components: {
    'input-datepicker': Datepicker
  },
  data: function () {
    return {
      rtype: null,
      valueDate: null,
      valueOcc: null
    }
  },
  computed: {
    isUntilChanged: function () {
      return [this.rtype, this.valueOcc, this.valueDate].join('')
    },
    isTypeChanged: function () {
      return this.rtype
    }
  },
  watch: {
    isUntilChanged: function () {
      var value = null
      if (this.rtype === 'date') {
        value = this.valueDate
      }

      if (this.rtype === 'occurrence') {
        value = this.valueOcc
      }

      this.$emit('data-changed', this.rtype, value)
    },
    isTypeChanged: function () {
      this.valueOcc = null
    }
  },
  methods: {
    setUntilDate (val) {
      this.valueDate = val
    }
  }
}
</script>
