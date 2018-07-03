<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Core\Configure;

echo $this->Html->css(
    [
        'Qobo/Calendar.fullcalendar.min.css',
        'AdminLTE./plugins/select2/select2.min',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Calendar.calendar',
    ]
);

echo $this->Html->script([
    'Qobo/Calendar./dist/vendor',
    'Qobo/Calendar./dist/app',
], [
    'block' => 'scriptBottom'
]);

$start = date('Y-m-01');
$end = date('Y-m-t');
$timezone = date_default_timezone_get();
?>
<section class="content-header hidden-print">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?php echo __('Calendars'); ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <?php
                    if ($this->elementExists('CsvMigrations.Menu/index_top')) {
                        $user = (!isset($user)) ? [] : $user;
                        echo $this->element('CsvMigrations.Menu/index_top', ['user' => $user]);
                    } else {
                        echo $this->Html->link(
                            '<i class="fa fa-plus"></i> ' . __('Add'),
                            [
                                'plugin' => 'Qobo/Calendar',
                                'controller' => 'Calendars',
                                'action' => 'add'
                            ],
                            [
                                'class' => 'btn btn-default',
                                'escape' => false,
                            ]
                        );
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content" id="qobo-calendar-app" start="<?= $start;?>" end="<?= $end;?>" timezone="<?= $timezone; ?>" token="<?= Configure::read('API.token');?>">
    <div class="row">
       <div class="col-md-4">
            <sidebar></sidebar>
        </div>
        <div class="col-md-8">
            <div class="box">
                <div class='box-body'>
                    <div id="qobrix-calendar">
                        <calendar
                            :ids="ids"
                            :start="start"
                            :timezone="timezone"
                            :end="end"
                            :events="events"
                            :editable="editable"
                            :show-print-button="true"
                            @interval-update="updateStartEnd"
                            @event-info="getEventInfo"
                            @modal-add-event="addCalendarEvent">
                        </calendar>
                    </div>
                </div>
            </div>
        </div>
        <calendar-modal
            :calendars-list="calendarsList"
            :timezone="timezone"
            :start="start"
            :end="end"
            :event-click="eventClick"
            @event-saved="addEventToResources">
        </calendar-modal>

        <div class="modal fade" id="calendar-modal-view-event" tabindex="-1" role="dialog" aria-labelledby="calendar-modal-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                </div> <!-- //modal-content -->
            </div> <!-- // modal-dialog -->
        </div>
    </div> <!-- //end first row -->
</section>
