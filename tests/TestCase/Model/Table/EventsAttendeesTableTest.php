<?php
namespace Qobo\Calendar\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Qobo\Calendar\Model\Table\EventsAttendeesTable;

/**
 * Qobo\Calendar\Model\Table\EventsAttendeesTable Test Case
 */
class EventsAttendeesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Qobo\Calendar\Model\Table\EventsAttendeesTable
     */
    public $EventsAttendees;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.qobo/calendar.events_attendees',
        'plugin.qobo/calendar.calendar_events',
        'plugin.qobo/calendar.calendar_attendees'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EventsAttendees') ? [] : ['className' => EventsAttendeesTable::class];
        $this->EventsAttendees = TableRegistry::get('EventsAttendees', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsAttendees);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
