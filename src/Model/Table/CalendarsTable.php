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
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Qobo\Calendar\Event\EventName;
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

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
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (empty($entity->get('source'))) {
            $entity->set('source', 'Plugin__');
        }

        // Default calendar color in case none is given.
        if (empty($entity->get('color'))) {
            $entity->set('color', $this->getColor($entity));
        }

        /** @var \Qobo\Calendar\Model\Table\CalendarEventsTable $calendarEventsTable */
        $calendarEventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $default = $calendarEventsTable->getEventTypeBy('default');
        $defaultKey = key($default);
        if (!empty($entity->get('event_types'))) {
            $types = json_decode($entity->get('event_types'), true);

            if (!in_array($defaultKey, $types)) {
                array_push($types, $defaultKey);
                asort($types);
                $entity->set('event_types', json_encode($types));
            }
        } else {
            $entity->set('event_types', json_encode([$defaultKey]));
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
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (empty($entity->get('source_id'))) {
            $entity->set('source_id', $entity->get('id'));
            $this->save($entity);
        }
    }

    /**
     * Get Calendar entities.
     *
     * @param mixed[] $options for filtering calendars
     *
     * @return mixed[] $result containing calendar entities with event_types
     */
    public function getCalendars(array $options = []): array
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

        foreach ($result as $item) {
            $item->event_types = $this->getEventTypes($item->event_types);
        }

        return $result;
    }

    /**
     * Get Default calendar color.
     *
     * @param \Cake\Datasource\EntityInterface $entity of the current calendar
     * @return string $color containing hexadecimal color notation.
     */
    public function getColor(?EntityInterface $entity = null): string
    {
        $color = Configure::read('Calendar.Configs.color');

        if (!empty($entity->get('color'))) {
            $color = $entity->get('color');
        }

        if (!$color) {
            $color = '#337ab7';
        }

        return $color;
    }

    /**
     * Synchronize calendars
     *
     * @param mixed[] $options passed from the outside.
     *
     * @return mixed[] $result of the synchronize method.
     */
    public function sync(array $options = []): array
    {
        $result = [];
        $event = new Event((string)EventName::PLUGIN_CALENDAR_MODEL_GET_CALENDARS(), $this, [
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
        }

        return $result;
    }

    /**
     * Get the list of Calendar instances
     *
     * Getting the list of calendars where following module is listed
     * in event_types field. For instance: Users::birthdays.
     *
     * @param string $tableName of the app's module
     * @param mixed[] $options with extra data
     *
     * @return mixed[] $result with calendar instances
     */
    public function getByAllowedEventTypes(?string $tableName = null, array $options = []): array
    {
        $result = [];
        $query = $this->find();
        $query->execute();
        $query->all();

        if (!$query->count()) {
            return $result;
        }

        $resultSet = $query->all();

        foreach ($resultSet as $calendar) {
            if (empty($calendar->event_types)) {
                continue;
            }

            $event_types = json_decode($calendar->event_types, true);

            $found = array_filter($event_types, function ($item) use ($tableName) {
                if (preg_match("/$tableName::/", $item, $matches)) {
                    return $item;
                }
            });

            if (!empty($found)) {
                $result[] = $calendar;
            }
        }

        return $result;
    }

    /**
     * Save Calendar Entity
     *
     * @param mixed[] $calendar data to be saved
     * @param mixed[] $options in case any extras required for conditions
     *
     * @return mixed[] $response containing the state of save operation
     */
    public function saveCalendarEntity(array $calendar = [], array $options = []): array
    {
        $response = [
            'errors' => [],
            'status' => false,
            'entity' => null,
        ];

        $query = $this->find()
            ->where($options['conditions']);

        $query->execute();

        if (!$query->count()) {
            $entity = $this->newEntity();
            $entity = $this->patchEntity($entity, $calendar);
        } else {
            /** @var \Cake\Datasource\EntityInterface $calEntity */
            $calEntity = $query->firstOrFail();
            $entity = $this->patchEntity($calEntity, $calendar);
        }

        $saved = $this->save($entity);

        if ($saved) {
            $response['status'] = true;
            $response['entity'] = $saved;
        } else {
            $response['errors'] = $entity->getErrors();
        }

        return $response;
    }

    /**
     * Get Event Types saved within Calendar
     *
     * @param string $data of the event type
     * @return mixed[] $result with event types decoded.
     */
    protected function getEventTypes(string $data): array
    {
        $result = [];

        if (empty($data)) {
            return $result;
        }

        $result = json_decode($data, true);

        return $result;
    }
}
