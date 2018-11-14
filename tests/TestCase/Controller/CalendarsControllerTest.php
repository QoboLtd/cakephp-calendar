<?php
namespace Qobo\Calendar\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Qobo\Calendar\Controller\CalendarsController;
use Qobo\Calendar\Model\Table\CalendarsTable;

/**
 * Qobo\Calendar\Controller\CalendarsController Test Case
 */
class CalendarsControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.users',
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

        $this->Calendars = TableRegistry::get('Calendars', ['className' => CalendarsTable::class]);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testIndexGetResponseOk(): void
    {
        $this->get('/calendars/calendars/index');
        $this->assertResponseOk();
    }

    public function testIndexPostResponseOk(): void
    {
        $data = [
            'public' => true,
        ];

        $this->post('/calendars/calendars/', $data);
        $this->assertResponseOk();
    }

    public function testViewResponseOk(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/calendars/calendars/view/' . $id);
        $this->assertResponseOk();
    }
    public function testAddResponseOk(): void
    {
        $this->get('/calendars/calendars/add');
        $this->assertResponseOk();

        $data = [
            'name' => 'Test Calendar - fake',
            'active' => true,
        ];

        $this->post('/calendars/calendars/add', $data);
        $this->assertRedirect('/calendars/calendars');

        $saved = $this->Calendars->find()
            ->where(['name' => $data['name']])
            ->first();

        $this->assertEquals($saved->name, $data['name']);
    }

    public function testAddResponseError(): void
    {
        $data = [];
        $this->post('/calendars/calendars/add', $data);
        $message = $this->_requestSession->read('Flash.flash.0');

        $this->assertEquals($message['message'], 'The calendar could not be saved. Please, try again.');
    }

    public function testAddResponseOkWithEventTypes(): void
    {
        $this->get('/calendars/calendars/add');
        $this->assertResponseOk();

        $data = [
            'name' => 'Test Calendar - fake',
            'active' => true,
            'event_types' => [
                'Config::Default::Default',
                'Json::Leads::Default'
            ]
        ];

        $this->post('/calendars/calendars/add', $data);
        $this->assertRedirect('/calendars/calendars');

        $saved = $this->Calendars->find()
            ->where(['name' => $data['name']])
            ->first();

        $eventTypes = json_decode($saved->event_types, true);
        $this->assertEquals($eventTypes, $data['event_types']);
        $this->assertEquals($saved->name, $data['name']);
    }

    public function testEditResponseOk(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->get('/calendars/calendars/edit/' . $calendarId);
        $this->assertResponseOk();

        $data = [
            'icon' => 'facebook',
            'event_types' => [
                'Bar::foo::default',
                'Foo::bar::default',
            ]
        ];

        $this->post('/calendars/calendars/edit/' . $calendarId, $data);
        $this->assertRedirect('/calendars/calendars');

        $edited = $this->Calendars->get($calendarId);

        $this->assertEquals($edited->icon, $data['icon']);
        $this->assertEquals($calendarId, $edited->id);
        $this->assertTrue(in_array('Config::Default::Default', json_decode($edited->event_types, true)));
    }

    public function testDeleteResponseOk(): void
    {
        $calendarId = '00000000-0000-0000-0000-000000000001';

        $this->delete('/calendars/calendars/delete/' . $calendarId);
        $this->assertRedirect('/calendars/calendars');
    }
}
