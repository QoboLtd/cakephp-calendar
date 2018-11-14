<?php
namespace Qobo\Calendar\Test\App\Config;

use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);

Router::connect('/:controller/:action/*');
Router::plugin(
    'Qobo/Calendar',
    ['path' => '/calendars'],
    function ($routes) {
        $routes->setExtensions(['json']);
        $routes->fallbacks('DashedRoute');
    }
);
