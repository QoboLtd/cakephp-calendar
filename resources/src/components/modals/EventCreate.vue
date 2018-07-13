<template>
  <div>

    <div class="row">
      <div class="col-xs-12 col-md-12">
        <div class="form-group">
          <v-select
            v-model="calendar"
            placeholder="-- Please choose Calendar --"
            label="name"
            :options="calendarsList"
            @input="updateCalendar">
          </v-select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12 col-md-12">
        <div class="form-group">
          <v-select
            v-model="eventType"
            :options="eventTypesList"
            placeholder="-- Please choose Event Type --"
            label="name"
            @input="updateEventType">
          </v-select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12 col-md-6">
        <datepicker
          label="Start"
          name="start"
          config-field="start_date"
          :event-click="currentMoment"
          :configs="eventTypeConfigs"
          @date-changed="updateStart">
        </datepicker>
      </div>

      <div class="col-xs-12 col-md-6">
        <datepicker
          label="End"
          name="end"
          config-field="end_date"
          :event-click="currentMoment"
          :configs="eventTypeConfigs"
          @date-changed="updateEnd">
        </datepicker>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <div class="form-group">
          <label class="control-label">Repeats on:</label>
          <recurrence-input @rrule-saved="updateRecurrence"></recurrence-input>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <div class="form-group text">
          <label> Attendees: </label>
          <v-select
            :value="attendeesIds"
            :debounce="400"
            :on-search="searchAttendees"
            :options="attendeesList"
            @input="updateAttendees"
            label="name"
            multiple>
          </v-select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12 col-md-12">
        <div class="form-group text">
          <label>Title:</label>
          <input type="text" v-model="title" class="form-control"/>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xs-12">
        <div class="form-group text">
          <label>Content: </label>
          <textarea v-model="content" class="form-control"></textarea>
        </div>
      </div>
    </div>

  </div>
</template>
<script>
import vSelect from 'vue-select'
import { mapGetters, mapActions, mapState } from 'vuex'
import Datepicker from '@/components/ui/Datepicker.vue'
import RecurrenceInput from '@/components/ui/RecurrenceInput.vue'

export default {
  props: ['currentMoment'],
  components: {
    RecurrenceInput,
    Datepicker,
    vSelect
  },
  data () {
    return {
      attendeesList: [],
      eventTypesList: [],
      eventTypeConfigs: null
    }
  },
  computed: {
    ...mapGetters({
      calendars: 'calendars/data',
      findCalendarById: 'calendars/getItemById',
      getEventTypeConfigName: 'event/getEventTypeField'
    }),
    ...mapState({
      attendeesIds: 'event/attendeesIds'
    }),
    calendar: {
      get () {
        return this.$store.getters['event/calendar']
      },
      set (value) {
        this.$store.commit('event/setCalendar', value)
      }
    },
    start: {
      get () {
        return this.$store.getters['event/start']
      },
      set (value) {
        this.$store.commit('event/setStart', value)
      }
    },
    end: {
      get () {
        return this.$store.getters['event/end']
      },
      set (value) {
        this.$store.commit('event/setEnd', value)
      }
    },
    eventType: {
      get () {
        return this.$store.getters['event/eventType']
      },
      set (value) {
        this.$store.commit('event/setEventType', value)
      }
    },
    title: {
      get () {
        return this.$store.getters['event/title']
      },
      set (value) {
        this.$store.commit('event/setTitle', value)
      }
    },
    content: {
      get () {
        return this.$store.getters['event/content']
      },
      set (value) {
        this.$store.commit('event/setContent', value)
      }
    },
    recurrence: {
      get () {
        return this.$store.getters['event/recurrence']
      },
      set (value) {
        this.$store.commit('event/setRecurrence', value)
      }
    },
    calendarsList () {
      let list = []

      this.calendars.forEach( element => {
        list.push({ id: element.id, name: element.name })
      })

      return list
    }
  },
  methods: {
    ...mapActions({
      getEventTypes: 'calendars/getEventTypes',
      getEventTypeConfig: 'calendars/getEventTypeConfig',
      getAttendees: 'calendars/getAttendees'
    }),
    updateStart (value) {
      this.start = value
    },
    updateEnd (value) {
      this.end = value
    },
    updateRecurrence (value) {
      this.recurrence = value
    },
    updateCalendar (value) {
      const self = this
      if (this.calendar) {
        this.getEventTypes({
          calendar_id: this.calendar.id,
          exclude: ['json']
        }).then(response => {
          self.eventTypesList = response.data
        })
      } else {
        self.eventTypesList = []
      }
    },
    updateEventType () {
      const self = this

      if (this.eventType) {
        this.getEventTypeConfig({ event_type: this.eventType.value }).then(response => {
          if (!response.data.success) {
            return
          }
          self.eventTypeConfigs = response.data.data
        })
      }
    },
    searchAttendees (search, loading) {
      const self = this
      if (search.length > 2) {
        loading(true)

        this.getAttendees({term: search}).then(response => {
          self.attendeesList = []
          response.data.forEach((element) =>{
            self.attendeesList.push({ id: element.id, name: element.text })
          })
          loading(false)
        })
      }
    },
    updateAttendees (value) {
      this.$store.commit('event/setAttendeesIds', value)
    }
  }
}
</script>
