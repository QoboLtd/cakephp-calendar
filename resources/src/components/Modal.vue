<template>
    <transition name="modal" v-if="modal.showModal">
      <div class="modal modal-mask" style="display: block">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" aria-label="Close"><span aria-hidden="true" @click="closeModal">&times;</span></button>
              <h4 class="modal-title"><slot name="header-title"></slot></h4>
            </div>
            <div class="modal-body">
              <notifications group="modal_notification" position="top center"/>
              <!-- view information of the calendar -->
              <slot name="body-content"></slot>
            </div>
            <div class="modal-footer" v-if="modal.showFooter">
              <a :href="modal.editUrl" class="btn btn-info" v-if="modal.showEdit"> Edit</a>
              <button type="button" class="btn btn-info" @click="saveModal" v-if="modal.showSaveButton === true"> Save </button>
              <button type="button" class="btn btn-info" @click="closeModal"> Close </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
</template>
<script>
export default {
  props: {
    modal: Object
  },
  methods: {
    closeModal () {
      this.$emit('modal-toggle', { value: false })
    },
    saveModal () {
      this.$emit('modal-save')
    }
  }
}
</script>
