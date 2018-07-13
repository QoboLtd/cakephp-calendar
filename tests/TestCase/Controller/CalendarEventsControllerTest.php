<?php
namespace Qobo\Calendar\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Qobo\Calendar\Controller\CalendarEventsController;
use Qobo\Calendar\Model\Table\CalendarEventsTable;

/**
 * Qobo\Calendar\Controller\CalendarEventsController Test Case
 */
class CalendarEventsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.users',
        'plugin.qobo/calendar.calendar_events',
        'plugin.qobo/calendar.events_attendees',
        'plugin.qobo/calendar.calendar_attendees',
        'plugin.qobo/calendar.calendars',
    ];

    public function setUp()
    {
        parent::setUp();

        $userId = '00000000-0000-0000-0000-000000000001';
        $this->session([
            'Auth' => [
                'User' => TableRegistry::get('Users')->get($userId)->toArray()
            ]
        ]);
        $config = TableRegistry::exists('CalendarEvents') ? [] : ['className' => CalendarEventsTable::class];
        $this->CalendarEvents = TableRegistry::get('CalendarEvents', $config);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testIndexGetException()
    {
        $this->get('/calendars/calendar-events');
        $this->assertResponseError();
    }

    public function testIndexPostResponseOk()
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/index', ['calendar_id' => $calendarId]);
        $events = $this->viewVariable('events');
        $this->assertNotEmpty($events);
        $this->assertTrue(is_array($events));
    }

    public function testViewResponseOk()
    {
        $eventId = '00000000-0000-0000-0000-000000000004';

        $this->post('/calendars/calendar-events/view', ['id' => $eventId]);

        $response = $this->viewVariable('response');
        $this->assertEquals(true, $response['success']);
    }

    public function testGetEventTypesResponseOk()
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/get-event-types', ['calendar_id' => $calendarId]);
        $eventTypes = $this->viewVariable('eventTypes');

        $this->assertNotEmpty($eventTypes);
    }

    public function testGetEventTypesResponseExclude()
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/get-event-types.json', ['calendar_id' => $calendarId, 'exclude' => ['json']]);
        $eventTypes = $this->viewVariable('eventTypes');
        $this->assertEquals(count($eventTypes), 1);
        $this->assertNotEmpty($eventTypes);
    }

    public function testAddResponseError()
    {
        $this->get('/calendars/calendar-events/add');
        $this->assertResponseError();
    }

    public function testAddErrorResponse()
    {
        $data = [
            'calendar_id' => null,
            'title' => 'Calendar Foobar',
            'content' => 'Foobar Content - 123',
            'start_date' => '2018-04-09 09:00:00',
            'end_date' => '2018-04-09 10:00:00',
            'is_recurring' => false,
        ];

        $this->post('/calendars/calendar-events/add', $data);
        $event = $this->viewVariable('response');

        $this->assertNotEmpty($event['errors']);
        $this->assertEquals($event['success'], false);
    }

    public function testAddGenerateTitle()
    {
        $data = [
            'calendar_id' => '00000000-0000-0000-0000-000000000001',
            'content' => 'Foobar - 123',
            'start_date' => '2018-04-09 09:00:00',
            'end_date' => '2018-04-09 10:00:00',
            'is_recurring' => false,
        ];

        $this->post('/calendars/calendar-events/add', $data);
        $saved = $this->CalendarEvents->find()
            ->where([
                'content' => $data['content']
            ])
            ->first();

        $this->assertEquals('Calendar - 1 Event', $saved->title);
        $this->assertEquals($saved->content, $data['content']);
    }

    public function testAddResponseOk()
    {
        $data = [
            'calendar_id' => '00000000-0000-0000-0000-000000000001',
            'content' => 'Foobar',
            'title' => 'Test Event',
            'start_date' => '2018-04-09 09:00:00',
            'end_date' => '2018-04-09 10:00:00',
            'is_recurring' => false,
        ];

        $this->post('/calendars/calendar-events/add', $data);
        $event = $this->viewVariable('response');
        $this->assertEquals($event['success'], true);

        $saved = $this->CalendarEvents->find()
            ->where([
                'title' => 'Test Event',
                'content' => $data['content']
            ])
            ->first();

        $this->assertEquals($saved->content, $data['content']);
    }

    public function testAddRecurringEvent()
    {
        $data = [
            'calendar_id' => '00000000-0000-0000-0000-000000000001',
            'content' => 'Recurring content',
            'title' => 'Recurring event - every day',
            'start_date' => '2018-04-09 09:00:00',
            'end_date' => '2018-04-09 10:00:00',
            'is_recurring' => true,
            'recurrence' => 'RRULE:FREQ=DAILY;INTERVAL=1;COUNT=5',
            'attendees_ids' => [
               '00000000-0000-0000-0000-000000000001'
            ]
        ];

        $this->post('/calendars/calendar-events/add', $data);
        $saved = $this->CalendarEvents->find()
            ->contain(['CalendarAttendees'])
            ->where([
                'title' => $data['title'],
            ])->first();
        $this->assertEquals($saved->title, $data['title']);
        $this->assertEquals(1, count($saved->calendar_attendees));
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $saved->calendar_attendees[0]->id);
    }

    public function testDeleteResponseOk()
    {
        $eventId = '00000000-0000-0000-0000-000000000004';

        $this->delete('/calendars/calendar-events/delete/' . $eventId);
        $this->assertRedirect('/calendars/calendars');
    }
}
