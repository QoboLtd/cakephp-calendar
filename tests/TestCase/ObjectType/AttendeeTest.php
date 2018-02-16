<?php
namespace Qobo\Calendar\Test\TestCase\ObjectType;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\ObjectType\ObjectTypeFactory;

class AttendeeTest extends TestCase
{
    protected $table;

    public $fixtures = [
        'plugin.qobo/calendar.calendar_attendees',
    ];

    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Qobo/Calendars.CalendarAttendees') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarAttendeesTable'];
        $this->table = TableRegistry::get('Qobo/Calendars.CalendarAttendees', $config);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testToEntity()
    {
        $entityId = '12613e81-aa1b-4c59-9eb4-e4016ac47aef';
        $entity = $this->table->find()
            ->where(['id' => $entityId])
            ->first();

        $object = ObjectTypeFactory::getInstance($entity, 'Attendee', 'Cake');
        $cakeEntity = $object->toEntity();
        $this->assertTrue(is_object($cakeEntity));
        $this->assertInstanceOf('\Qobo\Calendar\Model\Entity\CalendarAttendee', $cakeEntity);
        $this->assertEquals($cakeEntity->id, $entityId);
    }
}
