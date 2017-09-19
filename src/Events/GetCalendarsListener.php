<?php
namespace Qobo\Calendar\Events;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;

class GetCalendarsListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Plugin.Calendars.Model.getCalendars' => 'getPluginCalendars',
            'Plugin.Calendars.Model.getCalendarEvents' => 'getPluginCalendarEvents',
        ];
    }

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

        foreach ($calendars as $calendar) {
            array_push($result, $calendar);
        }

        $event->result = $result;
    }

    /**
     * Get calendar events from the plugin only.
     *
     * @param Cake\Event\Event $event passed through
     * @param \Cake\ORM\Entity $calendar instance
     * @param array $options for calendars
     *
     * @return void
     */
    public function getPluginCalendarEvents(Event $event, $calendar, $options = [])
    {
        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        $events = $table->getCalendarEvents($calendar, $options);

        $event->result = $events;
    }
}
