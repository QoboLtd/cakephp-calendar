<?php
namespace Qobo\Calendar\Test\TestCase\Shell\Task;

use Cake\TestSuite\TestCase;
use Qobo\Calendar\Shell\Task\GoogleCalendarsTask;

/**
 * Qobo\Calendar\Shell\Task\GoogleCalendarsTask Test Case
 */
class GoogleCalendarsTaskTest extends TestCase
{

    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo|\PHPUnit_Framework_MockObject_MockObject
     */
    public $io;

    /**
     * Test subject
     *
     * @var \Qobo\Calendar\Shell\Task\GoogleCalendarsTask
     */
    public $GoogleCalendars;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();

        $this->GoogleCalendars = $this->getMockBuilder('Qobo\Calendar\Shell\Task\GoogleCalendarsTask')
            ->setConstructorArgs([$this->io])
            ->getMock();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->GoogleCalendars);

        parent::tearDown();
    }

    /**
     * Test getOptionParser method
     *
     * @return void
     */
    public function testGetOptionParser()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test main method
     *
     * @return void
     */
    public function testMain()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
