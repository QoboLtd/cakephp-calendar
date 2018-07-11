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
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Calendar\Controller\AppController;
use Qobo\Calendar\Object\ObjectFactory;
use RRule\RRule;

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
        $response = [
            'success' => false,
            'data' => [],
            'errors' => [],
        ];

        $this->Calendars = TableRegistry::get('Calendars');

        $data = $this->request->getData();
        if (empty($data['calendar_id'])) {
            $response['errors'][] = "Calendar ID is missing";
            $this->set(compact('response'));
            $this->set('_serialize', 'response');

            return $this->response;
        }

        $calendar = $this->Calendars->get($data['calendar_id']);
        $postData = $this->CalendarEvents->setCalendarEventData($data, $calendar);

        $calendarEvent = $this->CalendarEvents->newEntity();
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
            $response['data'] = $this->CalendarEvents->getEventInfo($saved->id, $calendar);
        } else {
            $response['errors'] = $calendarEvent->getErrors();
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
        $response = [
            'success' => false,
            'data' => [],
            'errors' => []
        ];

        if ($this->request->is(['post', 'patch', 'put'])) {
            $data = $this->request->getData();
            $result = $this->CalendarEvents->getEventInfo($data['id']);

            if (!empty($result)) {
                $response['success'] = true;
                $response['data'] = $result;
            } else {
                $response['errors'][] = "Couldn't find Event with id {$data['id']}";
            }
        }

        $this->set(compact('response'));
        $this->set('_serialize', 'response');
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
