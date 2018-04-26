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
}
