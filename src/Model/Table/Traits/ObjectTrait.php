<?php
namespace Qobo\Calendar\Model\Table\Traits;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Qobo\Calendar\Object\Objects\Event as EventObject;
use \ArrayObject;

trait ObjectTrait
{

    protected $defaultCalendar;

    /**
     * Get Object Type instance
     *
     * Return prepopulated Object instance for being later saved in the db.
     *
     * @param \Cake\Datasource\EntityInterface $entity to be converted
     * @param \stdClass $map containing ORM\Entity translation to Object instance
     * @param \ArrayObject $options with passed from the app
     *
     * @return \Qobo\Calendar\Object\ObjectInterface $object
     */
    public function getObjectInstance(EntityInterface $entity, $map, ArrayObject $options)
    {
        $object = null;

        $object = new EventObject();
        foreach ($map as $field => $config) {
            $method = Inflector::variable('set ' . Inflector::variable($field));

            if ('field' == $config->type) {
                $object->$method($entity->{$config->value});
            }

            if ('value' == $config->type) {
                $object->$method($config->value);
            }

            if ('callback' == $config->type && method_exists($this, $config->value)) {
                $calleeResult = $this->{$config->value}($entity, $options, $map);

                $object->$method($calleeResult);
            }
        }

        return $object;
    }

    /**
     * Get Calendar ID.
     *
     * @param \Cake\Datasource\EntityInterface $entity of the received record
     * @param \ArrayObject $options passed from the event
     *
     * @return string|null $calendarId of the record
     */
    public function getCalendarId(EntityInterface $entity, ArrayObject $options, $map = null)
    {
        $calendarId = null;

        if (empty($options['calendar'])) {
            return $calendarId;
        }

        $calendarId = $options['calendar']->id;

        return $calendarId;
    }
}
