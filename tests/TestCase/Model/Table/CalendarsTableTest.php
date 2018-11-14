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
     * @var \Qobo\Calendar\Model\Table\CalendarsTable $Calendars
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
        $config = TableRegistry::exists('Qobo\Calendar.Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        /**
         * @var \Qobo\Calendar\Model\Table\CalendarsTable $table
         */
        $table = TableRegistry::get('Calendars', $config);
        $this->Calendars = $table;

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

    public function testBeforeSave(): void
    {
        $data = [
            'id' => '6d2b932f-b79a-4523-a2d2-3ddaadfa805c',
            'name' => 'Test Calendar for BeforeSave',
        ];

        $entity = $this->Calendars->newEntity($data);
        /**
         * @var \Qobo\Calendar\Model\Entity\Calendar $saved
         */
        $saved = $this->Calendars->save($entity);
        $this->assertEquals($saved->source, 'Plugin__');
        $this->assertEquals($saved->color, $this->Calendars->getColor());
    }

    public function testSync(): void
    {
        EventManager::instance()->setEventList(new EventList());
        $data = [];

        $foo = $this->Calendars->sync($data);

        $this->assertEventFired((string)EventName::PLUGIN_CALENDAR_MODEL_GET_CALENDARS(), EventManager::instance());
    }

    public function testGetCalendars(): void
    {
        $result = $this->Calendars->getCalendars();
        $this->assertTrue(!empty($result));

        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '00000000-0000-0000-0000-000000000001']]);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '00000000-0000-0000-0000-000000000001');
    }

    public function testGetCalendarsEmpty(): void
    {
        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '00000000-0000-0000-0000-120000000001']]);
        $this->assertEmpty($result);
    }

    public function testGetByAllowedEventTypes(): void
    {
        $result = $this->Calendars->getByAllowedEventTypes('Config');
        $this->assertNotEmpty($result);

        $result = $this->Calendars->getByAllowedEventTypes('Foobar');
        $this->assertEquals($result, []);
    }
}
