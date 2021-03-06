<template>
    <div class="modal fade" id="calendar-modal-add-event" tabindex="-1" role="dialog" aria-labelledby="calendar-modal-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content" v-if="calendarsList.length == 0">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="calendar-modal-label">Warning</h4>
                    </div>
                    <div class="modal-body">
                        <p>You don't have permissions to add event to calendars.</p>
                    </div>
                    <div class="modal-footer">
                        <button v-on:click="dismissModal" class="btn btn-default">Close</button>
                    </div>
                </div>
                <div class="modal-content" v-else>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="calendar-modal-label">Add Event</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <v-select v-model="calendarId" :options="calendarsList" placeholder="-- Please choose Calendar --"></v-select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <v-select v-model="eventType" :options="eventTypesList" placeholder="-- Please choose Event Type --"></v-select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                      <input-datepicker
                                        :configs="eventTypeConfig"
                                        :event-click="eventClick"
                                        config-field="start_date"
                                        format="YYYY-MM-DD HH:mm"
                                        label="Start Date:"
                                        name="CalendarEvents[start_date]"
                                        @date-changed="setStart"
                                      ></input-datepicker>
                                    </div>
                                    <div class="col-xs-12 col-md-6">
                                      <input-datepicker
                                        :configs="eventTypeConfig"
                                        :event-click="eventClick"
                                        config-field="end_date"
                                        format="YYYY-MM-DD HH:mm"
                                        name="CalendarEvents[end_date]"
                                        label="End Date:"
                                        @date-changed="setEnd"
                                      ></input-datepicker>
                                    </div>
                                    <div class="col-xs-12 col-md-12">
                                        <div class="form-group text">
                                            <label> Attendees: </label>
                                            <v-select
                                                v-model="attendeesList"
                                                :debounce="400"
                                                :on-search="searchAttendees"
                                                :options="attendees"
                                                multiple>
                                            </v-select>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="!isRecurring">
                                        <div class="form-group text">
                                        <label>Title:</label>
                                        <input type="text" v-model="title" class="form-control"/>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="!isRecurring">
                                        <div class="form-group text">
                                            <label>Content:</label>
                                            <textarea v-model="content" class="form-control"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group text">
                                                    <label>Repeats:</label>
                                                    <input type="checkbox" name="CalendarEvents[is_recurring]" v-model="isRecurring"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        <input-select
                                            name="CalendarEvents[frequency]"
                                            :options="frequencies"
                                            label="Frequency:"
                                            @changed="getFrequency">
                                        </input-select>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isWeekly || isYearly || isDaily || isMonthly">
                                        <input-select
                                            name="CalendarEvents[intervals]"
                                            :options="frequencyIntervals"
                                            label="Interval:"
                                            @changed="getInterval">
                                        </input-select>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isWeekly">
                                        <input-checkboxes @changed="getWeekDays"></input-checkboxes>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        <calendar-recurring-until @data-changed="getUntil"></calendar-recurring-until>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        Recurring Event: {{rruleResult}}
                                    </div>
                                </div> <!-- .row -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button v-on:click="submitEvent" class="btn btn-default">Save</button>
                        <button v-on:click="dismissModal" class="btn btn-default">Close</button>
                    </div>
                </div>
            </div>
        </div>

</template>
<script>
import * as $ from 'jquery'
import ajaxMixin from '@/mixins/ajaxMixin'
import configMixin from '@/mixins/configMixin'
import RRule from 'rrule'
import moment from 'moment'
import vSelect from 'vue-select'
import InputSelect from '@/components/ui/InputSelect.vue'
import InputCheckboxes from '@/components/ui/InputCheckboxes.vue'
import CalendarRecurringUntil from './CalendarRecurringUntil.vue'
import Datepicker from '@/components/ui/Datepicker.vue'
import { camelize } from 'inflected'

