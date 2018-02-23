<template>
    <a :href="getUrl(itemUrl, itemValue)" :class="itemClass">
      <i v-if="itemIcon" class="fa" v-bind:class="iconClass"></i>
    </a>
</template>
<script>
export default {
  props: ['itemIcon', 'itemValue', 'itemUrl', 'itemClass', 'itemDelete', 'itemConfirmMsg'],
  data () {
    return {
      iconClass: null
    }
  },
  beforeMount() {
    if (this.itemIcon) {
      this.iconClass = 'fa-' + this.itemIcon
    }
  },
  methods: {
    getUrl (url, id) {
      return (id) ? url + '/' + id : url
    }
  }
}
</script>
