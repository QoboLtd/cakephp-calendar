<?php
namespace Qobo\Calendar\Test\TestCase\ObjectType;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\ObjectType\ObjectTypeFactory;

class CalendarTest extends TestCase
{
    protected $caledarsTable;

    public $fixtures = [
        'plugin.qobo/calendar.calendars',
    ];

    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Qobo/Calendars.Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        $this->calendarsTable = TableRegistry::get('Qobo/Calendars.Calendars', $config);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testToEntity()
    {
        $calendarId = '9390cbc1-dc1d-474a-a372-de92dce85aaa';
        $calendar = $this->calendarsTable->find()
            ->where(['id' => $calendarId])
            ->first();

        $object = ObjectTypeFactory::getInstance($calendar, 'Calendar', 'Cake');

        $cakeEntity = $object->toEntity();
        $this->assertTrue(is_object($cakeEntity));
        $this->assertInstanceOf('\Qobo\Calendar\Model\Entity\Calendar', $cakeEntity);
        $this->assertEquals($cakeEntity->id, $calendarId);
    }
}
