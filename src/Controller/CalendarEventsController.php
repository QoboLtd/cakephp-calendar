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
use Qobo\Calendar\Object\ObjectFactory;

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
        $this->Calendars = TableRegistry::get('Calendars');
        $response = [
            'success' => false,
            'data' => [],
            'errors' => [],
        ];
        $data = $this->request->getData();
        $postData = [];

        $calendar = $this->Calendars->get($data['calendar_id']);
        $calendarEvent = $this->CalendarEvents->newEntity();

        if (empty($data['title'])) {
            $data['title'] = $this->CalendarEvents->setEventTitle($data, $calendar);
        }

        $postData['CalendarEvents'] = $data;

        if (!empty($data['attendees_ids'])) {
            $postData['calendar_attendees']['_ids'] = $data['attendees_ids'];
            unset($postData['attendees_ids']);
        }

        $calendarEvent = $this->CalendarEvents->patchEntity(
            $calendarEvent,
            $postData,
            [
                'associated' => ['CalendarAttendees'],
            ]
        );

        $saved = $this->CalendarEvents->save($calendarEvent);

        if ($saved) {
            $response['success'] = true;
            $response['message'] = 'Successfully saved Event';
            $response['data'] = [
                'id' => $saved->id,
                'calendar_id' => $calendar->id,
                'source_id' => $saved->source_id,
                'source' => $saved->source,
                'event_type' => $saved->event_type,
                'title' => $saved->title,
                'content' => $saved->content,
                'start' => $saved->start_date,
                'end' => $saved->end_date,
                'color' => $calendar->color,
                'is_recurring' => $saved->is_recurring,
                'recurrence' => json_decode($saved->recurrence, true),
            ];
        } else {
            $response['errors'] = $calendarEvent->getErrors();
            $response['success'] = false;
        }

        $this->set(compact('response'));
        $this->set('_serialize', 'response');
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

    /**
     * Event Type Config getter method
     *
     * Return event type configuration from ObjectFactory
     *
     * @return void
     */
    public function eventTypeConfig()
    {
        $info = [];
        $this->request->allowMethod(['post', 'put', 'patch']);
        $data = $this->request->getData();

        try {
            $config = ObjectFactory::getConfig(null, 'Event', $data['event_type']);

            if (!empty($config)) {
                $info = $config;
            }
        } catch (Exception $e) {
            // @TODO: log possible exceptions for troubleshooting.
        }

        $this->set(compact('info'));
        $this->set('_serialize', 'info');
    }
}
