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

/**
 * @var \App\View\AppView $this
 */

?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4>
                <?php echo $this->Html->link('Calendars', ['plugin' => 'Qobo/Calendar', 'controller' => 'calendars', 'action' => 'index']); ?>
                &raquo;
                <?php echo $calendar->name;?>
            </h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                <?php
                $elementFound = $this->elementExists('CsvMigrations.Menu/view_top');

                if ($elementFound) {
                    echo $this->element('CsvMigrations.Menu/view_top', [
                        'user' => $user,
                        'entity' => $calendar,
                        'options' => [
                            'entity' => $calendar,
                            ],
                        'displayField' => 'name',
                    ]);
                } else {
                    $url = [
                        'plugin' => $this->request->plugin,
                        'controller' => $this->request->controller,
                        'action' => 'edit',
                        $calendar->id
                    ];
                    $menu[] = [
                        'html' => $this->Html->link(
                            '<i class="fa fa-pencil"></i> ' . __('Edit'),
                            $url,
                            [
                                'title' => __('Edit'), 'escape' => false, 'class' => 'btn btn-default'
                            ]
                        ),
                        'url' => $url
                    ];
                    foreach ($menu as $item) {
                        echo $item['html'];
                    }
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= h($calendar->name);?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>ID:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->id;?>
                </div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Color:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->color; ?>
                </div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Created:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->created->format('Y-m-d H:i'); ?>
                </div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Name:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->name; ?>
                </div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Icon:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->icon; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Source & Source ID Links');?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Calendar Source ID:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->source_id; ?>
                </div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong>Calendar Source:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= $calendar->source; ?>
                </div>
            </div>
        </div>
    </div>

</section>
