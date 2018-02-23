<template>
  <div class="form-group checkbox">
    <label>
      <input type="checkbox" v-model="toggle" :value="value" :name="name" :multiple="{ multiple: itemIsMultiple }" :class="[itemClass]"/>
      <i v-if="icon" class="fa" v-bind:class="iconClass"></i> {{label}}
    </label>
  </div>
</template>
<script>
export default {
  props: ['label', 'value', 'icon', 'itemActive', 'name'],
  beforeMount: function () {
    this.toggle = this.itemActive
    if (this.icon) {
      this.iconClass = 'fa-' + this.icon
    }
  },
  data () {
    return {
      toggle: null,
      itemClass: 'calendar-id',
      itemIsMultiple: true,
      iconClass: null
    }
  },
  watch: {
    toggle: function (val) {
      this.$emit('toggle-calendar', this.toggle, this.value)
    }
  }
}
</script>
