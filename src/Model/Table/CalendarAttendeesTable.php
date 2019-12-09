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

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException;

/**
 * CalendarAttendees Model
 *
 * @property \Qobo\Calendar\Model\Table\CalendarEventsTable|\Cake\ORM\Association\BelongsTo $CalendarEvents
 * @property \Qobo\Calendar\Model\Table\SourcesTable|\Cake\ORM\Association\BelongsTo $Sources
 *
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee get($primaryKey, $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee newEntity($data = null, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee[] newEntities(array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee[] patchEntities($entities, array $data, array $options = [])
 * @method \Qobo\Calendar\Model\Entity\CalendarAttendee findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CalendarAttendeesTable extends Table
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

        $this->setTable('calendar_attendees');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');

        $this->belongsToMany('CalendarEvents', [
            'joinTable' => 'events_attendees',
            'foreignKey' => 'calendar_attendee_id',
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
            ->dateTime('trashed')
            ->allowEmpty('trashed');

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
        $rules->add($rules->existsIn(['calendar_event_id'], 'CalendarEvents'));

        return $rules;
    }

    /**
     * Save Calendar Attendees
     *
     * @param mixed[] $entity of the attendee
     * @return mixed[] $response containing save state and saved record
     */
    public function saveAttendee(array $entity): array
    {
        $response = [
            'status' => false,
            'errors' => [],
            'entity' => null,
        ];

        if (empty($entity['id'])) {
            unset($entity['id']);
        }

        $query = $this->find()
            ->where([
                'source' => $entity['source'],
                'source_id' => $entity['source_id'],
                'contact_details' => $entity['contact_details'],
            ]);

        $query->execute();

        if (!$query->count()) {
            $item = $this->newEntity();
            $item = $this->patchEntity($item, $entity);
        } else {
            $item = $query->firstOrFail();
            if (!($item instanceof EntityInterface)) {
                throw new RuntimeException('Expected instance of EntityInterface');
            }
            $item = $this->patchEntity($item, $entity);
        }

        $saved = $this->save($item);

        if ($saved) {
            $response['status'] = true;
            $response['entity'] = $saved;
        } else {
            $response['errors'] = $item->getErrors();
        }

        return $response;
    }
}
