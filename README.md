CakePHP-Calendar Plugin
=======================

[![Build Status](https://travis-ci.org/QoboLtd/cakephp-calendar.svg?branch=master)](https://travis-ci.org/QoboLtd/cakephp-calendar)
[![Latest Stable Version](https://poser.pugx.org/qobo/cakephp-calendar/v/stable)](https://packagist.org/packages/qobo/cakephp-calendar)
[![Total Downloads](https://poser.pugx.org/qobo/cakephp-calendar/downloads)](https://packagist.org/packages/qobo/cakephp-calendar)
[![Latest Unstable Version](https://poser.pugx.org/qobo/cakephp-calendar/v/unstable)](https://packagist.org/packages/qobo/cakephp-calendar)
[![License](https://poser.pugx.org/qobo/cakephp-calendar/license)](https://packagist.org/packages/qobo/cakephp-calendar)
[![codecov](https://codecov.io/gh/QoboLtd/cakephp-calendar/branch/master/graph/badge.svg)](https://codecov.io/gh/QoboLtd/cakephp-calendar)
[![BCH compliance](https://bettercodehub.com/edge/badge/QoboLtd/cakephp-calendar?branch=master)](https://bettercodehub.com/)

About
-----

CakePHP 3 plugin that uses FullCalendar JS (as part of AdminLTE) to manage calendar events and attendees.

Some of the things that we'll be adding shortly:
- [x] Calendar Attendees search via auto-complete (using Select2 checkbox).
- [x] Recurring calendar events.
- [x] Prototyping calendar attendees.
- [ ] Full re-write of jQuery to VueJS components.
- [ ] FreeBusy Calendar implementation.

This plugin is developed by [Qobo](https://www.qobo.biz) for [Qobrix](https://qobrix.com).  It can be used as standalone CakePHP plugin, or as part of the [project-template-cakephp](https://github.com/QoboLtd/project-template-cakephp) installation.

**NOTE**: The plugin is under development, so any **Bug Reports** and **Pull Requests** are more than welcomed.

Plugin installation
-------------------

Install with composer:

```
composer require qobo/cakephp-calendar
```

Load plugin and its requirements in the application. In `config/bootstrap.php`:

```php
# Optionally adding AdminLTE and Qobo Utils that are partially used inside.
Plugin::load('AdminLTE', ['bootstrap' => true, 'routes' => true]);
Plugin::load('Qobo/Utils');
Plugin::load('Qobo/Calendar', ['bootstrap' => true, 'routes' => true]);
```

Run database schema migrations to create tables that will hold calendars, events, attendees, etc.:

```
./bin/cake migrations migrate --plugin Qobo/Calendar
```

Customization
-------------

### JavaScript and Styling.

The plugin heavily relies on AdminLTE Bootstrap theme for styling, so you should make some adjustments in `src/Template/Calendars/index.ctp` in order to get it running.

```php
<?php
// 'scriptBotton' is an AdminLTE typo that I kept ;(

echo $this->Html->css(
    [
        'AdminLTE./plugins/fullcalendar/fullcalendar.min.css',
        'AdminLTE./plugins/daterangepicker/daterangepicker-bs3',
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style',
    ]
);

echo $this->Html->script(
    [
        'AdminLTE./plugins/jQuery/jQuery-2.1.4.min', // in case you didn't include it in the layout
        'AdminLTE./bootstrap/js/bootstrap',          // should be include in your layout.
        'AdminLTE./plugins/daterangepicker/moment.min',
        'AdminLTE./plugins/fullcalendar/fullcalendar.min.js',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
        'AdminLTE./plugins/select2/select2.min',
    ],
    ['block' => 'scriptBotton']
);

echo $this->Html->script(
    [
        'Qobo/Calendar.external/nlp',
        'Qobo/Calendar.external/rrule',
        'Qobo/Calendar.vue.min',
        'Qobo/Calendar.vue-select',
        'Qobo/Calendar.calendar.js',
    ],
    ['block' => 'scriptBotton']
);
?>
```

JavaScript files should go to your footer, so you can use native cake `fetch('scriptBottom')` and replace `scriptBotton` with `scriptBottom` in the index template (assuming that you have `$this->fetch('scriptBottom');` in your layout footer.
