<?php
namespace Qobo\Calendar\Test\TestCase\ObjectType;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\ObjectType\ObjectTypeFactory;

class ObjectTypeFactoryTest extends TestCase
{
    protected $calendarsTable;

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
        unset($this->calendarsTable);

        parent::tearDown();
    }

    public function testGetParser()
    {
        $result = ObjectTypeFactory::getParser('AppEntity');
        $this->assertTrue(is_object($result));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInstanceEmpty()
    {
        $result = ObjectTypeFactory::getCalendarInstance([], 'AppEntity');
    }

    public function testGetInstanceFromAppEntity()
    {
        $calendarId = '9390cbc1-dc1d-474a-a372-de92dce85aaa';
        $calendar = $this->calendarsTable->find()
            ->where(['id' => $calendarId])
            ->first();

        $object = ObjectTypeFactory::getCalendarInstance($calendar, 'AppEntity');

        $this->assertTrue(is_object($object));
        $this->assertInstanceOf('Qobo\Calendar\ObjectType\Calendars\Calendar', $object);
        $this->assertEquals($calendar->name, $object->getName());
        $this->assertEquals($calendar->id, $object->getId());
    }
}
