<?php
namespace Qobo\Calendar\Test\TestCase\Objects;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Objects\Calendar;

class CalendarTest extends TestCase
{

    public $instance;
    public $Calendars;

    public $fixtures = [
        'plugin.qobo/calendar.calendars',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->instance = new Calendar();
        $config = TableRegistry::exists('Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        $this->Calendars = TableRegistry::get('Calendars', $config);
    }

    /**
     * @expectedException \Exception
     */
    public function testFromArrayEmpty()
    {
        $this->instance->fromArray([]);
    }

    /**
     * @dataProvider testFromArrayProvider
     */
    public function testFromArray($data, $expected, $msg)
    {
        $dummyCalendar = new Calendar($data);

        $this->assertEquals(
            $dummyCalendar->getAttribute('sourceId'),
            $data['source_id']
        );

        $this->instance->fromArray($data);
        $this->assertEquals(
            $data['source_id'],
            $this->instance->getAttribute('sourceId'),
            $msg
        );
    }

    public function testFromArrayProvider()
    {
        return [
            [
                [
                    'source_id' => 'trading-point.com_82g3m4lvteuddp9cag1q9io9jg@group.calendar.google.com',
                    'source' => 'App__Integrations__c0177124-d76f-4d41-98f8-1861e2395787',
                    'name' => 'Conference 3rd Floor - Small',
                    'content' => null,
                    'editable' => false,
                ],
                [],
                'Couldnt convert from Array'
            ],
        ];
    }

    public function testFromEntity()
    {
        $entity = $this->Calendars->find()->first();
        if (!empty($entity)) {
            $this->instance->fromEntity($entity);
            $dummyCalendar = new Calendar($entity);

            $this->assertEquals(
                $entity->id,
                $dummyCalendar->getAttribute('id')
            );
            $this->assertEquals($entity->id, $this->instance->getAttribute('id'));
        } else {
            $this->markIncomplete('Calendar Fixture doesn\'t have any calendars');
        }
    }

    public function testGetAttributeWithInflector()
    {
        $this->instance->setAttribute('source_id', '123');
        $this->assertEquals($this->instance->getAttribute('source_id'), '123');
        $this->assertEquals($this->instance->getAttribute('sourceId'), '123');
    }

    /**
     * @expectedException \Exception
     */
    public function testGetAttribute()
    {
        $this->instance->getAttribute('sourceId123', 'foobar');
    }
}
