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
namespace Qobo\Calendar\Event\Plugin;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\View\View;
use Qobo\Calendar\Event\EventName;
use Qobo\Calendar\Object\ObjectFactory;
use Qobo\Calendar\Object\Objects\ObjectInterface;
use \ArrayObject;

class GetCalendarsListener implements EventListenerInterface
{
    /**
     * Implemented Events
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            (string)EventName::APP_MODEL_GET_CALENDARS => 'getPluginCalendars',
            (string)EventName::APP_MODEL_GET_EVENTS => 'getPluginCalendarEvents',
            (string)EventName::PLUGIN_CALENDAR_MODEL_GET_CALENDARS => 'sendGetCalendarsToApp',
            (string)EventName::PLUGIN_CALENDAR_MODEL_GET_EVENTS => 'sendGetCalendarEventsToApp',
            (string)EventName::APP_ADD_EVENT => 'addEvent',
        ];
    }

    /**
     * Add CalendarEvent from App
     *
     * Adding Calendar event based on the entity table.
     *
     * @param \Cake\Event\Event $event received from the app
     * @param \Cake\Datasource\EntityInterface $entity being recently saved.
     * @param \ArrayObject $options with extra configs for adding reminder
     *
     * @return void
     */
    public function addEvent(Event $event, EntityInterface $entity, ArrayObject $options = null)
    {
        $entities = $result = [];
        $table = $event->getSubject();

        $calendarsTable = TableRegistry::get('Qobo/Calendar.Calendars');
        $eventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $calendars = $calendarsTable->getByAllowedEventTypes($table->alias());

        $options['entity'] = $entity;
        $options['viewEntity'] = new View();

        $entities = $eventsTable->getEventsFromEntities($table, $calendars, $options);

        if (!empty($entities)) {
            foreach ($entities as $item) {
                $saved = $eventsTable->saveEvent($item);
                $result[] = $saved;
            }
        }

        $event->result = $result;
    }

    /**
     * Re-broadcasting the event outside of the plugin
     *
     * @param \Cake\Event\Event $event received by the plugin
     * @param array $options for calendar conditions
     *
     * @return void
     */
    public function sendGetCalendarsToApp(Event $event, $options = [])
    {
        $eventName = preg_replace('/^(Plugin)/', 'App', $event->getName());

        $ev = new Event($eventName, $this, [
            'options' => $options
        ]);

        EventManager::instance()->dispatch($ev);

        $event->result = $ev->result;
    }

    /**
     * Re-broadcasting the event outside of the plugin
     *
     * @param \Cake\Event\Event $event received by the plugin
     * @param \Cake\ORM\Entity $calendar instance
     * @param array $options for calendar conditions
     *
     * @return void
     */
    public function sendGetCalendarEventsToApp(Event $event, $calendar, $options = [])
    {
        $eventName = preg_replace('/^(Plugin)/', 'App', $event->getName());

        $ev = new Event($eventName, $this, [
            'calendar' => $calendar,
            'options' => $options
        ]);

        EventManager::instance()->dispatch($ev);

        $event->result = $ev->result;
    }

    /**
     * Get calendars from the plugin only.
     *
     * @param \Cake\Event\Event $event passed through
     * @param array $options for calendars
     *
     * @return void
     */
    public function getPluginCalendars(Event $event, $options = [])
    {
        $content = $result = [];
        $table = TableRegistry::get('Qobo/Calendar.Calendars');

        if (!empty($event->result)) {
            $result = $event->result;
        }

        // locally created calendars don't have source_id (external link).
        $options = array_merge($options, [
            'conditions' => [
                'source LIKE' => 'Plugin__%',
            ]
        ]);

        $calendars = $table->getCalendars($options);

        if (empty($calendars)) {
            return;
        }

        foreach ($calendars as $k => $calendar) {
            unset($calendar->calendar_events);
            $content[$k]['calendar'] = json_decode(json_encode($calendar), true);
        }

        if (!empty($content)) {
            $result = array_merge($result, $content);
        }

        $event->result = $result;
    }

    /**
     * Get calendar events from the plugin only.
     *
     * @param \Cake\Event\Event $event passed through
     * @param \Cake\ORM\Entity $calendar instance
     * @param array $options for calendars
     *
     * @return void
     */
    public function getPluginCalendarEvents(Event $event, $calendar, $options = [])
    {
        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        $events = $table->getEvents($calendar, $options);

        $event->result = $events;
    }
}
