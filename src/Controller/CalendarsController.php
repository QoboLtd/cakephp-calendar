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

use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Qobo\Calendar\Event\EventName;

/**
 * Calendars Controller
 *
 * @property \Qobo\Calendar\Model\Table\CalendarsTable $Calendars
 */
class CalendarsController extends AppController
{
    /**
     * Index method
     *
     * @return void
     */
    public function index(): void
    {
        $calendars = $options = [];

        // ajax-based request for public calendars
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = (array)$this->request->getData();

            if (!empty($data['public'])) {
                $options['conditions'] = ['is_public' => true];
            }
        }

        $calendars = $this->Calendars->getCalendars($options);

        $event = new Event((string)EventName::APP_CALENDARS_CHECK_PERMISSIONS(), $this, [
            'entities' => $calendars,
            'user' => $this->Auth->user(),
            'options' => []
        ]);

        $this->getEventManager()->dispatch($event);
        $calendars = $event->result;

        $this->set(compact('calendars'));
        $this->set('_serialize', 'calendars');
    }

    /**
     * View method
     *
     * @param string|null $id Calendar id.
     * @return void
     */
    public function view(?string $id = null): void
    {
        $calendar = null;

        $calendar = $this->Calendars->get($id);

        $this->set('calendar', $calendar);
        $this->set('_serialize', 'calendar');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        /** @var \Qobo\Calendar\Model\Table\CalendarEventsTable $calendarEventsTable */
        $calendarEventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $calendar = $this->Calendars->newEntity();

        $eventTypes = $calendarEventsTable->getEventTypes(['user' => $this->Auth->user()]);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data = is_array($data) ? $data : [];
            if (!empty($data['event_types'])) {
                $data['event_types'] = json_encode($data['event_types']);
            }

            $calendar = $this->Calendars->patchEntity($calendar, $data);

            if ($this->Calendars->save($calendar)) {
                $this->Flash->success((string)__('The calendar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error((string)__('The calendar could not be saved. Please, try again.'));
        }

        $this->set(compact('calendar', 'eventTypes'));
        $this->set('_serialize', 'calendar');
    }

    /**
     * Edit method
     *
     * @param string|null $id Calendar id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function edit(?string $id = null)
    {
        /** @var \Qobo\Calendar\Model\Table\CalendarEventsTable $calendarEventsTable */
        $calendarEventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $calendar = $this->Calendars->get($id);

        $eventTypes = $calendarEventsTable->getEventTypes(['user' => $this->Auth->user()]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = (array)$this->request->getData();

            $data['event_types'] = !empty($data['event_types']) ? $data['event_types'] : [];
            $data['event_types'] = json_encode($data['event_types']);

            $calendar = $this->Calendars->patchEntity($calendar, $data);

            if ($this->Calendars->save($calendar)) {
                $this->Flash->success((string)__('The calendar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error((string)__('The calendar could not be saved. Please, try again.'));
        }

        $this->set(compact('calendar', 'eventTypes'));
        $this->set('_serialize', 'calendar');
    }

    /**
     * Delete method
     *
     * @param string|null $id Calendar id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $calendar = $this->Calendars->get($id);

        if ($this->Calendars->delete($calendar)) {
            $this->Flash->success((string)__('The calendar has been deleted.'));
        } else {
            $this->Flash->error((string)__('The calendar could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
