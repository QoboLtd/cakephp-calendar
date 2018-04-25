<?php
namespace Qobo\Calendar\Test\TestCase\ObjectType;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Object\ObjectFactory;

class ObjectFactoryTest extends TestCase
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

    public function testGetParserConfig()
    {
        $result = ObjectFactory::getParserConfig('Leads', 'Event', []);
        $this->assertNotEmpty($result);
        $this->assertTrue(is_object($result));
        $this->assertEquals($result->calendar_id->value, 'test');
    }
}
