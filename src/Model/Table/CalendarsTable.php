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
use Cake\Validation\Validator;
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
     * Synchronize calendars
     *
     * @param array $options passed from the outside.
     *
     * @return array $result of the synchronize method.
     */
    public function syncCalendars($options = [])
    {
        $result = [];

        $event = new Event('Plugin.Calendars.Model.getCalendars', $this, [
            'options' => $options,
        ]);

        EventManager::instance()->dispatch($event);

        if (empty($event->result)) {
            return $result;
        }

        $appCalendars = $event->result;

        foreach ($appCalendars as $k => $calendarData) {
            $calendar = !empty($calendarData['calendar']) ? $calendarData['calendar'] : [];

            if (empty($calendar)) {
                continue;
            }

            // we don't pass period as it doesn't have time limits.
            $diffCalendar = $this->getItemDifferences(
                $this,
                $calendar
            );

            $result['modified'][] = $this->saveItemDifferences($this, $diffCalendar);
        }

        $ignored = $this->itemsToDelete($this, $result['modified']);

        $result['removed'] = $this->saveItemDifferences($this, ['delete' => $ignored]);

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
     * Synchronize calendar events
     *
     * @param \Model\Entity\Calendar $calendar instance from the db
     * @param array $options with extra configs
     *
     * @return array $result with events responses.
     */
    public function syncCalendarEvents($calendar, $options = [])
    {
        $result = [];
        $table = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        if (empty($calendar)) {
            return $result;
        }

        $event = new Event('Plugin.Calendars.Model.getCalendarEvents', $this, [
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

            foreach ($calendarInfo['events'] as $item) {
                $diff = $this->getItemDifferences(
                    $table,
                    $item,
                    $options
                );

                $savedDiff = $this->saveItemDifferences($table, $diff, [
                    'extra_fields' => [
                        'calendar_id' => $calendarInfo['calendar']->id
                    ],
                ]);

                $result['modified'][] = $savedDiff;
            }

            $ignored = $this->itemsToDelete($table, $result['modified'], [
                'extra_fields' => [
                    'calendar_id' => $calendarInfo['calendar']->id
                ],
            ]);
            $result['removed'] = $this->saveItemDifferences($table, ['delete' => $ignored]);
        }

        return $result;
    }

    /**
     * saveCalendarDifferences method
     *
     * Updating calendar DB with differences
     *
     * @param \Cake\ORM\Table $table of the instance
     * @param array $diff prepopulated calendars
     * @param array $options with extra configs if any.
     *
     * @return array $result with updated/deleted/added calendars.
     */
    public function saveItemDifferences($table, $diff = [], $options = [])
    {
        $result = [];
        $entityOptions = [];

        if (empty($diff)) {
            return $result;
        }

        foreach ($diff as $actionName => $items) {
            if (empty($items)) {
                continue;
            }

            foreach ($items as $k => $item) {
                $data = [];

                if (empty($item)) {
                    continue;
                }

                switch ($actionName) {
                    case 'add':
                        $entity = $table->newEntity();
                        $data = $item;
                        break;
                    case 'update':
                        $entity = $item['entity'];
                        $data = $item['data'];
                        break;
                }

                if (in_array($actionName, ['add', 'update']) && !empty($data)) {
                    if (!empty($options['extra_fields'])) {
                        $data = array_merge($data, $options['extra_fields']);
                    }

                    if (!empty($options['entity_options'])) {
                        $entityOptions = array_merge($entityOptions, $options['entity_options']);
                    }

                    $entity = $table->patchEntity($entity, $data, $entityOptions);
                    $result = $table->save($entity);
                }

                if (in_array($actionName, ['delete']) && !empty($item)) {
                    if ($table->delete($item)) {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Save Attendee Differences
     *
     * Checkes whether attendee should be added/updated/removed
     *
     * @param ORM\Table $table instance
     * @param array $diff containing the data
     * @param array $options with extra settings/fields to save/modify
     *
     * @return array $result containing diff results
     */
    public function saveAttendeeDifferences($table, $diff = [], $options = [])
    {
        $result = [];
        $entityOptions = [];

        if (empty($diff)) {
            return $result;
        }

        foreach ($diff as $actionName => $items) {
            if (empty($items)) {
                continue;
            }

            foreach ($items as $k => $item) {
                $data = [];

                if (empty($item)) {
                    continue;
                }

                switch ($actionName) {
                    case 'add':
                        $entity = $table->newEntity();
                        $data = $item;
                        break;
                    case 'update':
                        $entity = $item['entity'];
                        $data = $item['data'];
                        break;
                }

                if (in_array($actionName, ['add', 'update']) && !empty($data)) {
                    if (!empty($options['extra_fields'])) {
                        $data = array_merge($data, $options['extra_fields']);
                    }

                    if (!empty($options['entity_options'])) {
                        $entityOptions = array_merge($entityOptions, $options['entity_options']);
                    }

                    $entity = $table->patchEntity($entity, $data, $entityOptions);
                    $savedAttendee = $table->save($entity);
                }
                if (in_array($actionName, ['delete']) && !empty($item)) {
                    if ($table->delete($item)) {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Collect calendars difference.
     *
     * @param \Cake\ORM\Table $table related instance.
     * @param array $item to be checked for add/update (aka calendar or event).
     * @param array $options with extra configs
     *
     * @return $calendarDiff containing the list of calendars to add/update/delete.
     */
    public function getItemDifferences($table, $item = null, $options = [])
    {
        $conditions = [];
        $source = empty($options['source']) ? 'source' : $options['source'];
        $sourceId = empty($options['source_id']) ? 'source_id' : $options['source_id'];

        $diff = [
            'add' => [],
            'update' => [],
            'delete' => [],
        ];

        if (empty($item)) {
            return $diff;
        }

        if (is_null($item[$source])) {
            $conditions[$source . ' IS'] = $item[$source];
        } else {
            $conditions[$source] = $item[$source];
        }

        $conditions[$sourceId] = $item[$sourceId];

        $query = $table->find()
                ->where($conditions);

        $query->all();
        $dbItems = $query->toArray();

        $toAdd = $this->itemsToAdd($item, $dbItems, $sourceId);
        if (!empty($toAdd)) {
            $diff['add'][] = $toAdd;
        }

        $toUpdate = $this->itemsToUpdate($item, $dbItems, $sourceId);
        if (!empty($toUpdate)) {
            $diff['update'][] = $toUpdate;
        }

        return $diff;
    }

    /**
     * Get Attendee difference
     *
     * @param ORM\Table $table instance of attendees
     * @param array $item of the record
     * @param array $options for extra fields/conditions
     *
     * @return array $diff with sorted differences for the item.
     */
    public function getAttendeeDifferences($table, $item = null, $options = [])
    {
        $conditions = [];
        $sourceId = empty($options['source_id']) ? 'source_id' : $options['source_id'];

        $diff = [
            'add' => [],
            'update' => [],
            'delete' => [],
        ];

        if (empty($item)) {
            return $diff;
        }

        $conditions[$sourceId] = $item[$sourceId];

        $query = $table->find()
                ->where($conditions);

        $query->all();
        $dbItems = $query->toArray();

        $toAdd = $this->itemsToAdd($item, $dbItems, $sourceId);
        if (!empty($toAdd)) {
            $diff['add'][] = $toAdd;
        }

        $toUpdate = $this->itemsToUpdate($item, $dbItems, $sourceId);
        if (!empty($toUpdate)) {
            $diff['update'][] = $toUpdate;
        }

        return $diff;
    }

    /**
     * Check if calendar should be added
     *
     * @param array $item to inspect for adding
     * @param array $dbItems to be compared with
     * @param string $fieldToCheck lookup field name
     *
     * @return array $result with the comparison result.
     */
    public function itemsToAdd($item, $dbItems = [], $fieldToCheck = null)
    {
        $result = $item;

        if (empty($dbItems)) {
            return $result;
        }

        foreach ($dbItems as $k => $dbItem) {
            if ($dbItem->$fieldToCheck == $item[$fieldToCheck]) {
                $result = [];
                break;
            }
        }

        return $result;
    }

    /**
     * Check if the calendar should be updated
     *
     * @param array $item to be checked for update
     * @param array $dbItems to be checked against
     * @param string $fieldToCheck lookup field name
     *
     * @return array $result containing comparison result
     */
    public function itemsToUpdate($item, $dbItems = [], $fieldToCheck = null)
    {
        $found = null;
        $result = [
            'entity' => [],
            'data' => []
        ];

        if (empty($dbItems)) {
            return $result;
        }

        foreach ($dbItems as $dbItem) {
            if ($dbItem->$fieldToCheck == $item[$fieldToCheck]) {
                $found = $dbItem;
            }
        }

        if (empty($found)) {
            return $result;
        }

        $result['entity'] = $found;
        $result['data'] = $item;

        return $result;
    }

    /**
     * Remove item from from the set
     *
     * @param ORM\Table $table instance of the target
     * @param array $items containing current items
     * @param array $options with extra config
     *
     * @return array $result containing the items that should be deleted.
     */
    public function itemsToDelete($table, $items, $options = [])
    {
        $result = $conditions = [];
        $source = empty($options['source']) ? 'source' : $options['source'];
        $sourceId = empty($options['source_id']) ? 'source_id' : $options['source_id'];

        if (!empty($options['period'])) {
            if (!empty($options['period']['start_date'])) {
                $conditions['start_date >='] = $options['period']['start_date'];
            }

            if (!empty($options['period']['end_date'])) {
                $conditions['end_date <='] = $options['period']['end_date'];
            }
        }

        if (!empty($options['extra_fields']['calendar_id'])) {
            $conditions['calendar_id'] = $options['extra_fields']['calendar_id'];
        }

        $query = $table->find()
                    ->where($conditions);

        $query->all();
        $dbItems = $query->toArray();

        if (empty($dbItems) || empty($items)) {
            return $result;
        }

        foreach ($dbItems as $key => $dbItem) {
            foreach ($items as $k => $item) {
                if ($dbItem->$source == $item->$source
                    && $dbItem->$sourceId == $item->$sourceId) {
                    unset($dbItems[$key]);
                }
            }
        }

        if (!empty($dbItems)) {
            $result = $dbItems;
        }

        return $result;
    }
}
