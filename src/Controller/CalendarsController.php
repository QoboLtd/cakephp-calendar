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
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Qobo\Calendar\Controller\AppController;
use Qobo\Calendar\Event\EventName;
use Qobo\Utils\Utility;

/**
 * Calendars Controller
 *
 * @property \Qobo\Calendar\Model\Table\CalendarsTable $Calendars
 *
 * @method \Qobo\Calendar\Model\Entity\Calendar[] paginate($object = null, array $settings = [])
 */
class CalendarsController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->set('icons', Utility::getIcons());
        $this->set('colors', Utility::getColors());
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $calendars = $options = [];

        // ajax-based request for public calendars
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

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

        $this->eventManager()->dispatch($event);
        $calendars = $event->result;

        $this->set(compact('calendars'));
        $this->set('_serialize', 'calendars');
    }

    /**
     * View method
     *
     * @param string|null $id Calendar id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $calendar = null;

        $calendar = $this->Calendars->get($id);

        $this->set('calendar', $calendar);
        $this->set('_serialize', 'calendar');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->CalendarEvents = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $calendar = $this->Calendars->newEntity();

        $eventTypes = $this->CalendarEvents->getEventTypes(['user' => $this->Auth->user()]);

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if (!empty($data['event_types'])) {
                $data['event_types'] = json_encode($data['event_types']);
            }

            $calendar = $this->Calendars->patchEntity($calendar, $data);

            if ($this->Calendars->save($calendar)) {
                $this->Flash->success(__('The calendar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The calendar could not be saved. Please, try again.'));
        }

        $this->set(compact('calendar', 'eventTypes'));
        $this->set('_serialize', 'calendar');
    }

    /**
     * Edit method
     *
     * @param string|null $id Calendar id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->CalendarEvents = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $calendar = $this->Calendars->get($id);

        $eventTypes = $this->CalendarEvents->getEventTypes(['user' => $this->Auth->user()]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            if (!empty($data['event_types'])) {
                $data['event_types'] = json_encode($data['event_types']);
            }
            $calendar = $this->Calendars->patchEntity($calendar, $data);

            if ($this->Calendars->save($calendar)) {
                $this->Flash->success(__('The calendar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The calendar could not be saved. Please, try again.'));
        }

        $this->set(compact('calendar', 'eventTypes'));
        $this->set('_serialize', 'calendar');
    }

    /**
     * Delete method
     *
     * @param string|null $id Calendar id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $calendar = $this->Calendars->get($id);

        if ($this->Calendars->delete($calendar)) {
            $this->Flash->success(__('The calendar has been deleted.'));
        } else {
            $this->Flash->error(__('The calendar could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
