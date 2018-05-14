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
namespace Qobo\Calendar\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Calendar\Controller\AppController;

/**
 * CalendarEvents Controller
 *
 * @property \Qobo\Calendar\Model\Table\CalendarEventsTable $CalendarEvents
 *
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent[] paginate($object = null, array $settings = [])
 */
class CalendarEventsController extends AppController
{
    /**
     * Delete method
     *
     * @param string|null $id Calendar Event id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $calendarEvent = $this->CalendarEvents->get($id);
        if ($this->CalendarEvents->delete($calendarEvent)) {
            $this->Flash->success(__('The calendar event has been deleted.'));
        } else {
            $this->Flash->error(__('The calendar event could not be deleted. Please, try again.'));
        }

        return $this->redirect(['plugin' => 'Qobo/Calendar', 'controller' => 'Calendars', 'action' => 'index']);
    }

    /**
     * Create Event via AJAX call
     *
     * @return void
     */
    public function add()
    {
        $this->request->allowMethod(['post', 'patch', 'put']);
        $result = [];

        $calendarEvent = $this->CalendarEvents->newEntity(null, [
            'associated' => ['CalendarAttendees'],
        ]);

        $data = $this->request->getData();

        $this->Calendars = TableRegistry::get('Calendars');
        $calendar = $this->Calendars->get($data['CalendarEvents']['calendar_id']);

        $data['CalendarEvents']['title'] = $this->CalendarEvents->setEventTitle($data, $calendar);

        $calendarEvent = $this->CalendarEvents->patchEntity(
            $calendarEvent,
            $data,
            [
                'associated' => ['CalendarAttendees'],
            ]
        );

        $saved = $this->CalendarEvents->save($calendarEvent);
        if ($saved) {
            $result['status'] = true;
            $result['message'] = 'Successfully saved Event';
            $result['entity'] = [
                'id' => $saved->id,
                'title' => $saved->title,
                'content' => $saved->content,
                'start_date' => $saved->start_date,
                'end_date' => $saved->end_date,
                'color' => $calendar->color,
                'calendar_id' => $calendar->id,
                'event_type' => $saved->event_type,
                'is_recurring' => $saved->is_recurring,
                'source' => $saved->source,
                'source_id' => $saved->source_id,
                'recurrence' => json_decode($saved->recurrence, true),
            ];
        } else {
            $result['entity'] = $calendarEvent->getErrors();
            $result['message'] = 'Couldn\'t save Calendar Event';
            $result['status'] = false;
        }

        $event = $result;

        $this->set(compact('event'));
        $this->set('_serialize', ['event']);
    }

    /**
     * View Event via AJAX
     *
     * @return void
     */
    public function view()
    {
        $calEvent = [];
        $this->viewBuilder()->setLayout('Qobo/Calendar.ajax');

        if ($this->request->is(['post', 'patch', 'put'])) {
            $data = $this->request->getData();

            if (preg_match('/\_\_/', $data['id'])) {
                $parts = explode('__', $data['id']);
                $data['id'] = $parts[0];
                $data['timestamp'] = $parts[1];
            }

            $calEvent = $this->CalendarEvents->getEventInfo($data);
        }

        $this->set(compact('calEvent'));
        $this->set('_serialize', ['calEvent']);
    }

    /**
     * Get Event types based on the calendar id
     *
     * @return void
     */
    public function getEventTypes()
    {
        $this->request->allowMethod(['post', 'patch', 'put']);
        $this->Calendars = TableRegistry::Get('Qobo/Calendar.Calendars');

        $eventTypes = [];
        $data = $this->request->getData();

        $calendar = $this->Calendars->get($data['calendar_id']);
        $types = $this->CalendarEvents->getEventTypes(['calendar' => $calendar, 'user' => $this->Auth->user()]);

        foreach ($types as $item) {
            if (isset($item['name'])) {
                $eventTypes[] = $item;
            } else {
                $eventTypes[] = ['name' => $item, 'value' => $item];
            }
        }

        $this->set(compact('eventTypes'));
        $this->set('_serialize', 'eventTypes');
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $this->Calendars = TableRegistry::get('Qobo/Calendar.Calendars');

        $events = [];
        $data = $this->request->getData();

        if (!empty($data['calendar_id'])) {
            $calendar = $this->Calendars->get($data['calendar_id']);
            $events = $this->CalendarEvents->getEvents($calendar, $data);
        }

        $this->set(compact('events'));
        $this->set('_serialize', 'events');
    }

    public function eventTypeConfig()
    {
        $info = [];
        $this->request->allowMethod(['post', 'put', 'patch']);

        $data = $this->request->getData();
        // @FIXME: prepopulate event_type info and custom values.
        $info = $data;

        $this->set(compact('info'));
        $this->set('_serialize', 'info');
    }
}
