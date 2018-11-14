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
use Cake\ORM\Entity;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use Qobo\Calendar\Event\EventName;
use Qobo\Calendar\Object\ObjectFactory;
use \ArrayObject;
use \RRule\RRule;

/**
 * CalendarEvents Model
 * @property \Qobo\Calendar\Model\Table\CalendarsTable $Calendars
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

        /** @var \Qobo\Calendar\Model\Table\CalendarsTable $table */
        $table = TableRegistry::get('Qobo/Calendar.Calendars');
        $this->Calendars = $table;
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
     * Set ID suffix for recurring events
     *
     * We attach timestamp suffix for recurring events
     * that haven't been saved in the DB yet.
     *
     * @param \Cake\Datasource\EntityInterface|array $entity of the event
     *
     * @return string $result with suffix.
     */
    public function setRecurrenceEventId($entity = null): string
    {
        $start = $entity instanceof EntityInterface ? $entity->get('start_date') : $entity['start_date'];
        $end = $entity instanceof EntityInterface ? $entity->get('end_date') : $entity['end_date'];
        $id = $entity instanceof EntityInterface ? $entity->get('id') : $entity['id'];

        $result = sprintf("%s__%s_%s", $id, strtotime($start), strtotime($end));

        return $result;
    }

    /**
     * Parse recurrent event id suffix
     *
     * @param string $id containing date suffix
     * @return mixed[] $result containing start/end pair.
     */
    public function getRecurrenceEventId(?string $id = null): array
    {
        $result = [];

        if (empty($id)) {
            return $result;
        }

        if (preg_match('/(^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})(__(\d+)?_(\d+)?)?/', $id, $matches)) {
            $result['id'] = $matches[1];
            $result['start'] = !empty($matches[3]) ? date('Y-m-d H:i:s', $matches[3]) : null;
            $result['end'] = !empty($matches[4]) ? date('Y-m-d H:i:s', $matches[4]) : null;
        }

        return $result;
    }

    /**
     * Get Events of specific calendar
     *
     * @param \Cake\Datasource\EntityInterface|null $calendar record
     * @param mixed[] $options with filter params
     * @param bool $isInfinite flag to find inifinite events like birthdays
     *
     * @return mixed[] $result of events (minimal structure)
     */
    public function getEvents(?EntityInterface $calendar, array $options = [], bool $isInfinite = true): array
    {
        $result = [];

        if (!$calendar) {
            return $result;
        }

        $events = $this->findCalendarEvents($options);

        foreach ($events as $event) {
            $eventItem = $this->prepareEventData($event->toArray(), $calendar);

            if (empty($eventItem['recurrence'])) {
                array_push($result, $eventItem);
                continue;
            }

            $recurrence = $this->getRRuleConfiguration($eventItem['recurrence']);

            if (empty($recurrence)) {
                continue;
            }

            $intervals = $this->getRecurrence($recurrence, [
                'start' => $eventItem['start_date'],
                'end' => $eventItem['end_date'],
            ]);

            foreach ($intervals as $interval) {
                $entity = $this->prepareRecurringEventData($eventItem, $interval, $calendar);
                array_push($result, $entity->toArray());
            }
        }

        $infiniteEvents = $this->getInfiniteEvents($events, $options, $isInfinite);

        foreach ($infiniteEvents as $event) {
            $eventItem = $this->prepareEventData($event->toArray(), $calendar);

            $recurrence = $this->getRRuleConfiguration($eventItem['recurrence']);

            if (empty($recurrence)) {
                continue;
            }

            $intervals = $this->getRecurrence($recurrence, [
                'start' => $eventItem['start_date'],
                'end' => $options['period']['end_date'],
                'from' => $options['period']['start_date'],
                'fixTime' => true,
            ]);

            foreach ($intervals as $interval) {
                $entity = $this->prepareRecurringEventData($eventItem, $interval, $calendar);
                array_push($result, $entity->toArray());
            }
        }

        return $result;
    }

    /**
     * Get Event Range condition
     *
     * @param mixed[] $options containiner period with start_date/end_date params
     * @return mixed[] $result containing start/end keys with month dates.
     */
    public function getEventRange(array $options = []): array
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
     * @param mixed[] $events from findCalendarEvents
     * @param mixed[] $options containing month viewport (end/start interval).
     * @param bool $isInfinite flag for infinite events like birthdays
     *
     * @return mixed[] $result containing event records
     */
    public function getInfiniteEvents(array $events, array $options = [], bool $isInfinite = true): array
    {
        $result = $existingEventIds = [];
        $query = $this->findCalendarEvents($options, $isInfinite);

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

            $rule = $this->getRRuleConfiguration($item->recurrence);
            $rrule = new RRule($rule);

            if ($rrule->isInfinite()) {
                array_push($result, $item);
            }
        }

        return $result;
    }

    /**
     * Get RRULE configuration from the event
     *
     * @param string $recurrence received from the calendar
     *
     * @return string|null $result containing the RRULE
     */
    public function getRRuleConfiguration(string $recurrence = null): ?string
    {
        $result = null;

        if (empty($recurrence)) {
            return $result;
        }

        $result = preg_match('/^RRULE:/', $recurrence) ? $recurrence : 'RRULE:' . $recurrence;

        return $result;
    }

    /**
     * Set valid RRULE string
     *
     * @param string $recurrence from the UI
     * @return string|null $result json encoded array with RRULE
     */
    public function setRRuleConfiguration(string $recurrence = null): ?string
    {
        $result = null;

        if (empty($recurrence)) {
            return $result;
        }

        if (!preg_match('/RRULE:/', $recurrence)) {
            $result = 'RRULE:' . $recurrence;
        }

        return $result;
    }

    /**
     * Get Event Types from Configure::read()
     *
     * @param string $name of the event type
     *
     * @return mixed[] $result containing key/value pair of event types.
     */
    public function getEventTypeBy(? string $name = null): array
    {
        $result = [];
        $configs = Configure::read('Calendar.Types');

        if (empty($configs)) {
            return $result;
        }

        if (!empty($name)) {
            $configs = array_filter($configs, function ($item) use ($name) {
                if ($name == $item['value']) {
                    return $item;
                }
            });
        }

        $configs = array_values($configs);

        foreach ($configs as $k => $calendar) {
            if (empty($calendar['calendar_events'])) {
                continue;
            }

            foreach ($calendar['calendar_events'] as $type => $properties) {
                $value = $this->getEventTypeName([
                    'name' => $calendar['name'],
                    'type' => $type,
                ]);

                $result[$value] = $value;
            }
        }

        asort($result);

        return $result;
    }

    /**
     * Get Event types for the calendar event
     *
     * @param mixed[] $options of the data including user
     * @return mixed[] $result with event types.
     */
    public function getEventTypes(array $options = []): array
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

        $configEventTypes = $this->getEventTypeBy();
        $result = array_merge($result, $configEventTypes);

        return $result;
    }

    /**
     * Get Event Type name
     *
     * @param mixed[] $data with name parts
     * @param mixed[] $options for extra settings if needed
     *
     * @return string|null $name containing event type definition.
     */
    public function getEventTypeName(array $data = [], array $options = []): ?string
    {
        if (empty($data['name'])) {
            return null;
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
     * @param string $id of the record
     * @param \Cake\Datasource\EntityInterface $calendar instance
     *
     * @return array|\Cake\Datasource\EntityInterface $result containing record data
     */
    public function getEventInfo(?string $id = null, ?EntityInterface $calendar = null)
    {
        $result = [];

        if (empty($id)) {
            return $result;
        }

        $options = $this->getRecurrenceEventId($id);

        $query = $this->find()
                ->where(['id' => $options['id']])
                ->contain(['CalendarAttendees']);
        $result = $query->first();

        if (!($result instanceof EntityInterface)) {
            return [];
        }

        //@NOTE: we're faking the start/end intervals for recurring events
        if (!empty($options['end'])) {
            $time = Time::parse($options['end']);
            $result->set('end_date', $time);
            $result->set('end', $time);
            unset($time);
        }

        if (!empty($options['start'])) {
            $time = Time::parse($options['start']);
            $result->set('start_date', $time);
            $result->set('start', $time);
            unset($time);
        }

        if ($calendar) {
            if (empty($result->calendar_id)) {
                $result->set('calendar_id', $calendar->id);
            }
        }

        $result->set('color', $this->Calendars->getColor($calendar));

        return $result;
    }

    /**
     * Prepare Recurring Event Data
     *
     * Substitute original dates with recurring dates
     *
     * @param mixed[] $event of the original instance
     * @param mixed[] $interval pair with start/end dates to be used
     * @param \Cake\Datasource\EntityInterface $calendar instance
     *
     * @return \Cake\Datasource\EntityInterface|null $entity of the recurring event
     */
    public function prepareRecurringEventData(array $event = [], array $interval = [], EntityInterface $calendar = null): ?EntityInterface
    {
        $entity = null;

        if (empty($event)) {
            return $entity;
        }

        $entity = $this->newEntity();
        $entity = $this->patchEntity($entity, $event);
        $entity->set('start_date', $interval['start']);
        $entity->set('end_date', $interval['end']);

        $entity->set('start', $entity->get('start_date')->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $entity->set('end', $entity->get('end_date')->i18nFormat('yyyy-MM-dd HH:mm:ss'));
        $entity->set('id', $event['id']);

        $entity->set('id', $this->setRecurrenceEventId($entity));
        $entity->set('color', $this->Calendars->getColor($calendar));

        return $entity;
    }

    /**
     * PrepareEventData method
     *
     * @param mixed[] $event of the calendar
     * @param \Cake\Datasource\EntityInterface $calendar currently checked
     * @param mixed[] $options with extra configs
     *
     * @return mixed[] $item containing calendar event record.
     */
    protected function prepareEventData(array $event = [], ?EntityInterface $calendar = null, array $options = []): array
    {
        $item = [];

        if (empty($options['title'])) {
            $event['title'] = $this->getEventTitle($event);
        }

        $item = [
            'id' => $event['id'],
            'title' => (!empty($options['title']) ? $options['title'] : $event['title']),
            'content' => $event['content'],
            'start_date' => date('Y-m-d H:i:s', strtotime($event['start_date'])),
            'end_date' => date('Y-m-d H:i:s', strtotime($event['end_date'])),
            'start' => date('Y-m-d H:i:s', strtotime($event['start_date'])),
            'end' => date('Y-m-d H:i:s', strtotime($event['end_date'])),
            'color' => (empty($event['color']) ? $calendar->get('color') : $event['color']),
            'source' => $event['source'],
            'source_id' => $event['source_id'],
            'calendar_id' => $calendar->get('id'),
            'event_type' => (!empty($event['event_type']) ? $event['event_type'] : null),
            'is_recurring' => $event['is_recurring'],
            'is_allday' => $event['is_allday'],
            'recurrence' => $event['recurrence'],
        ];

        return $item;
    }

    /**
     * findCalendarEvents method
     *
     * @param mixed[] $options containing conditions for query
     * @param bool $isInfinite flag to find recurring infinite events like (birthdays)
     *
     * @return mixed[] $result with events found.
     */
    protected function findCalendarEvents(array $options = [], bool $isInfinite = false): array
    {
        $conditions = [];
        if ($isInfinite) {
            $range = $this->getEventRange($options);
            $conditions['is_recurring'] = true;
        }

        if (!empty($options['calendar_id'])) {
            $conditions['calendar_id'] = $options['calendar_id'];
        }

        if (!empty($options['period']['start_date'])) {
            $conditions['start_date >='] = $options['period']['start_date'];
        }

        if (!empty($options['period']['end_date'])) {
            $conditions['end_date <='] = $options['period']['end_date'];
        }

        if ($isInfinite && !empty($range)) {
            unset($conditions['start_date >=']);
            unset($conditions['end_date <=']);
            $conditions = array_merge($conditions, $range['start'], $range['end']);
        }

        if (empty($conditions)) {
            return [];
        }

        $result = $this->find()
                ->where($conditions)
                ->contain(['CalendarAttendees'])
                ->all();

        if (!$isInfinite) {
            $result = $result->toArray();
        }

        return $result;
    }

    /**
     * Set Event Title
     *
     * @param mixed[] $data from the request
     * @param \Cake\Datasource\EntityInterface $calendar from db
     *
     * @return string $title with the event content
     */
    public function setEventTitle(array $data, EntityInterface $calendar): string
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
     * Get Event Title based on the Event information
     *
     * @param mixed[] $event of the calendar event instance
     *
     * @return string $event[title] with new title if extras present
     */
    public function getEventTitle(array $event = []): string
    {
        $extra = [];

        if (!empty($event['calendar_attendees'])) {
            foreach ($event['calendar_attendees'] as $att) {
                if ($att instanceof EntityInterface) {
                    array_push($extra, $att->get('display_name'));
                }
            }
        }

        if (!empty($extra)) {
            $event['title'] .= ' - ' . implode("\n", $extra);
        }

        return $event['title'];
    }

    /**
     * Synchronize calendar events
     *
     * @param \Cake\Datasource\EntityInterface $calendar instance from the db
     * @param mixed[] $options with extra configs
     *
     * @return mixed[] $result with events responses.
     */
    public function sync(EntityInterface $calendar, array $options = []): array
    {
        $result = [];

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

        foreach ($calendarEvents as $calendarInfo) {
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
    public function saveEvent(EntityInterface $entity): array
    {
        $response = [
            'status' => false,
            'errors' => [],
            'entity' => null,
        ];

        if ($entity instanceof EntityInterface) {
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

        $event = (!$query->count()) ? $this->newEntity() : $query->first();
        $event = $this->patchEntity($event, $entity);

        $saved = $this->save($event);

        if ($saved) {
            $response['status'] = true;
            $response['entity'] = $saved;
        } else {
            $response['errors'] = $event->getErrors();
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
     * @param mixed[] $calendars that it might be saved to
     * @param \ArrayObject $options originally received from event caught
     *
     * @return mixed[] $entities of converted CalendarEvent entitites.
     */
    public function getEventsFromEntities(Table $table, array $calendars, ArrayObject $options): array
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
     * @param mixed[] $data with start/end string from the form
     *
     * @return mixed[] $result containing index hashes of start/end Time objects.
     */
    public function getRecurrence(string $recurrence, array $data): array
    {
        $result = [];

        $startDate = new Time($data['start']);
        $untilDate = new Time($data['end']);
        $fromDate = !empty($data['from']) ? new Time($data['from']) : new Time($data['start']);

        $fixTime = isset($data['fixTime']) ? $data['fixTime'] : false;
        $limit = (!empty($data['limit']) ? $data['limit'] : null);

        $rrule = new RRule($recurrence, $startDate);
        $intervals = $rrule->getOccurrencesBetween($fromDate, $untilDate, $limit);

        if (!empty($intervals)) {
            $diff = $startDate->diff($untilDate);
            foreach ($intervals as $item) {
                $st = new Time($item->format('Y-m-d H:i:s'));
                $end = new Time($item->format('Y-m-d H:i:s'));

                if (!$fixTime) {
                    $end->addHour((int)$diff->format('%h'));
                    $end->addMinute((int)$diff->format('%i'));
                }

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
     * @param mixed[] $data from the POST
     * @param \Cake\Datasource\EntityInterface $calendar record
     * @return mixed[] $result to be saved in ORM
     */
    public function setCalendarEventData(array $data = [], ?EntityInterface $calendar = null): array
    {
        $result = [
            'CalendarEvents' => [],
        ];

        $result['CalendarEvents'] = $data;
        if (!empty($data['recurrence'])) {
            $recurrence = $this->setRRuleConfiguration($data['recurrence']);
            $result['CalendarEvents']['is_recurring'] = true;
            $result['CalendarEvents']['recurrence'] = $recurrence;
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
}
