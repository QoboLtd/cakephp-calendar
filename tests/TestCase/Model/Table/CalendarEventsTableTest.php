<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Model\Table\CalendarEventsTable;

/**
 * Qobo\Calendar\Model\Table\CalendarEventsTable Test Case
 */
class CalendarEventsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Qobo\Calendar\Model\Table\CalendarEventsTable
     */
    public $CalendarEvents;

    /**
     * Calendars subject
     *
     * @var \Qobo\Calendar\Model\Table\Calendars
     */
    public $Calendars;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.calendar_events',
        'plugin.qobo/calendar.calendars',
        'plugin.qobo/calendar.calendar_attendees',
        'plugin.qobo/calendar.events_attendees',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('CalendarEvents') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarEventsTable'];
        $calendarConfig = TableRegistry::exists('Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];

        $this->Calendars = TableRegistry::get('Calendars', $calendarConfig);
        $this->CalendarEvents = TableRegistry::get('CalendarEvents', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->CalendarEvents);

        parent::tearDown();
    }

    public function testGetEventTypesWithCalendarEntity()
    {
        $calendars = $this->Calendars->getCalendars([
            'conditions' => [
                'id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa',
            ],
        ]);

        $calendarWithEventTypes = (!empty($calendars) ? $calendars[0] : []);

        $result = $this->CalendarEvents->getEventTypes($calendarWithEventTypes);
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
    }

    /**
     * @dataProvider testGetEventTypesProvider
     */
    public function testGetEventTypes($data, $expected, $msg)
    {
        $result = $this->CalendarEvents->getEventTypes($data);
        $this->assertEquals($result, $expected, $msg);
    }

    public function testGetEventTypesProvider()
    {
        return [
            [null, [], "Couldn't pass NULL calendar to getEventTypes"],
        ];
    }
}
