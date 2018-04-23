<?php
namespace Qobo\Calendar\Test\TestCase\Object\Objects;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Object\ObjectFactory;
use Qobo\Calendar\Object\Objects\Calendar;

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

    public function testSetId()
    {
        $id = '123';
        $obj = new Calendar();
        $obj->setId($id);
        $this->assertEquals($id, $obj->getId());
    }

    public function testGetEntityProvider()
    {
        $obj = new Calendar();
        $this->assertNotEmpty($obj->getEntityProvider());
    }

    public function testSetName()
    {
        $obj = new Calendar();
        $name = 'Foobar';
        $obj->setName($name);
        $this->assertEquals($name, $obj->getName());
    }

    public function testSetIcon()
    {
        $icon = 'fa-close';
        $obj = new Calendar();

        $obj->setIcon($icon);
        $this->assertEquals($icon, $obj->getIcon());
    }

    public function testSetColor()
    {
        $color = 'red';
        $obj = new Calendar();

        $obj->setColor($color);
        $this->assertEquals($color, $obj->getColor());
    }

    public function testSetCalendarType()
    {
        $type = 'default';
        $obj = new Calendar();

        $obj->setCalendarType($type);
        $this->assertEquals($type, $obj->getCalendarType());
    }

    public function testSetSource()
    {
        $source = 'App__';
        $obj = new Calendar();

        $obj->setSource($source);
        $this->assertEquals($source, $obj->getSource());
    }

    public function testSetActive()
    {
        $active = true;
        $obj = new Calendar();
        $obj->setActive($active);
        $this->assertEquals($active, $obj->getActive());
    }

    public function testSetEditable()
    {
        $editable = false;
        $obj = new Calendar();
        $obj->setEditable($editable);
        $this->assertEquals($editable, $obj->getEditable());
    }

    public function testSetIsPublic()
    {
        $isPublic = true;
        $obj = new Calendar();
        $obj->setIsPublic($isPublic);
        $this->assertEquals($isPublic, $obj->getIsPublic());
    }

    public function testGetSourceId()
    {
        $sourceId = '1234';
        $obj = new Calendar();
        $obj->setSourceId($sourceId);
        $this->assertEquals($sourceId, $obj->getSourceId());
    }
}
