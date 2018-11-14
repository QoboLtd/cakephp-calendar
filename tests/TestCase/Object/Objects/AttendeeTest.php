<?php
namespace Qobo\Calendar\Test\TestCase\Object\Objects;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Object\ObjectFactory;
use Qobo\Calendar\Object\Objects\Attendee;

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

    public function testSetDisplayName(): void
    {
        $name = 'Francis';
        $obj = new Attendee();
        $obj->setDisplayName($name);
        $this->assertEquals($name, $obj->getDisplayName());
    }

    public function testSetContactDetails(): void
    {
        $details = 'mymymy@example.com';
        $obj = new Attendee();
        $obj->setContactDetails($details);

        $this->assertEquals($details, $obj->getContactDetails());
    }
}
