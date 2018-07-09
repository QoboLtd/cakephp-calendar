<template>
  <div>

    <div class="box box-info">
      <div class="box-header with-border">
        <i class="fa fa-calendar"></i>
        <h4 class="box-title">Details</h4>
      </div>
      <div class="box-body">
        <div class="col-xs-4 col-md-2 text-right"><strong>Start:</strong></div>
        <div class="col-xs-8 col-md-4">{{start}}</div>
        <div class="clearfix visible-xs visible-sm"></div>

        <div class="col-xs-4 col-md-2 text-right"><strong>End:</strong></div>
        <div class="col-xs-8 col-md-4">{{end}}</div>
        <div class="clearfix visible-xs visible-sm"></div>
      </div>
    </div>

    <div class="box box-info" v-if="content">
      <div class="box-header with-border">
        <i class="fa fa-comment"></i>
        <h4 class="box-title">Content</h4>
      </div>
      <div class="box-body" v-html>
        <span v-html="content"></span>
      </div>
    </div>

    <div class="box box-info" v-if="attendees">
      <div class="box-header with-border">
        <i class="fa fa-user"></i>
        <h4 class="box-title">Attendees</h4>
      </div>

      <div class="box-body">
        <ul>
          <li v-for="item in attendees">{{item.display_name}} ({{item.contact_details}})</li>
        </ul>
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
    attendees () {
      let list = []

      if (this.eventInfo.calendar_attendees) {
        this.eventInfo.calendar_attendees.forEach(element => {
          list.push({
            display_name: element.display_name,
            contact_details: element.contact_details
          })
        })
      }

      return list
    }
  }
}
</script>
