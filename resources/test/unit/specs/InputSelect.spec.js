import Vue from 'vue'
import InputSelect from '@/components/InputSelect'
import { createVM } from '../helpers/utils'
import { expect } from 'chai'

describe('InputSelect.vue', function () {
  it('should render the component icon', function () {
    const selectOptions = [
      { label: 'A', value: 1 },
      { label: 'B', value: 2 }
    ]
    const vm = createVM(this,
      `<InputSelect name="foo" label="user" options="${selectOptions}"></InputSelect>`,
      {
        components: { InputSelect}
      }
    )
  })
})
