import Vue from 'vue'
import CalendarLink from '@/components/CalendarLink.vue'
import { createVM } from '../helpers/utils'
import { expect } from 'chai'

describe('CalendarLink.vue', function () {
  it('should render the caledar-item', function () {
    const vm = createVM(this,
      `<CalendarLink item-url="/foo/bar" item-class="foobar" name="testLink" item-icon="user"></CalendarLink>`,
      {
        components: { CalendarLink }
      }
    )

    expect(vm.$el.querySelector('.foobar')).to.exist
  })
})
