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
namespace Qobo\Calendar\Event;

use MyCLabs\Enum\Enum;

/**
 * Event Name enum
 */
class EventName extends Enum
{
    const PLUGIN_CALENDAR_MODEL_GET_CALENDARS = 'Plugin.Calendars.Model.getCalendars';
    const PLUGIN_CALENDAR_MODEL_GET_EVENTS = 'Plugin.Calendars.Model.getCalendarEvents';
    const APP_CALENDARS_CHECK_PERMISSIONS = 'App.Calendars.checkCalendarsPermissions';
    const APP_MODEL_GET_CALENDARS = 'App.Calendars.Model.getCalendars';
    const APP_MODEL_GET_EVENTS = 'App.Calendars.Model.getCalendarEvents';
}
