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
}
