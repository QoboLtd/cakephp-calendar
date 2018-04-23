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

use Cake\ORM\TableRegistry;
use Qobo\Calendar\Controller\AppController;

/**
 * CalendarAttendees Controller
 *
 * @property \Qobo\Calendar\Model\Table\CalendarAttendeesTable $CalendarAttendees
 *
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee[] paginate($object = null, array $settings = [])
 */
class CalendarAttendeesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $calendarAttendees = $this->paginate($this->CalendarAttendees);

        $this->set(compact('calendarAttendees'));
        $this->set('_serialize', ['calendarAttendees']);
    }

    /**
     * View method
     *
     * @param string|null $id Calendar Attendee id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $calendarAttendee = $this->CalendarAttendees->get($id, [
            'contain' => ['CalendarEvents']
        ]);

        $this->set(compact('calendarAttendee'));
        $this->set('_serialize', ['calendarAttendee']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Calendar Attendee id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $calendarAttendee = $this->CalendarAttendees->get($id);
        if ($this->CalendarAttendees->delete($calendarAttendee)) {
            $this->Flash->success(__('The calendar attendee has been deleted.'));
        } else {
            $this->Flash->error(__('The calendar attendee could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Lookup method
     *
     * Return the list of attendees allowed for the event
     *
     * @return void
     */
    public function lookup()
    {
        $result = [];
        $searchTerm = $this->request->query('term');
        $calendarId = $this->request->query('calendar_id');
        $eventType = $this->request->query('event_type');

        $eventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        $query = $this->CalendarAttendees->find()
            ->where([
                'OR' => [
                    'display_name LIKE' => "%$searchTerm%",
                    'contact_details LIKE' => "%$searchTerm%"
                ]
            ]);

        $attendees = $query->toArray();

        foreach ($attendees as $k => $att) {
            $result[] = [
                'id' => $att->id,
                'text' => "{$att->display_name} - {$att->contact_details}",
            ];
        }

        $this->set(compact('result'));
        $this->set('_serialize', 'result');
    }
}
