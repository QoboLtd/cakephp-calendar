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
    const QOBO_CALENDAR_MODEL_GET_CALENDARS = 'Qobo/Calendar.Model.getCalendars';
    const QOBO_CALENDAR_MODEL_GET_EVENTS = 'Qobo/Calendar.Model.getCalendarEvents';
    const APP_CALENDARS_CHECK_PERMISSIONS = 'Qobo/Calendar.Controller.checkCalendarsPermissions';
    const APP_MODEL_GET_CALENDARS = 'Qobo/Calendar.Model.getCalendars';
    const APP_MODEL_GET_EVENTS = 'Qobo/Calendar.Model.getCalendarEvents';
    const APP_ADD_EVENT = 'Qobo/Calendar.Model.addEvent';
}
