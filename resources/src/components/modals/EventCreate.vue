<template>
  <div>

    <div class="row">
      <div class="col-xs-12 col-md-12">
        <div class="form-group">
          <v-select
            v-model="calendarId"
            placeholder="-- Please choose Calendar --"
            label="name"
            :options="calendarsList"
            @input="updateCalendarId">
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
        <datepicker label="Start" name="start" format="YYYY-MM-DD HH:mm" event-click="" @date-changed="updateStart"></datepicker>
      </div>

      <div class="col-xs-12 col-md-6">
        <datepicker label="End" name="end" format="YYYY-MM-DD HH:mm" event-click="" @date-changed="updateEnd"></datepicker>
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
            v-model="attendeesIds"
            :debounce="400"
            :on-search="searchAttendees"
            :options="attendees"
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
import { mapGetters, mapActions } from 'vuex'
import Datepicker from '@/components/ui/Datepicker.vue'
import RecurrenceInput from '@/components/ui/RecurrenceInput.vue'

export default {
  components: {
    RecurrenceInput,
    Datepicker,
    vSelect
  },
  data () {
    return {
      calendarId: null,
      eventType: null,
      title: null,
      content: null,
      attendees: [],
      attendeesIds: []
    }
  },
  computed: {
    ...mapGetters({
      calendars: 'calendars/data'
    }),
    calendarsList () {
      let list = []

      this.calendars.forEach( element => {
        list.push({ id: element.id, name: element.name })
      })

      return list
    },
    eventTypesList () {
      console.log('event-types-list')
      return []
    }
  },
  methods: {
    updateStart (value) {
      console.log('start: ' + value)
    },
    updateEnd (value) {
      console.log('end:' + value)
    },
    updateRecurrence (value) {
      console.log('recurrence: ' + value)
    },
    updateCalendarId () {
      console.log('calendar_id:' + this.calendarId)
    },
    updateEventType () {
      console.log('event type:' + this.eventType)
    },
    searchAttendees (search, loading) {
      console.log('searching: ' + search)
    }
  }
}
</script>
