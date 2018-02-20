<template>
  <div class="form-group checkbox">
    <label>
      <input type="checkbox" v-model="toggle" :value="value" :name="name" :multiple="{ multiple: itemIsMultiple }" :class="[itemClass]"/>
      <icon-component v-if="icon" :name="icon"></icon-component> {{label}}
    </label>
  </div>
</template>
<script>
import IconComponent from './IconComponent.vue'

export default {
  props: ['label', 'value', 'icon', 'itemActive', 'name'],
  components: {
    'icon-component': IconComponent
  },
  beforeMount: function () {
    this.toggle = this.itemActive
  },
  data () {
    return {
      toggle: null,
      itemClass: 'calendar-id',
      itemIsMultiple: true
    }
  },
  watch: {
    toggle: function (val) {
      this.$emit('toggle-calendar', this.toggle, this.value)
    }
  }
}
</script>
