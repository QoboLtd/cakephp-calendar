<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Model\Table\CalendarEventsTable;
use Qobo\Calendar\Model\Table\CalendarsTable;

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
        $config = TableRegistry::exists('CalendarEvents') ? [] : ['className' => CalendarEventsTable::class];
        $this->CalendarEvents = TableRegistry::get('CalendarEvents', $config);

        $config = TableRegistry::exists('Calendars') ? [] : ['className' => CalendarsTable::class];
        $this->Calendars = TableRegistry::get('Calendars', $config);
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
                'id' => '00000000-0000-0000-0000-000000000004',
            ]
        ]);

        $options = [
            'calendar_id' => '00000000-0000-0000-0000-000000000004',
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

        $result = $this->CalendarEvents->setRecurrenceEventId($event);
        $resultObj = $this->CalendarEvents->setRecurrenceEventId($eventObj);

        $this->assertNotEmpty($result);
        $this->assertEquals($result, $resultObj);
    }

    public function testGetRecurrenceEventId()
    {
        $event = [
            'id' => '0e03bd09-7437-4f9b-9cb4-f2801f87b850',
            'start_date' => '2019-08-01 09:00:00',
            'end_date' => '2019-08-02 09:00:00',
        ];

        $result = $this->CalendarEvents->setRecurrenceEventId($event);
        $timestamp = $this->CalendarEvents->getRecurrenceEventId($result);
        $this->assertEquals($timestamp['start'], $event['start_date'], 'Start dates unequal');
        $this->assertEquals($timestamp['end'], $event['end_date'], 'End dates unequal');

        $result = str_replace('_', '', $result);
        $wrongTimestamp = $this->CalendarEvents->getRecurrenceEventId($result);
        $this->assertEquals([], $this->CalendarEvents->getRecurrenceEventId());

        $this->assertEquals($wrongTimestamp['id'], $event['id']);
        $this->assertEquals($wrongTimestamp['start'], null);
        $this->assertEquals([], $this->CalendarEvents->getRecurrenceEventId());
    }

    public function testGetEventInfo()
    {
        $eventId = '00000000-0000-0000-0000-000000000003';

        $result = $this->CalendarEvents->getEventInfo($eventId);
        $this->assertNotEmpty($result);

        $result = $this->CalendarEvents->getEventInfo([]);
        $this->assertEmpty($result);

        $result = $this->CalendarEvents->getEventInfo($eventId . '__' . '1564650000_1564736400');

        $this->assertEquals(true, $result->dirty('end_date'));
        $this->assertEquals(true, $result->dirty('start_date'));
    }

    public function testGetEventTypes()
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $calendar = $this->Calendars->get($calendarId);
        $result = $this->CalendarEvents->getEventTypes(['calendar' => $calendar, 'user' => null]);
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
    }

    /**
     * @dataProvider testGetEventRangeProvider
     */
    public function testGetEventRange($data, $expected)
    {
        $result = $this->CalendarEvents->getEventRange($data);
        $this->assertEquals($result, $expected);
    }

    public function testGetEventRangeProvider()
    {
        return [
            [
                [],
                []
            ],
            [
                [
                    'period' => [
                        'start_date' => '2018-04-09 09:30:00',
                        'end_date' => '2018-05-01 08:00:00'
                    ]
                ],
                [
                    'start' => [
                        'MONTH(start_date) >=' => '04',
                    ],
                    'end' => [
                        'MONTH(end_date) <=' => '05',
                    ]
                ]
            ]
        ];
    }
}
