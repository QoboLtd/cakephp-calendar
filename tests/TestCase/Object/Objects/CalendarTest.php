<?php
namespace Qobo\Calendar\Test\TestCase\Object\Objects;

use Cake\TestSuite\TestCase;
use Qobo\Calendar\Object\ObjectFactory;
use Qobo\Calendar\Object\Objects\Calendar;

class CalendarTest extends TestCase
{
    public $fixtures = [
        'plugin.qobo/calendar.calendars',
    ];

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSetId(): void
    {
        $id = '123';
        $obj = new Calendar();
        $obj->setId($id);
        $this->assertEquals($id, $obj->getId());
    }

    public function testGetEntityProvider(): void
    {
        $obj = new Calendar();
        $this->assertNotEmpty($obj->getEntityProvider());
    }

    public function testSetName(): void
    {
        $obj = new Calendar();
        $name = 'Foobar';
        $obj->setName($name);
        $this->assertEquals($name, $obj->getName());
    }

    public function testSetIcon(): void
    {
        $icon = 'fa-close';
        $obj = new Calendar();

        $obj->setIcon($icon);
        $this->assertEquals($icon, $obj->getIcon());
    }

    public function testSetColor(): void
    {
        $color = 'red';
        $obj = new Calendar();

        $obj->setColor($color);
        $this->assertEquals($color, $obj->getColor());
    }

    public function testSetCalendarType(): void
    {
        $type = 'default';
        $obj = new Calendar();

        $obj->setCalendarType($type);
        $this->assertEquals($type, $obj->getCalendarType());
    }

    public function testSetSource(): void
    {
        $source = 'App__';
        $obj = new Calendar();

        $obj->setSource($source);
        $this->assertEquals($source, $obj->getSource());
    }

    public function testSetActive(): void
    {
        $active = true;
        $obj = new Calendar();
        $obj->setActive($active);
        $this->assertEquals($active, $obj->getActive());
    }

    public function testSetEditable(): void
    {
        $editable = false;
        $obj = new Calendar();
        $obj->setEditable($editable);
        $this->assertEquals($editable, $obj->getEditable());
    }

    public function testSetIsPublic(): void
    {
        $isPublic = true;
        $obj = new Calendar();
        $obj->setIsPublic($isPublic);
        $this->assertEquals($isPublic, $obj->getIsPublic());
    }

    public function testGetSourceId(): void
    {
        $sourceId = '1234';
        $obj = new Calendar();
        $obj->setSourceId($sourceId);
        $this->assertEquals($sourceId, $obj->getSourceId());
    }
}
