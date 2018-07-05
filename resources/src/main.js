import Vue from 'vue'
import { mapActions, mapGetters } from 'vuex'
import Calendar from '@/components/Calendar.vue'
import Sidebar from '@/components/Sidebar.vue'
import ApiService from '@/common/ApiService'
import Notifications from 'vue-notification'
import store from '@/store'

Vue.use(Notifications)
ApiService.init()

new Vue({
  el: '#qobo-calendar-app',
  store,
  components: {
    Calendar,
    Sidebar
  },
  data () {
    return {
      apiToken: null
    }
  },
  computed: {
    ...mapGetters({
      calendars: 'calendars/data'
    }),
    editable () {
      return this.$store.getters['calendars/getOption']('editable')
    },
    timezone () {
      return this.$store.getters['calendars/getOption']('timezone')
    },
    public () {
      return this.$store.getters['calendars/getOption']('public')
    }
  },
  beforeMount () {
    this.apiToken = this.$el.attributes.token.value
    this.$store.commit('calendars/setOption', { key: 'timezone', value: this.$el.attributes.timezone.value })

    ApiService.setHeader(this.apiToken)

    if (this.$el.attributes.public) {
      let isPublic = (this.$el.attributes.public.value == 'true')
      this.$store.commit('calendars/setOption', { key: 'public', value: isPublic })
    }

    this.getCalendars({ public: this.public })
  },
  methods: {
    ...mapActions({
      getCalendars: 'calendars/getData',
    })
  }
})
