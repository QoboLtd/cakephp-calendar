<?php
namespace Qobo\Calendar\Test\TestCase\Object\Objects;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Object\ObjectFactory;
use Qobo\Calendar\Object\Objects\Event;

class EventTest extends TestCase
{
    protected $table;

    public $fixtures = [
        'plugin.Qobo/Calendar.CalendarEvents',
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

    public function testSetCalendarId(): void
    {
        $id = '123123';
        $obj = new Event();
        $obj->setCalendarId($id);
        $this->assertEquals($id, $obj->getCalendarId());
    }

    public function testSetTitle(): void
    {
        $title = 'Thou shall not pass';
        $obj = new Event();
        $obj->setTitle($title);
        $this->assertEquals($title, $obj->getTitle());
    }

    public function testSetContent(): void
    {
        $content = 'Hello, Dolly!';
        $obj = new Event();

        $obj->setContent($content);
        $this->assertEquals($content, $obj->getContent());
    }

    public function testSetStartDate(): void
    {
        $date = '2018-04-21 09:00:00';
        $obj = new Event();
        $obj->setStartDate($date);
        $obj->setEndDate($date);
        $this->assertEquals($date, $obj->getStartDate()->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $this->assertEquals($date, $obj->getEndDate()->i18nFormat('yyyy-MM-dd HH:mm:ss'));
    }

    public function testSetEventType(): void
    {
        $type = 'default';
        $obj = new Event();
        $obj->setEventType($type);
        $this->assertEquals($type, $obj->getEventType());
    }

    public function testSetIsRecurring(): void
    {
        $isRecurring = false;
        $obj = new Event();
        $obj->setIsRecurring($isRecurring);
        $this->assertEquals($isRecurring, $obj->getIsRecurring());
    }

    public function testSetRecurrence(): void
    {
        $recurrence = 'foo';

        $obj = new Event();
        $obj->setRecurrence($recurrence);
        $this->assertEquals($recurrence, $obj->getRecurrence());
    }

    public function testSetIsAllday(): void
    {
        $isAllday = true;
        $obj = new Event();
        $obj->setIsAllday($isAllday);
        $this->assertEquals($isAllday, $obj->getIsAllday());
    }
}
