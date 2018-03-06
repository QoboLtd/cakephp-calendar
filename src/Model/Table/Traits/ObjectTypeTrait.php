<?php
namespace Qobo\Calendar\Model\Table\Traits;

use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;
use Qobo\Calendar\ObjectType\Event as EventObject;
use \ArrayObject;

trait ObjectTypeTrait
{

    protected $defaultCalendar;

    /**
     * Get Object Type instance
     *
     * Return prepopulated ObjectType instance for being later saved in the db.
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \stdClass $map containing ORM\Entity translation to ObjectType instance
     * @param \ArrayObject $options with passed from the app
     *
     * @return \Qobo\Calendar\ObjectType\ObjectTypeInterface $object
     */
    public function getObjectTypeInstance(EntityInterface $entity, $map, ArrayObject $options)
    {
        $object = null;

        $object = new EventObject();

        foreach ($map->properties as $field => $config) {
            $method = Inflector::variable('set ' . Inflector::variable($field));

            if ('field' == $config->type) {
                $object->$method($entity->{$config->value});
            }

            if ('value' == $config->type) {
                $object->$method($config->value);
            }

            if ('callback' == $config->type && method_exists($this, $config->value)) {
                $calleeResult = $this->{$config->value}($entity, $options);

                $object->$method($calleeResult);
            }
        }

        return $object;
    }

    public function getDefaultCalendar(EntityInterface $entity, ArrayObject $options)
    {
        $result = null;
        $tableName = $this->alias();
        $table = TableRegistry::get('Qobo/Calendar.Calendars');

        $source = 'App__' . $tableName . '__DEFAULT_CALENDAR';

        $query = $table->find();
        $query->where(['source' => $source]);

        $query->execute();

        if (!$query->count()) {
            $calendar = $table->newEntity();
            $calendar->name = Inflector::humanize($tableName);
            $calendar->source = $source;
            $calendar->calendar_type = 'default';
            $calendar->color = '#337ab7';

            $saved = $table->save($calendar);
            $result = $saved;
        } else {
            $result = $query->first();
        }

        return $result;
    }

    /**
     * Get Calendar ID.
     */
    public function getCalendarId(EntityInterface $entity, ArrayObject $options)
    {
        $calendarId = null;

        if (!$this->defaultCalendar) {
            $this->defaultCalendar = $this->getDefaultCalendar($entity, $options);
        }

        $calendarId = $this->defaultCalendar->id;

        return $calendarId;
    }

}
