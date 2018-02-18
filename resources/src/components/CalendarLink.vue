<template>
    <a :href="getUrl(itemUrl, itemValue)" :class="itemClass">
        <icon-component v-if="itemIcon" :name="itemIcon"></icon-component>
    </a>
</template>

<script>

import IconComponent from './IconComponent.vue'

export default {
  props: ['itemIcon', 'itemValue', 'itemUrl', 'itemClass', 'itemDelete', 'itemConfirmMsg'],
  components: {
    'icon-component': IconComponent
  },
  methods: {
    getUrl (url, id) {
      return (id) ? url + '/' + id : url
    }
  }
}
</script>
