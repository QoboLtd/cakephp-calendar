<?php

use Cake\Core\Configure;
use Cake\Event\EventManager;
use Qobo\Calendar\Event\Plugin\GetCalendarsListener;

$config = Configure::read('Calendar');
if (empty($config)) {
    Configure::load('Qobo/Calendar.calendar', 'default');
}

EventManager::instance()->on(new GetCalendarsListener());
