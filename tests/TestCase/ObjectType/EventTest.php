<?php
namespace Qobo\Calendar\Test\TestCase\ObjectType;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\ObjectType\ObjectTypeFactory;

class EventTest extends TestCase
{
    protected $table;

    public $fixtures = [
        'plugin.qobo/calendar.calendar_events',
    ];

    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Qobo/Calendars.CalendarEvents') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarEventsTable'];
        $this->table = TableRegistry::get('Qobo/Calendars.CalendarEvents', $config);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testToEntity()
    {
        $entityId = '688580e6-2224-4dcb-a8df-32337b82e1e6';
        $entity = $this->table->find()
            ->where(['id' => $entityId])
            ->first();

        $object = ObjectTypeFactory::getInstance($entity, 'Event', 'Cake');

        $cakeEntity = $object->toEntity();
        $this->assertTrue(is_object($cakeEntity));
        $this->assertInstanceOf('\Qobo\Calendar\Model\Entity\CalendarEvent', $cakeEntity);
        $this->assertEquals($cakeEntity->id, $entityId);
    }
}
