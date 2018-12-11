<template>
  <div class="form-group">
    <label v-for="item in options">
      <span style="padding:5px;"> <input type="checkbox" v-model="values" :value="item.value"/> {{item.label}}</span>
    </label>
  </div>
</template>
<script>
import { DAYS } from '@/common/recurrence.config'

export default {
  data () {
    return {
      options: DAYS,
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
