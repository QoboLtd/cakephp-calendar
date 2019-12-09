<?php
namespace Qobo\Calendar\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventsAttendeesFixture
 *
 */
class EventsAttendeesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'calendar_event_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'calendar_attendee_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'response_status' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '8e5223c7-fb0e-4cae-85a1-7f2dc52e1300',
            'calendar_event_id' => '688580e6-2224-4dcb-a8df-32337b82e1e3',
            'calendar_attendee_id' => '12613e81-aa1b-4c59-9eb4-e4016ac47aef',
            'trashed' => null,
            'response_status' => 'needsAction',
        ],
        [
            'id' => '00000000-0000-0000-0000-000000000001',
            'calendar_event_id' => '00000000-0000-0000-0000-000000000005',
            'calendar_attendee_id' => '00000000-0000-0000-0000-000000000001',
            'trashed' => null,
            'response_status' => 'needsAction',
        ],
    ];
}
