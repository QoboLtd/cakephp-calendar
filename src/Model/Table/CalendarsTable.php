<?php
namespace Qobo\Calendar\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use Qobo\Calendar\Objects\Calendar as CalendarObject;
use \ArrayObject;

/**
 * Calendars Model
 *
 * @property \Cake\ORM\Association\BelongsTo $CalendarSources
 * @property \Cake\ORM\Association\HasMany $CalendarEvents
 *
 * @method \Qobo\Calendar\Model\Entity\Calendar get($primaryKey, $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar newEntity($data = null, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar[] newEntities(array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar[] patchEntities($entities, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\Calendar findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CalendarsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('calendars');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');
        $this->addBehavior('AuditStash.AuditLog');

        $this->hasMany('CalendarEvents', [
            'foreignKey' => 'calendar_id',
            'className' => 'Qobo/Calendar.CalendarEvents'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('name');

        $validator
            ->allowEmpty('color');

        $validator
            ->allowEmpty('icon');

        $validator
            ->allowEmpty('source');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        return $rules;
    }

    /**
     * beforeSave method
     *
     * @param \Cake\Event\Event $event passed through the callback
     * @param \Cake\Datasource\EntityInterface $entity about to be saved
     * @param \ArrayObject $options to be passed
     *
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (empty($entity->source)) {
            $entity->source = 'Plugin__';
        }

        // Default calendar color in case none is given.
        if (empty($entity->color)) {
            $entity->color = Configure::read('Calendar.Configs.color');
        }
    }

    /**
     * afterSave method
     *
     * In case we don't get the source_id let the calendar point to itself
     *
     * @param \Cake\Event\Event $event passed
     * @param \Cake\Datasource\EntityInterface $entity of saved record
     * @param \ArrayObject $options passed
     *
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (empty($entity->source_id)) {
            $entity->source_id = $entity->id;
            $this->save($entity);
        }
    }

    /**
     * Get Calendar entities.
     *
     * @param array $options for filtering calendars
     *
     * @return array $result containing calendar entities with event_types
     */
    public function getCalendars($options = [])
    {
        $result = $conditions = [];

        if (!empty($options['conditions'])) {
            $conditions = $options['conditions'];
        }

        $query = $this->find()
                ->where($conditions)
                ->order(['name' => 'ASC'])
                ->all();
        $result = $query->toArray();

        if (empty($result)) {
            return $result;
        }

        $this->CalendarEvents = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        //adding event_types & events attached for the calendars
        foreach ($result as $k => $calendar) {
            $result[$k]->event_types = $this->CalendarEvents->getEventTypes($calendar);
        }

        return $result;
    }

    /**
     * Get Calendar Types
     *
     * @param string $type of current calendar instance
     *
     * @return array $result containing calendar types.
     */
    public function getCalendarTypes($type = null)
    {
        $result = [];

        $config = Configure::read('Calendar.Types');

        if (!empty($config)) {
            foreach ($config as $k => $val) {
                $result[$val['value']] = $val['name'];
            }
        }

        return $result;
    }

    /**
     * Synchronize calendar events
     *
     * @param \Model\Entity\Calendar $calendar instance from the db
     * @param array $data with extra configs
     *
     * @return array $result with events responses.
     */
    public function syncEventsAttendees($calendar, $data = [])
    {
        $result = [];
        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $attendeeTable = TableRegistry::get('Qobo/Calendar.CalendarAttendees');

        if (empty($data)) {
            return $result;
        }

        foreach ($data['modified'] as $k => $item) {
            if (empty($item['attendees'])) {
                continue;
            }

            foreach ($item['attendees'] as $attendee) {
                $diff = $this->getAttendeeDifferences(
                    $attendeeTable,
                    $attendee,
                    [
                        'source_id' => 'contact_details',
                    ]
                );
                $savedAttendee = $this->saveAttendeeDifferences($attendeeTable, $diff, [
                    'entity_options' => [
                        'associated' => ['CalendarEvents'],
                    ],
                    'extra_fields' => [
                        'calendar_events' => [
                            [
                                'id' => $item->id,
                                '_joinData' => [
                                    'response_status' => $attendee['response_status'],
                                ]
                            ]
                        ],
                    ],
                ]);

                $result['modified'][] = $savedAttendee;
            }
        }

        return $result;
    }

    /**
     * Collect calendars difference.
     *
     * @param \Cake\ORM\Table $table related instance.
     * @param object $calendarObject to be checked for add/update (aka calendar or event).
     *
     * @return array $result containing the diff.
     */
    public function getDifferences($calendarObject)
    {
        $calendarObject = $this->setObjectDifference(
            $this,
            $calendarObject,
            ['source', 'source_id']
        );

        $eventObjects = $calendarObject->getAttribute('events');

        if (empty($eventObjects)) {
            return $calendarObject;
        }

        $eventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $attendeesTable = TableRegistry::get('Qobo/Calendar.CalendarAttendees');

        foreach ($eventObjects as $eventObject) {
            // if we found calendar in db, we match its id foreign key.
            if ('update' == $calendarObject->getAttribute('diff_status')) {
                $eventObject->setAttribute(
                    'calendar_id',
                    $calendarObject->getAttribute('id')
                );
            }

            $eventObject = $this->setObjectDifference(
                $eventsTable,
                $eventObject,
                ['source', 'source_id']
            );

            $attendeeObjects = $eventObject->getAttribute('attendees');

            if (!empty($attendeeObjects)) {
                foreach ($attendeeObjects as $attendeeObject) {
                    $attendeeObject = $this->setObjectDifference(
                        $attendeesTable,
                        $attendeeObject,
                        ['display_name', 'contact_details']
                    );
                }
            }
        }

        $calendarObject->setAttribute('events', $eventObjects);

        return $calendarObject;
    }

    /**
     * Sycnrhonize calendars with corresponding events and attendees
     *
     * @param array $options with extra configs
     */
    public function sync(array $options = [])
    {
        $status = false;
        $data = $calendars = [];
        $event = new Event('App.Calendars.Model.getCalendars', $this, [
            'options' => $options
        ]);

        EventManager::instance()->dispatch($event);

        if (!empty($event->result)) {
            $calendars = $event->result;
        }

        if (empty($calendars)) {
            return $status;
        }
        unset($event);

        foreach ($calendars as $k => $calendarObject) {
            $event = new Event('App.Calendars.Model.getCalendarEvents', $this, [
                'calendar' => $calendarObject,
                'options' => $options,
            ]);

            EventManager::instance()->dispatch($event);

            if (!empty($event->result)) {
                $calendarObject->setEvents($event->result);
            }
        }

        foreach ($calendars as $k => $calendarObject) {
            $diff = $this->getDifferences($calendarObject);

            if (!empty($diff)) {
                array_push($data, $diff);
            }
        }

        if (!empty($data)) {
            foreach ($data as $k => $calendarObject) {
                $calendarEntity = $this->syncSaveCalendarObject($calendarObject);

                if (!$calendarEntity) {
                    continue;
                }

                $events = $calendarObject->getAttribute('events');

                if (empty($events)) {
                    continue;
                }

                $eventEntities = $this->syncSaveCalendarEventObjects($events);
            }
        }
        return $data;
    }

    protected function syncSaveCalendarEventObjects($eventObjects)
    {
        $result = false;

        return $result;
    }

    protected function syncSaveCalendarObject($object = null)
    {
        $result = false;

        $status = $object->getAttribute('diff_status');

        if (!in_array($status, ['add','update'])) {
            return $result;
        }

        $calendarsTable = TableRegistry::get('Qobo/Calendar.Calendars');

        $calendarEntity = $object->toEntity();
        $calendarData = $calendarEntity->toArray();

        $events = $calendarEntity->events;
        $calendarEntity->events = [];

        $entity = $calendarsTable->patchEntities($calendarEntity, $calendarData);

        $savedCalendar = $calendarsTable->save($entity);

        if (!$savedCalendar) {
            return $result;
        }

        return $result;
    }

    protected function getObjectConditions($object, array $fields = [])
    {
        $conditions = [];

        if (empty($fields)) {
            return $conditions;
        }

        foreach ($fields as $name) {
            $conditions[$name] = $object->getAttribute($name);
        }

        foreach ($conditions as $name => $val) {
            if (is_null($val)) {
                $conditions["$name IS"] = $val;
                unset($conditions[$name]);
            }
        }

        return $conditions;
    }

    protected function setObjectDifference($table, $object, $fields = [])
    {
        $status = 'add';
        $conditions = $this->getObjectConditions($object, $fields);

        $query = $table->find()
                ->where($conditions);
        $record = $query->first();

        if ($record) {
            $status = 'update';
            $object->setAttribute('id', $record->id);
        }

        $object->setAttribute('diff_status', $status);

        return $object;
    }
}
