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

        $item = $this->CalendarEvents->get($eventId);

        $this->post('/calendars/calendar-events/view', ['id' => $eventId]);
        $calEvent = $this->viewVariable('calEvent');

        $this->assertEquals($item->title, $calEvent->title);
    }

    public function testGetEventTypesResponseOk()
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->post('/calendars/calendar-events/get-event-types', ['calendar_id' => $calendarId]);
        $eventTypes = $this->viewVariable('eventTypes');

        $this->assertNotEmpty($eventTypes);
        $this->assertEquals($eventTypes[0]['name'], 'Config::Default::default');
    }

    public function testAddResponseError()
    {
        $this->get('/calendars/calendar-events/add');
        $this->assertResponseError();
    }

    public function testAddResponseOk()
    {
        $data = [
            'CalendarEvents' => [
                'calendar_id' => '00000000-0000-0000-0000-000000000001',
                'content' => 'Foobar',
                'title' => 'Test Event',
                'start_date' => '2018-04-09 09:00:00',
                'end_date' => '2018-04-09 10:00:00',
                'is_recurring' => false,
            ]
        ];

        $this->post('/calendars/calendar-events/add', $data);
        $event = $this->viewVariable('event');

        $this->assertEquals('Successfully saved Event', $event['message']);

        $saved = $this->CalendarEvents->find()
            ->where(['title' => 'Test Event'])
            ->first();

        $this->assertEquals($saved->content, $data['CalendarEvents']['content']);
    }
}
