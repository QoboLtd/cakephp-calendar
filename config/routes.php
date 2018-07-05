<?php
use Cake\Routing\Router;

Router::plugin(
    'Qobo/Calendar',
    ['path' => '/calendars'],
    function ($routes) {
        $routes->extensions(['json']);
        $routes->fallbacks('DashedRoute');
    }
);
