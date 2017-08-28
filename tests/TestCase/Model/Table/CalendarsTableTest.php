<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Model\Table\CalendarsTable;

/**
 * Qobo\Calendar\Model\Table\CalendarsTable Test Case
 */
class CalendarsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Qobo\Calendar\Model\Table\CalendarsTable
     */
    public $Calendars;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.calendars',
        'plugin.qobo/calendar.calendar_events'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Calendars') ? [] : ['className' => 'Qobo\Calendar\Model\Table\CalendarsTable'];
        $this->Calendars = TableRegistry::get('Calendars', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Calendars);

        parent::tearDown();
    }

    public function testGetCalendars()
    {
        $result = $this->Calendars->getCalendars();
        $this->assertTrue(!empty($result));

        $result = $this->Calendars->getCalendars(['conditions' => ['name' => 'foobar']]);
        $this->assertEquals($result, []);

        $result = $this->Calendars->getCalendars(['id' => '9390cbc1-dc1d-474a-a372-de92dce85aae']);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '9390cbc1-dc1d-474a-a372-de92dce85aae');

        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa']]);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[0]->id, '9390cbc1-dc1d-474a-a372-de92dce85aaa');

        Configure::write('Calendar.Types', ['foo' => ['name' => 'bar', 'value' => 'bar']]);
        $result = $this->Calendars->getCalendars(['conditions' => ['id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa']]);
    }

    public function testGetTypesWithEmptyConfig()
    {
        Configure::write('Calendar.Types', []);
        $result = $this->Calendars->getTypes();

        $this->assertEquals($result, []);
    }

    /**
     * @dataProvider testGetTypesProvider
     */
    public function testGetTypes($expected, $msg)
    {
        $result = $this->Calendars->getTypes();
        $this->assertEquals($result, $expected, $msg);
    }

    public function testGetTypesProvider()
    {
        return [
            [
                ['default' => 'Default', 'shifts' => 'Shifts'], "Wrong set of calendar types returned"
            ]
        ];
    }
}
