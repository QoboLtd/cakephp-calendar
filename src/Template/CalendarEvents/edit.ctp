<?php
/**
 * @var \App\View\AppView $this
 */
echo $this->Html->css(
    [
        'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
    ]
);

echo $this->Html->script(
    [
        'AdminLTE./plugins/daterangepicker/moment.min',
        'AdminLTE./plugins/fullcalendar/fullcalendar.min.js',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Calendar.calendar.misc',
    ],
    ['block' => 'scriptBottom']
);

?>
<?= $this->Form->create($calendarEvent) ?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?php echo __('Edit Calendar Event'); ?></h4>
        </div>
        <div class="col-xs-12 col-md-6"></div>
    </div>
</section>
<div class="content">
    <div class='box box-primary'>
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Calendar Details');?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('title');?>
                    <?= $this->Form->control(
                        'calendar_id',
                        [
                            'type' => 'select',
                            'class' => 'calendar-dyn-calendar-type',
                            'options' => $calendars,
                            'empty' => 'Choose Calendar'
                        ]
                    );?>
                    <?= $this->Form->control(
                        'event_type',
                        [
                            'type' => 'select',
                            'class' => 'calendar-dyn-event-type',
                            'options' => [],
                            'empty' => 'Choose Event Type'
                        ]
                    );?>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->control(
                                'start_date',
                                [
                                    'value' => $calendarEvent->start_date->format('Y-m-d H:i'),
                                    'type' => 'text',
                                    'class' => 'calendar-datetimepicker calendar-start_date'
                                ]
                            );?>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <?= $this->Form->control(
                                'end_date',
                                [
                                    'type' => 'text',
                                    'value' => $calendarEvent->end_date->format('Y-m-d H:i'),
                                    'class' => 'calendar-datetimepicker calendar-end_date'
                                ]
                            );?>
                        </div>
                    </div>
                    <?= $this->Form->control('content'); ?>
                </div>
            </div> <!-- row -->
        </div>
    </div> <!-- box -->
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Attendees Details'); ?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <a href="#" class="form-control btn btn-default event-add-row" data-target="attendees-container"><i class="fa fa-plus"></i> Add Attendee</a>
                    </div>
                </div>
            </div> <!-- .row -->
            <div class="row">
            <div class="col-xs-12 col-md-12">
                <div class="attendees-container" id="rows-collection">
                <?php if (!empty($calendarEvent->calendar_attendees)) : ?>
                    <?php foreach ($calendarEvent->calendar_attendees as $k => $attendee) : ?>
                    <div class="row">
                        <div class="col-xs-5 col-md-5">
                            <?= $this->Form->control('calendar_attendees.' . $k . '.display_name', ['type' => 'text']);?>
                        </div>
                        <div class="col-xs-5 col-md-6">
                            <?= $this->Form->control('calendar_attendees.' . $k . '.contact_details', ['type' => 'text']);?>
                        </div>
                        <div class="col-xs-2 col-md-1">
                            <div class="form-group">
                                <label> &nbsp;</label>
                                <a href="#" class="btn btn-default form-control event-remove-row" data-target="attendees-container"><i class="fa fa-minus"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
            </div>
        </div> <!-- .box-body -->
    </div>
    <div class="">
        <?= $this->Form->button(__('Submit')) ?>
        <?= $this->Form->end() ?>
    </div>
</div> <!-- .content -->
