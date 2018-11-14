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

    public function testGetEvents(): void
    {
        $result = $this->CalendarEvents->getEvents(null);
        $this->assertEquals($result, []);
        $dbItems = $this->Calendars->getCalendars();
        $options = [
            'calendar_id' => $dbItems[0]->id,
        ];
        $result = $this->CalendarEvents->getEvents($dbItems[0], $options, false);
        $this->assertNotEmpty($result);
    }

    public function testGetEventsWithTimePeriod(): void
    {
        $options = [
            'calendar_id' => '00000000-0000-0000-0000-000000000001',
            'period' => [
                'start_date' => '2017-08-10 09:00:00',
                'end_date' => '2017-08-12 09:00:00'
            ],
        ];
        $calendar = $this->Calendars->get($options['calendar_id']);
        $result = $this->CalendarEvents->getEvents($calendar, $options, false);
        $this->assertNotEmpty($result);

        $result = $this->CalendarEvents->getEvents($calendar, [], false);
        $this->assertEquals($result, []);
    }

    public function testSetIdSuffix(): void
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

    public function testGetRecurrenceEventId(): void
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

    public function testGetEventInfo(): void
    {
        $eventId = '00000000-0000-0000-0000-000000000003';

        $result = $this->CalendarEvents->getEventInfo($eventId);
        $this->assertNotEmpty($result);

        $result = $this->CalendarEvents->getEventInfo([]);
        $this->assertEmpty($result);

        $result = $this->CalendarEvents->getEventInfo($eventId . '__' . '1564650000_1564736400');

        $this->assertEquals(true, $result->isDirty('end_date'));
        $this->assertEquals(true, $result->isDirty('start_date'));
    }

    public function testGetEventTypes(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $calendar = $this->Calendars->get($calendarId);
        $result = $this->CalendarEvents->getEventTypes(['calendar' => $calendar, 'user' => null]);
        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
    }

    public function testSetRRuleConfiguration(): void
    {
        $data = 'FREQ=MONTHLY;COUNT=30;WKST=MO';
        $recurrence = $this->CalendarEvents->setRRuleConfiguration($data);
        $this->assertEquals($recurrence, 'RRULE:' . $data);
    }

    /**
     * @dataProvider getRRuleConfigurationProvider
     * @param string $data Data
     * @param string $expected Expected result
     */
    public function testGetRRuleConfiguration(string $data = null, string $expected = null): void
    {
        $result = $this->CalendarEvents->getRRuleConfiguration($data);
        $this->assertEquals($expected, $result);
    }

    public function testGetDefaultEventType(): void
    {
        $result = $this->CalendarEvents->getEventTypeBy('default');
        $this->assertNotEmpty($result);
    }

    /**
     * @return mixed[]
     */
    public function getRRuleConfigurationProvider(): array
    {
        return [
            ['FREQ=DAILY;COUNT=5', 'RRULE:FREQ=DAILY;COUNT=5'],
            ['RRULE:FREQ=MONTHLY;COUNT=1', 'RRULE:FREQ=MONTHLY;COUNT=1'],
            ['', null],
            [null, null],
        ];
    }

    /**
     * @dataProvider getEventRangeProvider
     * @param mixed[] $data Data
     * @param mixed[] $expected Expected result
     */
    public function testGetEventRange(array $data, array $expected): void
    {
        $result = $this->CalendarEvents->getEventRange($data);
        $this->assertEquals($result, $expected);
    }

    /**
     * @return mixed[]
     */
    public function getEventRangeProvider(): array
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
