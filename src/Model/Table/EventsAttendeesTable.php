<?php
namespace Qobo\Calendar\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventsAttendees Model
 *
 * @property \Qobo\Calendar\Model\Table\CalendarEventsTable|\Cake\ORM\Association\BelongsTo $CalendarEvents
 * @property \Qobo\Calendar\Model\Table\CalendarAttendeesTable|\Cake\ORM\Association\BelongsTo $CalendarAttendees
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventsAttendeesTable extends Table
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

        $this->setTable('events_attendees');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Muffin/Trash.Trash');

        $this->belongsTo('CalendarEvents', [
            'foreignKey' => 'calendar_event_id',
            'joinType' => 'INNER',
            'className' => 'Qobo/Calendar.CalendarEvents'
        ]);
        $this->belongsTo('CalendarAttendees', [
            'foreignKey' => 'calendar_attendee_id',
            'joinType' => 'INNER',
            'className' => 'Qobo/Calendar.CalendarAttendees'
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
            ->dateTime('trashed')
            ->allowEmpty('trashed');

        $validator
            ->scalar('response_status')
            ->maxLength('response_status', 255)
            ->allowEmpty('response_status');

        return $validator;
    }
}
