<template>
    <i class="fa" v-bind:class="getIcon(name)">&nbsp;</i>
</template>
<script>
export default {
  props: ['name'],
  methods: {
    getIcon (name) {
      return (name) ? 'fa-' + name : ''
    }
  }
}
</script>
