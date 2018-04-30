<template>
  <div class="form-group">
    <label v-for="item in options">
      <span style="padding:5px;"> <input type="checkbox" v-model="values" :value="item.value"/> {{item.label}}</span>
    </label>
  </div>
</template>
<script>
export default {
  data () {
    return {
      options: [
        { label: 'Monday', value: 'MO' },
        { label: 'Tuesday', value: 'TU' },
        { label: 'Wednesday', value: 'WE' },
        { label: 'Thursday', value: 'TH' },
        { label: 'Friday', value: 'FR' },
        { label: 'Saturday', value: 'SA' },
        { label: 'Sunday', value: 'SU' }
      ],
      values: []
    }
  },
  watch: {
    values: function () {
      this.$emit('changed', this.values)
    }
  }
}
</script>
