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
}
