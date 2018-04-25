<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Event\EventName;
use Qobo\Calendar\Model\Table\CalendarsTable;

/**
 * Qobo\Calendar\Model\Table\CalendarsTable Test Case
 */
class CalendarsTableTest extends TestCase
{

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
        'plugin.qobo/calendar.calendars',
        'plugin.qobo/calendar.calendar_events'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        $this->Calendars = TableRegistry::get('Calendars', $config);

        // @TODO: return something useful to test sync method.
        EventManager::instance()->on((string)EventName::PLUGIN_CALENDAR_MODEL_GET_CALENDARS(), function ($event, $table) {
            return [];
        });
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Calendars);

        parent::tearDown();
    }

    public function testBeforeSave()
    {
        $data = [
            'id' => '6d2b932f-b79a-4523-a2d2-3ddaadfa805c',
            'name' => 'Test Calendar for BeforeSave',
        ];

        $entity = $this->Calendars->newEntity($data);
        $saved = $this->Calendars->save($entity);
        $this->assertEquals($saved->source, 'Plugin__');
        $this->assertEquals($saved->color, $this->Calendars->getColor());
    }

    public function testSync()
    {
        EventManager::instance()->setEventList(new EventList());
        $data = [];

        $foo = $this->Calendars->sync($data);

        $this->assertEventFired((string)EventName::PLUGIN_CALENDAR_MODEL_GET_CALENDARS(), EventManager::instance());
    }

    public function testGetCalendarTypes()
    {
        $result = $this->Calendars->getTypes();
        $this->assertTrue(is_array($result));
    }

    public function testGetCalendars()
    {
        $result = $this->Calendars->getCalendars();
        $this->assertTrue(!empty($result));

        $result = $this->Calendars->getCalendars(['id' => '00000000-0000-0000-0000-000000000001']);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '00000000-0000-0000-0000-000000000001');

        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '00000000-0000-0000-0000-000000000001']]);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '00000000-0000-0000-0000-000000000001');
    }
}
