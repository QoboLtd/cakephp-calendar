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

use Cake\Http\Response;
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
     * View method
     *
     * @param string|null $id Calendar Attendee id.
     * @return \Cake\Http\Response|void
     */
    public function view(?string $id = null)
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
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $calendarAttendee = $this->CalendarAttendees->get($id);
        if ($this->CalendarAttendees->delete($calendarAttendee)) {
            $this->Flash->success((string)__('The calendar attendee has been deleted.'));
        } else {
            $this->Flash->error((string)__('The calendar attendee could not be deleted. Please, try again.'));
        }

        return $this->redirect(['plugin' => 'Qobo/Calendar', 'controller' => 'Calendars', 'action' => 'index']);
    }

    /**
     * Lookup method
     *
     * Return the list of attendees allowed for the event
     *
     * @return void
     */
    public function lookup(): void
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $result = [];
        $data = (array)$this->request->getData();
        $searchTerm = empty($data['term']) ? '' : $data['term'];

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
