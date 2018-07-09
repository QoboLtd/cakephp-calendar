<template>
  <div>

    <div class="row">
      <div class="col-xs-4 col-md-2 text-right"><strong>Start:</strong></div>
      <div class="col-xs-8 col-md-4">{{start}}</div>
      <div class="clearfix visible-xs visible-sm"></div>

      <div class="col-xs-4 col-md-2 text-right"><strong>End:</strong></div>
      <div class="col-xs-8 col-md-4">{{end}}</div>
      <div class="clearfix visible-xs visible-sm"></div>
    </div>
    <hr/>

    <div class="row">
      <div class="col-xs-12" v-html>
        <h4>Content:</h4>
        <span v-html="content"></span>
      </div>
    </div>

  </div> <!-- container -->
</template>
<script>
import moment from 'moment'

export default {
  props: ['eventInfo'],
  computed: {
    content () {
      return this.eventInfo.content
    },
    start () {
      if (this.eventInfo.start_date) {
        return moment(this.eventInfo.start_date).format('YYYY-MM-DD HH:mm')
      } else {
        return 'N/A'
      }
    },
    end () {
      if (this.eventInfo.end_date) {
        return moment(this.eventInfo.end_date).format('YYYY-MM-DD HH:mm')
      } else {
        return 'N/A'
      }
    },
    eventType () {
      return this.eventInfo.event_type
    }
  }
}
</script>
