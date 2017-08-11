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

    public function testGetEvents()
    {
        $result = $this->CalendarEvents->getEvents(null);
        $this->assertEquals($result, []);

        $this->Calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $this->Calendars->getCalendars();

        $options = [
            'calendar_id' => $dbItems[0]->id,
        ];

        $result = $this->CalendarEvents->getEvents($dbItems[0], $options);
        $this->assertNotEmpty($result);
    }

    public function testGetEventsNoEvents()
    {
        $result = $this->CalendarEvents->getEvents(null);
        $this->assertEquals($result, []);

        $this->Calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $this->Calendars->getCalendars([
            'conditions' => [
                'id' => '9390cbc1-dc1d-474a-a372-de92dce85aac',
            ]
        ]);

        $options = [
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aac',
        ];

        $result = $this->CalendarEvents->getCalendarEvents($dbItems[0], $options);
        $this->assertEmpty($result);
    }

    public function testGetRecurringEvents()
    {
        $event = $this->CalendarEvents->find()
            ->where([
                'is_recurring' => true,
                'event_type' => 'default_event',
            ])
            ->first();

        $event->recurrence = json_decode($event->recurrence, true);
        $result = $this->CalendarEvents->getRecurringEvents($event->toArray(), [
            'period' => [
                'start_date' => '2017-08-01 09:00:00',
                'end_date' => '2020-08-01 09:00:00',
            ],
        ]);

        $this->assertNotEmpty($result);
    }

    public function testGetRecurringEventsEmptyRRule()
    {
        $event = $this->CalendarEvents->find()
            ->where([
                'is_recurring' => true,
                'event_type' => 'special_event',
            ])
            ->first();
        $event->recurrence = json_decode($event->recurrence, true);

        $result = $this->CalendarEvents->getRecurringEvents($event->toArray(), [
            'period' => [
                'start_date' => '2017-08-01 09:00:00',
                'end_date' => '2020-08-01 09:00:00',
            ],
        ]);

        $this->assertEmpty($result);
    }

    public function testGetCalendarEvents()
    {
        $calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $calendars->getCalendars();

        $result = $this->CalendarEvents->getCalendarEvents($dbItems[0]);
        $this->assertTrue(is_array($result));
        $this->assertNotEmpty($result);
    }

    public function testGetCalendarEventsWithPeriod()
    {
        $calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $calendars->getCalendars();

        $result = $this->CalendarEvents->getCalendarEvents($dbItems[0], [
            'period' => [
                'start_date' => '2017-06-16 09:00:00',
                'end_date' => '2017-06-16 20:00:00',
            ],
        ]);

        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));

        $result = $this->CalendarEvents->getCalendarEvents(null);
        $this->assertEquals($result, []);
        $this->assertTrue(is_array($result));
    }

    /**
     * @dataProvider testEventTitleProvider
     */
    public function testSetEventTitle($data, $expected)
    {
        $calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $calendars->getCalendars();

        $title = $this->CalendarEvents->setEventTitle($data, $dbItems[0]);
        $this->assertEquals($title, $expected);
    }

    public function testEventTitleProvider()
    {
        return [
            [
                ['CalendarEvents' => [
                    'start_date' => '2017-09-01 09:00:00',
                    'end_date' => '2017-09-02 09:00:00'
                    ]
                ],
                'Calendar - 1 Event',
            ],
            [
                ['CalendarEvents' => [
                    'start_date' => '2017-09-01 09:00:00',
                    'end_date' => '2017-09-02 09:00:00',
                    'event_type' => 'foobar',
                    ]
                ],
                'Calendar - 1 - Foobar',
            ]
        ];
    }

    public function testGetEventTypes()
    {
        $calendars = TableRegistry::get('Qobo/Calendar.Calendars');
        $dbItems = $calendars->getCalendars();

        foreach ($dbItems as $item) {
            $this->assertNotEmpty($item->event_types);
        }

        $this->assertEquals([], $this->CalendarEvents->getEventTypes());

        $testType = [
            'foo' => [
                'name' => 'foo',
                'value' => 'foo',
            ]
        ];

        $testCalendar = clone $dbItems[0];

        $testCalendar->event_types = $testType;

        $result = $this->CalendarEvents->getEventTypes($testCalendar);
        $this->assertEquals([ ['name' => 'foo', 'value' => 'foo'] ], $result);
    }

    /**
     * @dataProvider testGetRRuleConfigurationProvider
     * @exp
     */
    public function testGetRRuleConfguration($data, $expected, $msg)
    {
        $result = $this->CalendarEvents->getRRuleConfiguration($data);

        $this->assertEquals($result, $expected, $msg);
    }

    public function testGetRRuleConfigurationProvider()
    {
        return [
            [ ['foo' => 'bar'], '', 'RRule wasnt found'],
            [ [], '', 'Empty array' ],
            [ ['RRULE:FREQ=YEARLY'], 'RRULE:FREQ=YEARLY', 'Couldnt fetch correct RRULE element for array' ],
        ];
    }

    public function testSetIdSuffix()
    {
        $event = [
            'id' => '123',
            'start_date' => '2019-08-01 09:00:00',
            'end_date' => '2019-08-02 09:00:00',
        ];

        $eventObj = (object)$event;

        $result = $this->CalendarEvents->setIdSuffix($event);
        $resultObj = $this->CalendarEvents->setIdSuffix($eventObj);

        $this->assertNotEmpty($result);
        $this->assertEquals($result, $resultObj);
    }

    public function testGetEventInfo()
    {
        $eventId = '688580e6-2224-4dcb-a8df-32337b82e1e4';

        $result = $this->CalendarEvents->getEventInfo(['id' => $eventId]);
        $this->assertNotEmpty($result);

        $result = $this->CalendarEvents->getEventInfo([]);
        $this->assertEmpty($result);
    }
}