export default {
  mixins: [ajaxMixin, configMixin],
  components: {
    'v-select': vSelect,
    'input-datepicker': Datepicker,
    'input-select': InputSelect,
    'input-checkboxes': InputCheckboxes,
    'calendar-recurring-until': CalendarRecurringUntil
  },
  props: ['calendarsList', 'timezone', 'eventClick', 'apiToken'],
  data: function () {
    return {
      attendees: [],
      attendeesList: [],
      calendarId: null,
      content: null,
      endDate: null,
      eventType: null,
      eventTypeConfig: null,
      eventTypes: [],
      eventTypesList: [],
      frequency: null,
      frequencyIntervals: [],
      frequencies: [
        { value: 3, label: 'Daily' },
        { value: 2, label: 'Weekly' },
        { value: 1, label: 'Monthly' },
        { value: 0, label: 'Yearly' }
      ],
      interval: null,
      isRecurring: 0,
      rruleResult: null,
      rrule: null,
      startDate: null,
      title: null,
      untilOption: null,
      untilValue: null,
      weekDays: []
    }
  },
  beforeMount: function () {
    this.frequencyIntervals = []

    for (var i = 1; i <= 30; i++) {
      this.frequencyIntervals.push({ value: i, label: i.toString() })
    }
  },
  watch: {
    calendarId: function () {
      if (this.calendarId) {
        this.getEventTypes()
      }
    },
    eventType: function () {
      if (this.calendarId && this.eventType) {
        this.getEventTypeInfo(this.calendarId.value, this.eventType.value, {
          'types': ['Config']
        })
      }
    },
    eventTypeConfig: function () {
      if (!this.eventTypeConfig) {
        return
      }

      for (let key in this.eventTypeConfig) {
        let obj = this.eventTypeConfig[key]
        let componentProperty = camelize(key, false)
        let method = 'get' + camelize(key)

        if (this.hasOwnProperty(componentProperty)) {

          if (configMixin.methods.hasOwnProperty(method)) {
            let val = configMixin.methods[method]( {
                'name': componentProperty,
                'key': key,
                'value': this[componentProperty]
              },
              this.eventTypeConfig
            )

            this.$data[componentProperty] = val
          }
        }
      }
    }
  },
  computed: {
    isDaily: function () {
      if (this.frequency === 3 && this.isRecurring) {
        this.weekDays = []
        this.setFrequencyIntervals(365)
        this.getRecurringRule()

        return true
      }

      return false
    },
    isMonthly: function () {
      if (this.frequency === 1 && this.isRecurring) {
        this.weekDays = []
        this.setFrequencyIntervals(12)
        this.getRecurringRule()

        return true
      }

      return false
    },
    isWeekly: function () {
      if (this.frequency === 2 && this.isRecurring) {
        this.setFrequencyIntervals(52)
        this.getRecurringRule()

        return true
      }

      return false
    },
    isYearly: function () {
      if (this.frequency === 0 && this.isRecurring) {
        this.weekDays = []
        this.setFrequencyIntervals(10)
        this.getRecurringRule()
        return true
      }

      return false
    }
  },
  methods: {
    getEventTypeInfo (calendarId, eventType, options) {
      this.apiEventTypeConfig(calendarId, eventType, options).then( (response) => {
        this.eventTypeConfig = response
      })
    },
    setFrequencyIntervals (end) {
      this.frequencyIntervals = []
      for (var i = 1; i <= end; i++) {
        this.frequencyIntervals.push({ value: i, label: i.toString() })
      }
    },
    dismissModal () {
      $('#calendar-modal-add-event').modal('hide')
    },
    submitEvent () {
      var self = this

      var postdata = {
        CalendarEvents: {
          calendar_id: this.calendarId.value,
          title: this.title,
          content: this.content,
          start_date: this.startDate,
          end_date: this.endDate,
          event_type: ((this.eventType) ? this.eventType.value : null),
          is_recurring: this.isRecurring,
          recurrence: [this.rrule]
        }
      }

      if (this.attendeesList.length) {
        postdata.CalendarEvents['calendar_attendees'] = {
          '_ids': []
        }

        this.attendeesList.forEach((elem, key) => {
          postdata.CalendarEvents.calendar_attendees._ids.push(elem.value)
        })
      }

      this.apiAddCalendarEvent(postdata).then((resp) => {
        self.$emit('event-saved', resp.event.entity)
      })
    },
    searchAttendees (search, loading) {
      var self = this

      if (search.length > 2) {
        loading(true)

        this.apiGetAttendees(search).then((resp) => {
          if (resp.length) {
            self.attendees = []
            resp.forEach((elem, key) => {
              self.attendees.push({ label: elem.text, value: elem.id })
            })
          }
          loading(false)
        })
      }
    },
    getUntil (rtype, value) {
      this.untilOption = rtype
      this.untilValue = value
    },
    getFrequency (val) {
      this.frequency = val
      if (!val) {
        this.getRecurringRule()
      }
    },
    getInterval (val) {
      this.interval = val
      if (!val) {
        this.getRecurringRule()
      }
    },
    getWeekDays (val) {
      this.weekDays = val
      if (!val) {
        this.getRecurringRule()
      }
    },
    setStart (startDate) {
      this.startDate = startDate
    },
    setEnd (endDate) {
      this.endDate = endDate
    },
    setDateRange (startDate, endDate) {
      this.startDate = startDate
      this.endDate = endDate
    },
    getEventTypes () {
      var self = this
      this.eventTypes = []
      this.eventTypesList = []

      if (!this.calendarId.value) {
        return
      }

      if (this.calendarId.value) {
        this.apiGetEventTypes(this.calendarId.value).done(function (types) {
          if (types.length) {
            types.forEach((elem, key) => {
              self.eventTypes.push(elem)
              self.eventTypesList.push({ label: elem.name, value: elem.value })
            })

            self.eventType = self.eventTypesList[0]
          }
        })
      }
    },
    getRecurringRule: function () {
      if (!this.isRecurring) {
        return null
      }

      var byweekdays = []
      var opts = { freq: this.frequency }

      if (this.weekDays.length) {
        this.weekDays.forEach((day, k) => {
          byweekdays.push(RRule[day])
        })

        opts.byweekday = byweekdays
      }

      if (this.interval) {
        opts.interval = this.interval
      }

      if (this.untilOption == 'occurrence' && this.untilValue) {
        opts.count = this.untilValue
      }

      if (this.untilOption == 'date' && this.untilValue) {
        opts.until = moment(this.untilValue).toDate()
      }

      var rule = new RRule(opts)

      this.rruleResult = rule.toText()
      this.rrule = 'RRULE:' + rule.toString()
    }
  }
}
</script>
