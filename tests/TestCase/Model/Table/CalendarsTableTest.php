<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Model\Table\CalendarsTable;

/**
 * Qobo\Calendar\Model\Table\CalendarsTable Test Case
 */
class CalendarsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Qobo\Calendar\Model\Table\CalendarsTable
     */
    public $Calendars;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.calendars',
        'plugin.qobo/calendar.calendar_events'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        $this->Calendars = TableRegistry::get('Calendars', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Calendars);

        parent::tearDown();
    }

    public function testGetCalendarTypes()
    {
        $result = $this->Calendars->getCalendarTypes();
        $this->assertTrue(is_array($result));

        Configure::write('Calendar.Types', ['foo' => ['name' => 'bar', 'value' => 'bar']]);
        $result = $this->Calendars->getCalendarTypes();
        $this->assertEquals(['bar' => 'bar'], $result);
    }

    public function testGetCalendars()
    {
        $result = $this->Calendars->getCalendars();
        $this->assertTrue(!empty($result));

        $result = $this->Calendars->getCalendars(['id' => '9390cbc1-dc1d-474a-a372-de92dce85aae']);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '9390cbc1-dc1d-474a-a372-de92dce85aae');

        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa']]);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '9390cbc1-dc1d-474a-a372-de92dce85aaa');

        Configure::write('Calendar.Types', ['foo' => ['name' => 'bar', 'value' => 'bar']]);
        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa']]);
    }
}
