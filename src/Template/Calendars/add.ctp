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

use Qobo\Utils\Utility;

$icons = Utility::getIcons();
$colors = Utility::getColors();

/**
 * @var \App\View\AppView $this
 */
echo $this->Html->css(
    [
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
    ],
    ['block' => 'css']
);

echo $this->Html->script(
    [
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'Qobo/Utils.select2.init',
    ],
    ['block' => 'scriptBottom']
);

foreach ($icons as $k => $v) {
    $icons[$v] = '<i class="fa fa-' . $v . '"></i>&nbsp;&nbsp;' . $v;
    unset($icons[$k]);
}

?>
<?= $this->Form->create($calendar) ?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?php echo __d('Qobo/Calendar', 'Add Calendar'); ?></h4>
        </div>
        <div class="col-xs-12 col-md-6"></div>
    </div>
</section>
<div class="content">
    <div class='box box-primary'>
        <div class="box-header with-border">
            <h3 class="box-title"><?= __d('Qobo/Calendar', 'Calendar Details');?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('name'); ?>
                    <?= $this->Form->control('is_public', ['label' => __d('Qobo/Calendar', 'Publicly Accessible')]);?>
                    <?= $this->Form->control('event_types', ['type' => 'select', 'options' => $eventTypes, 'class' => 'select2', 'multiple' => 'multiple', 'empty' => true]);?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('color', [
                        'type' => 'select',
                        'options' => $colors,
                        'class' => 'select2',
                        'empty' => true
                    ]) ?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('icon', [
                        'type' => 'select',
                        'options' => $icons,
                        'class' => 'select2',
                        'empty' => true
                    ]) ?>

                    <?= $this->Form->control('active');?>
                </div>
            </div>
        </div>
    </div>
    <div>
        <?= $this->Form->button(__d('Qobo/Calendar', 'Submit')) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
