<template>
  <div class="form-group">
    <label>{{label}}</label>
    <select v-model="value" class="form-control" :name="name">
      <option v-for="item in options" :value="item.value">{{item.label}}</option>
    </select>
  </div>
</template>
<script>
export default {
  props: ['options', 'name', 'label'],
  data () {
    return { value: null }
  },
  watch: {
    value: function () {
      this.$emit('changed', this.value)
    }
  }
}
</script>
