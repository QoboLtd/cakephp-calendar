<?php
namespace Qobo\Calendar\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

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
            ->allowEmpty('event_source');

        $validator
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->requirePresence('content', 'create')
            ->notEmpty('content');

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
}
