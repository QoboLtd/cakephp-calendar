import Vue from 'vue'
import IconComponent from '@/components/IconComponent'
import { createVM } from '../helpers/utils'
import { expect } from 'chai'

describe('IconComponent.vue', function () {
  it('should render the component icon', function () {
    const vm = createVM(this,
      `<IconComponent name="user"> Foobar</IconComponent> (Icon on the left, fontawesome not included yet)`,
      {
        components: { IconComponent}
      }
    )
    expect(vm.$el.querySelector('.fa').className).to.equal('fa fa-user')
  })
})
