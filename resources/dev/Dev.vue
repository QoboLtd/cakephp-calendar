<template>
  <div id="app">
    <Modal :modal="{showModal:true, showFooter:true, title:'W00t'}"/>
  </div>
</template>
<script>
import Vue from 'vue'
import Notifications from 'vue-notification'
import Modal from '@/components/Modal.vue'

Vue.use(Notifications)

export default {
  components: {
    Modal
  }
}
</script>
