<template>
  <div class='box'>
    <div class='box-header with-border hidden-print'>
      <h3 class='box-title'> Calendars </h3>
    </div>

    <div class='box-body hidden-print'>
      <div class="row">
        <div class="col-md-12">
          <div class="row" v-for="item in calendars">

            <div class="col-xs-8">
              <div class="form-group checkbox">
                <label>
                  <input type="checkbox" :value="item.id" name="Calendar[_id]" multiple="true" @click="toggleActiveId(item.id)"/>
                  <i v-if="item.icon" class="fa" v-bind:class="'fa-' + item.icon"></i> {{item.name}}
                </label>
              </div>
            </div>

            <div class="col-xs-4">
              <div class="btn-group btn-group-xs pull-right">
                <a v-if="item.permissions.view" :href="'/calendars/calendars/view/' + item.id" class="btn btn-default">
                  <i class="fa fa-eye"></i>
                </a>

                <a v-if="item.editable || item.permissions.edit" :href="'/calendars/calendars/edit/' + item.id" class="btn btn-default">
                  <i class="fa fa-pencil"></i>
                </a>
            </div>
          </div> <!-- v-for -->
        </div> <!-- col-md-12 -->
      </div> <!-- .row -->
    </div> <!-- box-body -->
  </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  computed: {
    ...mapGetters({
      calendars: 'calendars/data',
      toggle: 'calendars/activeIds'
    })
  },
  methods: {
    ...mapActions({
      getCalendarEvents: 'calendars/events/getData'
    }),
    toggleActiveId (calendarId) {
      const self = this
      if (!this.toggle.includes(calendarId)) {
        this.$store.commit('calendars/addActiveId', calendarId)
        this.getCalendarEvents({ calendar_id:calendarId })
      } else {
        this.$store.commit('calendars/removeActiveId', calendarId)
        this.$store.commit('calendars/events/removeDataSource', { id: calendarId })
      }
    }
  }
}
</script>
