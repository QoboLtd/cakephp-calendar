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
namespace Qobo\Calendar\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use DateTime;
use Qobo\Calendar\Object\ObjectFactory;
use \ArrayObject;
use \RRule\RRule;

/**
 * CalendarEvents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Calendars
 * @property \Cake\ORM\Association\BelongsTo $EventSources
 *
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent get($primaryKey, $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent newEntity($data = null, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent[] newEntities(array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent[] patchEntities($entities, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarEvent findOrCreate($search, callable $callback = null, $options = [])
 */
class CalendarEventsTable extends Table
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

        $this->setTable('calendar_events');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');

        $this->belongsTo('Calendars', [
            'foreignKey' => 'calendar_id',
            'joinType' => 'INNER',
            'className' => 'Qobo/Calendar.Calendars'
        ]);

        $this->belongsToMany('CalendarAttendees', [
            'joinTable' => 'events_attendees',
            'foreignKey' => 'calendar_event_id',
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
            ->allowEmpty('source');

        $validator
            ->requirePresence('title', 'create')
            ->allowEmpty('title');

        $validator
            ->requirePresence('content', 'create')
            ->allowEmpty('content');

        $validator
            ->dateTime('start_date')
            ->allowEmpty('start_date');

        $validator
            ->dateTime('end_date')
            ->allowEmpty('end_date');

        $validator
            ->time('duration')
            ->allowEmpty('duration');

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
        $rules->add($rules->existsIn(['calendar_id'], 'Calendars'));

        return $rules;
    }

    /**
     * beforeMarshal method
     *
     * We make sure that recurrence rule is saved as JSON.
     *
     * @param \Cake\Event\Event $event passed through the callback
     * @param \ArrayObject $data about to be saved
     * @param \ArrayObject $options to be passed
     *
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (!empty($data['recurrence'])) {
            $data['recurrence'] = json_encode($data['recurrence']);
        }
    }

    /**
     * Get Events of specific calendar
     *
     * @param \Cake\ORM\Table $calendar record
     * @param array $options with filter params
     *
     * @return array $result of events (minimal structure)
     */
    public function getCalendarEvents($calendar, $options = [])
    {
        $result = [];

        if (!$calendar) {
            return $result;
        }

        $options = array_merge($options, ['calendar_id' => $calendar->id]);
        $resultSet = $this->findCalendarEvents($options);
        if (empty($resultSet)) {
            return $result;
        }

        foreach ($resultSet as $k => $event) {
            $eventItem = $this->prepareEventData($event, $calendar);

            array_push($result, $eventItem);
        }

        return $result;
    }

    /**
     * Get Events of specific calendar
     *
     * @param \Cake\ORM\Table $calendar record
     * @param array $options with filter params
     *
     * @return array $result of events (minimal structure)
     */
    public function getEvents($calendar, $options = [])
    {
        $result = $infiniteEvents = [];

        if (!$calendar) {
            return $result;
        }
        $events = $this->findCalendarEvents($options);
        $infiniteEvents = $this->getInfiniteEvents($calendar->id, $events, $options);

        if (!empty($infiniteEvents)) {
            $events = array_merge($events, $infiniteEvents);
        }

        if (empty($events)) {
            return $result;
        }

        foreach ($events as $k => $event) {
            $extra = [];
            if (!empty($event['calendar_attendees'])) {
                foreach ($event['calendar_attendees'] as $att) {
                    array_push($extra, $att->display_name);
                }
            }

            if (!empty($extra)) {
                $event['title'] .= ' - ' . implode("\n", $extra);
            }

            $eventItem = $this->prepareEventData($event, $calendar);

            if (!empty($eventItem['recurrence'])) {
                $recurrence = $this->getRRuleConfiguration($eventItem['recurrence']);

                $intervals = $this->getRecurrence($recurrence, [
                    'start' => $eventItem['start_date'],
                    'end' => $eventItem['end_date'], //$options['period']['end_date'],
                ]);
                $recurringEvents = [];
                if (!empty($intervals)) {
                    foreach ($intervals as $interval) {
                        $entity = $this->newEntity();
                        $entity = $this->patchEntity($entity, $eventItem);
                        $entity->id = $eventItem['id'];
                        $entity->start_date = $interval['start'];
                        $entity->end_date = $interval['end'];
                        $entity->start = $entity->start_date->i18nFormat('yyyy-MM-dd HH:mm:ss');
                        $entity->end = $entity->end_date->i18nFormat('yyyy-MM-dd HH:mm:ss');

                        $entity->id = $entity->id . '__' . $this->setIdSuffix($entity);

                        array_push($recurringEvents, $entity->toArray());
                        unset($entity);
                    }
                }

                if (!empty($recurringEvents)) {
                    $result = array_merge($result, $recurringEvents);
                }
            } else {
                array_push($result, $eventItem);
            }
        }

        return $result;
    }

    /**
     * Get Event Range condition
     *
     * @param array $options containiner period with start_date/end_date params
     * @return array $result containing start/end keys with month dates.
     */
    public function getEventRange(array $options = [])
    {
        $result = [];

        if (empty($options['period'])) {
            return $result;
        }

        //@NOTE: sqlite doesn't support date_format or month functions
        if (!empty($options['period']['start_date'])) {
            $result['start'] = [
                'MONTH(start_date) >=' => date('m', strtotime($options['period']['start_date']))
            ];
        }

        if (!empty($options['period']['end_date'])) {
            $result['end'] = [
                'MONTH(end_date) <=' => date('m', strtotime($options['period']['end_date']))
            ];
        }

        return $result;
    }

    /**
     * Get infinite calendar events for given calendar
     *
     * @param mixed $calendarId as its id.
     * @param array $events from findCalendarEvents
     * @param array $options containing month viewport (end/start interval).
     *
     * @return array $result containing event records
     */
    public function getInfiniteEvents($calendarId, $events, $options = [])
    {
        $result = $existingEventIds = [];
        $range = $this->getEventRange($options);

        $query = $this->find();
        $query->where([
            'is_recurring' => true,
            'calendar_id' => $calendarId
        ]);

        if (!empty($range['start'])) {
            $query->andWhere($range['start']);
        }

        if (!empty($range['end'])) {
            $query->andWhere($range['end']);
        }

        $query->contain(['CalendarAttendees']);

        if (!$query) {
            return $result;
        }

        if (!empty($events)) {
            $existingEventIds = array_map(function ($item) {
                return $item->id;
            }, $events);
        }

        foreach ($query as $item) {
            if (in_array($item->id, $existingEventIds) || empty($item->recurrence)) {
                continue;
            }

            $rule = $this->getRRuleConfiguration(json_decode($item->recurrence, true));
            $rrule = new RRule($rule);

            if ($rrule->isInfinite()) {
                array_push($result, $item);
            }
        }

        return $result;
    }

    /**
     * Pre-populate Recurring events based on the parent event
     *
     * @param array $origin event object
     * @param array $options with events configs
     *
     * @return array $result with assembled recurring entities
     */
    public function getRecurringEvents($origin, array $options = [])
    {
        $result = [];
        $rule = $this->getRRuleConfiguration($origin['recurrence']);

        if (empty($rule) || empty($options['period'])) {
            return $result;
        }

        $rrule = new RRule($rule, new \DateTime($origin['start_date']));

        $st = $en = null;

        if (!empty($options['period']['end_date'])) {
            $en = new DateTime($options['period']['end_date']);
        }

        //new \DateTime($origin['start_date']),
        $eventDates = $rrule->getOccurrencesBetween(
            new \DateTime($options['period']['start_date']),
            $en
        );

        $startDateTime = new \DateTime($origin['start_date'], new \DateTimeZone('UTC'));
        $endDateTime = new \DateTime($origin['end_date'], new \DateTimeZone('UTC'));
        $diff = $startDateTime->diff($endDateTime);

        $diffString = $diff->format('%R%y years, %R%a days, %R%h hours, %R%i minutes');

        foreach ($eventDates as $eventDate) {
            if ($eventDate->format('Y-m-d') == $startDateTime->format('Y-m-d')) {
                continue;
            }

            $entity = $this->newEntity();
            $entity = $this->patchEntity($entity, $origin);

            $entity->start_date->year((int)$eventDate->format('Y'));
            $entity->start_date->month((int)$eventDate->format('m'));
            $entity->start_date->day((int)$eventDate->format('d'));

            $entity->end_date = clone $entity->start_date;
            $entity->end_date->modify($diffString);

            $entity->start_date->i18nFormat('yyyy-MM-dd HH:mm:ss');
            $entity->end_date->i18nFormat('yyyy-MM-dd HH:mm:ss');
            $entity->start = $entity->start_date->i18nFormat('yyyy-MM-dd HH:mm:ss');
            $entity->end = $entity->end_date->i18nFormat('yyyy-MM-dd HH:mm:ss');

            $entity->id = $origin['id'] . '__' . $this->setIdSuffix($entity);

            array_push($result, $entity->toArray());

            unset($entity);
        }

        return $result;
    }

    /**
     * Set ID suffix for recurring events
     *
     * We attach timestamp suffix for recurring events
     * that haven't been saved in the DB yet.
     *
     * @param array $entity of the event
     *
     * @return string $result with suffix.
     */
    public function setIdSuffix($entity = null)
    {
        if (is_object($entity)) {
            $result = strtotime($entity->start_date) . '_' . strtotime($entity->end_date);
        } else {
            $result = strtotime($entity['start_date']) . '_' . strtotime($entity['end_date']);
        }

        return $result;
    }

    /**
     * Parse recurrent event id suffix
     *
     * @param string $timestamp containing date suffix
     * @return array $result containing start/end pair.
     */
    public function getIdSuffix($timestamp = null)
    {
        $result = [];

        if (empty($timestamp)) {
            return $result;
        }

        if (preg_match('/(\d+)\_(\d+)/i', $timestamp, $parts)) {
            $result['start'] = date('Y-m-d H:i:s', $parts[1]);
            $result['end'] = date('Y-m-d H:i:s', $parts[2]);
        }

        return $result;
    }

    /**
     * Get RRULE configuration from the event
     *
     * @param array $recurrence received from the calendar
     *
     * @return array $result containing the RRULE
     */
    public function getRRuleConfiguration($recurrence = [])
    {
        $result = '';

        if (empty($recurrence) || is_null($recurrence)) {
            return $result;
        }

        if (is_string($recurrence)) {
            $recurrence = json_decode($recurrence, true);
        }
        if (!empty($recurrence)) {
            foreach ($recurrence as $rule) {
                if (preg_match('/^RRULE:/i', $rule)) {
                    $result = $rule;
                }
            }
        }

        return $result;
    }

    /**
     * Set valid RRULE string
     *
     * @param string $recurrence from the UI
     * @return object $result json encoded array with RRULE
     */
    public function setRRuleConfiguration($recurrence = null)
    {
        $result = [];

        if (!empty($recurrence)) {
            if (!preg_match('/RRULE:/', $recurrence)) {
                $result = 'RRULE:' . $recurrence;
            }
        }

        $result = json_encode($result);

        return $result;
    }

    /**
     * Get Event types for the calendar event
     *
     * @param array $options of the data including user
     * @return array $result with event types.
     */
    public function getEventTypes(array $options = [])
    {
        $result = [];

        if (!empty($options['calendar'])) {
            $types = json_decode($options['calendar']->event_types, true);
            foreach ($types as $type) {
                $result[$type] = $type;
            }

            asort($result);

            return $result;
        }

        $event = new Event('App.Calendars.getCalendarEventTypes', $this, [
            'user' => $options['user'],
        ]);

        EventManager::instance()->dispatch($event);

        if (!empty($event->result)) {
            $result = array_merge($result, $event->result);
        }

        $configs = Configure::read('Calendar.Types');
        foreach ($configs as $calendar) {
            if (empty($calendar['calendar_events'])) {
                continue;
            }

            foreach ($calendar['calendar_events'] as $type => $properties) {
                $value = $this->getEventTypeName([
                    'name' => $calendar['name'],
                    'type' => $type,
                ]);

                if (empty($value)) {
                    continue;
                }

                $result[$value] = $value;
            }
        }

        if (!empty($eventTypes) && is_string($eventTypes)) {
            $eventTypes = json_decode($eventTypes, true);

            foreach ($eventTypes as $eventType) {
                array_push($result, $eventType);
            }
        }

        asort($result);

        return $result;
    }

    /**
     * Get Event Type name
     *
     * @param array $data with name parts
     * @param array $options for extra settings if needed
     *
     * @return string $name containing event type definition.
     */
    public function getEventTypeName(array $data = [], array $options = [])
    {
        if (empty($data['name'])) {
            return;
        }

        $prefix = !empty($options['prefix']) ? $options['prefix'] : 'Config';
        $type = !empty($data['type']) ? $data['type'] : 'default';
        $delimiter = '::';

        $name = $prefix . $delimiter . $data['name'] . $delimiter . Inflector::camelize($type);

        return $name;
    }

    /**
     * Get Event info
     *
     * @param array $options containing event id
     *
     * @return array $result containing record data
     */
    public function getEventInfo($options = [])
    {
        $result = [];
        $end = $start = null;

        if (empty($options)) {
            return $result;
        }

        if (!empty($options['timestamp'])) {
            $range = $this->getIdSuffix($options['timestamp']);
        }

        $result = $this->find()
                ->where(['id' => $options['id']])
                ->contain(['CalendarAttendees'])
                ->first();

        //@NOTE: we're faking the start/end intervals for recurring events
        if (!empty($range['end'])) {
            $time = Time::parse($range['end']);
            $result->end_date = $time;
            unset($time);
        }

        if (!empty($range['start'])) {
            $time = Time::parse($range['start']);
            $result->start_date = $time;
            unset($time);
        }

        return $result;
    }

    /**
     * PrepareEventData method
     *
     * @param array $event of the calendar
     * @param array $calendar currently checked
     * @param array $options with extra configs
     *
     * @return array $item containing calendar event record.
     */
    protected function prepareEventData($event, $calendar, $options = [])
    {
        $item = [];

        $item = [
            'id' => $event['id'],
            'title' => (!empty($options['title']) ? $options['title'] : $event['title']),
            'content' => $event['content'],
            'start_date' => date('Y-m-d H:i:s', strtotime($event['start_date'])),
            'end_date' => date('Y-m-d H:i:s', strtotime($event['end_date'])),
            'start' => date('Y-m-d H:i:s', strtotime($event['start_date'])),
            'end' => date('Y-m-d H:i:s', strtotime($event['end_date'])),
            'color' => (empty($event['color']) ? $calendar->color : $event['color']),
            'source' => $event['source'],
            'source_id' => $event['source_id'],
            'calendar_id' => $calendar->id,
            'event_type' => (!empty($event['event_type']) ? $event['event_type'] : null),
            'is_recurring' => $event['is_recurring'],
            'is_allday' => $event['is_allday'],
            'recurrence' => (!empty($event['recurrence']) ? json_decode($event['recurrence'], true) : null),
        ];

        return $item;
    }

    /**
     * findCalendarEvents method
     *
     * @param array $options containing conditions for query
     *
     * @return array $result with events found.
     */
    protected function findCalendarEvents($options = [])
    {
        $conditions = [];

        if (!empty($options['calendar_id'])) {
            $conditions['calendar_id'] = $options['calendar_id'];
        }

        if (!empty($options['period']['start_date'])) {
            $conditions['start_date >='] = $options['period']['start_date'];
        }

        if (!empty($options['period']['end_date'])) {
            $conditions['end_date <='] = $options['period']['end_date'];
        }

        $result = $this->find()
                ->where($conditions)
                ->contain(['CalendarAttendees'])
                ->toArray();

        return $result;
    }

    /**
     * Set Event Title
     *
     * @param array $data from the request
     * @param \Cake\Datasource\EntityInterface $calendar from db
     *
     * @return string $title with the event content
     */
    public function setEventTitle($data, $calendar)
    {
        $title = (!empty($data['CalendarEvents']['title']) ? $data['CalendarEvents']['title'] : '');

        if (empty($title)) {
            $title .= !empty($calendar->name) ? $calendar->name : '';

            if (!empty($data['CalendarEvents']['event_type'])) {
                $title .= ' - ' . Inflector::humanize($data['CalendarEvents']['event_type']);
            } else {
                $title .= ' Event';
            }
        }

        return $title;
    }

    /**
     * Synchronize calendar events
     *
     * @param \Cake\ORM\Entity $calendar instance from the db
     * @param array $options with extra configs
     *
     * @return array $result with events responses.
     */
    public function sync($calendar, $options = [])
    {
        $result = [];
        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        if (empty($calendar)) {
            return $result;
        }

        $event = new Event((string)EventName::PLUGIN_CALENDAR_MODEL_GET_EVENTS(), $this, [
            'calendar' => $calendar,
            'options' => $options,
        ]);

        EventManager::instance()->dispatch($event);

        $calendarEvents = $event->result;
        if (empty($calendarEvents)) {
            return $result;
        }

        foreach ($calendarEvents as $k => $calendarInfo) {
            if (empty($calendarInfo['events'])) {
                continue;
            }
        }

        return $result;
    }

    /**
     * Save Calendar Event wrapper
     *
     * @param \Cake\Datasource\EntityInterface $entity of generic entity object
     * @return array $response containing saving state of entity.
     */
    public function saveEvent($entity)
    {
        $response = [
            'status' => false,
            'errors' => [],
            'entity' => null,
        ];

        if ($entity instanceof \Cake\Datasource\EntityInterface) {
            $entity = $entity->toArray();
        }

        if (empty($entity['id'])) {
            unset($entity['id']);
        }

        $query = $this->find()
            ->where([
                //'source' => empty($entity['source']) ? null : $entity['source'],
                'source_id' => $entity['source_id'],
                'calendar_id' => $entity['calendar_id'],
            ]);

        $query->execute();

        if (!$query->count()) {
            $event = $this->newEntity();
            $event = $this->patchEntity($event, $entity);
        } else {
            $event = $this->patchEntity($query->first(), $entity);
        }

        $saved = $this->save($event);

        if ($saved) {
            $response['status'] = true;
            $response['entity'] = $saved;
        } else {
            $response['errors'] = $event->getErrors();
            dd($entity);
        }

        return $response;
    }

    /**
     * Convert incoming ORM Entities to CalendarEvent entities
     *
     * We create multiple entities for each calendar to avoid accidential
     * crossing of instances.
     *
     * @param \Cake\ORM\Table $table instance of original entity table
     * @param array $calendars that it might be saved to
     * @param \ArrayObject $options originally received from event caught
     *
     * @return array $entities of converted CalendarEvent entitites.
     */
    public function getEventsFromEntities($table, $calendars, $options)
    {
        $entities = [];

        if (empty($calendars)) {
            return $entities;
        }

        $map = ObjectFactory::getConfig($table->alias(), 'Event', $options['event_type']);
        foreach ($calendars as $calendar) {
            $options = array_merge($options->getArrayCopy(), ['calendar' => $calendar]);
            $options = new ArrayObject($options);
            $entity = $options['entity'];
            $eventObject = $table->getObjectInstance($entity, $map, $options);

            $calendarEntity = $eventObject->toEntity();

            if (!$calendarEntity) {
                continue;
            }

            $entities[] = $calendarEntity;
        }

        return $entities;
    }

    /**
     * Get Recurrences
     *
     * Recurrence Rule RFC is used to define
     * date intervals when the user will be working
     *
     * @param string $recurrence in RRULE RFC standard
     * @param array $data with start/end string from the form
     *
     * @return array $result containing index hashes of start/end Time objects.
     */
    public function getRecurrence($recurrence, array $data)
    {
        $result = [];
        $startDate = new Time($data['start']);
        $untilDate = new Time($data['end']);

        $limit = (!empty($data['limit']) ? $data['limit'] : null);

        $rrule = new RRule($recurrence, $startDate);
        $intervals = $rrule->getOccurrencesBetween($startDate, $untilDate, $limit);

        if (!empty($intervals)) {
            $diff = $startDate->diff($untilDate);

            foreach ($intervals as $item) {
                $st = new Time($item->format('Y-m-d H:i:s'));
                $end = new Time($item->format('Y-m-d H:i:s'));

                $end->addHour($diff->format('%h'));
                $end->addMinute($diff->format('%i'));

                $range = [
                    'start' => $st,
                    'end' => $end,
                ];

                array_push($result, $range);
            }
        }

        return $result;
    }

    /**
     * Set Calendar Event Post data
     *
     * @param array $data from the POST
     * @return array $result to be saved in ORM
     */
    public function setCalendarEventData(array $data = [], $calendar = null)
    {
        $result = [
            'CalendarEvents' => [],
        ];

        $result['CalendarEvents'] = $data;

        if (!empty($data['recurrence'])) {
            $recurrence = $this->getRRuleConfiguration($data['recurrence']);
            $intervals = $this->getRecurrence($recurrence, [
                'start' => $data['start_date'],
                'end' => $data['end_date'],
                'limit' => 1
            ]);
            $result['CalendarEvents']['end_date'] = $intervals[0]['end'];
            $result['CalendarEvents']['is_recurring'] = true;
            $result['CalendarEvents']['recurrence'] = $this->setRRuleConfiguration($data['recurrence']);
        }

        if (!empty($data['attendees_ids'])) {
            $result['calendar_attendees']['_ids'] = $data['attendees_ids'];
            unset($result['CalendarEvents']['attendees_ids']);
        }

        if (empty($data['title'])) {
            $result['CalendarEvents']['title'] = $this->setEventTitle($data, $calendar);
        }

        return $result;
    }

    /**
     * Get Calendar Event for UI
     *
     * @param string $id uuid of the record
     * @return array $result of the record.
     */
    public function getCalendarEventById($id = null, $calendar = null)
    {
        $this->Calendars = TableRegistry::get('Qobo/Calendar.Calendars');

        $result = $attendees = [];

        if (empty($id)) {
            return $result;
        }

        $query = $this->find()
            ->contain(['CalendarAttendees'])
            ->where(['id' => $id]);

        $entity = $query->first();

        if (!$entity) {
            return $result;
        }

        if (!empty($entity->calendar_attendees)) {
            $attendees = $entity->calendar_attendees;
            $entity->__unset('calendar_attendees');
        }

        $entity->start = $entity->start_date;
        $entity->end = $entity->end_date;

        if ($calendar) {
            if (empty($entity->calendar_id)) {
                $entity->calendar_id = $calendar->id;
            }
        }

        $entity->color = $this->Calendars->getColor($calendar);
        $result = $entity->toArray();

        return $result;
    }
}
