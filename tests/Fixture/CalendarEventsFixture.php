<?php
namespace Qobo\Calendar\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CalendarEventsFixture
 *
 */
class CalendarEventsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'calendar_id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'source_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'title' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'content' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'start_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'end_date' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'event_type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'is_recurring' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'is_allday' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null],
        'recurrence' => ['type' => 'text', 'length' => 4294967295, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        '_indexes' => [
            'calendar_id' => ['type' => 'index', 'columns' => ['calendar_id'], 'length' => []],
            'is_recurring' => ['type' => 'index', 'columns' => ['is_recurring'], 'length' => []],
            'start_date' => ['type' => 'index', 'columns' => ['start_date'], 'length' => []],
            'end_date' => ['type' => 'index', 'columns' => ['end_date'], 'length' => []],
        ],
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
            'id' => '688580e6-2224-4dcb-a8df-32337b82e1e3',
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aae',
            'source_id' => null,
            'source' => null,
            'title' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat.',
            'start_date' => '2017-06-16 09:00:00',
            'end_date' => '2017-06-16 20:00:00',
            'created' => '2017-08-10 15:25:38',
            'modified' => '2017-08-10 15:25:38',
            'trashed' => null,
            'event_type' => 'Lorem ipsum dolor sit amet',
            'is_recurring' => 0,
            'recurrence' => '',
        ],
        [
            'id' => '688580e6-2224-4dcb-a8df-32337b82e1e4',
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aaf',
            'source_id' => null,
            'source' => null,
            'title' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet',
            'start_date' => '2017-08-10 15:25:38',
            'end_date' => '2017-08-10 15:25:38',
            'created' => '2017-08-10 15:25:38',
            'modified' => '2017-08-10 15:25:38',
            'trashed' => null,
            'event_type' => 'Lorem ipsum dolor sit amet',
            'is_recurring' => 0,
            'recurrence' => null,
        ],
        [
            'id' => '688580e6-2224-4dcb-a8df-32337b82e1e5',
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aaf',
            'source_id' => null,
            'source' => null,
            'title' => 'Recurring Annual Event with 5 count',
            'content' => 'Description of recurring event',
            'start_date' => '2017-08-10 09:20:00',
            'end_date' => '2017-08-11 15:20:00',
            'created' => '2017-08-10 15:25:38',
            'modified' => '2017-08-10 15:25:38',
            'trashed' => null,
            'event_type' => 'default_event',
            'is_recurring' => 1,
            'recurrence' => '["RRULE:FREQ=YEARLY;COUNT=5"]',
        ],
        [
            'id' => '688580e6-2224-4dcb-a8df-32337b82e1e6',
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aaf',
            'source_id' => null,
            'source' => null,
            'title' => 'Recurring Annual Event with 5 count',
            'content' => 'Description of recurring event',
            'start_date' => '2017-08-10 09:20:00',
            'end_date' => '2017-08-11 15:20:00',
            'created' => '2017-08-10 15:25:38',
            'modified' => '2017-08-10 15:25:38',
            'trashed' => null,
            'event_type' => 'special_event',
            'is_recurring' => 1,
            'recurrence' => '',
        ],
        [
            'id' => '688580e6-2224-4dcb-a8df-32337b82e1e7',
            'calendar_id' => '9390cbc1-dc1d-474a-a372-de92dce85aba',
            'source_id' => null,
            'source' => null,
            'title' => 'Recurring Event with Count 2',
            'content' => 'Description of recurring event',
            'start_date' => '2017-08-10 09:20:00',
            'end_date' => '2017-08-11 15:20:00',
            'created' => '2017-08-10 15:25:38',
            'modified' => '2017-08-10 15:25:38',
            'trashed' => null,
            'event_type' => 'special_event',
            'is_recurring' => 1,
            'recurrence' => '["RRULE:FREQ=MONTHLY;COUNT=2"]',
        ],
    ];
}
