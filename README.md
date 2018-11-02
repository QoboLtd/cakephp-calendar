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
- [x] Full re-write of jQuery to VueJS components.
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

echo $this->Html->css(
    [
        'Qobo/Calendar.fullcalendar.min.css',
        'AdminLTE./plugins/select2/select2.min',
        'AdminLTE./plugins/daterangepicker/daterangepicker',
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

?>
```

In order to initialise Calendar VueJS application, you should define `#qobo-calendar-app` element:

```html

<section class="content" id="qobo-calendar-app" token="YourApiToken">
    <calendar :timezone="timezone" :editable="editable" :show-print-button="true"></calendar>
</section>

```

VueJS Contributions
-------------

Calendar Plugin has `package.json` of all required modules, in order to modify `dist` compiled JS files.
Run `yarn` command to install required `node_module` to proceed with development.

<<<<<<< HEAD
In case you need HotReload functionality, run:

```
yarn watch
```

Preparing production ready build:

```
yarn build:prod
```

For more scripts and linters etc., please check `package.json` file.
