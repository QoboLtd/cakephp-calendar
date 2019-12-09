<?php
namespace Qobo\Calendar\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Qobo\Calendar\Controller\CalendarEventsController;
use Qobo\Calendar\Model\Table\CalendarEventsTable;
use Qobo\Utils\TestSuite\JsonIntegrationTestCase;

/**
 * Qobo\Calendar\Controller\CalendarEventsController Test Case
 */
class CalendarEventsControllerTest extends JsonIntegrationTestCase
{
    /**
     * @var \Qobo\Calendar\Model\Table\CalendarEventsTable
     */
    protected $CalendarEvents;

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
                'User' => TableRegistry::get('Users')->get($userId)->toArray(),
            ],
        ]);
        $this->setRequestHeaders();
        $config = TableRegistry::exists('Qobo\Calendar.CalendarEvents') ? [] : ['className' => CalendarEventsTable::class];
        /**
         * @var \Qobo\Calendar\Model\Table\CalendarEventsTable $table
         */
        $table = TableRegistry::get('CalendarEvents', $config);
        $this->CalendarEvents = $table;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testIndexGetException(): void
    {
        $this->get('/calendars/calendar-events');
        $this->assertResponseError();
    }

    public function testIndexPostResponseOk(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/index', ['calendar_id' => $calendarId]);
        $events = $this->viewVariable('events');
        $this->assertNotEmpty($events);
        $this->assertTrue(is_array($events));
    }

    public function testViewResponseOk(): void
    {
        $eventId = '00000000-0000-0000-0000-000000000004';

        $this->post('/calendars/calendar-events/view', ['id' => $eventId]);

        $response = $this->viewVariable('response');
        $this->assertEquals(true, $response['success']);
    }

    public function testGetEventTypesResponseOk(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/get-event-types', ['calendar_id' => $calendarId]);
        $eventTypes = $this->viewVariable('eventTypes');

        $this->assertNotEmpty($eventTypes);
    }

    public function testGetEventTypesResponseExclude(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/get-event-types.json', ['calendar_id' => $calendarId, 'exclude' => ['json']]);
        $eventTypes = $this->viewVariable('eventTypes');
        $this->assertEquals(count($eventTypes), 1);
        $this->assertNotEmpty($eventTypes);
    }

    public function testAddResponseError(): void
    {
        $this->get('/calendars/calendar-events/add');
        $this->assertResponseError();
    }

    public function testAddErrorResponse(): void
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

    public function testAddGenerateTitle(): void
    {
        $data = [
            'calendar_id' => '00000000-0000-0000-0000-000000000001',
            'content' => 'Foobar - 123',
            'start_date' => '2018-04-09 09:00:00',
            'end_date' => '2018-04-09 10:00:00',
            'is_recurring' => false,
        ];

        $this->post('/calendars/calendar-events/add', $data);

        /** @var \Cake\Datasource\EntityInterface $saved */
        $saved = $this->CalendarEvents->find()
            ->where([
                'content' => $data['content'],
            ])
            ->first();

        $this->assertEquals('Calendar - 1 Event', $saved->get('title'));
        $this->assertEquals($saved->get('content'), $data['content']);
    }

    public function testAddResponseOk(): void
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

        /** @var \Cake\Datasource\EntityInterface $saved */
        $saved = $this->CalendarEvents->find()
            ->where([
                'title' => 'Test Event',
                'content' => $data['content'],
            ])
            ->first();

        $this->assertEquals($saved->get('content'), $data['content']);
    }

    public function testAddRecurringEvent(): void
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
               '00000000-0000-0000-0000-000000000001',
            ],
        ];

        $this->post('/calendars/calendar-events/add', $data);

        /** @var \Cake\ORM\Query */
        $query = $this->CalendarEvents
            ->find()
            ->where([
                'title' => $data['title'],
            ])
            ->contain(['CalendarAttendees']);

        /** @var \Cake\Datasource\EntityInterface $saved */
        $saved = $query->first();

        $this->assertEquals($saved->get('title'), $data['title']);
        $this->assertEquals(1, count($saved->get('calendar_attendees')));
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $saved->get('calendar_attendees')[0]->id);
    }

    public function testDeleteResponseOk(): void
    {
        $eventId = '00000000-0000-0000-0000-000000000004';

        $this->delete('/calendars/calendar-events/delete/' . $eventId);
        $this->assertRedirect('/calendars/calendars');
    }
}
