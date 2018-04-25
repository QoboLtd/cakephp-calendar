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

    public function testIndexGetResponseOk()
    {
        $this->get('/calendars/calendars/index');
        $this->assertResponseOk();
    }

    public function testIndexPostResponseOk()
    {
        $data = [
            'public' => true,
        ];

        $this->post('/calendars/calendars/', $data);
        $this->assertResponseOk();
    }

    public function testViewResponseOk()
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/calendars/calendars/view/' . $id);
        $this->assertResponseOk();
    }
}
