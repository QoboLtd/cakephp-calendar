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
            $entity->color = $this->getColor($entity);
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
    public function getCalendars(array $options = [])
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

        // @TODO: fix event types being merged properly within calendar.
        //adding event_types & events attached for the calendars
        foreach ($result as $k => $calendar) {
        //    $result[$k]->event_types = $this->CalendarEvents->getEventTypes($calendar);
        }

        return $result;
    }

    /**
     * Get Default calendar color.
     *
     * @param \Cake\ORM\Entity $entity of the current calendar
     * @return string $color containing hexadecimal color notation.
     */
    public function getColor($entity = null)
    {
        $color = Configure::read('Calendar.Configs.color');

        if (!$color) {
            $color = '#337ab7';
        }

        return $color;
    }

    /**
     * Get Calendar Types
     *
     * @param array $options with extra filters
     *
     * @return array $result containing calendar types.
     */
    public function getTypes($options = [])
    {
        $result = [];

        $config = Configure::read('Calendar.Types');

        if (empty($config)) {
            return $result;
        }

        $result = Hash::combine($config, '{n}.value', '{n}.name');

        return $result;
    }

    /**
     * Synchronize calendars
     *
     * @param array $options passed from the outside.
     *
     * @return array $result of the synchronize method.
     */
    public function sync(array $options = [])
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
     * @param array $options with extra data
     *
     * @return array $result with calendar instances
     */
    public function getByAllowedEventTypes($tableName = null, array $options = [])
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
}
