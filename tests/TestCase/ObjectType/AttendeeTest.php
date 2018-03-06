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
}
