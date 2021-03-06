<?php
namespace Qobo\Calendar\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Qobo\Calendar\Model\Table\CalendarAttendeesTable;
use Qobo\Utils\TestSuite\JsonIntegrationTestCase;

/**
 * Qobo\Calendar\Controller\CalendarAttendeesController Test Case
 */
class CalendarAttendeesControllerTest extends JsonIntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Qobo/Calendar.Users',
        'plugin.Qobo/Calendar.CalendarAttendees',
        'plugin.Qobo/Calendar.CalendarEvents',
        'plugin.Qobo/Calendar.EventsAttendees',
        'plugin.Qobo/Calendar.Calendars',
    ];

    /** @var \Qobo\Calendar\Model\Table\CalendarAttendeesTable */
    private $CalendarAttendees;

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

        $config = TableRegistry::exists('CalendarAttendees') ? [] : ['className' => CalendarAttendeesTable::class];

        /** @var \Qobo\Calendar\Model\Table\CalendarAttendeesTable $table */
        $table = TableRegistry::get('CalendarAttendees', $config);
        $this->CalendarAttendees = $table;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testDeleteResponseOk(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';

        $this->delete('/calendars/calendar-attendees/delete/' . $id);
        $this->assertRedirect('/calendars/calendars');
    }

    public function testViewResponseOk(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/calendars/calendar-attendees/view/' . $id);
        $calendarAttendee = $this->viewVariable('calendarAttendee');

        $attendee = $this->CalendarAttendees->get($id);
        $this->assertEquals($calendarAttendee['id'], $attendee->id);
    }

    public function testLookup(): void
    {
        $term = [
            'term' => 'John',
        ];

        $this->post('/calendars/calendar-attendees/lookup.json', $term);
        $this->assertResponseOk();

        $result = $this->viewVariable('result');
        $this->assertNotEmpty($result);
        $this->assertEquals('00000000-0000-0000-0000-000000000001', $result[0]['id']);
    }
}
