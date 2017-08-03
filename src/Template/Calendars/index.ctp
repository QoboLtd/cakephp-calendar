<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/fullcalendar/fullcalendar.min.css',
        'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Calendar.select2-style',
    ]
);

echo $this->Html->script(
    [
        'AdminLTE./plugins/daterangepicker/moment.min',
        'AdminLTE./plugins/fullcalendar/fullcalendar.min.js',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'AdminLTE./plugins/select2/select2.min',
    ],
    ['block' => 'scriptBotton']
);

echo $this->Html->script(
    [
        'Qobo/Calendar.calendar.misc',
        'Qobo/Calendar.nlp',
        'Qobo/Calendar.rrule',
        'https://unpkg.com/vue@2.3.4',
        'https://unpkg.com/vue-select@2.2.0',
        //'Qobo/Calendar.vue.min',
        'Qobo/Calendar.calendar.js',
    ],
    ['block' => 'scriptBotton']
);

$start = date('Y-m-01');
$end = date('Y-m-t');
$timezone = date_default_timezone_get();
?>
<section class="content-header">
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
                        echo $this->Html->link(__('Add'), ['plugin' => 'Qobo/Calendar', 'controller' => 'Calendars', 'action' => 'add'], ['class' => 'btn btn-default']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content" id="qobo-calendar-app" start="<?= $start;?>" end="<?= $end;?>" timezone="<?= $timezone; ?>">
    <div class="row">
       <div class="col-md-4">
            <div class='box'>
                <div class='box-header with-border'>
                    <h3 class='box-title'><?= __('Calendars');?></h3>
                </div>
                <div class='box-body'>
                    <div class="row">
                        <div class="col-md-12">
                            <!-- loop through calendars -->
                            <div class="row" v-for="item in calendars">
                                <div class="col-xs-8">
                                     <calendar-item
                                        name="Calendar[_id]"
                                        :label="item.name"
                                        :icon="item.icon"
                                        :value="item.id"
                                        :item-active="item.active"
                                        @toggle-calendar="updateCalendarIds">
                                    </calendar-item>
                                </div>
                                <div class="col-xs-4">
                                    <div class="btn-group btn-group-xs pull-right">
                                        <calendar-link
                                            item-url="<?= $this->Url->build(['plugin' => 'Qobo/Calendar', 'controller' => 'Calendars', 'action' => 'view']);?>"
                                            :item-value="item.id"
                                            item-icon="eye"
                                            item-class="btn btn-default">
                                        </calendar-link>
                                        <calendar-link
                                            item-url="<?= $this->Url->build(['plugin' => 'Qobo/Calendar', 'controller' => 'Calendars', 'action' => 'edit']);?>"
                                            :item-value="item.id"
                                            item-icon="pencil"
                                            item-class="btn btn-default"
                                        ></calendar-link>
                                    </div>
                                </div>
                            </div>
                            <!-- .loop through calendars -->
                        </div>
                    </div>
                </div>
            </div>
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
                            @interval-update="updateStartEnd"
                            @event-info="getEventInfo"
                            @modal-add-event="addCalendarEvent"></calendar>
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
