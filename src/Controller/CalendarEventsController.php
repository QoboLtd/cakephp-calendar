<?php
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
        $result = [];
        $calendarEvent = $this->CalendarEvents->newEntity(null, [
            'associated' => ['CalendarAttendees'],
        ]);

        $this->Calendars = TableRegistry::get('Calendars');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $calendar = $this->Calendars->get($data['CalendarEvents']['calendar_id']);

            $data['CalendarEvents']['title'] = $this->CalendarEvents->setEventTitle($data, $calendar);

            $calendarEvent = $this->CalendarEvents->patchEntity(
                $calendarEvent,
                $data,
                ['associated' => ['CalendarAttendees']]
            );

            $saved = $this->CalendarEvents->save($calendarEvent);
            $entity = $this->CalendarEvents->prepareEventData(
                $saved->toArray(),
                $calendar
            );

            if ($saved) {
                $result['entity'] = $entity;
                $result['message'] = 'Successfully saved Event';
            } else {
                $result['entity'] = [];
                $result['message'] = 'Couldn\'t save Calendar Event';
            }
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
        $eventTypes = [];

        if ($this->request->is(['post', 'patch', 'put'])) {
            $data = $this->request->getData();

            $this->Calendars = TableRegistry::Get('Qobo/Calendar.Calendars');

            $calendars = $this->Calendars->getCalendars([
                'conditions' => [
                    'id' => $data['calendar_id'],
                ],
            ]);

            if (!empty($calendars)) {
                $calendar = array_shift($calendars);
                $eventTypes = $this->CalendarEvents->getEventTypes($calendar);
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
        $events = [];

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $this->Calendars = TableRegistry::get('Qobo/Calendar.Calendars');

            if (!empty($data['calendar_id'])) {
                $calendar = null;

                $calendars = $this->Calendars->getCalendars([
                    'conditions' => [
                        'id' => $data['calendar_id'],
                    ]
                ]);

                if (!empty($calendars)) {
                    $events = $this->CalendarEvents->getEvents($calendars[0], $data);
                }
            }
        }

        $this->set(compact('events'));
        $this->set('_serialize', 'events');
    }
}
