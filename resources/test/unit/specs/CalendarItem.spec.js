import Vue from 'vue'
import CalendarItem from '@/components/CalendarItem.vue'
import { createVM } from '../helpers/utils'
import { expect } from 'chai'

describe('CalendarItem.vue', function () {
  it('should render the caledar-item', function () {
    const vm = createVM(this,
      `<CalendarItem label="testLabel" value="testValue" icon="user" item-active="true" name="testName"></CalendarItem>`,
      {
        components: { CalendarItem }
      }
    )
    expect(vm.$el.querySelector('input[name="testName"]').value).to.equal('testValue')
  })
})
