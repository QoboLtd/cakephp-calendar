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
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'AdminLTE./bower_components/bootstrap-daterangepicker/daterangepicker',
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
            <h4><?php echo __d('Qobo/Calendar', 'Calendars'); ?></h4>
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
                            '<i class="fa fa-plus"></i> ' . __d('Qobo/Calendar', 'Add'),
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
<section class="content" id="qobo-calendar-app" timezone="<?= $timezone; ?>" token="<?= Configure::read('API.token');?>">
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <sidebar></sidebar>
        </div>
        <div class="col-md-8 col-xs-12">
            <div class="box">
                <div class='box-body'>
                    <calendar :timezone="timezone" :editable="editable" :show-print-button="true"></calendar>
                </div>
            </div>
        </div>
    </div> <!-- //end first row -->
</section>
