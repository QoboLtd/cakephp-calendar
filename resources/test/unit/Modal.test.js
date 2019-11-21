import { shallowMount } from '@vue/test-utils'
import Modal from '@/components/Modal.vue'

const wrapper = shallowMount(Modal, {
  propsData: {
    modal: {
      showModal: false,
      showFooter: true,
      showEdit: false,
      showSaveButton: false,
      editUrl: null,
      title: null,
      type: null
    }
  }
})

describe('Modal component tests', () => {
  it('should properly set props for the component', () => {
    expect(wrapper.props().modal.showModal).toBe(false)
    expect(wrapper.props().modal.showFooter).toBe(true)
    expect(wrapper.props().modal.editUrl).toBe(null)
  })

  it('should emit event when modal is closed', () => {

    wrapper.vm.closeModal()

    expect(wrapper.emitted()['modal-toggle']).toBeTruthy()
    expect(wrapper.emitted()['modal-toggle'][0][0].value).toBe(false)
  })

  it('should emit save event on modal save operation', () => {
    wrapper.vm.saveModal()

    expect(wrapper.emitted()['modal-save']).toBeTruthy()
  })
})
