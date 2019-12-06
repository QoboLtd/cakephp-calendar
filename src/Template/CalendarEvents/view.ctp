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
$calEvent = !empty($response['data']) ? $response['data'] : null;

$color = (!empty($calEvent->calendar->color) ? $calEvent->calendar->color : null);
$icon = (!empty($calEvent->calendar->icon) ? $calEvent->calendar->icon : null);

$title = (!empty($icon) ? "<i class='fa fa-$icon'></i>&nbsp;": '');
$title .= $calEvent->title;

if (!empty($color)) {
    $title = '<div style="color: ' . $color . '">' . $title . '</div>';
}

$attendeesList = [];

if (!empty($calEvent->calendar_attendees)) {
    foreach ($calEvent->calendar_attendees as $attendee) {
        $attendeesList[] = $attendee->display_name;
    }
}

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="calendar-modal-label"><?= $title;?></h4>
</div>
<div class="modal-body">
    <?php if (!empty($calEvent->content)) : ?>
    <div class="row">
        <div class="col-xs-12">
            <?= $calEvent->content;?>
        </div>
    </div>
    <hr/>
    <?php endif; ?>
    <div class="row">
        <div class="col-xs-12">
            <strong>When:</strong>
        </div>
        <?php if (!$calEvent->is_allday) : ?>
            <div class="col-xs-12">
                <?= $calEvent->start_date->format('Y-m-d H:i'); ?> &#8212; <?= $calEvent->end_date->format('Y-m-d H:i'); ?>
            </div>
        <?php else :?>
            <div class="col-xs-12">
                <?= $calEvent->start_date->format('Y-m-d');?> - <?= __d('Qobo/Calendar', 'All Day');?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($calEvent->calendar_attendees)) : ?>
        <div class="row">
            <div class="col-xs-12">
                <strong>Attendees:</strong>
                <ul>
                    <?php foreach ($calEvent->calendar_attendees as $att) : ?>
                        <li><?= $att->display_name;?> <?= $att->contact_details;?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>
    <div class="modal-footer">
        <?= $this->Form->button(__d('Qobo/Calendar', 'Close'), ['data-dismiss' => 'modal', 'class' => 'btn btn-success']);?>
    </div> <!-- //modal-footer -->
</div>

