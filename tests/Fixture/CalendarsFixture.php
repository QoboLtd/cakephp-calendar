<?php
namespace Qobo\Calendar\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CalendarsFixture
 *
 */
class CalendarsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'color' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'icon' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source_id' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'trashed' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'calendar_type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
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
            'id' => '9390cbc1-dc1d-474a-a372-de92dce85aae',
            'name' => 'Calendar - 1',
            'color' => '#05497d',
            'icon' => 'google',
            'source_id' => '',
            'source' => 'source-1',
            'created' => '2017-05-22 11:19:02',
            'modified' => '2017-05-22 11:19:02',
            'trashed' => null,
            'calendar_type' => 'foobar',
        ],
        [
            'id' => '9390cbc1-dc1d-474a-a372-de92dce85aaa',
            'name' => 'Calendar - 2',
            'color' => '#29c619',
            'icon' => 'user',
            'source_id' => 'source-2',
            'source' => null,
            'created' => '2017-05-22 11:19:02',
            'modified' => '2017-05-22 11:19:02',
            'trashed' => null,
            'calendar_type' => 'default',
        ],
        [
            'id' => '9390cbc1-dc1d-474a-a372-de92dce85aac',
            'name' => 'Calendar Without Events',
            'color' => '#29c619',
            'icon' => 'user',
            'source_id' => 'source-2',
            'source' => null,
            'created' => '2017-05-22 11:19:02',
            'modified' => '2017-05-22 11:19:02',
            'trashed' => null,
            'calendar_type' => null,
        ],
        [
            'id' => '9390cbc1-dc1d-474a-a372-de92dce85aba',
            'name' => 'Calendar With Recurring Event',
            'color' => '#29c619',
            'icon' => 'user',
            'source_id' => 'source-3',
            'source' => null,
            'created' => '2017-05-22 11:19:02',
            'modified' => '2017-05-22 11:19:02',
            'trashed' => null,
            'calendar_type' => null,
        ],
    ];
}
